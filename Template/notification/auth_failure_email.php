<h2><?php echo t($action); ?></h2>
<p><?php echo t('Username'); ?>: <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></p>
<p><?php echo t('A failed login attempt used your username. If this was not you, consider changing your password.'); ?></p>
<hr>
<p><?php echo \Kanboard\Plugin\NotifyMe\Product::footer(); ?></p>
