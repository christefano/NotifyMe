<?php

namespace Kanboard\Plugin\NotifyMe\Model;

use Kanboard\Core\Translator;
use Kanboard\Model\UserNotificationModel as BaseUserNotificationModel;
use Kanboard\Model\UserUnreadNotificationModel;
use Kanboard\Notification\MailNotification;

/**
 * Core's sendUserNotification() (1.2.54) with the email type skipped for a user in vacation
 * mode. Every other type, the Notifications menu included, still runs. Works whichever plugin
 * handles the email type (core, Mailmagik, or TagAlong).
 *
 * After each notification the user's Notifications menu is trimmed to the limit set under
 * Application settings, so a busy account never accumulates thousands of unread rows.
 */
class UserNotificationModel extends BaseUserNotificationModel
{
    /** Allowed values for the 'notifyme_max_unread' setting. 0 keeps everything. */
    const UNREAD_LIMITS = array(100, 500, 1000, 0);

    /** Used until the setting is saved, so installing the plugin is enough to keep the menu page loadable. */
    const UNREAD_DEFAULT = 1000;

    public function sendUserNotification(array $user, $event_name, array $event_data)
    {
        $this->deliver($user, $event_name, $event_data);
        $this->trimUnread((int) $user['id'], $this->configuredLimit());
    }

    public function trimUser($userId)
    {
        $this->trimUnread((int) $userId, $this->configuredLimit());
    }

    /** Deletes every user's oldest unread rows beyond $max. Called when the setting is saved. */
    public function trimAllUnread($max)
    {
        $max = $this->normalizeLimit($max);
        $table = UserUnreadNotificationModel::TABLE;

        foreach ($this->db->table($table)->distinct('user_id')->findAllByColumn('user_id') as $userId) {
            $this->trimUnread((int) $userId, $max);
        }
    }

    public function configuredLimit()
    {
        return $this->normalizeLimit($this->configModel->get('notifyme_max_unread', self::UNREAD_DEFAULT));
    }

    private function normalizeLimit($max)
    {
        $max = (int) $max;

        return in_array($max, self::UNREAD_LIMITS, true) ? $max : self::UNREAD_DEFAULT;
    }

    private function deliver(array $user, $event_name, array $event_data)
    {
        if (!$this->notifyMeVacation->isMuted($user['id'], $this->projectIdOf($event_data))) {
            return parent::sendUserNotification($user, $event_name, $event_data);
        }

        $loadedLocales = Translator::$locales;
        Translator::unload();

        if (! empty($user['language'])) {
            Translator::load($user['language']);
        } else {
            Translator::load($this->configModel->get('application_language', 'en_US'));
        }

        foreach ($this->userNotificationTypeModel->getSelectedTypes($user['id']) as $type) {
            if ($type !== MailNotification::TYPE) {
                $this->userNotificationTypeModel->getType($type)->notifyUser($user, $event_name, $event_data);
            }
        }

        Translator::$locales = $loadedLocales;
    }

    /** Deletes the user's oldest unread rows beyond the limit. A missing or unrecognized setting uses the default. */
    private function trimUnread($userId, $max)
    {
        if ($max === 0) {
            return;
        }

        $table = UserUnreadNotificationModel::TABLE;
        $oldestKept = $this->db->table($table)->eq('user_id', $userId)->desc('id')->offset($max - 1)->limit(1)->findOneColumn('id');

        if ($oldestKept !== null && $oldestKept !== false) {
            $this->db->table($table)->eq('user_id', $userId)->lt('id', $oldestKept)->remove();
        }
    }

    /** Project of the event: the task's, or the first overdue task's. 0 when there is none. */
    private function projectIdOf(array $event_data)
    {
        if (isset($event_data['task']['project_id'])) {
            return (int) $event_data['task']['project_id'];
        }

        if (isset($event_data['tasks'][0]['project_id'])) {
            return (int) $event_data['tasks'][0]['project_id'];
        }

        return 0;
    }
}
