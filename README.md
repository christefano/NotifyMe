# NotifyMe

Email and Notifications menu notifications for your own actions in Kanboard.


## About NotifyMe

By default, Kanboard only notifies you about actions others make. NotifyMe fills that gap by notifying you via Email and Notifications menu notifications about your own actions.


## Actions NotifyMe supports

Users receive email and Notifications menu notifications for:

- Task created by you
- Task updated by you
- You assigned a task to someone
- Task you created is closed
- Task you created is reopened
- Task you created is moved to a different column
- Task you created is moved to a different swimlane
- Subtask created by you
- Subtask updated by you
- Subtask deleted by you
- Comment added by you on any task
- Comment updated by you on any task
- Comment deleted by you on any task
- File attached by you to any task
- File deleted by you from any task
- Time entry added by you
- Time entry deleted by you
- Project created by you
- Project deleted by you
- Category added by you to any task
- Category removed by you from any task


## Hooks

```php
        $hooks = array(
            'model:task-create:success' => 'onTaskCreate',
            'model:task-update:success' => 'onTaskUpdate',
            'model:task-assignee_change:success' => 'onTaskAssigneeChange',
            'model:task-close:success' => 'onTaskClose',
            'model:task-open:success' => 'onTaskOpen',
            'model:task-move-column:success' => 'onTaskMoveColumn',
            'model:task-move-swimlane:success' => 'onTaskMoveSwimlane',
            'model:subtask-create:success' => 'onSubtaskCreate',
            'model:subtask-update:success' => 'onSubtaskUpdate',
            'model:subtask-delete:success' => 'onSubtaskDelete',
            'model:comment-create:success' => 'onCommentCreate',
            'model:comment-update:success' => 'onCommentUpdate',
            'model:comment-delete:success' => 'onCommentDelete',
            'model:task-file-add:success' => 'onFileAttached',
            'model:task-file-delete:success' => 'onFileDeleted',
            'model:subtask-time-tracking:create:success' => 'onTimeTrackingCreated',
            'model:subtask-time-tracking:delete:success' => 'onTimeTrackingDeleted',
            'model:project-create:success' => 'onProjectCreated',
            'model:project-remove:success' => 'onProjectRemoved',
            'model:task-category:add:success' => 'onCategoryAdded',
            'model:task-category:remove:success' => 'onCategoryRemoved',
        );
```


## Installation

1. Extract `NotifyMe.zip` to `/path/to/kanboard/plugins/NotifyMe/`
2. …
3. Profit! All users will receive notifications about their own actions


## Configuration

Zero configuration is needed, and NotifyMe works immediately after installation.


## License

GNU General Public License v2 — See LICENSE file for details.
