<?php

namespace Kanboard\Plugin\NotifyMe\Action;

use Kanboard\Core\Base;

class NotifyMeAction extends Base
{
    private $eventMap = array(
        'task_created' => array('creator_id'),
        'task_updated' => array('creator_id', 'owner_id'),
        'task_assigned' => array('owner_id'),
        'task_closed' => array('creator_id'),
        'task_opened' => array('creator_id'),
        'task_moved_column' => array('creator_id'),
        'task_moved_swimlane' => array('creator_id'),
    );

    public function onTaskCreate(array $data)
    {
        return $this->handleTaskEvent($data, 'task_created');
    }

    public function onTaskUpdate(array $data)
    {
        return $this->handleTaskEvent($data, 'task_updated');
    }

    public function onTaskAssigneeChange(array $data)
    {
        return $this->handleTaskEvent($data, 'task_assigned');
    }

    public function onTaskClose(array $data)
    {
        return $this->handleTaskEvent($data, 'task_closed');
    }

    public function onTaskOpen(array $data)
    {
        return $this->handleTaskEvent($data, 'task_opened');
    }

    public function onTaskMoveColumn(array $data)
    {
        return $this->handleTaskEvent($data, 'task_moved_column');
    }

    public function onTaskMoveSwimlane(array $data)
    {
        return $this->handleTaskEvent($data, 'task_moved_swimlane');
    }

    public function onSubtaskCreate(array $data)
    {
        return $this->handleSubtaskEvent($data, 'subtask_created');
    }

    public function onSubtaskUpdate(array $data)
    {
        return $this->handleSubtaskEvent($data, 'subtask_updated');
    }

    public function onSubtaskDelete(array $data)
    {
        return $this->handleSubtaskEvent($data, 'subtask_deleted');
    }

    public function onCommentCreate(array $data)
    {
        return $this->handleCommentEvent($data, 'comment_created');
    }

    public function onCommentUpdate(array $data)
    {
        return $this->handleCommentEvent($data, 'comment_updated');
    }

    public function onCommentDelete(array $data)
    {
        return $this->handleCommentEvent($data, 'comment_deleted');
    }

    public function onFileAttached(array $data)
    {
        return $this->handleTaskRelatedEvent($data, 'file_attached', 'file');
    }

    public function onFileDeleted(array $data)
    {
        return $this->handleTaskRelatedEvent($data, 'file_deleted', 'file');
    }

    public function onTimeTrackingCreated(array $data)
    {
        return $this->handleTaskRelatedEvent($data, 'time_tracking_created', 'timetracking');
    }

    public function onTimeTrackingDeleted(array $data)
    {
        return $this->handleTaskRelatedEvent($data, 'time_tracking_deleted', 'timetracking');
    }

    public function onProjectCreated(array $data)
    {
        return $this->handleProjectEvent($data, 'project_created');
    }

    public function onProjectRemoved(array $data)
    {
        return $this->handleProjectEvent($data, 'project_removed');
    }

    public function onCategoryAdded(array $data)
    {
        return $this->handleTaskRelatedEvent($data, 'category_added', 'category');
    }

    public function onCategoryRemoved(array $data)
    {
        return $this->handleTaskRelatedEvent($data, 'category_removed', 'category');
    }

    private function handleTaskEvent(array $data, $action)
    {
        if (empty($data['task_id'])) {
            return false;
        }

        $task = $this->taskModel->getById($data['task_id']);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->getById($task['project_id']);
        if (!$project) {
            return false;
        }

        $userIds = $this->eventMap[$action];
        $result = true;

        foreach ($userIds as $userIdField) {
            if (!empty($task[$userIdField])) {
                $result = $this->notifyUser($task, $project, $task[$userIdField], $action, 'task', null) && $result;
            }
        }

        if ($action === 'task_updated' && !empty($data['user_id'])) {
            $result = $this->notifyUser($task, $project, $data['user_id'], $action, 'task', null) && $result;
        }

        return $result;
    }

    private function handleSubtaskEvent(array $data, $action)
    {
        $subtaskId = $data['subtask_id'] ?? null;
        $taskId = $data['task_id'] ?? null;

        if (!$subtaskId || !$taskId) {
            return false;
        }

        $subtask = ($action === 'subtask_deleted') ? array('id' => $subtaskId) : $this->subtaskModel->getById($subtaskId);
        if (!$subtask || !$subtask['task_id']) {
            return false;
        }

        $task = $this->taskModel->getById($subtask['task_id']);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->getById($task['project_id']);
        if (!$project) {
            return false;
        }

        $result = true;
        if (isset($task['creator_id'])) {
            $result = $this->notifyUser($task, $project, $task['creator_id'], $action, 'subtask', $subtask) && $result;
        }
        if (!empty($task['owner_id'])) {
            $result = $this->notifyUser($task, $project, $task['owner_id'], $action, 'subtask', $subtask) && $result;
        }

        return $result;
    }

    private function handleCommentEvent(array $data, $action)
    {
        $commentId = $data['comment_id'] ?? null;
        if (!$commentId) {
            return false;
        }

        if ($action === 'comment_deleted') {
            $comment = array('id' => $commentId, 'user_id' => $data['user_id'] ?? 0, 'text' => '');
            $taskId = $data['task_id'] ?? null;
        } else {
            $comment = $this->commentModel->getById($commentId);
            $taskId = $comment['task_id'] ?? null;
        }

        if (!$taskId) {
            return false;
        }

        $task = $this->taskModel->getById($taskId);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->getById($task['project_id']);
        if (!$project) {
            return false;
        }

        $commenterId = $comment['user_id'] ?? 0;
        $result = true;

        if (isset($task['creator_id'])) {
            $result = $this->notifyUser($task, $project, $task['creator_id'], $action, 'comment', $comment) && $result;
        }
        if (!empty($task['owner_id']) && $task['owner_id'] !== $task['creator_id']) {
            $result = $this->notifyUser($task, $project, $task['owner_id'], $action, 'comment', $comment) && $result;
        }
        if ($commenterId) {
            $result = $this->notifyUser($task, $project, $commenterId, $action, 'comment', $comment) && $result;
        }

        return $result;
    }

    private function handleTaskRelatedEvent(array $data, $action, $type)
    {
        $taskId = $data['task_id'] ?? null;
        if (!$taskId) {
            return false;
        }

        $task = $this->taskModel->getById($taskId);
        if (!$task) {
            return false;
        }

        $project = $this->projectModel->getById($task['project_id']);
        if (!$project) {
            return false;
        }

        $result = true;
        if (isset($task['creator_id'])) {
            $result = $this->notifyUser($task, $project, $task['creator_id'], $action, $type, $data) && $result;
        }
        if (!empty($task['owner_id'])) {
            $result = $this->notifyUser($task, $project, $task['owner_id'], $action, $type, $data) && $result;
        }
        if (!empty($data['user_id'])) {
            $result = $this->notifyUser($task, $project, $data['user_id'], $action, $type, $data) && $result;
        }

        return $result;
    }

    private function handleProjectEvent(array $data, $action)
    {
        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return false;
        }

        $user = $this->userModel->getById($userId);
        if (!$user || empty($user['email'])) {
            return false;
        }

        $projectId = $data['project_id'] ?? null;
        $project = $projectId ? $this->projectModel->getById($projectId) : null;
        if (!$project) {
            $project = array('id' => $projectId, 'name' => $data['project_name'] ?? 'Unknown');
        }

        $subject = sprintf(t('[NotifyMe] Project: %s'), $project['name']);
        $html = $this->template->render('NotifyMe:notification/project_email', array(
            'user' => $user,
            'project' => $project,
            'action' => $action,
        ));

        return $this->sendEmail($user, $subject, $html, $action);
    }

    private function notifyUser(array $task, array $project, $userId, $action, $type, $context)
    {
        $user = $this->userModel->getById($userId);
        if (!$user || empty($user['email'])) {
            return false;
        }

        $subject = sprintf('[%s] %s (#%d)', $project['name'], $task['title'], $task['id']);
        $taskUrl = $this->helper->url->absoluteUrl('task', 'show', array(
            'project_id' => $task['project_id'],
            'task_id' => $task['id']
        ));

        $html = $this->template->render('NotifyMe:notification/' . $type . '_email', array(
            'user' => $user,
            'task' => $task,
            'project' => $project,
            'action' => $action,
            'context' => $context,
            'task_url' => $taskUrl,
        ));

        return $this->sendEmail($user, $subject, $html, $action);
    }

    private function sendEmail(array $user, $subject, $html, $action)
    {
        try {
            $this->emailClient->send($user['email'], $user['name'], $subject, $html);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('NotifyMe: Email send failed', array(
                'user_id' => $user['id'],
                'action' => $action,
                'error' => $e->getMessage()
            ));
            return false;
        }
    }
}
