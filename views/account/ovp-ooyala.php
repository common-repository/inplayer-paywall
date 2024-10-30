<?php
$response = InPlayerPlugin::request( 'GET', INPLAYER_ACCOUNTS . '/external/ooyala', true );

?>
<hr>
<div class="wrap" id="inplayer-ovp-ooyala">
	<h2><?php esc_attr_e( 'OVP Accounts', INPLAYER_TEXT_DOMAIN ); ?></h2>
	<h3><?php esc_attr_e( 'Connect your Ooyala account', INPLAYER_TEXT_DOMAIN ); ?></h3>
	<form method="post">
		<input type="hidden" name="action" value="inplayer_ovp_ooyala">
		<input type="hidden" name="external_type" value="ooyala">
		<?php wp_nonce_field( 'inplayer_ovp_integration' ); ?>
		<table>
			<tbody>
			<tr>
				<th scope="row"><?php esc_attr_e( 'API Secret', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td>
					<p><input type="password" id="ooyala-secret" required autocomplete="off" size="50"
					          name="ooyala_secret" placeholder="Please enter your Ooyala API secret here" value="<?php echo $response['body']['private_key']; ?>">
						<button class="show-input" data-toggle="on">
							<span class="ip-sp-show"><?php esc_attr_e( 'Show', INPLAYER_TEXT_DOMAIN ); ?></span>
							<span class="ip-sp-hide" style="display:none;"><?php esc_attr_e( 'Hide', INPLAYER_TEXT_DOMAIN ); ?></span>
						</button>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_attr_e( 'API Key', INPLAYER_TEXT_DOMAIN ); ?></th>
				<td>
					<p><input type="password" id="ooyala_key" required autocomplete="off" size="50" name="ooyala_key"
					          placeholder="Please enter your Ooyala APi key here" value="<?php echo $response['body']['public_key']; ?>">
						<button class="show-input" data-toggle="on">
							<span class="ip-sp-show"><?php esc_attr_e( 'Show', INPLAYER_TEXT_DOMAIN ); ?></span>
							<span class="ip-sp-hide" style="display:none;"><?php esc_attr_e( 'Hide', INPLAYER_TEXT_DOMAIN ); ?></span>
						</button>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<p>
						<button id="button-ovp-ooyala" class="button button-primary">
							<?php esc_attr_e( 'Update', INPLAYER_TEXT_DOMAIN ); ?>
						</button>
						<span class="spinner inplayer-spinner"></span></p>
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>