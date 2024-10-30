<?php
$response = InPlayerPlugin::request( 'GET', INPLAYER_ASSETS . '/ovp/ooyala/assets', true );
if ( $response['response']['code'] < 400 ):
	$assets = $response['body']['assets'];
	$retrivedContent = json_decode( htmlspecialchars_decode( $this->asset( 'content' ) ) );
?>

<h2 class="hndle"><span><?php esc_attr_e( 'Your Ooyala Asset', INPLAYER_TEXT_DOMAIN ); ?></span></h2>
<div class="inside">
	<form method="post">
		<input type="hidden" name="pcode" id="pcode" value="<?php echo $response['body']['pcode']; ?>">

		<label for="ooyala-video"><?php esc_attr_e( 'Choose your video from this list',	INPLAYER_TEXT_DOMAIN ); ?></label>
		<select id="ooyala-video">
			<option>-- <?php esc_attr_e( 'please select a video', INPLAYER_TEXT_DOMAIN ); ?> --</option>
			<?php foreach ( $assets as $asset ): ?>
				<option <?php if ( $retrivedContent->name == $asset['name'] ) echo 'selected'; ?> data-content='<?php echo json_encode( $asset ); ?>'>
					<?php echo $asset['name']; ?>
				</option>
			<?php endforeach; ?>
		</select>


	</form>

</div>
<?php else: ?>
	<h2><?php esc_attr_e( 'In order to create Ooyala asset please check your OVP Accounts in InPlayer Paywall Settings – Accounts' , INPLAYER_TEXT_DOMAIN ); ?></h2>
<?php endif; ?>