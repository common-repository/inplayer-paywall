<select name="inplayer_settings[currency]">
<?php
$current    = $this->inplayer->settings('currency');
$currencies = include __DIR__ . '/../../includes/currencies.php';
foreach ($currencies as $iso => $name): ?>
<option value="<?php echo $iso; ?>"<?php selected($iso, $current); ?>><?php echo $name; ?></option>
<?php endforeach; ?>
</select>
