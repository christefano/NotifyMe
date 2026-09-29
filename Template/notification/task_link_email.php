<h2><?php echo t($action); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><?php echo t('Task'); ?>: <strong><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></strong> (#<?php echo (int) $task['id']; ?>)</p>
<?php if (!empty($context['label'])): ?>
<p><?php echo t('Link type'); ?>: <strong><?php echo htmlspecialchars($context['label'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php endif; ?>
<?php if (!empty($opposite_task['title'])): ?>
<p><?php echo t('Linked task'); ?>: <strong><?php echo htmlspecialchars($opposite_task['title'], ENT_QUOTES, 'UTF-8'); ?></strong> (#<?php echo (int) $opposite_task['id']; ?>)</p>
<?php endif; ?>
<p><a href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo t('View Task'); ?></a></p>
<hr>
<p><?php echo t('This is an automated notification from Kanboard.'); ?></p>
