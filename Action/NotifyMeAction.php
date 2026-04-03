<?php

namespace Kanboard\Plugin\NotifyMe\Action;

use Kanboard\Core\Base;

class NotifyMeAction extends Base
{
    /**
     * Whitelist of allowed template types to prevent path traversal.
     * Fixes: #2 template path injection.
     */
    private $allowedTypes = array(
        'task', 'subtask', 'comment', 'file', 'timetracking', 'category', 'project',
    );

    /**
     * Maps task-level actions to the user-ID fields that should be notified.
     */
    private $taskEventUserFields = array(
        'task_created'        => array('creator_id'),
        'task_updated'        => array('creator_id', 'owner_id'),
        'task_assigned'       => array('owner_id'),
        'task_closed'         => array('creator_id'),
        'task_opened'         => array('creator_id'),
        'task_moved_column'   => array('creator_id'),
        'task_moved_swimlane' => array('creator_id'),
    );

    // ---------------------------------------------------------------
    // Public hook entry points (kept as thin one-liners for the hook
    // system which requires concrete method names).
    // Fixes: #7 — collapsed to single-line delegators.
    // ---------------------------------------------------------------

    public function onTaskCreate(array $data)          { return $this->handleTaskEvent($data, 'task_created'); }
    public function onTaskUpdate(array $data)          { return $this->handleTaskEvent($data, 'task_updated'); }
    public function onTaskAssigneeChange(array $data)  { return $this->handleTaskEvent($data, 'task_assigned'); }
    public function onTaskClose(array $data)           { return $this->handleTaskEvent($data, 'task_closed'); }
    public function onTaskOpen(array $data)            { return $this->handleTaskEvent($data, 'task_opened'); }
    public function onTaskMoveColumn(array $data)      { return $this->handleTaskEvent($data, 'task_moved_column'); }
    public function onTaskMoveSwimlane(array $data)    { return $this->handleTaskEvent($data, 'task_moved_swimlane'); }
    public function onSubtaskCreate(array $data)       { return $this->handleSubtaskEvent($data, 'subtask_created'); }
    public function onSubtaskUpdate(array $data)       { return $this->handleSubtaskEvent($data, 'subtask_updated'); }
    public function onSubtaskDelete(array $data)       { return $this->handleSubtaskEvent($data, 'subtask_deleted'); }
    public function onCommentCreate(array $data)       { return $this->handleCommentEvent($data, 'comment_created'); }
    public function onCommentUpdate(array $data)       { return $this->handleCommentEvent($data, 'comment_updated'); }
    public function onCommentDelete(array $data)       { return $this->handleCommentEvent($data, 'comment_deleted'); }
    public function onFileAttached(array $data)        { return $this->handleTaskRelatedEvent($data, 'file_attached', 'file'); }
    public function onFileDeleted(array $data)         { return $this->handleTaskRelatedEvent($data, 'file_deleted', 'file'); }
    public function onTimeTrackingCreated(array $data) { return $this->handleTaskRelatedEvent($data, 'time_tracking_created', 'timetracking'); }
    public function onTimeTrackingDeleted(array $data) { return $this->handleTaskRelatedEvent($data, 'time_tracking_deleted', 'timetracking'); }
    public function onProjectCreated(array $data)      { return $this->handleProjectEvent($data, 'project_created'); }
    public function onProjectRemoved(array $data)      { return $this->handleProjectEvent($data, 'project_removed'); }
    public function onCategoryAdded(array $data)       { return $this->handleTaskRelatedEvent($data, 'category_added', 'category'); }
    public function onCategoryRemoved(array $data)     { return $this->handleTaskRelatedEvent($data, 'category_removed', 'category'); }

    // ---------------------------------------------------------------
    // Handlers
    // ---------------------------------------------------------------

    private function handleTaskEvent(array $data, $action)
    {
        $task = $this->resolveTask($data);
        if (!$task) {
            return false;
        }

        $project = $this->resolveProject($task['project_id']);
        if (!$project) {
            return false;
        }

        // Collect user IDs from the event map, plus the acting user for updates.
        // Fixes: #5 — deduplicate before sending.
        $userIds = array();
        foreach ($this->taskEventUserFields[$action] as $field) {
            if (!empty($task[$field])) {
                $userIds[] = (int) $task[$field];
            }
        }

        if ($action === 'task_updated' && !empty($data['user_id'])) {
            $userIds[] = (int) $data['user_id'];
        }

        return $this->notifyUsers(array_unique($userIds), $task, $project, $action, 'task', null);
    }

    private function handleSubtaskEvent(array $data, $action)
    {
        // Fixes: #10 — consistent use of isset + cast everywhere.
        $subtaskId = isset($data['subtask_id']) ? (int) $data['subtask_id'] : 0;
        $taskId    = isset($data['task_id'])    ? (int) $data['task_id']    : 0;

        if (!$subtaskId || !$taskId) {
            return false;
        }

        // Fixes: #11 — for deleted subtasks, build a safe phantom with
        // all keys downstream templates may access.
        if ($action === 'subtask_deleted') {
            $subtask = array('id' => $subtaskId, 'task_id' => $taskId, 'title' => '');
        } else {
            $subtask = $this->subtaskModel->getById($subtaskId);
            if (!$subtask || empty($subtask['task_id'])) {
                return false;
            }
        }

        $task = $this->resolveTask(array('task_id' => $subtask['task_id']));
        if (!$task) {
            return false;
        }

        $project = $this->resolveProject($task['project_id']);
        if (!$project) {
            return false;
        }

        $userIds = $this->collectTaskUserIds($task);

        return $this->notifyUsers($userIds, $task, $project, $action, 'subtask', $subtask);
    }

    private function handleCommentEvent(array $data, $action)
    {
        $commentId = isset($data['comment_id']) ? (int) $data['comment_id'] : 0;
        if (!$commentId) {
            return false;
        }

        // Fixes: #11 — safe phantom for deleted comments.
        if ($action === 'comment_deleted') {
            $comment = array(
                'id'      => $commentId,
                'user_id' => isset($data['user_id']) ? (int) $data['user_id'] : 0,
                'text'    => '',
                'task_id' => isset($data['task_id']) ? (int) $data['task_id'] : 0,
            );
            $taskId = $comment['task_id'];
        } else {
            $comment = $this->commentModel->getById($commentId);
            $taskId  = isset($comment['task_id']) ? (int) $comment['task_id'] : 0;
        }

        if (!$taskId) {
            return false;
        }

        $task = $this->resolveTask(array('task_id' => $taskId));
        if (!$task) {
            return false;
        }

        $project = $this->resolveProject($task['project_id']);
        if (!$project) {
            return false;
        }

        // Fixes: #5 — deduplicate creator + owner + commenter.
        $userIds = $this->collectTaskUserIds($task);
        $commenterId = isset($comment['user_id']) ? (int) $comment['user_id'] : 0;
        if ($commenterId) {
            $userIds[] = $commenterId;
        }

        return $this->notifyUsers(array_unique($userIds), $task, $project, $action, 'comment', $comment);
    }

    private function handleTaskRelatedEvent(array $data, $action, $type)
    {
        $task = $this->resolveTask($data);
        if (!$task) {
            return false;
        }

        $project = $this->resolveProject($task['project_id']);
        if (!$project) {
            return false;
        }

        // Fixes: #5 — deduplicate creator + owner + acting user.
        $userIds = $this->collectTaskUserIds($task);
        if (!empty($data['user_id'])) {
            $userIds[] = (int) $data['user_id'];
        }

        return $this->notifyUsers(array_unique($userIds), $task, $project, $action, $type, $data);
    }

    private function handleProjectEvent(array $data, $action)
    {
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : 0;
        if (!$userId) {
            return false;
        }

        $user = $this->userModel->getById($userId);
        if (!$user || empty($user['email'])) {
            return false;
        }

        $projectId = isset($data['project_id']) ? (int) $data['project_id'] : 0;
        $project   = $projectId ? $this->resolveProject($projectId) : null;
        if (!$project) {
            $project = array(
                'id'   => $projectId,
                'name' => isset($data['project_name']) ? $data['project_name'] : 'Unknown',
            );
        }

        // Fixes: #1 — escape project name in email subject.
        $subject = sprintf(
            t('[NotifyMe] Project: %s'),
            htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8')
        );

        $html = $this->renderTemplate('project', array(
            'user'    => $user,
            'project' => $project,
            'action'  => $action,
        ));

        if ($html === false) {
            return false;
        }

        return $this->sendEmail($user, $subject, $html, $action);
    }

    // ---------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------

    /**
     * Resolve a task from event data, returning the full task array or null.
     * Fixes: #4 — single extraction point; callers don't duplicate lookups.
     */
    private function resolveTask(array $data)
    {
        $taskId = isset($data['task_id']) ? (int) $data['task_id'] : 0;
        if (!$taskId) {
            return null;
        }

        return $this->taskModel->getById($taskId) ?: null;
    }

    /**
     * Resolve a project by ID, returning the full project array or null.
     * Fixes: #4 — single extraction point.
     */
    private function resolveProject($projectId)
    {
        $projectId = (int) $projectId;
        if (!$projectId) {
            return null;
        }

        return $this->projectModel->getById($projectId) ?: null;
    }

    /**
     * Collect deduplicated user IDs from a task's creator and owner.
     * Fixes: #5 — central dedup, #10 — consistent empty() checks.
     */
    private function collectTaskUserIds(array $task)
    {
        $ids = array();

        if (!empty($task['creator_id'])) {
            $ids[] = (int) $task['creator_id'];
        }
        if (!empty($task['owner_id'])) {
            $ids[] = (int) $task['owner_id'];
        }

        return array_unique($ids);
    }

    /**
     * Send notifications to a deduplicated set of user IDs.
     * Fixes: #5 — single send per user, #9 — verifies project membership.
     */
    private function notifyUsers(array $userIds, array $task, array $project, $action, $type, $context)
    {
        $result = true;

        // Fixes: #9 — pre-fetch project member IDs for authorization.
        // Falls back to allowing all if the permission model is unavailable.
        $projectUserIds = array();
        if (isset($this->projectPermissionModel)) {
            $projectUserIds = $this->projectPermissionModel->getActiveUserIds($project['id']);
        }

        foreach ($userIds as $userId) {
            // Fixes: #9 — skip users who no longer have access to the project.
            if (!empty($projectUserIds) && !in_array($userId, $projectUserIds, true)) {
                continue;
            }

            $user = $this->userModel->getById($userId);
            if (!$user || empty($user['email'])) {
                continue;
            }

            // Fixes: #1 — escape dynamic values in email subject line.
            $subject = sprintf(
                '[%s] %s (#%d)',
                htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'),
                (int) $task['id']
            );

            $taskUrl = $this->helper->url->absoluteUrl('task', 'show', array(
                'project_id' => $task['project_id'],
                'task_id'    => $task['id'],
            ));

            $html = $this->renderTemplate($type, array(
                'user'     => $user,
                'task'     => $task,
                'project'  => $project,
                'action'   => $action,
                'context'  => $context,
                'task_url' => $taskUrl,
            ));

            if ($html === false) {
                $result = false;
                continue;
            }

            $result = $this->sendEmail($user, $subject, $html, $action) && $result;
        }

        return $result;
    }

    /**
     * Render a notification template by type, with whitelist validation.
     * Fixes: #2 — rejects any type not in $this->allowedTypes.
     *
     * @return string|false  Rendered HTML, or false on invalid type.
     */
    private function renderTemplate($type, array $vars)
    {
        if (!in_array($type, $this->allowedTypes, true)) {
            $this->logger->error('NotifyMe: Invalid template type requested', array(
                'type' => $type,
            ));
            return false;
        }

        return $this->template->render('NotifyMe:notification/' . $type . '_email', $vars);
    }

    /**
     * Send an email, catching transport errors.
     */
    private function sendEmail(array $user, $subject, $html, $action)
    {
        try {
            $this->emailClient->send(
                $user['email'],
                $user['name'],
                $subject,
                $html
            );
            return true;
        } catch (\Exception $e) {
            $this->logger->error('NotifyMe: Email send failed', array(
                'user_id' => $user['id'],
                'action'  => $action,
                'error'   => $e->getMessage(),
            ));
            return false;
        }
    }
}
