<?php
$item_type = isset( $_GET['type'] ) ? $_GET['type'] : '';
$item_id   = isset( $_GET['asset'] ) ? $_GET['asset'] : '';

if ( $item_id ) {
	$response = InPlayerPlugin::request( 'GET', INPLAYER_ASSETS . '/assets/' . $item_id );

	if ( $response['response']['code'] < 400 ) {
		$this->asset = $response['body'];
	}
}

// If item_type is not present load the asset creation editor else load the populated editor
if ( $item_type ) {
	include __DIR__ . '/../../views/item/edit-asset.php';

} else {
	include __DIR__ . '/../../views/item/create-asset.php';
}