<?php
$payment_method = InPlayerPlugin::request('GET', INPLAYER_PAYMENT . '/method/verify?gateway=paypal');

if ($payment_method['response']['code'] >= 400): ?>
    <div class="notice notice-info is-dismissible inplayer-notice"><p><b><?php esc_attr_e('Please note!', INPLAYER_TEXT_DOMAIN) ?></b>
    <?php esc_attr_e('text_connect_to_paypal', INPLAYER_TEXT_DOMAIN) ?></p></div>
<?php endif; ?>

<div class="wrap" id="inplayer-verify-payment-method">
    <div class="notice hidden">
        <p></p>
    </div>

    <h2><?php esc_attr_e('Connect your PayPal account', INPLAYER_TEXT_DOMAIN); ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="inplayer_verify_payment_method">
        <input type="hidden" name="application_uuid" value="<?php echo INPLAYER_PLUGIN_UUID; ?>">
        <input type="hidden" name="gateway" value="paypal">
        <?php wp_nonce_field( 'inplayer_verify_payment_method' ); ?>
        <table>
            <tbody>
            <tr>
                <th scope="row">
                    <?php esc_attr_e('PayPal account email', INPLAYER_TEXT_DOMAIN); ?>
                </th>
                <td>
                    <?php if (isset($payment_method['body']['email'])): ?>
                        <span class="dashicons dashicons-yes inplayer-ok"></span>
                    <?php endif; ?>

                    <input type="email" name="email" autocomplete="off" size="30"
                           placeholder="<?php echo __( 'Please enter your valid PayPal account' ); ?>"
                           value="<?php echo $payment_method['body']['email'] ?: ''; ?>" required>
                    <p><?php esc_attr_e( 'This has to be an active PayPal account email', INPLAYER_TEXT_DOMAIN ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_attr_e('First Name', INPLAYER_TEXT_DOMAIN); ?>
                </th>
                <td>
                    <input type="text" name="first_name" autocomplete="off" size="30" required value="<?php echo $payment_method['body']['first_name'] ?: ''; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_attr_e('Last Name', INPLAYER_TEXT_DOMAIN); ?>
                </th>
                <td>
                    <input type="text" name="last_name" autocomplete="off" size="30" required value="<?php echo $payment_method['body']['last_name'] ?: ''; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                </th>
                <td>
                    <button id="button-verify-payment_method" class="button button-primary">
                        <?php esc_attr_e('Connect account', INPLAYER_TEXT_DOMAIN); ?>
                    </button><span class="spinner inplayer-spinner"></span>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<?php unset($payment_method); ?>