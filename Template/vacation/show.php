<div class="page-header">
    <h2><?= t('Vacation mode') ?></h2>
</div>

<p><?= $on ? t('Vacation mode is on.') : t('Vacation mode is off.') ?></p>

<form method="post" action="<?= $this->url->href('VacationController', 'save', array('plugin' => 'NotifyMe', 'user_id' => $user['id'])) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>
    <?= $this->form->hidden('on', array('on' => $on ? '' : '1')) ?>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= $on ? t('Turn off vacation mode') : t('Turn on vacation mode') ?></button>
    </div>
</form>
