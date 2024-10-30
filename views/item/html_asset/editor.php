<h2 class="hndle"><span><?php esc_attr_e( 'HTML Asset Content', INPLAYER_TEXT_DOMAIN ); ?></span></h2>
<div class="inside">

	<p><?php esc_attr_e( 'Enter the asset you wish to protect in the HTML editor. This can be a video link, text, or any
		embeddable content.', INPLAYER_TEXT_DOMAIN ); ?></p>
	<div class="wrap">
		<?php
		$editor_id = 'html-editor';
		$content   = stripslashes( htmlspecialchars_decode( $this->asset['content'] ) );
		$settings = array( 'tinymce' => false );
		wp_editor( $content, $editor_id, $settings );
		?>
	</div>
</div>