<?php

namespace Kanboard\Plugin\NotifyMe\Controller;

use Kanboard\Controller\BaseController;

/**
 * Vacation mode for the members of one project, for its managers. Kanboard doesn't let a
 * project manager edit users, so a flag set here pauses task emails about this project only.
 */
class ProjectVacationController extends BaseController
{
    public function show()
    {
        $project = $this->getProject();
        $members = array();

        // getAllUsers() returns user_id => display name, direct members and group members alike.
        foreach ($this->projectUserRoleModel->getAllUsers($project['id']) as $userId => $name) {
            $members[] = array(
                'id'         => (int) $userId,
                'name'       => $name,
                'username'   => '',
                'everywhere' => $this->notifyMeVacation->isOn($userId),
                'here'       => $this->notifyMeVacation->isOnInProject($userId, $project['id']),
            );
        }

        $this->response->html($this->helper->layout->project('NotifyMe:vacation/project', array(
            'project' => $project,
            'members' => $members,
            'title'   => t('Vacation mode'),
        )));
    }

    public function save()
    {
        $project = $this->getProject();
        $this->checkCSRFForm();

        $values = $this->request->getValues();
        $userId = isset($values['user_id']) ? (int) $values['user_id'] : 0;
        $user = $userId > 0 ? $this->userModel->getById($userId) : array();

        if (empty($user) || !$this->projectPermissionModel->isMember($project['id'], $userId)) {
            throw new \Kanboard\Core\Controller\AccessForbiddenException();
        }

        $on = !empty($values['on']);
        $this->notifyMeVacation->set($userId, $project['id'], $on, $this->userSession->getId());

        $setter = $this->userModel->getById($this->userSession->getId());
        $this->notifyMeVacation->sendNotice($user, $project, $setter ?: array(), $on);

        if ($on) {
            $this->flash->success(t('Vacation mode is on.'));
        } else {
            $this->flash->success(t('Vacation mode turned off.'));
        }

        $this->response->redirect($this->helper->url->to('ProjectVacationController', 'show', array('plugin' => 'NotifyMe', 'project_id' => $project['id'])));
    }
}
