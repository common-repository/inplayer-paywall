<?php
$assetsList = new AssetsListTable;
$assetsList->prepare_items();
?>

<div class="wrap" id="inplayer-transactions">
	<div class="notice hidden">
		<p></p>
	</div>

	<h1><?php esc_attr_e( 'InPlayer Assets', INPLAYER_TEXT_DOMAIN ); ?> <a href="?page=inplayer-asset" class="page-title-action"><?php esc_attr_e( 'Add New', INPLAYER_TEXT_DOMAIN ); ?></a></h1>

	<div style="float: right;">
		<label for="asset-selection"><?php esc_attr_e( 'Display:', INPLAYER_TEXT_DOMAIN ); ?></label>
		<select id="asset-selection">
			<option value="1"><?php esc_attr_e( 'Published Assets', INPLAYER_TEXT_DOMAIN ); ?></option>
			<option <?php if(isset($_GET['inactive'])) echo 'selected'; ?> value="0"><?php esc_attr_e( 'Deleted Assets', INPLAYER_TEXT_DOMAIN ); ?></option>
		</select>
	</div>

	<?php $assetsList->display(); ?>
</div>