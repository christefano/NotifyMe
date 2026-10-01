<?php

namespace Kanboard\Plugin\NotifyMe;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Core\Security\Role;
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

    /**
     * Vacation mode pauses task emails to a user until the next login. Core's
     * userNotificationModel is replaced so the pause covers Kanboard's own
     * emails whichever plugin sends them, and the web notification still runs.
     */
    private function initializeVacationMode()
    {
        $this->container['notifyMeVacation'] = function ($c) {
            return new \Kanboard\Plugin\NotifyMe\Model\Vacation($c);
        };

        try {
            $this->container['userNotificationModel'] = function ($c) {
                return new \Kanboard\Plugin\NotifyMe\Model\UserNotificationModel($c);
            };
        } catch (\Exception $e) {
            $this->logger->error('NotifyMe: userNotificationModel already in use, vacation mode covers NotifyMe emails only: '.$e->getMessage());
        }

        // Core has no hook after a settings save, and configModel is already frozen when plugins
        // load. So when a request posts the limit, trim after the response is sent, to whatever
        // value the database holds then (an unauthorized or failed save leaves it unchanged).
        if (isset($_POST['notifyme_max_unread'])) {
            register_shutdown_function(function () {
                try {
                    $options = $this->configModel->getAll();
                    $this->userNotificationModel->trimAllUnread(isset($options['notifyme_max_unread']) ? $options['notifyme_max_unread'] : \Kanboard\Plugin\NotifyMe\Model\UserNotificationModel::UNREAD_DEFAULT);
                } catch (\Throwable $e) {
                    $this->logger->error('NotifyMe: trim after settings save failed: '.$e->getMessage());
                }
            });
        }

        $this->applicationAccessMap->add('VacationController', '*', Role::APP_USER);
        $this->projectAccessMap->add('ProjectVacationController', '*', Role::PROJECT_MANAGER);

        $this->template->hook->attach('template:config:application', 'NotifyMe:config/unread_limit');
        $this->template->hook->attach('template:user:sidebar:actions', 'NotifyMe:vacation/user_sidebar');
        $this->template->hook->attach('template:project:sidebar', 'NotifyMe:vacation/project_sidebar');

        // Fires on every login, including a remembered session and before any
        // two-factor code is checked.
        $this->dispatcher->addListener(AuthenticationManager::EVENT_SUCCESS, function () {
            $userId = (int) $this->userSession->getId();

            if ($userId > 0 && $this->notifyMeVacation->clearAll($userId)) {
                $this->flash->success(t('Vacation mode turned off.'));

                $user = $this->userModel->getById($userId);

                if (!empty($user)) {
                    $this->notifyMeVacation->sendNotice($user, array(), array(), false);
                }
            }
        });
    }

    public function initialize()
    {
        // Register as a shared service (one instance reused across all hooks).
        $this->container['notifyMeAction'] = function ($c) {
            return new \Kanboard\Plugin\NotifyMe\Action\NotifyMeAction($c);
        };

        $this->initializeVacationMode();

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
        return t('Email and Notifications menu notifications for your own actions, and failed-login alerts to the account owner');
    }

    public function getPluginAuthor()
    {
        return 'Christefano Reyes';
    }

    public function getPluginVersion()
    {
        return '1.2.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/christefano/KanboardNotifyMe';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.20';
    }
}
