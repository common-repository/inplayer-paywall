<?php
$message = maybe_unserialize(get_option('inplayer_flash_message'));
if ($message): ?>
<div class="<?php echo 'notice notice-' . $message['type'] . ' is-dismissible'; ?>">
	<p><?php echo $message['text']; ?></p>
</div>
<?php endif; unset($message); ?>