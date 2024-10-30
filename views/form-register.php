<hr>

<div class="wrap" id="inplayer-register-account">
	<h2><?php esc_attr_e( 'Create your InPlayer account', INPLAYER_TEXT_DOMAIN ); ?></h2>

	<form method="post">
		<input type="hidden" name="action" value="inplayer_register_account">
		<?php wp_nonce_field( 'inplayer_register_account' ); ?>

		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Your full name', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td><input type="text" name="full_name" required autocomplete="off" maxlength="250">
					<p><?php esc_attr_e( 'Enter your full name', INPLAYER_TEXT_DOMAIN ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Your email', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td><input type="email" name="email" required autocomplete="off">
					<p><?php esc_attr_e( 'Enter your email address', INPLAYER_TEXT_DOMAIN ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Choose password', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td><input type="password" name="password" required autocomplete="off" pattern=".{8,}"
				           title="<?php esc_attr_e( 'Minimum 8 characters', INPLAYER_TEXT_DOMAIN ); ?>"
				           onchange="this.setCustomValidity(this.validity.patternMismatch ? this.title ? '');
                           if (this.checkValidity()) { form.password_confirm = this.value; }">
					<p><?php esc_attr_e( 'Minimum 8 characters', INPLAYER_TEXT_DOMAIN ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_attr_e( 'Confirm your password', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td><input type="password" name="password_confirmation" required autocomplete="off" pattern=".{8,}"
				           title="<?php esc_attr_e( 'Please enter the same password as above', INPLAYER_TEXT_DOMAIN ); ?>"
				           onchange="this.setCustomValidity(this.validity.patternMismatch ? this.title ? '');">
					<p><?php esc_attr_e( 'Please re-enter your password', INPLAYER_TEXT_DOMAIN ); ?></p></td>
			</tr>
			<tr>
				<th scope="row">
				</th>
				<td>
					<button id="button-register-account" class="button button-primary">
						<?php esc_attr_e( 'Create Account', INPLAYER_TEXT_DOMAIN ); ?>
					</button><span class="spinner inplayer-spinner"></span>
				</td>
			</tr>
			</tbody>
		</table>

	</form>
</div>