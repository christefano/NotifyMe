<h2><?php echo t($action); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo $project['name']; ?></strong></p>
<p><?php echo t('Task'); ?>: <strong><?php echo $task['title']; ?></strong> (#<?php echo $task['id']; ?>)</p>
<?php if (!empty($task['description'])): ?>
<h3><?php echo t('Description'); ?></h3>
<p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
<?php endif; ?>
<?php if (!empty($task['owner_id'])): ?>
<p><?php echo t('Assigned to'); ?>: <strong><?php echo $task['assignee_name']; ?></strong></p>
<?php endif; ?>
<?php if (!empty($task['due_date'])): ?>
<p><?php echo t('Due date'); ?>: <strong><?php echo $task['due_date']; ?></strong></p>
<?php endif; ?>
<p><a href="<?php echo $task_url; ?>"><?php echo t('View Task'); ?></a></p>
<hr>
<p><?php echo t('This is an automated notification from Kanboard.'); ?></p>
