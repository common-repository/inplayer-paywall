<?php

class TransactionsReport
{
    private $total_purchases = [];
    private $gross_amounts   = [];
    private $current_balance = [];

    /**
     * Get the data to populate the transaction Widgets
     */
    public function execute()
    {
        $response = InPlayerPlugin::request('GET', INPLAYER_ACCOUNTING . '/report/transactions');

        if ($response['response']['code'] === 401) {
            delete_option( InPlayerPlugin::AUTH_KEY );
            wp_redirect('admin.php?page=inplayer-login', 301);
            wp_die();
        }

        if ($response['response']['code'] === 403) {
            $message = json_decode($response['body'], true);
            wp_die('<div class="error"><p>' . $message['errors'][403] . '</p></div>', 403);
        }

        if (!isset($response['body']['collection']) || !$response['body']['total']) {
            return $this;
        }

        // extract the report data
        $this->total_purchases = $response['body']['total_purchases'];
        $this->gross_amounts   = $response['body']['total_gross_amounts'];
        $this->current_balance = $response['body']['current_balance'];

        return $this;
    }

    public function get_total_purchases()
    {
        return $this->total_purchases;
    }

    public function get_gross_amounts()
    {
        return $this->gross_amounts;
    }

    public function get_current_balance()
    {
        return $this->current_balance;
    }

}
