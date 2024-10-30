<div class="wrap inplayer-settings">
	<h2>InPlayer PayWall: <?php esc_attr_e( 'General Settings', INPLAYER_TEXT_DOMAIN ) ?></h2>
	<?php
	settings_errors();

	$tabs = [
		'payments' => __( 'Receiving Payments', INPLAYER_TEXT_DOMAIN ),
		'account'  => __( 'Accounts', INPLAYER_TEXT_DOMAIN ),
		'about'    => __( 'About', INPLAYER_TEXT_DOMAIN )
	];

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'payments';
	if ( ! array_key_exists( $active_tab, $tabs ) ):
		$active_tab = 'payments';
	endif;
	?>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab => $label ):
			$class = $active_tab == $tab ? ' nav-tab-active' : '';
			echo '<a href="?page=inplayer&tab=' . $tab . '" class="nav-tab' . $class . '">' . $label . '</a>';
		endforeach ?>
	</h2>
	<?php if ( $active_tab == 'payments' ):
		settings_fields( 'inplayer-payments' );
		do_settings_sections( 'inplayer-payments' );
	elseif ( $active_tab == 'account' ):
		settings_fields( 'inplayer-account' );
		do_settings_sections( 'inplayer-account' );
	elseif ( $active_tab == 'about' ):
		do_settings_sections( 'inplayer-about' );
	endif; ?>
</div>
