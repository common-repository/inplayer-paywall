<?php
/** @var InPlayerForms $this */
$_access_fees = [];
if ($asset_id = (int)$this->asset('id')) {
	$_access_fees = InPlayerPlugin::request('GET', INPLAYER_ASSETS . '/' . $asset_id . '/access-fees')['body'];
}
/*
 * get access types from InPlayer
 *
 */
$_access_types = InPlayerPlugin::request( 'GET', INPLAYER_ASSETS . '/access-types' )['body'];
/*
 * @todo pull allowed currencies for this merchant (or payment gateway?)
 * build the currencies combobox options
 *
 */
$_currencies = [
	'EUR' => 'Euro',
	'USD' => 'USD Dollar',
	'GBP' => 'British Pound',
];
?>

<div id="access-fees">
	<span class="add button"><?php echo __( 'Create New Access Fee' ); ?></span>
	<li style="display:none;">
		<input type="hidden" name="access_fee[id][]" value="0">
		<input type="text" name="access_fee[description][]" maxlength="255" autocomplete="off"
		       placeholder="<?php echo __( 'Enter description' ); ?>"
		       title="<?php echo __( 'Enter description' ); ?>">
		<select name="access_fee[type][]" title="">
			<?php echo $this->build_access_types_combobox_options( $_access_types ); ?>
		</select>
		<input type="number" name="access_fee[amount][]" value="0" step="0.01" title="">
		<select name="access_fee[currency][]" title="">
			<?php echo $this->build_currency_combobox_options( $_currencies ); ?>
		</select>
		<span class="remove button"><?php echo __( 'Remove' ); ?></span>
	</li>
</div>

<?php // list of all saved access fees for the asset ?>
<div>
	<ul id="access-fees-list">
		<?php foreach ( $_access_fees as $fee ): ?>
			<li>
                <input type="hidden" name="access_fee[item_type][]" value="<?php echo $fee['item_type']; ?>">
				<input type="hidden" name="access_fee[id][]" value="<?php echo $fee['id']; ?>">
				<input type="text" name="access_fee[description][]" value="<?php echo $fee['description']; ?>"
				       required="required" maxlength="255" autocomplete="off"
				       placeholder="<?php echo __( 'Enter description' ); ?>"
				       title="<?php echo __( 'Enter description' ); ?>">
				<select name="access_fee[type][]" title="">
					<?php echo $this->build_access_types_combobox_options( $_access_types, $fee['access_type']['id'] ); ?>
				</select>
				<input type="number" name="access_fee[amount][]" value="<?php echo $fee['amount']; ?>" step="0.05"
				       required="required" title="">
				<select name="access_fee[currency][]" title="">
					<?php echo $this->build_currency_combobox_options( $_currencies, $fee['currency'] ); ?>
				</select>
				<span class="remove button"><?php echo __( 'Remove' ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php unset($_access_fees, $_access_types, $_currencies); ?>