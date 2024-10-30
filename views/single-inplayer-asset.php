<?php

global $post, $inplayer;

$consumer_token = $inplayer->consumer_token();

if (!empty($consumer_token)) {
	$asset_id = get_post_meta( $post->ID, INPLAYER_ASSET_ID, true );
	$endpoint = INPLAYER_ASSETS . '/' . $asset_id . '/access';
	$response = InPlayerPlugin::request( 'GET', $endpoint, false, [], [ 'Authorization' => 'Bearer ' . $consumer_token ], false );

	if (!isset($response['body']['errors'])) {
		if ( have_posts() ) {
			while ( have_posts() ) : the_post();
				the_content();
			endwhile;
		}
	}
}
unset($consumer_token, $asset_id, $endpoint, $response);