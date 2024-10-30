<div class="wrap" id="inplayer-activate-account">
	<div class="notice hidden">
		<p></p>
	</div>

	<h2><?php esc_attr_e( 'Activate Your Merchant Account', INPLAYER_TEXT_DOMAIN ); ?></h2>
	<?php _e( 'text_account_activation_explain', INPLAYER_TEXT_DOMAIN ); ?>

	<form method="post">
		<input type="hidden" name="referrer" value="<?php echo get_site_url(); ?>">
		<input type="hidden" name="action" value="inplayer_activate_account">
		<?php wp_nonce_field( 'inplayer_activate_account' ); ?>

		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Confirmation code', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td>
					<input type="text" name="token" required="required" autocomplete="off"
					       title="<?php esc_attr_e( 'Add your activation code', INPLAYER_TEXT_DOMAIN ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
				</th>
				<td>
					<button id="button-activate-account" class="button button-primary">
						<?php esc_attr_e( 'Activate Your Merchant Account', INPLAYER_TEXT_DOMAIN ); ?>
					</button><span class="spinner inplayer-spinner"></span>
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>