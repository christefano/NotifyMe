<?php if ((int) $user['id'] === (int) $this->user->getId()): ?>
    <li <?= $this->app->checkMenuSelection('VacationController') ?>>
        <?= $this->url->link(t('Vacation mode'), 'VacationController', 'show', array('plugin' => 'NotifyMe', 'user_id' => $user['id'])) ?>
    </li>
<?php endif ?>
