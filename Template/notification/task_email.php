<h2><?php echo htmlspecialchars(t($action), ENT_QUOTES, 'UTF-8'); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><?php echo t('Task'); ?>: <strong><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></strong> (#<?php echo (int) $task['id']; ?>)</p>
<?php if (!empty($task['description'])): ?>
<h3><?php echo t('Description'); ?></h3>
<p><?php echo nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')); ?></p>
<?php endif; ?>
<?php if (!empty($task['owner_id']) && !empty($task['assignee_name'])): ?>
<p><?php echo t('Assigned to'); ?>: <strong><?php echo htmlspecialchars($task['assignee_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php endif; ?>
<?php if (!empty($task['due_date'])): ?>
<p><?php echo t('Due date'); ?>: <strong><?php echo htmlspecialchars($task['due_date'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php endif; ?>
<p><a href="<?php echo htmlspecialchars($task_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo t('View Task'); ?></a></p>
<hr>
<p><?php echo t('This is an automated notification from Kanboard.'); ?></p>
