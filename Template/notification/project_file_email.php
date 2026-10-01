<h2><?php echo t($action); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php if (!empty($context['name'])): ?>
<p><?php echo t('File'); ?>: <strong><?php echo htmlspecialchars($context['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<?php endif; ?>
<hr>
<p><?php echo \Kanboard\Plugin\NotifyMe\Product::footer(); ?></p>
