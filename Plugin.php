<?php

namespace Kanboard\Plugin\NotifyMe;

use Kanboard\Core\Plugin\Base;

class Plugin extends Base
{
    public function initialize()
    {
        // Register as a shared service — one instance reused across all hooks.
        $this->container['notifyMeAction'] = function($c) {
            return new \Kanboard\Plugin\NotifyMe\Action\NotifyMeAction($c);
        };

        $action = $this->container['notifyMeAction'];

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

        foreach ($hooks as $hook => $method) {
            $this->hook->on($hook, array($action, $method));
        }
    }

    public function getPluginName()
    {
        return 'NotifyMe';
    }

    public function getPluginDescription()
    {
        return t('Email and Notifications menu notifications for all task activity');
    }

    public function getPluginAuthor()
    {
        return 'Christefano Reyes';
    }

    public function getPluginVersion()
    {
        return '1.0.1';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/christefano/NotifyMe';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.20';
    }
}
