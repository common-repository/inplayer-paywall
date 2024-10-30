<?php

const INPLAYER_UUID          = '3b39b5ab-b5fc-4ba3-b770-73155d20e61f';
const INPLAYER_TEXT_DOMAIN   = 'inplayer-paywall';
const INPLAYER_ASSET_ID      = 'inplayer-asset-id';
const INPLAYER_PLUGIN_UUID   = '1';

const INPLAYER_FLASH_MESSAGE = 'inplayer_flash_message';
const INPLAYER_ASSET_TYPE    = 'inplayer-asset-type';

const INPLAYER_PAYMENT	     = 'https://services.inplayer.com/payments';
const INPLAYER_ASSETS        = 'https://services.inplayer.com/items';
const INPLAYER_ACCOUNTS      = 'https://services.inplayer.com/accounts';
const INPLAYER_ACCOUNTING    = 'https://services.inplayer.com/accounting';
const INPLAYER_INJECTOR      = 'https://assets.inplayer.com/injector/latest';
const INPLAYER_STOMP         = 'https://notify.inplayer.com:15671/stomp';

require __DIR__ . '/includes/InPlayerForms.php';
require __DIR__ . '/includes/InPlayerPlugin.php';
$inplayer = ( new InPlayerPlugin )->register();

register_activation_hook( __FILE__, [ $inplayer, 'activate' ] );
register_deactivation_hook( __FILE__, [ $inplayer, 'deactivate' ] );

if ( is_admin() ) {
    require __DIR__ . '/includes/TransactionsReport.php';
    require __DIR__ . '/includes/InPlayerAdmin.php';
    ( new InPlayerAdmin( $inplayer, new InPlayerForms ) )->register();
}