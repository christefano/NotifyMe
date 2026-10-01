<div class="page-header">
    <h2><?= t('Vacation mode') ?></h2>
</div>

<?php if (empty($members)): ?>
    <p class="alert"><?= t('There is no user in this project.') ?></p>
<?php else: ?>
    <table class="table-striped">
        <tr>
            <th><?= t('User') ?></th>
            <th><?= t('Vacation mode') ?></th>
            <th><?= t('Action') ?></th>
        </tr>
        <?php foreach ($members as $member): ?>
        <tr>
            <td><?= $this->text->e($member['name'] ?: $member['username']) ?></td>
            <td>
                <?php if ($member['everywhere']): ?>
                    <?= t('On in all projects') ?>
                <?php elseif ($member['here']): ?>
                    <?= t('On') ?>
                <?php else: ?>
                    <?= t('Off') ?>
                <?php endif ?>
            </td>
            <td>
                <?php if (! $member['everywhere']): ?>
                <form method="post" action="<?= $this->url->href('ProjectVacationController', 'save', array('plugin' => 'NotifyMe', 'project_id' => $project['id'])) ?>" autocomplete="off">
                    <?= $this->form->csrf() ?>
                    <?= $this->form->hidden('user_id', array('user_id' => $member['id'])) ?>
                    <?= $this->form->hidden('on', array('on' => $member['here'] ? '' : '1')) ?>
                    <button type="submit" class="btn" aria-label="<?= $member['here'] ? t('Turn off vacation mode for %s', $member['name'] ?: $member['username']) : t('Turn on vacation mode for %s', $member['name'] ?: $member['username']) ?>"><?= $member['here'] ? t('Turn off') : t('Turn on') ?></button>
                </form>
                <?php endif ?>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>
