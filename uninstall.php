<?php
/**
 * InPlayer PayWall Uninstall
 *
 * Uninstalling InPlayer PayWall deletes the plugin settings options.
 *
 * @author      InPlayer Team
 * @version     1.0.6
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require __DIR__ . '/includes/InPlayerPlugin.php';

delete_option( InPlayerPlugin::MERCHANT_UUID );
delete_option( InPlayerPlugin::SETTINGS );
delete_option( InPlayerPlugin::AUTH_KEY );

delete_transient( InPlayerPlugin::MERCHANT_EMAIL );