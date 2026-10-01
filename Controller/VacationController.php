<?php

namespace Kanboard\Plugin\NotifyMe\Controller;

use Kanboard\Controller\BaseController;

/**
 * A user's own vacation mode, for every project. Only the user can see or change it.
 */
class VacationController extends BaseController
{
    public function show()
    {
        $user = $this->ownUser();

        $this->response->html($this->helper->layout->user('NotifyMe:vacation/show', array(
            'user' => $user,
            'on'   => $this->notifyMeVacation->isOn($user['id']),
        )));
    }

    public function save()
    {
        $user = $this->ownUser();
        $this->checkCSRFForm();

        $values = $this->request->getValues();
        $on = !empty($values['on']);

        $this->notifyMeVacation->set($user['id'], 0, $on);

        if ($on) {
            $this->notifyMeVacation->sendNotice($user);
            $this->flash->success(t('Vacation mode is on.'));
        } else {
            $this->notifyMeVacation->sendNotice($user, array(), array(), false);
            $this->flash->success(t('Vacation mode turned off.'));
        }

        $this->response->redirect($this->helper->url->to('VacationController', 'show', array('plugin' => 'NotifyMe', 'user_id' => $user['id'])));
    }

    private function ownUser()
    {
        $user = $this->getUser();

        if ((int) $user['id'] !== (int) $this->userSession->getId()) {
            throw new \Kanboard\Core\Controller\AccessForbiddenException();
        }

        return $user;
    }
}
