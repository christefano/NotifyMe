<?php if ($this->user->hasProjectAccess('ProjectVacationController', 'show', $project['id'])): ?>
    <li <?= $this->app->checkMenuSelection('ProjectVacationController') ?>>
        <?= $this->url->link(t('Vacation mode'), 'ProjectVacationController', 'show', array('plugin' => 'NotifyMe', 'project_id' => $project['id'])) ?>
    </li>
<?php endif ?>
