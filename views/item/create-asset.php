<div class="wrap" id="inplayer_asset_editor">
	<h1><?php esc_attr_e( 'Add New InPlayer Premium Asset', INPLAYER_TEXT_DOMAIN ); ?></h1>
	<div class="notice hidden">
		<p></p>
	</div>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-1">
			<div id="post-body-content" style="position: relative;">
				<div id="titlediv">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_attr_e( 'What type of asset would you like to sell?', INPLAYER_TEXT_DOMAIN ); ?></span></h2>
						<div class="inside">
							<?php include __DIR__ . '/../../views/item/asset-types.php'; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>