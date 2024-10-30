<?php
/*
Plugin Name: InPlayer - PayWall Plugin
Plugin URI:  https://plugins.svn.wordpress.org/inplayer-paywall/
Description: Plugin for integration with InPlayer platform with PayWall application.
Author:      InPlayer Team
Author URI:  https://inplayer.com
Version:     1.0.6
License:     GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: inplayer-paywall
Domain Path: /languages
*/

// Check for required PHP version
if ( version_compare( PHP_VERSION, '5.4', '<' ) )
{
    exit( sprintf( 'The InPlayer Paywall plugin requires PHP 5.4 or higher. You’re using %s.', PHP_VERSION ) );
}

require 'inplayer.php';