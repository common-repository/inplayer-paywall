<div class="wrap" hidden id="inplayer-forgot-password">
	<hr>
	<h2><?php esc_attr_e( 'Forgot password', INPLAYER_TEXT_DOMAIN ); ?></h2>

	<form method="post">
		<input type="hidden" name="action" value="inplayer_forgot_password">
		<input type="hidden" name="merchant_uuid" value="<?php echo INPLAYER_UUID; ?>">
		<?php wp_nonce_field( 'inplayer_forgot_password' ); ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<?php esc_attr_e( 'Your Merchant Email Address', INPLAYER_TEXT_DOMAIN ); ?>
				</th>
				<td><input type="email" name="email" autocomplete="on" size="40"
				           placeholder="<?php esc_attr_e( 'Please enter your merchant email address', INPLAYER_TEXT_DOMAIN ); ?>" required></td>
			</tr>
			<tr>
				<th scope="row">
				</th>
				<td>
					<button id="button-forgot-password" class="button button-primary">
						<?php esc_attr_e( 'Reset password', INPLAYER_TEXT_DOMAIN ); ?>
					</button><span class="spinner inplayer-spinner"></span>
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>

<?php if (isset($_GET['reset'])): ?>
	<div class="wrap" id="inplayer-reset-password">
		<hr>
		<h2><?php esc_attr_e( 'Reset password', INPLAYER_TEXT_DOMAIN ); ?></h2>

		<form method="post">
			<input type="hidden" name="action" value="inplayer_reset_password">
			<?php wp_nonce_field( 'inplayer_reset_password' ); ?>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">
						<?php esc_attr_e( 'Token', INPLAYER_TEXT_DOMAIN ); ?>
					</th>
					<td><input type="text" name="token" autocomplete="off" size="40"
					           placeholder="<?php esc_attr_e( 'Please enter the reset token', INPLAYER_TEXT_DOMAIN ); ?>" required>
						<p><?php esc_attr_e( 'Please check your email', INPLAYER_TEXT_DOMAIN ); ?></p></td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_attr_e( 'Your New Password', INPLAYER_TEXT_DOMAIN ); ?>
					</th>
					<td><input type="password" name="password" autocomplete="off" size="40"
							   placeholder="<?php esc_attr_e( 'Please enter your new password', INPLAYER_TEXT_DOMAIN ); ?>" required></td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_attr_e( 'Confirm Your New Password', INPLAYER_TEXT_DOMAIN ); ?>
					</th>
					<td><input type="password" name="password_confirmation" autocomplete="off" size="40"
							   placeholder="<?php esc_attr_e( 'Please confirm your enter your new password', INPLAYER_TEXT_DOMAIN ); ?>" required></td>
				</tr>
				<tr>
					<th scope="row">
					</th>
					<td>
						<button id="button-reset-password" class="button button-primary">
							<?php esc_attr_e( 'Reset password', INPLAYER_TEXT_DOMAIN ); ?>
						</button><span class="spinner inplayer-spinner"></span>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
<?php endif; ?>
