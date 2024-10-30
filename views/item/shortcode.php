<?php
/** @var InPlayerForms $this */
if ( $asset_id = $this->asset( 'id' ) and $this->asset('is_active') ): ?>
	<code>[inplayer id="<?php echo $asset_id; ?>"]</code>

	<?php add_thickbox(); ?>

	<a href="#TB_inline?width=300&height=250&inlineId=inplayer_shortcode_options"
	   name="<?php echo esc_attr__( 'Shortcode Options', INPLAYER_TEXT_DOMAIN ); ?>" id="inplayer_shortcode_option"
	   class="thickbox">
		<?php echo esc_attr__( 'Options', INPLAYER_TEXT_DOMAIN ); ?>
	</a>
	<div id="inplayer_shortcode_options" style="display:none;">
		<p><?php echo esc_attr__( 'text_shortcode_copy_explain', INPLAYER_TEXT_DOMAIN ); ?></p>
		<p><code>[inplayer id="<?php echo $asset_id; ?>"]</code></p>
		<a href="#" class="button" id="inplayer_shortcode_close" onclick="self.parent.tb_remove(); return false;">
			<?php echo esc_attr__( 'Done', INPLAYER_TEXT_DOMAIN ); ?>
		</a>
	</div>

<?php else: ?>
	<p class="error-message">
		<?php echo esc_attr__( 'The InPlayer asset had been deleted/not yet created.', INPLAYER_TEXT_DOMAIN ); ?>
	</p>
	<?php unset( $asset_id ); endif; ?>
