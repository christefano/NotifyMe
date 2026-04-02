<h2><?php echo t('New comment on'); ?> <?php echo $task['title']; ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo $project['name']; ?></strong></p>
<p><?php echo t('Task'); ?>: <strong><?php echo $task['title']; ?></strong> (#<?php echo $task['id']; ?>)</p>
<?php if (!empty($context['text'])): ?>
<h3><?php echo t('Comment'); ?></h3>
<p><?php echo nl2br(htmlspecialchars($context['text'])); ?></p>
<?php endif; ?>
<p><a href="<?php echo $task_url; ?>"><?php echo t('View Task'); ?></a></p>
<hr>
<p><?php echo t('This is an automated notification from Kanboard.'); ?></p>
