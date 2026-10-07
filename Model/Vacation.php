<?php

namespace Kanboard\Plugin\NotifyMe\Model;

use Kanboard\Core\Base;
use Kanboard\Plugin\NotifyMe\Product;

/**
 * Vacation mode: task emails to a user pause, everywhere (set by the user) or in one project
 * (set by that project's manager), until the user next logs in. Notifications menu entries,
 * failed login alerts, invitations, and password resets are never paused. Flags live in user
 * metadata, so no table is added.
 */
class Vacation extends Base
{
    const KEY = 'notifyme_vacation';
    const PROJECT_PREFIX = 'notifyme_vacation_project_';
    const SETTER_PREFIX = 'notifyme_vacation_setter_';  // plus the project id: the manager who set that project's flag

    public function isOn($userId)
    {
        return $this->userMetadataModel->get((int) $userId, self::KEY, '') === '1';
    }

    public function isOnInProject($userId, $projectId)
    {
        return (int) $projectId > 0
            && $this->userMetadataModel->get((int) $userId, self::PROJECT_PREFIX.(int) $projectId, '') === '1';
    }

    /** True when task emails to this user about this project are paused. */
    public function isMuted($userId, $projectId)
    {
        return $this->isOn($userId) || $this->isOnInProject($userId, $projectId);
    }

    /**
     * Turn the flag on or off, everywhere when $projectId is 0. A project flag also keeps the manager who set it ($setterId), so
     * the login that turns it off can tell that manager. A flag set before the setter was kept has none.
     */
    public function set($userId, $projectId, $on, $setterId = 0)
    {
        $key = (int) $projectId > 0 ? self::PROJECT_PREFIX.(int) $projectId : self::KEY;
        $setterKey = self::SETTER_PREFIX.(int) $projectId;

        if ($on) {
            $values = array($key => '1');

            if ((int) $projectId > 0 && (int) $setterId > 0) {
                $values[$setterKey] = (string) (int) $setterId;
            }

            return $this->userMetadataModel->save((int) $userId, $values);
        }

        if ((int) $projectId > 0) {
            $this->userMetadataModel->remove((int) $userId, $setterKey);
        }

        return $this->userMetadataModel->remove((int) $userId, $key);
    }

    /** Project id => id of the manager who set the user's flag there, for the flags that have one. */
    public function getSetters($userId)
    {
        $setters = array();

        foreach ($this->userMetadataModel->getAll((int) $userId) as $name => $value) {
            if (strpos($name, self::SETTER_PREFIX) === 0 && (int) $value > 0) {
                $projectId = (int) substr($name, strlen(self::SETTER_PREFIX));

                if ($projectId > 0 && $this->isOnInProject($userId, $projectId)) {
                    $setters[$projectId] = (int) $value;
                }
            }
        }

        return $setters;
    }

    /** Remove every flag the user has. Returns true when there was one to remove. */
    public function clearAll($userId)
    {
        $cleared = false;

        foreach (array_keys($this->userMetadataModel->getAll((int) $userId)) as $name) {
            if ($name === self::KEY || strpos($name, self::PROJECT_PREFIX) === 0 || strpos($name, self::SETTER_PREFIX) === 0) {
                $this->userMetadataModel->remove((int) $userId, $name);
                $cleared = true;
            }
        }

        return $cleared;
    }

    /**
     * Email the flagged user, and the manager who set it when that is someone else, that
     * vacation mode is on, or off when $on is false. $notifyUser false leaves the user out, for the notice that
     * goes only to a manager. Sent directly, so vacation mode never pauses it.
     */
    public function sendNotice(array $user, array $project = array(), array $setter = array(), $on = true, $notifyUser = true)
    {
        $recipients = $notifyUser ? array($user) : array();

        if (!empty($setter) && (int) $setter['id'] !== (int) $user['id']) {
            $recipients[] = $setter;
        }

        $product = Product::get('product_name');
        $subject = sprintf('[%s] %s', !empty($project) ? $project['name'] : $product, $on ? t('Vacation mode is on') : t('Vacation mode is off'));

        foreach ($recipients as $recipient) {
            if (empty($recipient['email'])) {
                continue;
            }

            try {
                $this->emailClient->send(
                    $recipient['email'],
                    $recipient['name'] ?: $recipient['username'],
                    $subject,
                    $this->template->render('NotifyMe:notification/vacation_email', array(
                        'user'    => $user,
                        'project' => $project,
                        'product' => $product,
                        'on'      => $on,
                    ))
                );
            } catch (\Exception $e) {
                $this->logger->error('NotifyMe: vacation notice failed', array('user_id' => $recipient['id'], 'error' => $e->getMessage()));
            }
        }
    }
}
