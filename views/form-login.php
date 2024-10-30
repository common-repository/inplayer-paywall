<div class="wrap">
	<h1 class="inplayer-h1"><img src="<?php echo plugins_url( '/assets/img/inplayer-logo.png', __DIR__ ); ?>">Paywall Setup</h1>
	<p>
		<?php echo __('text_login_and_toc', INPLAYER_TEXT_DOMAIN); ?>
	</p>
</div>

<div class="wrap" id="inplayer-platform-login">
	<h2><?php esc_attr_e( 'Login to your InPlayer account', INPLAYER_TEXT_DOMAIN ); ?></h2>
	<p><?php esc_attr_e( 'Do you already have an account? Log in here (you only have to do this once)', INPLAYER_TEXT_DOMAIN ); ?></p>

	<div class="notice hidden">
		<p></p>
	</div>

	<form method="post">
		<input type="hidden" name="action" value="inplayer_platform_login">
		<input type="hidden" name="merchant_uuid" value="<?php echo INPLAYER_UUID; ?>">
		<?php wp_nonce_field( 'inplayer_platform_login' ); ?>

		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Your email', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td><input type="email" name="email" required autocomplete="off"></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Password', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td><input type="password" name="password" required autocomplete="off">
					<p><a href="#forgot-password" id="link-forgot-password">Forgot your password?</a></p></td>

			</tr>
			<tr>
				<th scope="row">
				</th>
				<td>
					<button id="button-login" class="button button-primary">
						<?php esc_attr_e( 'Login', INPLAYER_TEXT_DOMAIN ); ?>
					</button><span class="spinner inplayer-spinner"></span>
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>
