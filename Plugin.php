<?php

namespace Kanboard\Plugin\NotifyMe;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Core\Translator;
use Kanboard\Model\CommentModel;
use Kanboard\Model\ProjectFileModel;
use Kanboard\Model\SubtaskModel;
use Kanboard\Model\TaskFileModel;
use Kanboard\Model\TaskLinkModel;
use Kanboard\Model\TaskModel;

class Plugin extends Base
{
    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function initialize()
    {
        // Register as a shared service (one instance reused across all hooks).
        $this->container['notifyMeAction'] = function ($c) {
            return new \Kanboard\Plugin\NotifyMe\Action\NotifyMeAction($c);
        };

        $action = $this->container['notifyMeAction'];

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
    }

    public function getPluginName()
    {
        return 'NotifyMe';
    }

    public function getPluginDescription()
    {
        return t('Email and Notifications menu notifications for your own actions');
    }

    public function getPluginAuthor()
    {
        return 'Christefano Reyes';
    }

    public function getPluginVersion()
    {
        return '1.1.0';
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
