<div id="inplayer-2" class="postbox">
    <h2 class="hndle"><span><?php esc_attr_e('Total Gross Transactions', INPLAYER_TEXT_DOMAIN); ?></span></h2>
    <table class="wp-list-table widefat fixed stripped">
        <thead></thead>
        <tbody>
        <?php $records = $transactions->get_gross_amounts();
        if (empty($records)):
            echo '<tr><td>' . __('No Transactions', INPLAYER_TEXT_DOMAIN) . '</td></tr>';
        else:
            foreach ($transactions->get_gross_amounts() as $amount):
                echo '<tr><td>' . $amount['currency_iso'] . '</td><td style="float:right;">' . number_format($amount['amount'], 2, '.', '') . '</td></tr>';
            endforeach;
        endif;
        unset($records); ?>
        </tbody>
    </table>
</div>