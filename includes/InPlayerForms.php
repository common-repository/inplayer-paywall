<?php

final class InPlayerForms
{

    /**
     * Process merchant account registration form.
     *
     */
    public function register_account()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_register_account')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Please try again.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = wp_remote_post(INPLAYER_ACCOUNTS, [
            'httpversion' => '1.1',
            'body'        => [
                'type'                  => 'merchant',
                'full_name'             => $_POST['full_name'],
                'email'                 => $_POST['email'],
                'password'              => $_POST['password'],
                'password_confirmation' => $_POST['password_confirmation'],
                'referrer'              => get_site_url(),
                'merchant_uuid'         => INPLAYER_UUID,
                'client_activate_url'   => admin_url() . '?page=inplayer-activate'
            ]
        ]);

        $response = InPlayerPlugin::handle_error_response($response, true);
        $result   = json_decode($response['body'], true);

        wp_die($result);
    }

    /**
     * Process merchant account activation form.
     *
     */
    public function activate_account()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_activate_account')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode([
                'errors' => [
                    __('Failed to execute. Please try again.', INPLAYER_TEXT_DOMAIN)
                ]
            ]));
        }

        $endpoint = INPLAYER_ACCOUNTS . '/activate/' . $_POST['token'];
        $response = wp_remote_request($endpoint, ['method' => 'PUT', 'httpversion' => '1.1']);
        $this->handle_account_activation_response($response);
    }

    /**
     * Login as merchant to InPlayer Platform.
     */
    public function platform_login()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_platform_login')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Login failed.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = wp_remote_post(INPLAYER_ACCOUNTS . '/login', [
            'httpversion' => '1.1',
            'body'        => [
                'email'         => $_POST['email'],
                'password'      => $_POST['password'],
                'merchant_uuid' => INPLAYER_UUID,
            ]
        ]);

        $this->handle_login_response($response);
    }

    /**
     * Re-sends the activation token to the merchant
     */
    public function resend_activation()
    {
        if (empty($_POST)) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Please try again.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('POST', INPLAYER_ACCOUNTS . '/activate', true, [
            'email'         => $_POST['email'],
            'merchant_uuid' => INPLAYER_UUID,
        ]);

        if ($response['response']['code'] < 400) {
            set_transient(InPlayerPlugin::MERCHANT_EMAIL, $_POST['email']);
            wp_send_json_success($response['body']['explain']);
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Change the merchant InPlayer Password.
     */
    public function platform_change_password()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_change_password')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Password change failed.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('POST', INPLAYER_ACCOUNTS . '/change-password', true, [
            'old_password'          => $_POST['old_password'],
            'password'              => $_POST['password'],
            'password_confirmation' => $_POST['password_confirmation']
        ]);

        if ($response['response']['code'] < 400) {
            wp_send_json_success($response['body']['explain']);
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Merchant request for forgotten InPlayer password.
     */
    public function platform_forgot_password()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_forgot_password')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Password reset request failed.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('POST', INPLAYER_ACCOUNTS . '/forgot-password', true, [
            'email'         => $_POST['email'],
            'site_url'      => $_POST['site_url'],
            'merchant_uuid' => $_POST['merchant_uuid']
        ]);

        if ($response['response']['code'] < 400) {
            wp_send_json_success($response['body']['explain']);
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Reset the Merchants InPlayer password.
     */
    public function platform_reset_password()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_reset_password')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Password reset failed.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('PUT', INPLAYER_ACCOUNTS . '/forgot-password/' . $_POST['token'],
            true, [
                'password'              => $_POST['password'],
                'password_confirmation' => $_POST['password_confirmation']
            ]);

        if ($response['response']['code'] < 400) {
            wp_send_json_success($response['body']);
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Save the Merchant External keys in the InPlayer platform
     */
    public function platform_ovp_ooyala_integration()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_ovp_integration')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Failed to save.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('POST', INPLAYER_ACCOUNTS . '/external', true, [
            'private_key'   => $_POST['ooyala_secret'],
            'public_key'    => $_POST['ooyala_key'],
            'external_type' => $_POST['external_type'],
        ]);

        if ($response['response']['code'] < 400) {
            wp_send_json_success($response['body']['explain']);
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     *  Verify the merchant PayPal account and link it to InPlayer platform.
     */
    public function platform_verify_payment_method()
    {
        if (empty($_POST) || false === check_admin_referer('inplayer_verify_payment_method')) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Payment Method verification failed.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $email = $_POST['email'];

        $response = InPlayerPlugin::request('POST', INPLAYER_PAYMENT . '/method/verify', true, [
            'email'      => $email,
            'first_name' => $_POST['first_name'],
            'last_name'  => $_POST['last_name'],
            'gateway'    => $_POST['gateway'],
            'app_uuid'   => $_POST['application_uuid']
        ]);

        if ($response['response']['code'] == 200) {
            wp_send_json_success($response['body']['message']);
        } else {
            wp_send_json_error($response['body']['errors']['explain']);
        }
    }

    /**
     * Single transaction details popup.
     */
    public function platform_transactions_history()
    {
        require __DIR__ . '/../views/payments/transaction-details.php';
    }

    /**
     * Merchant's payout request from InPlayer platform
     */
    public function platform_payout()
    {
        if (empty($_POST)) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Payment Method verification failed.', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('POST', INPLAYER_ACCOUNTING . '/payout', true, [
            'gateway'      => $_POST['gateway'],
            'amount'       => $_POST['amount'],
            'currency_iso' => $_POST['currency_iso'],
        ]);

        if ($response['response']['code'] < 400) {
            wp_send_json_success($response['body']['message']);
        } else {
            wp_send_json_error($response['body']['errors']['explain']);
        }
    }

    /**
     * Form the InPlayer Asset editor
     */
    public function asset_editor()
    {
        include __DIR__ . '/../views/item/asset-editor.php';
    }

    /**
     * Save the asset in the InPlayer platform
     */
    public function save_asset()
    {
        $payload = $this->prepare_asset_payload_data();
//debug($payload);
        // InPlayer API, upsert everything
        $response = InPlayerPlugin::request('PATCH', INPLAYER_ASSETS . '/asset', true, $payload);

        if ($response['response']['code'] < 400) {
            $url = '&asset=' . $response['body']['id'] . '&type=' . $payload['item_type'];
            wp_send_json_success($url);
        } else {
            // API error
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Deactivates this InPlayer Asset in the Platform
     */
    public function delete_asset()
    {
        $asset_id = $_POST['id'];

        $response = InPlayerPlugin::request('DELETE', INPLAYER_ASSETS . '/' . $asset_id, true);

        if ($response['response']['code'] < 400) {
            wp_send_json_success('Asset successfully deleted');
        } else {
            wp_send_json_error($response['body']['errors']['explain']);
        }
    }

    /**
     * Reactivate this InPlayer Asset in the Platform
     */
    public function reactivate_asset()
    {
        $asset_id = $_POST['id'];

        $response = InPlayerPlugin::request('PUT', INPLAYER_ASSETS . '/reactivate-item/' . $asset_id, true);

        if ($response['response']['code'] < 400) {
            wp_send_json_success('Asset successfully reactivated');
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Form the InPlayer Package editor
     */
    public function package_editor()
    {
        include __DIR__ . '/../views/item/package/package-editor.php';
    }

    /**
     * Save a package in the InPlayer platform
     */
    public function save_package()
    {
        $payload = $this->prepare_package_payload_data();

//		unset($payload['items']);

        $response = InPlayerPlugin::request('POST', INPLAYER_ASSETS . '/packages', true, $payload);

        if ($response['response']['code'] < 400) {
            $url = '&asset=' . $response['body']['id'] . '&type=' . $payload['item_type'];
            wp_send_json_success($url);
        } else {
            // API error
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Update a package in the InPlayer platform
     */
    public function update_package()
    {
        $payload = $this->prepare_package_payload_data();

        $response = InPlayerPlugin::request('PUT', INPLAYER_ASSETS . '/packages/' . $payload['item_id'], true,
            $payload);

        if ($response['response']['code'] < 400) {
            $url = '&asset=' . $response['body']['id'] . '&type=' . $payload['item_type'];
            wp_send_json_success($url);
        } else {
            // API error
            wp_send_json_error($response['body']['errors']);
        }
    }

    /**
     * Deactivates this InPlayer Package in the Platform
     */
    public function delete_package()
    {
        $package_id = $_POST['id'];

        $response = InPlayerPlugin::request('DELETE', INPLAYER_ASSETS . '/packages/' . $package_id, true);

        if ($response['response']['code'] < 400) {
            wp_send_json_success('Package successfully deleted');
        } else {
            wp_send_json_error($response['body']['errors']['explain']);
        }
    }

    /**
     * Reactivate this InPlayer Package in the Platform
     */
    public function reactivate_package()
    {
        $package_id = $_POST['id'];

        $response = InPlayerPlugin::request('PUT', INPLAYER_ASSETS . '/packages/reactivate-package/' . $package_id,
            true);

        if ($response['response']['code'] < 400) {
            wp_send_json_success('Package successfully reactivated');
        } else {
            wp_send_json_error($response['body']['errors']);
        }
    }

    /*
     *
     * Utility methods
     *
     */

    /**
     * Get a value from the loaded asset.
     *
     * @param string $key
     *
     * @return mixed A single value, or the whole asset if $key is empty
     */
    public function asset($key = null)
    {
        if (empty($this->asset)) {
            return '';
        }

        if (empty($key)) {
            return $this->asset;
        }

        return isset($this->asset[$key]) ? $this->asset[$key] : '';
    }

    /**
     * Re-create the response body by adding the redirect URL and status code.
     *
     * @param array  $response
     * @param int    $code
     * @param string $email [optional]
     *
     * @return array
     */
    private function handle_account_activate_redirection($response, $code, $email = '')
    {
        if ((int)$response['response']['code'] === $code) {
            // the account is created, but not activated
            $response['response']['code'] = 302;
            $response['body']             = json_encode(['location' => 'admin.php?page=inplayer-activate']);

            // keep the email temporarily for the activate page
            if ( ! empty($email)) {
                set_transient(InPlayerPlugin::MERCHANT_EMAIL, $email);
            }
        }

        return $response;
    }

    /**
     * @param array|WP_Error $response
     */
    private function handle_account_activation_response($response)
    {
        if ($response instanceof WP_Error) {
            $statusCode = $response->get_error_code();
            $body       = $response->get_error_message();
        } else {
            $statusCode = (int)$response['response']['code'];
            $body       = $response['body'];
        }

        switch ($statusCode) {
            case 201:
                // the account was activated
                $result = json_decode($body, true);
                update_option(InPlayerPlugin::MERCHANT_UUID, $result['uuid']);
                $statusCode = 200;
                break;

            case 422:
                // the token has expired or it's invalid. Request a new one
                $response = wp_remote_post(INPLAYER_ACCOUNTS . '/activate', [
                    'httpversion' => '1.1',
                    'body'        => [
                        'email'         => get_transient(InPlayerPlugin::MERCHANT_EMAIL),
                        'merchant_uuid' => INPLAYER_UUID
                    ]
                ]);

                $statusCode = (int)$response['response']['code'];
                break;
        }

        header('Content-type: application/json', true, $statusCode);
        wp_die($response['body'], $statusCode);
    }

    /**
     * @param array|WP_Error $response
     */
    private function handle_login_response($response)
    {
        if ($response instanceof WP_Error) {
            $statusCode = $response->get_error_code();
            $body       = json_decode($response->get_error_message(), true);
        } else {
            $statusCode = (int)$response['response']['code'];
            $body       = json_decode($response['body'], true);
        }

        $result = array_replace(['access_token' => '', 'account' => ['uuid' => '']], $body);

        if ($statusCode === 409) {
            $msg = json_decode($response['body'], true);
            header('Content-type: application/json', true, 409);
            wp_die(json_encode([
                'errors' => [
                    $msg['errors']['409']
                ],
                'link'   => ' Or, <a href="#activation" data-email="' . $_POST['email'] . '"
                class="platform-resend-activation"> click here</a> to resend your activation code.'
            ]));
        }

        if (empty($result['access_token'])) {
            // no access token sent from the API
            delete_option(InPlayerPlugin::AUTH_KEY);

            header('Content-type: application/json', true, 422);
            wp_die(json_encode([
                'errors' => [
                    __('Sorry, your email or password was incorrect. Please try again.', INPLAYER_TEXT_DOMAIN)
                ]
            ]));
        }

		// use the TTL from the API login response to keep the access token in db
		// used delete_option/add_option instead of update_option since WP versions < 4.2 doesn't support autoload flag on update_option
		delete_option( InPlayerPlugin::AUTH_KEY );
		$auth = array (
			'key' => $result['access_token'],
			'ttl' => $result['expires']
		);
		add_option( InPlayerPlugin::AUTH_KEY, $auth, '', false );
		update_option( InPlayerPlugin::MERCHANT_UUID, $result['account']['uuid'] );

        header('Content-type: application/json', true, $statusCode);
        wp_die();
        exit;
    }

    /**
     * Builds a list of all the published Asset for the active (logged in) merchant
     *
     * @todo need refactor with pagination
     */
    public function assets_shortcodes_list()
    {
        $response = InPlayerPlugin::request('GET',
            INPLAYER_ASSETS . '/' . get_option(InPlayerPlugin::MERCHANT_UUID) . '?limit=400');

        if (($response['body']['collection']) and ($response['response']['code'] < 400)) {
            require __DIR__ . '/../views/item/shortcodes-list.php';
        } else {
            echo '<h4>' . __('No Assets Found', INPLAYER_TEXT_DOMAIN) . '</h4>';
        }
        wp_die();
    }


    /**
     * Validates and prepares the posted form data for the InPlayer API.
     *
     * @param WP_Post $post The post object
     *
     * @return array
     */
    private function prepare_asset_payload_data()
    {

        $access_fees = [];
        for ($i = 1, $c = count($_POST['access_fee']['description']); $i < $c; $i++) {
            // validate
            if (empty($_POST['access_fee']['type'][$i])
                || empty($_POST['access_fee']['description'][$i])
                || empty($_POST['access_fee']['currency'][$i])
            ) {
                InPlayerAdmin::flash_message(__('Some access fee values are invalid.'), 'error');
            }

			$access_fees[] = [
				'id'             => $_POST['access_fee']['id'][ $i ],
				'access_type_id' => $_POST['access_fee']['type'][ $i ],
				'description'    => $_POST['access_fee']['description'][ $i ],
				'amount'         => (float) ( $_POST['access_fee']['amount'][ $i ] ),
				'currency'       => $_POST['access_fee']['currency'][ $i ],
			];
		}

        return [
            'item_id'     => $_POST['id'],
            'title'       => isset($_POST['post_title']) ? $_POST['post_title'] : 'no title',
            'item_type'   => isset($_POST['asset_type']) ? $_POST['asset_type'] : 'html_asset',
            'content'     => urldecode(isset($_POST['asset_content']) ? $_POST['asset_content'] : ''),
            'metadata'    => [
                'client_app'          => INPLAYER_PLUGIN_UUID, // @todo not implemented yet in the API
                'preview_title'       => $_POST['post_title'],
                'preview_description' => $_POST['asset_description'],
                'preview_image'       => $_POST['asset_image'],
            ],
            'access_fees' => $access_fees,
        ];
    }

    /**
     * Validates and prepares the posted form data for the InPlayer API.
     *
     * @param WP_Post $post The post object
     *
     * @return array
     */
    private function prepare_package_payload_data()
    {

        $access_fees = [];
        for ($i = 1, $c = count($_POST['access_fee']['description']); $i < $c; $i++) {
            // validate
            if (empty($_POST['access_fee']['type'][$i])
                || empty($_POST['access_fee']['description'][$i])
                || empty($_POST['access_fee']['currency'][$i])
            ) {
                InPlayerAdmin::flash_message(__('Some access fee values are invalid.'), 'error');
            }

            $access_fees[] = [
                'id'             => $_POST['access_fee']['id'][$i],
                'access_type_id' => $_POST['access_fee']['type'][$i],
                'description'    => $_POST['access_fee']['description'][$i],
                'amount'         => (float)($_POST['access_fee']['amount'][$i]),
                'currency'       => $_POST['access_fee']['currency'][$i],
            ];
        }

        $result = [
            'item_id'     => $_POST['id'],
            'title'       => isset($_POST['post_title']) ? $_POST['post_title'] : 'no title',
            'item_type'   => 'package',
            'content'     => urldecode(isset($_POST['post_content']) ? $_POST['post_content'] : ''),
            'access_fees' => $access_fees,
        ];

        if ( ! empty($_POST['new-assets'])) {
            $result['items'] = explode(',', rtrim($_POST['new-assets'], ','));
        }

        return $result;
    }

    /**
     * Remove merchants access fee.
     */
    public function platform_remove_access_fee()
    {
        if (empty($_POST)) {
            header('Content-type: application/json', true, 500);
            wp_die(json_encode(['errors' => [__('Access Fee removal failed', INPLAYER_TEXT_DOMAIN)]]));
        }

        $response = InPlayerPlugin::request('DELETE', INPLAYER_ASSETS . '/access-fees/' . $_POST['id'], true);

        if ($response['response']['code'] < 400) {
            wp_send_json_success($response['body']);
        } else {
            wp_send_json_error($response['body']);
        }
    }

    /**
     * Used in views/item/access-fees.php
     *
     * @param array  $currencies
     * @param string $selected
     *
     * @return string
     */
    private function build_currency_combobox_options(array $currencies, $selected = '')
    {
        $options = '';
        foreach ($currencies as $iso => $name) {
            if ($selected && $selected === $iso) {
                $options .= '<option value="' . $iso . '" selected="selected">' . $name . '</option>';
            } else {
                $options .= '<option value="' . $iso . '">' . $name . '</option>';
            }
        }

        return $options;
    }

    /**
     * Used in views/item/access-fees.php
     *
     * @param array  $types
     * @param string $selected
     *
     * @return string
     */
    private function build_access_types_combobox_options(array $types, $selected = '')
    {
        $options = '';
        foreach ($types as $type) {
            $name = $type['quantity'] . ' ' . $type['period'];
            if ($selected && ((int)$selected === (int)$type['id'])) {
                $options .= '<option value="' . $type['id'] . '" selected="selected">' . $name . '</option>';
            } else {
                $options .= '<option value="' . $type['id'] . '">' . $name . '</option>';
            }
        }

        return $options;
    }
}
