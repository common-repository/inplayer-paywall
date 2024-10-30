<div class="wrap" id="inplayer_asset_editor">
	<form method="post">
		<input type="hidden" name="action" value="inplayer_save_asset">
		<input type="hidden" name="id" value="<?php echo $this->asset( 'id' ); ?>">
		<input type="hidden" name="asset_type" value="<?php echo $item_type; ?>">
		<input type="hidden" name="asset_content" id="asset-content" value="">

		<?php if ( $item_id ): ?>
			<h1><?php esc_attr_e( 'Edit InPlayer Premium Asset', INPLAYER_TEXT_DOMAIN ); ?>
				<a href="?page=inplayer-asset" class="page-title-action"><?php esc_attr_e( 'Add New', INPLAYER_TEXT_DOMAIN ); ?></a></h1>
		<?php else: ?>
			<h1><?php esc_attr_e( 'Create InPlayer Premium Asset', INPLAYER_TEXT_DOMAIN ); ?></h1>
		<?php endif; ?>

		<div class="notice hidden">
			<p></p>
		</div>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" style="position: relative;">
					<div id="titlediv">
						<div id="titlewrap">
							<input type="text" name="post_title" size="30"
							       placeholder="<?php esc_attr_e( 'Enter title here', INPLAYER_TEXT_DOMAIN ); ?>"
							       value="<?php echo $this->asset['title']; ?>" id="title" spellcheck="true"
							       autocomplete="off">
						</div>
					</div>

					<div id="inplayer-asset-content-editor" class="postbox ">
						<?php if ( $item_type === 'ooyala_asset' ): ?>
							<?php include __DIR__ . '/../../views/item/ooyala_asset/editor.php'; ?>
						<?php else: ?>
							<?php include __DIR__ . '/../../views/item/html_asset/editor.php'; ?>
						<?php endif; ?>
					</div>

					<div id="inplayer-asset-access-fees" class="postbox ">
						<h2 class="hndle"><span><?php esc_attr_e( 'Set your price', INPLAYER_TEXT_DOMAIN ); ?></span></h2>
						<div class="inside">
							<?php include __DIR__ . '/../../views/item/access-fees.php'; ?>
						</div>
					</div>

					<div id="inplayer-asset-preview-template" class="postbox">
						<h2 class="hndle"><span><?php esc_attr_e( 'Asset Preview Template', INPLAYER_TEXT_DOMAIN ); ?></span></h2>
						<div class="inside">
							<?php include __DIR__ . '/../../views/item/description.php'; ?>
							<?php include __DIR__ . '/../../views/item/preview-image.php'; ?>
						</div>
					</div>

				</div> <!-- post-body-content END -->

				<!-- Sidebar Container -->
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box">

						<div id="shortcodediv" class="postbox ">
							<h2 class="hndle"><span><?php esc_attr_e( 'This asset Shortcode:',
										INPLAYER_TEXT_DOMAIN ); ?></span></h2>
							<div class="inside">
								<?php include __DIR__ . '/../../views/item/shortcode.php'; ?>
							</div>
						</div>

						<div id="submitdiv" class="postbox ">
							<h2 class="hndle"><span><?php esc_attr_e( 'Publish Asset:',
										INPLAYER_TEXT_DOMAIN ); ?></span></h2>
							<div class="inside" style="text-align:center;">
								<?php if ( $item_id and ! ( $this->asset['is_active'] ) ): ?>
									<button id="reactivate-asset" data-asset="<?php echo $item_id; ?>" class="button button-primary reactivate-asset">
										<?php esc_attr_e( 'Reactivate Asset', INPLAYER_TEXT_DOMAIN ); ?>
									</button>
								<?php elseif ( $this->asset['is_active'] ): ?>
									<button id="save-asset" class="button button-primary">
										<?php esc_attr_e( 'Publish Asset', INPLAYER_TEXT_DOMAIN ); ?>
									</button>
									<button id="delete-asset" data-asset="<?php echo $item_id; ?>" class="button button-cancel">
										<?php esc_attr_e( 'Delete Asset', INPLAYER_TEXT_DOMAIN ); ?>
									</button>
								<?php else: ?>
									<button id="save-asset" class="button button-primary">
										<?php esc_attr_e( 'Publish Asset', INPLAYER_TEXT_DOMAIN ); ?>
									</button>
								<?php endif; ?>
								<span class="spinner inplayer-spinner ip-edit-spinner"></span>
							</div>
						</div>

					</div>
				</div><!-- Sidebar END -->

			</div><!-- post-body END -->
		</div><!-- poststuff END -->
	</form>
</div> <!-- wrap END -->