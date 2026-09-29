# NotifyMe

Email and Notifications menu notifications for your own actions in Kanboard.


## About NotifyMe

By default, Kanboard only notifies you about actions others make. NotifyMe fills that gap by notifying you via Email and Notifications menu notifications about your own actions, so a full activity trail reaches your inbox even when nobody else touched the task.

The recipient is always the person who performed the action, gated by project membership.


## Actions NotifyMe supports

Users receive both an email and a Notifications menu entry for:

- Task created
- Task updated
- Task assignee changed (including self-assignment)
- Task closed
- Task opened
- Task moved to a different column
- Task moved to a different swimlane
- Subtask created
- Subtask updated
- Subtask deleted
- Comment added
- Comment updated
- Comment deleted
- File attached to a task
- File deleted from a task
- Task link created or updated
- Task link deleted

Users receive an email only (no Notifications menu entry) for:

- File attached to a project
- File deleted from a project
- Failed login attempt using your username, if your account is active
- Wiki page created, updated, or deleted (see below)

Kanboard's own Notifications menu has no title or click-through target for
these event types, so an entry there would only ever read
"Notification" and link to a nonexistent task. Email still delivers in full.


## Actions NotifyMe does not support

Project create/update/remove, category add/remove, and time-tracking entry create/delete are not supported. Kanboard's core does not dispatch an event for any of these actions, so a plugin has no signal to listen for. This is a limitation of Kanboard itself and not a choice made by NotifyMe.


## Optional support for the Wiki plugin

If the third-party Wiki plugin (funktechno/kanboard-plugin-wiki) is installed, NotifyMe also notifies you by email when you create, update, or delete a wiki page. This is email-only for the same reason project file and failed-login events are: Kanboard's core has no title builder or redirect target for wiki events.

If Wiki is not installed, these listeners are registered but simply never fire. There is no error and no dependency on Wiki being present.


## Event registration

```php
$events = array(
    TaskModel::EVENT_CREATE              => 'onTaskCreate',
    TaskModel::EVENT_UPDATE              => 'onTaskUpdate',
    TaskModel::EVENT_CLOSE               => 'onTaskClose',
    TaskModel::EVENT_OPEN                => 'onTaskOpen',
    TaskModel::EVENT_MOVE_COLUMN         => 'onTaskMoveColumn',
    TaskModel::EVENT_MOVE_SWIMLANE       => 'onTaskMoveSwimlane',
    TaskModel::EVENT_ASSIGNEE_CHANGE     => 'onTaskAssigneeChange',
    SubtaskModel::EVENT_CREATE           => 'onSubtaskCreate',
    SubtaskModel::EVENT_UPDATE           => 'onSubtaskUpdate',
    SubtaskModel::EVENT_DELETE           => 'onSubtaskDelete',
    CommentModel::EVENT_CREATE           => 'onCommentCreate',
    CommentModel::EVENT_UPDATE           => 'onCommentUpdate',
    CommentModel::EVENT_DELETE           => 'onCommentDelete',
    TaskFileModel::EVENT_CREATE          => 'onTaskFileCreate',
    TaskFileModel::EVENT_DESTROY         => 'onTaskFileDestroy',
    TaskLinkModel::EVENT_CREATE_UPDATE   => 'onTaskLinkCreateUpdate',
    TaskLinkModel::EVENT_DELETE          => 'onTaskLinkDelete',
    ProjectFileModel::EVENT_CREATE       => 'onProjectFileCreate',
    ProjectFileModel::EVENT_DESTROY      => 'onProjectFileDestroy',
    AuthenticationManager::EVENT_FAILURE => 'onAuthFailure',
    'wikipage.create'                    => 'onWikiPageCreate',
    'wikipage.update'                    => 'onWikiPageUpdate',
    'wikipage.delete'                    => 'onWikiPageDelete',
);

foreach ($events as $eventName => $method) {
    $this->dispatcher->addListener($eventName, array($action, $method));
}
```


## Installation

1. Extract `NotifyMe.zip` to `/path/to/kanboard/plugins/NotifyMe/`
2. Restart PHP-FPM (or your webserver) if Kanboard's plugin cache does not pick up the new files
3. All users will receive notifications about their own actions


## Configuration

Zero configuration is needed, and NotifyMe works immediately after installation.


## License

GNU General Public License v2. See LICENSE file for details.
