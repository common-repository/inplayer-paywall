<?php
$labels    = [
    'charge'                => __( '1-time', INPLAYER_TEXT_DOMAIN ),
    'subscription'          => __( 'Subscriptions', INPLAYER_TEXT_DOMAIN ),
    'refund'                => __( 'Refund', INPLAYER_TEXT_DOMAIN ),
    'free-trial'            => __( 'Free trial', INPLAYER_TEXT_DOMAIN ),
    'recurrent'             => __( 'Recurrent', INPLAYER_TEXT_DOMAIN ),
    'store-payment'         => __( 'Store payment', INPLAYER_TEXT_DOMAIN )
];
$purchases = $transactions->get_total_purchases();
unset( $purchases['payout'] );
?>
    <div id="inplayer-1" class="postbox">
        <h2 class="hndle"><span><?php esc_attr_e( 'Total Purchases', INPLAYER_TEXT_DOMAIN ); ?></span></h2>
        <table class="wp-list-table widefat stripped">
            <thead></thead>
            <tbody>
            <?php
            if ( empty( $purchases ) ):
                echo '<tr><td>' . __( 'No Purchases', INPLAYER_TEXT_DOMAIN ) . '</td></tr>';
            else:
                foreach ( $purchases as $type => $count ):
                    echo '<tr><td>' . (isset($labels[ $type ]) ? $labels[ $type ] : $type) . '</td><td style="float:right;">' . $count . '</td></tr>';
                endforeach;
            endif; ?>
            </tbody>
        </table>
    </div>
<?php unset( $purchases, $type, $count ); ?>