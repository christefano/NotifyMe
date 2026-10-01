<?php if (!empty($project)): ?>
<p><?php echo t('Project'); ?>: <strong><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><?php echo t('User'); ?>: <strong><?php echo htmlspecialchars($user['name'] ?: $user['username'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php endif; ?>
<?php if ($on): ?>
<p><?php echo t('Vacation mode is on. %s email notifications are paused. Logging back in to %s will turn vacation mode off.', $product, $product); ?></p>
<?php else: ?>
<p><?php echo t('Vacation mode is off. %s email notifications have resumed.', $product); ?></p>
<?php endif; ?>
<hr>
<p><?php echo \Kanboard\Plugin\NotifyMe\Product::footer(); ?></p>
