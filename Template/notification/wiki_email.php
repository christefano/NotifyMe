<h2><?php echo t($action); ?></h2>
<p><?php echo t('Project'); ?>: <strong><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><?php echo t('Wiki page'); ?>: <strong><?php echo htmlspecialchars($wiki['title'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><a href="<?php echo htmlspecialchars($wiki_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo t('View wiki page'); ?></a></p>
<hr>
<p><?php echo \Kanboard\Plugin\NotifyMe\Product::footer(); ?></p>
