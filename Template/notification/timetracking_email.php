<h2><?php echo t('Time Tracking'); ?> - <?php echo htmlspecialchars(t($action), ENT_QUOTES, 'UTF-8'); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><?php echo t('Task'); ?>: <strong><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></strong> (#<?php echo (int) $task['id']; ?>)</p>
<?php if (!empty($context['time_spent'])): ?>
<p><?php echo t('Time Spent'); ?>: <strong><?php echo htmlspecialchars($context['time_spent'], ENT_QUOTES, 'UTF-8'); ?> hours</strong></p>
<?php endif; ?>
<p><a href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo t('View Task'); ?></a></p>
<hr>
<p><?php echo t('This is an automated notification from Kanboard.'); ?></p>
