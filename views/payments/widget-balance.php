<div id="inplayer-3" class="postbox">
    <h2 class="hndle"><span><?php esc_attr_e('Available Balance', INPLAYER_TEXT_DOMAIN); ?></span></h2>
    <table class="wp-list-table widefat stripped">
        <thead></thead>
        <tbody>
        <?php $balances = $transactions->get_current_balance();
        if (empty($balances)):
            echo '<tr><td>' . __('No Purchases', INPLAYER_TEXT_DOMAIN) . '</td></tr>';
        else:
            foreach ($balances as $currency_iso => $current_balance):
                $current_balance = number_format($current_balance, 2, '.', '');
                ?>
                <tr>
                    <td><?php echo $currency_iso; ?></td>
                    <td><?php echo $current_balance; ?></td>
                    <td style="text-align:right;">
                        <form method="post">
                            <input type="hidden" name="action" value="inplayer_platform_payout">
                            <span class="spinner inplayer-spinner"></span>
                            <a href="<?php echo admin_url('admin.php?page=inplayer-settings'); ?>" class="button button-primary"><?php esc_attr_e('Withdraw', INPLAYER_TEXT_DOMAIN); ?></a>
                        </form>
                    </td>
                </tr>
            <?php
            endforeach;
        endif;
        unset($balances, $currency_iso, $current_balance);
        ?>
        </tbody>
    </table>
</div>