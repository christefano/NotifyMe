<h2><?php echo t('Time Tracking'); ?> - <?php echo t($action); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo $project['name']; ?></strong></p>
<p><?php echo t('Task'); ?>: <strong><?php echo $task['title']; ?></strong> (#<?php echo $task['id']; ?>)</p>
<?php if (!empty($context['time_spent'])): ?>
<p><?php echo t('Time Spent'); ?>: <strong><?php echo $context['time_spent']; ?> hours</strong></p>
<?php endif; ?>
<p><a href="<?php echo $task_url; ?>"><?php echo t('View Task'); ?></a></p>
<hr>
<p><?php echo t('This is an automated notification from Kanboard.'); ?></p>
