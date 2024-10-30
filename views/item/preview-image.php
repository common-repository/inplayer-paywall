<?php
if ( ! did_action( 'wp_enqueue_media' ) ) {
	wp_enqueue_media();
}
?>

<div>
	<h4><?php echo esc_attr__( 'Asset Preview Image', INPLAYER_TEXT_DOMAIN ); ?></h4>
	<p><?php echo __( 'text_preview_image_explain', INPLAYER_TEXT_DOMAIN ); ?></p>

	<input type="text" id="asset-image" size="30" name="asset_image" autocomplete="off"
	       placeholder="<?php echo esc_attr__( 'Attach a preview image' ); ?>"
	       value="<?php if ( $image = $this->asset( 'metadata' ) ) echo $image['preview_image']; ?>">
	<input type="button" id="upload_image_button" value="<?php echo __( 'Upload Image' ); ?>" class="button">
</div>