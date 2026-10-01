<?php
$options = array();

foreach (\Kanboard\Plugin\NotifyMe\Model\UserNotificationModel::UNREAD_LIMITS as $limit) {
    $options[$limit] = $limit === 0 ? t('Unlimited') : (string) $limit;
}

$values += array('notifyme_max_unread' => \Kanboard\Plugin\NotifyMe\Model\UserNotificationModel::UNREAD_DEFAULT);
?>
<fieldset>
    <legend>NotifyMe</legend>
    <?= $this->form->label(t('Notifications menu entries kept per user'), 'notifyme_max_unread') ?>
    <?= $this->form->select('notifyme_max_unread', $options, $values, $errors) ?>
    <p class="form-help"><?= t('The oldest entries are deleted as new ones arrive.') ?></p>
</fieldset>
