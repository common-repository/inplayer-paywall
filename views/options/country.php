<select name="inplayer_settings[country]">
<?php
$current   = $this->inplayer->settings('country');
$countries = include __DIR__ . '/../../includes/countries.php';
foreach ($countries as $iso => $name): ?>
	<option value="<?php echo $iso; ?>"<?php selected($iso, $current); ?>><?php echo $name; ?></option>
<?php endforeach; ?>
</select>