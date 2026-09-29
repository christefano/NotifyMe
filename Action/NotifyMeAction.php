<?php

namespace Kanboard\Plugin\NotifyMe\Action;

use Kanboard\Core\Base;
use Kanboard\Event\AuthFailureEvent;
use Kanboard\Event\GenericEvent;
use Kanboard\Model\TaskLinkModel;

class NotifyMeAction extends Base
{
    /**
     * Whitelist of allowed template types to prevent path traversal.
     */
    private $allowedTypes = array(
        'task', 'subtask', 'comment', 'file', 'task_link', 'project_file', 'auth_failure', 'wiki',
    );

    /**
     * Maps event names to translation keys used as the notification's action label.
     */
    private $actionLabels = array(
        'task.create'                      => 'task_created',
        'task.update'                      => 'task_updated',
        'task.close'                       => 'task_closed',
        'task.open'                        => 'task_opened',
        'task.move.column'                 => 'task_moved_column',
        'task.move.swimlane'               => 'task_moved_swimlane',
        'task.assignee_change'             => 'task_assignee_changed',
        'subtask.create'                   => 'subtask_created',
        'subtask.update'                   => 'subtask_updated',
        'subtask.delete'                   => 'subtask_deleted',
        'comment.create'                   => 'comment_created',
        'comment.update'                   => 'comment_updated',
        'comment.delete'                   => 'comment_deleted',
        'task.file.create'                 => 'file_attached',
        'task.file.destroy'                => 'file_deleted',
        'task_internal_link.create_update' => 'task_link_updated',
        'task_internal_link.delete'        => 'task_link_deleted',
        'project.file.create'              => 'project_file_attached',
        'project.file.destroy'             => 'project_file_deleted',
        'auth.failure'                     => 'auth_failure',
        'wikipage.create'                  => 'wiki_page_created',
        'wikipage.update'                  => 'wiki_page_updated',
        'wikipage.delete'                  => 'wiki_page_deleted',
    );

    // ---------------------------------------------------------------
    // Public hook entry points (thin one-liners; the dispatcher requires
    // concrete method names per registration).
    // ---------------------------------------------------------------

    public function onTaskCreate(GenericEvent $event, $eventName)         { return $this->handleTaskEvent($event, $eventName); }
    public function onTaskUpdate(GenericEvent $event, $eventName)         { return $this->handleTaskEvent($event, $eventName); }
    public function onTaskClose(GenericEvent $event, $eventName)          { return $this->handleTaskEvent($event, $eventName); }
    public function onTaskOpen(GenericEvent $event, $eventName)           { return $this->handleTaskEvent($event, $eventName); }
    public function onTaskMoveColumn(GenericEvent $event, $eventName)     { return $this->handleTaskEvent($event, $eventName); }
    public function onTaskMoveSwimlane(GenericEvent $event, $eventName)   { return $this->handleTaskEvent($event, $eventName); }
    public function onTaskAssigneeChange(GenericEvent $event, $eventName) { return $this->handleTaskEvent($event, $eventName); }
    public function onSubtaskCreate(GenericEvent $event, $eventName)      { return $this->handleSubtaskEvent($event, $eventName); }
    public function onSubtaskUpdate(GenericEvent $event, $eventName)      { return $this->handleSubtaskEvent($event, $eventName); }
    public function onSubtaskDelete(GenericEvent $event, $eventName)      { return $this->handleSubtaskEvent($event, $eventName); }
    public function onCommentCreate(GenericEvent $event, $eventName)      { return $this->handleCommentEvent($event, $eventName); }
    public function onCommentUpdate(GenericEvent $event, $eventName)      { return $this->handleCommentEvent($event, $eventName); }
    public function onCommentDelete(GenericEvent $event, $eventName)      { return $this->handleCommentEvent($event, $eventName); }
    public function onTaskFileCreate(GenericEvent $event, $eventName)     { return $this->handleTaskFileEvent($event, $eventName); }
    public function onTaskFileDestroy(GenericEvent $event, $eventName)    { return $this->handleTaskFileEvent($event, $eventName); }
    public function onTaskLinkCreateUpdate(GenericEvent $event, $eventName) { return $this->handleTaskLinkEvent($event, $eventName); }
    public function onTaskLinkDelete(GenericEvent $event, $eventName)     { return $this->handleTaskLinkEvent($event, $eventName); }
    public function onProjectFileCreate(GenericEvent $event, $eventName)  { return $this->handleProjectFileEvent($event, $eventName); }
    public function onProjectFileDestroy(GenericEvent $event, $eventName) { return $this->handleProjectFileEvent($event, $eventName); }
    public function onAuthFailure(AuthFailureEvent $event, $eventName)    { return $this->handleAuthFailureEvent($event, $eventName); }
    public function onWikiPageCreate(GenericEvent $event, $eventName)     { return $this->handleWikiEvent($event, $eventName); }
    public function onWikiPageUpdate(GenericEvent $event, $eventName)     { return $this->handleWikiEvent($event, $eventName); }
    public function onWikiPageDelete(GenericEvent $event, $eventName)     { return $this->handleWikiEvent($event, $eventName); }

    // ---------------------------------------------------------------
    // Handlers
    // ---------------------------------------------------------------

    private function handleTaskEvent(GenericEvent $event, $eventName)
    {
        $task = $event['task'];
        if (empty($task)) {
            return false;
        }

        $projectId = (int) $task['project_id'];
        $project   = $this->resolveProject($projectId);
        if (!$project) {
            return false;
        }

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'task',
            $eventName,
            $this->buildSubject($project['name'], $task['title'], $task['id']),
            array(
                'task'     => $task,
                'project'  => $project,
                'action'   => $this->actionLabel($eventName),
                'task_url' => $this->buildTaskUrl($projectId, $task['id']),
            ),
            $event->getAll()
        );
    }

    private function handleSubtaskEvent(GenericEvent $event, $eventName)
    {
        $subtask = $event['subtask'];
        $task    = $event['task'];
        if (empty($subtask) || empty($task)) {
            return false;
        }

        $projectId = (int) $task['project_id'];
        $project   = $this->resolveProject($projectId);
        if (!$project) {
            return false;
        }

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'subtask',
            $eventName,
            $this->buildSubject($project['name'], $task['title'], $task['id']),
            array(
                'task'     => $task,
                'project'  => $project,
                'action'   => $this->actionLabel($eventName),
                'task_url' => $this->buildTaskUrl($projectId, $task['id']),
                'context'  => $subtask,
            ),
            $event->getAll()
        );
    }

    private function handleCommentEvent(GenericEvent $event, $eventName)
    {
        $comment = $event['comment'];
        $task    = $event['task'];
        if (empty($comment) || empty($task)) {
            return false;
        }

        $projectId = (int) $task['project_id'];
        $project   = $this->resolveProject($projectId);
        if (!$project) {
            return false;
        }

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'comment',
            $eventName,
            $this->buildSubject($project['name'], $task['title'], $task['id']),
            array(
                'task'     => $task,
                'project'  => $project,
                'action'   => $this->actionLabel($eventName),
                'task_url' => $this->buildTaskUrl($projectId, $task['id']),
                'context'  => $comment,
            ),
            $event->getAll()
        );
    }

    private function handleTaskFileEvent(GenericEvent $event, $eventName)
    {
        $file = $event['file'];
        $task = $event['task'];
        if (empty($file) || empty($task)) {
            return false;
        }

        $projectId = (int) $task['project_id'];
        $project   = $this->resolveProject($projectId);
        if (!$project) {
            return false;
        }

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'file',
            $eventName,
            $this->buildSubject($project['name'], $task['title'], $task['id']),
            array(
                'task'     => $task,
                'project'  => $project,
                'action'   => $this->actionLabel($eventName),
                'task_url' => $this->buildTaskUrl($projectId, $task['id']),
                'context'  => $file,
            ),
            $event->getAll()
        );
    }

    private function handleTaskLinkEvent(GenericEvent $event, $eventName)
    {
        $taskLink = $event['task_link'];
        $task     = $event['task'];
        if (empty($taskLink) || empty($task)) {
            return false;
        }

        // TaskLinkModel::create()/update() dispatch this event once per side
        // of the link (A-to-B, then B-to-A). Handle exactly one side so the
        // actor gets one notification per action instead of two; the email
        // includes both tasks either way.
        if ($eventName === TaskLinkModel::EVENT_CREATE_UPDATE
            && (int) $taskLink['task_id'] > (int) $taskLink['opposite_task_id']
        ) {
            return false;
        }

        $projectId = (int) $task['project_id'];
        $project   = $this->resolveProject($projectId);
        if (!$project) {
            return false;
        }

        $oppositeTask = !empty($taskLink['opposite_task_id'])
            ? $this->taskModel->getById($taskLink['opposite_task_id'])
            : null;

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'task_link',
            $eventName,
            $this->buildSubject($project['name'], $task['title'], $task['id']),
            array(
                'task'          => $task,
                'project'       => $project,
                'action'        => $this->actionLabel($eventName),
                'task_url'      => $this->buildTaskUrl($projectId, $task['id']),
                'context'       => $taskLink,
                'opposite_task' => $oppositeTask ?: null,
            ),
            $event->getAll()
        );
    }

    /**
     * project.file.* has no web-notification render path in core:
     * NotificationModel::getIteratorBuilder() wires no builder for it, and
     * WebNotificationController::redirect() always targets TaskViewController.
     * A web entry here would only ever show "Notification" linking to task 0,
     * so this event is email-only.
     */
    private function handleProjectFileEvent(GenericEvent $event, $eventName)
    {
        $file    = $event['file'];
        $project = $event['project'];
        if (empty($file) || empty($project)) {
            return false;
        }

        $projectId = (int) $project['id'];

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'project_file',
            $eventName,
            sprintf('[%s] %s', $project['name'], $file['name']),
            array(
                'project' => $project,
                'action'  => $this->actionLabel($eventName),
                'context' => $file,
            ),
            null
        );
    }

    /**
     * auth.failure has no logged-in actor and no project context: look up the
     * attempted username and notify that account if it resolves to a real,
     * active user. A disabled account has nobody meaningfully protected by
     * being notified, so it's excluded. Repeated failures against one
     * username are bounded by core's own bruteforce lockout
     * (UserLockingModel, default 6 attempts / 15 minutes); this plugin
     * relies on that instead of reimplementing rate limiting.
     *
     * No web-notification entry: like project.file.* above, core has no
     * title builder or redirect target for auth.failure.
     */
    private function handleAuthFailureEvent(AuthFailureEvent $event, $eventName)
    {
        $username = $event->getUsername();
        if ($username === '') {
            return false;
        }

        $user = $this->userModel->getByUsername($username);
        if (empty($user) || empty($user['is_active'])) {
            return false;
        }

        if (empty($user['email'])) {
            return false;
        }

        $html = $this->renderTemplate('auth_failure', array(
            'user'     => $user,
            'username' => $username,
            'action'   => $this->actionLabel($eventName),
        ));

        if ($html === false) {
            return false;
        }

        $subject = sprintf('[NotifyMe] %s', t($this->actionLabel($eventName)));

        return $this->sendEmail($user, $subject, $html, $eventName);
    }

    /**
     * Third-party Wiki plugin (funktechno/kanboard-plugin-wiki) support, if
     * installed. Listens on literal event-name strings and not the plugin's
     * own model constants, so NotifyMe has no hard dependency on Wiki being
     * present; if it isn't, these events simply never fire.
     *
     * wikipage.create fires before the row is persisted, so $wiki has no
     * 'id' yet; the link falls back to the project's wiki index instead of
     * the page itself. wikipage.update fires with the pre-update row, so
     * title/content reflect the page's state before this edit, not what was
     * just saved; do not render $wiki['content'] here, and do not claim the
     * shown title is current.
     *
     * No web-notification path exists for these events, same reason as
     * project.file.* and auth.failure above: core's
     * NotificationModel::getIteratorBuilder() wires no builder for Wiki, so
     * this is email-only.
     */
    private function handleWikiEvent(GenericEvent $event, $eventName)
    {
        $wiki    = $event['wiki'];
        $project = $event['project'];
        if (empty($wiki) || empty($project)) {
            return false;
        }

        $projectId = (int) $project['id'];
        $wikiUrl   = !empty($wiki['id'])
            ? $this->helper->url->to('WikiController', 'detail', array(
                'project_id' => $projectId,
                'wiki_id'    => $wiki['id'],
            ), '', true)
            : $this->helper->url->to('WikiController', 'show', array(
                'project_id' => $projectId,
            ), '', true);

        return $this->deliver(
            $this->userSession->getId(),
            $projectId,
            'wiki',
            $eventName,
            sprintf('[%s] %s', $project['name'], $wiki['title']),
            array(
                'wiki'     => $wiki,
                'project'  => $project,
                'action'   => $this->actionLabel($eventName),
                'wiki_url' => $wikiUrl,
            ),
            null
        );
    }

    // ---------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------

    /**
     * Resolve a project by ID, returning the full project array or null.
     */
    private function resolveProject($projectId)
    {
        $projectId = (int) $projectId;
        if (!$projectId) {
            return null;
        }

        return $this->projectModel->getById($projectId) ?: null;
    }

    private function buildTaskUrl($projectId, $taskId)
    {
        return $this->helper->url->to('TaskViewController', 'show', array(
            'project_id' => $projectId,
            'task_id'    => $taskId,
        ), '', true);
    }

    /**
     * Email subjects are a plain-text header, not HTML: no htmlspecialchars
     * here, matching how core's own MailNotification::getMailSubject() builds
     * subjects from raw project/task names.
     */
    private function buildSubject($projectName, $taskTitle, $taskId)
    {
        return sprintf('[%s] %s (#%d)', $projectName, $taskTitle, (int) $taskId);
    }

    private function actionLabel($eventName)
    {
        return isset($this->actionLabels[$eventName]) ? $this->actionLabels[$eventName] : $eventName;
    }

    /**
     * Resolve the recipient (the acting user), gate on project membership,
     * render and send the email, then record the web notification unless
     * $webEventData is null (no render path exists for that event type; see
     * handleProjectFileEvent/handleAuthFailureEvent).
     */
    private function deliver($userId, $projectId, $type, $eventName, $subject, array $templateVars, $webEventData)
    {
        $userId = (int) $userId;
        if (!$userId || !$this->projectPermissionModel->isMember($projectId, $userId)) {
            return false;
        }

        $user = $this->userModel->getById($userId);
        if (empty($user)) {
            return false;
        }

        $result = true;

        if (!empty($user['email'])) {
            $html = $this->renderTemplate($type, array_merge($templateVars, array('user' => $user)));

            if ($html === false) {
                $result = false;
            } else {
                $result = $this->sendEmail($user, $subject, $html, $eventName);
            }
        }

        // Web channel fires independently of the email outcome.
        if ($webEventData !== null) {
            $this->userUnreadNotificationModel->create($userId, $eventName, $webEventData);
        }

        return $result;
    }

    /**
     * Render a notification template by type, with whitelist validation.
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
    private function sendEmail(array $user, $subject, $html, $eventName)
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
                'event'   => $eventName,
                'error'   => $e->getMessage(),
            ));
            return false;
        }
    }
}
