<div class="wrap" id="inplayer-change-password">
    <div class="notice hidden">
        <p></p>
    </div>
    <h2><?php esc_attr_e( 'InPlayer Account', INPLAYER_TEXT_DOMAIN ); ?></h2>
    <h3><?php esc_attr_e( 'Change your password'); ?></h3>
    <form method="post">
        <input type="hidden" name="action" value="inplayer_change_password">
        <?php wp_nonce_field( 'inplayer_change_password' ); ?>
        <table>
            <tbody>
            <tr>
                <th scope="row">
                    <?php esc_attr_e('Your Password', INPLAYER_TEXT_DOMAIN); ?>
                </th>
                <td>
                    <input type="password" name="old_password" autocomplete="off" size="30"
                           placeholder="<?php esc_attr_e('Please enter your old password', INPLAYER_TEXT_DOMAIN); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_attr_e('New Password', INPLAYER_TEXT_DOMAIN); ?></th>
                <td><input id="password" type="password" name="password" required autocomplete="off" pattern=".{8,}"
                           size="30"
                           placeholder="<?php esc_attr_e('Please enter a new password', INPLAYER_TEXT_DOMAIN); ?>"
                           title="<?php esc_attr_e('Minimum 8 characters', INPLAYER_TEXT_DOMAIN); ?>">
                    <p><?php esc_attr_e('Minimum 8 characters', INPLAYER_TEXT_DOMAIN); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_attr_e('Password Confirmation', INPLAYER_TEXT_DOMAIN); ?></th>
                <td><input id="password_confirmation" type="password" name="password_confirmation" required
                           autocomplete="off" pattern=".{8,}"
                           placeholder="<?php esc_attr_e('Please confirm your new password', INPLAYER_TEXT_DOMAIN); ?>"
                           size="30"
                           title="<?php esc_attr_e('Please enter the same password as above',
                               INPLAYER_TEXT_DOMAIN); ?>">
                    <p><?php esc_attr_e('Please re-enter your password', INPLAYER_TEXT_DOMAIN); ?></p></td>
            </tr>


            <tr>
                <th scope="row">
                </th>
                <td>
                    <button id="button-change-password" class="button button-primary">
                        <?php esc_attr_e('Change password', INPLAYER_TEXT_DOMAIN); ?>
                    </button><span class="spinner inplayer-spinner"></span>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
