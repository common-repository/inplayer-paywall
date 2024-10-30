/* InPlayer Administration Pages */

+function ($) {

    'use strict';

    if (typeof notifications != 'undefined') {
        var socket = new SockJS(notifications.stomp.url, null, {}),
            client = new Stomp.over(socket),
            onmessage = function (message) {
                var body = JSON.parse(message.body),
                    clicked = $('input#' + body.currency_iso);

                if (body.hasOwnProperty('errors')) {
                    var msg = body.errors.hasOwnProperty('explain') ? body.errors.explain : 'Server error. Please try again.';
                    resetSpinner(clicked);
                    showErrorNotice($('.notice'), msg);
                } else if (body.state) {
                    handleInplayerNotification(body);
                } else {
                    resetSpinner(clicked);
                    showSuccessNotice($('.notice'), 'Successful payout: ' + body.currency_iso + ' ' + body.amount + '. ' + body.message);
                }

                message.ack();
            };

        client.heartbeat.outgoing = 0;
        client.heartbeat.incoming = 0;
        client.debug = null;

        client.connect({
            login: notifications.stomp.login,
            passcode: notifications.stomp.password,
            'client-id': notifications.uuid
        }, function () {
            client.subscribe('/exchange/notifications/' + notifications.uuid, onmessage, {
                id: notifications.uuid,
                ack: 'client'
            });

        }, function (frame) {
            if (typeof frame !== 'string') {
                console.warn('Stomp error: ', frame);
            }
        });
    }

    /**
     * @api POST /accounts
     * Uses the InPlayer Service Accounts endpoint to create a new merchant account.
     */
    $('#button-register-account').on('click', function (event) {
        event.preventDefault();

        var $form = $('form', '#inplayer-register-account'),
            $notice = $('.notice');

        $('.spinner', '#inplayer-register-account').addClass('is-active');
        $('#button-register-account').prop('disabled', true);

        $form.find('input').removeClass('error-border');

        $.ajax({
            action: 'inplayer_register_account',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $form.serializeArray()
        }).done(function (res) {
            $('.spinner', '#inplayer-register-account').removeClass('is-active');
            $('#button-register-account').prop('disabled', false);

            if ('0' === res) {
                scrollToTop();
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else {
                showSuccessNotice($notice, '<strong>Account registered.</strong> Please check the provided email to activate your account.');
                setTimeout(function () {
                    window.location.replace('admin.php?page=inplayer-activate');
                }, 2000);
            }
        }).fail(function (xhr) {
            $('.spinner', '#inplayer-register-account').removeClass('is-active');
            $('#button-register-account').prop('disabled', false);

            if (xhr.status === 302) {
                return window.location.replace(xhr.responseJSON.location);
            }

            var $errors = [], $el;

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                    if ($el = $('input[name=' + k + ']', $form)) {
                        $el.addClass('error-border');
                    }
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                scrollToTop();
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * @api PUT /accounts/activate/<token>
     * Uses the InPlayer Service Accounts endpoint to activate the merchant account.
     */
    $('#button-activate-account').on('click', function (event) {
        event.preventDefault();

        var $form = $('form', '#inplayer-activate-account'),
            $notice = $('.notice', '#inplayer-activate-account'),
            data = $form.serializeArray();

        $('.spinner', '#inplayer-activate-account').addClass('is-active');
        $('#button-activate-account').prop('disabled', true);

        $form.find('input').removeClass('error-border');

        $.ajax({
            action: 'inplayer_activate_account',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: data
        }).done(function (res, statusText, xhr) {
            var statusCode = ~~xhr.status;

            $('.spinner', '#inplayer-activate-account').removeClass('is-active');
            $('#button-activate-account').prop('disabled', false);

            if ('0' === res) {
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (statusCode == 201) {
                showSuccessNotice($notice, xhr.responseJSON.explain);
            } else {
                showSuccessNotice($notice, '<strong>Account activated.</strong> Congratulations, you can now login and start to use the InPlayer Platform.');
                setTimeout(function () {
                    window.location.replace('admin.php?page=inplayer-login');
                }, 3000);
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.spinner', '#inplayer-activate-account').removeClass('is-active');
            $('#button-activate-account').prop('disabled', false);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * @api POST /account/login
     * Performs the InPlayer platform login.
     */
    $('#button-login').on('click', function (event) {
        event.preventDefault();

        var $notice = $('.notice');

        $('.spinner', '#inplayer-platform-login').addClass('is-active');
        $('#button-login').prop('disabled', true);

        $.ajax({
            action: 'inplayer_platform_login',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $('form', '#inplayer-platform-login').serializeArray()
        }).done(function (res, statusText, xhr) {
            var statusCode = ~~xhr.status;

            $('.spinner', '#inplayer-platform-login').removeClass('is-active');
            $('#button-login').prop('disabled', false);

            if ('0' === res) {
                scrollToTop();
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (statusCode === 200) {
                showSuccessNotice($notice, '<strong>Logged in.</strong>');
                return window.location.replace('admin.php?page=inplayer-settings');
            }
        }).fail(function (xhr) {
            var activationLink = '';

            $('.spinner', '#inplayer-platform-login').removeClass('is-active');
            $('#button-login').prop('disabled', false);

            if (xhr.status === 302) {
                return window.location.replace(xhr.responseJSON.location);
            }
            if (xhr.status === 409) {
                activationLink = xhr.responseJSON.link;
            }
            var $errors = [];

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                scrollToTop();
                showErrorNotice($notice, $errors.join(', ') + activationLink);
            }
        });
    });

    /**
     * Return the generated shortcode for the selected Asset into the WordPress visual editor
     */
    $(document).on('click', '.return_shortcode', function () {
        var id = $(this).data('id');
        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, '[inplayer id="' + id + '"]');
        tb_remove();
    });

    /**
     * @api POST /accounts/activate
     * Re-send activation code
     */
    $(document).on("click", ".platform-resend-activation", function (event) {
        event.preventDefault();
        var $notice = $('.notice'),
            msg = '';

        $.ajax({
            action: 'inplayer_resend_activation',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: {
                'action': 'inplayer_resend_activation',
                'email': $('.platform-resend-activation').data('email')
            }
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                setTimeout(function () {
                    window.location.replace('admin.php?page=inplayer-activate');
                }, 2000);
                showSuccessNotice($notice, data.data);
            } else {
                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                scrollToTop();
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * Toggle the InPlayer Forgot Password form
     */
    $('#link-forgot-password').on('click', function () {
        event.preventDefault();

        $('#inplayer-forgot-password').toggle();
        $('#link-forgot-password').toggle();

    });

    /**
     * Merchant request for forgotten password
     */
    $('#button-forgot-password').on('click', function () {
        event.preventDefault();
        var $notice = $('.notice'),
            $dataToSend = $('form', '#inplayer-forgot-password'),
            msg = '';

        $('.spinner', '#inplayer-forgot-password').addClass('is-active');
        $('#button-forgot-password').prop('disabled', true);

        $.ajax({
            action: 'inplayer_forgot_password',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            $('.spinner', '#inplayer-forgot-password').removeClass('is-active');
            $('#button-forgot-password').prop('disabled', false);

            if ('0' === res) {
                scrollToTop();
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, data.data);
                window.setTimeout(function() {
                    window.location.href = '?page=inplayer-login&reset';
                }, 1000);
            } else {
                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                scrollToTop();
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.spinner', '#inplayer-forgot-password').removeClass('is-active');
            $('#button-forgot-password').prop('disabled', false);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * Reset the Merchant's password
     */
    $('#button-reset-password').on('click', function () {
        event.preventDefault();
        var $notice = $('.notice'),
            $dataToSend = $('form', '#inplayer-reset-password'),
            msg = '';

        $('.spinner', '#inplayer-reset-password').addClass('is-active');
        $('#button-reset-password').prop('disabled', true);

        $.ajax({
            action: 'inplayer_reset_password',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            $('.spinner', '#inplayer-reset-password').removeClass('is-active');
            $('#button-reset-password').prop('disabled', false);

            if ('0' === res) {
                scrollToTop();
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, 'Password was successfully changed.');
                window.setTimeout(function() {
                    window.location.href = '?page=inplayer-login';
                }, 1000);
            } else {
                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                scrollToTop();
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.spinner', '#inplayer-reset-password').removeClass('is-active');
            $('#button-reset-password').prop('disabled', false);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * @api POST /account/change-password
     * Perform InPlayer change password.
     */
    $('#button-change-password').on('click', function (event) {
        event.preventDefault();
        var $notice = $('.notice'),
            $dataToSend = $('form', '#inplayer-change-password'),
            msg = '';

        $('.spinner', '#inplayer-change-password').addClass('is-active');
        $('#button-change-password').prop('disabled', true);

        if (!$dataToSend[0].checkValidity()) {
            showErrorNotice($notice, 'There are errors in the form, please check and try again.');
            $('.spinner', '#inplayer-change-password').removeClass('is-active');
            $('#button-change-password').prop('disabled', false);
            return false;
        }

        $.ajax({
            action: 'inplayer_change_password',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            $('.spinner', '#inplayer-change-password').removeClass('is-active');
            $('#button-change-password').prop('disabled', false);

            if ('0' === res) {
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                $($dataToSend)[0].reset();
                showSuccessNotice($notice, data.data);
            } else {
                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.spinner', '#inplayer-change-password').removeClass('is-active');
            $('#button-change-password').prop('disabled', false);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * Add Ooyala keys for this account
     */
    $('#button-ovp-ooyala').on('click', function (event) {
        event.preventDefault();

        var $notice = $('.notice'),
            $dataToSend = $('form', '#inplayer-ovp-ooyala'),
            msg = '';

        $('.spinner', '#inplayer-ovp-ooyala').addClass('is-active');
        $('#button-ovp-ooyala').prop('disabled', true);

        $.ajax({
            action: 'inplayer_ovp_ooyala',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            $('.spinner', '#inplayer-ovp-ooyala').removeClass('is-active');
            $('#button-ovp-ooyala').prop('disabled', false);

            if ('0' === res) {
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                $($dataToSend)[0].reset();
                showSuccessNotice($notice, data.data);
                window.setTimeout(function() {
                    location.reload();
                }, 1000)
            } else {
                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.spinner', '#inplayer-ovp-ooyala').removeClass('is-active');
            $('#button-ovp-ooyala').prop('disabled', false);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });
    /**
     * @api POST /payment/verify
     * Perform InPlayer verify payment method.
     */
    $('#button-verify-payment_method').on('click', function (event) {
        event.preventDefault();
        var $notice = $('.notice'),
            $dataToSend = $('form', '#inplayer-verify-payment-method');

        if (!$dataToSend[0].checkValidity()) {
            showErrorNotice($notice, 'There are errors in the form, please check and try again.');
            $('.inplayer-notice').hide();
            return false;
        }

        $('.spinner', '#inplayer-verify-payment-method').addClass('is-active');
        $('#button-verify-payment_method').prop('disabled', true);

        $.ajax({
            action: 'inplayer_verify_payment_method',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            $('.spinner', '#inplayer-verify-payment-method').removeClass('is-active');
            $('#button-verify-payment_method').prop('disabled', false);

            if ('0' === res) {
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                $($dataToSend)[0].reset();
                showSuccessNotice($notice, '<span style="color:green;">Great success!</span> You are now ready to add your ' +
                    '<a href="?page=inplayer-asset">first asset</a> and start selling.');
                $('.inplayer-notice').hide();
            } else {
                showErrorNotice($notice, '<span style="color:red;">Try again!</span> The account details you added aren’t from a valid PayPal account. ' +
                    'Register with PayPal <a href="https://www.paypal.com/" target="_blank">here</a> if you don’t have an account.');
                $('.inplayer-notice').hide();
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.spinner', '#inplayer-verify-payment-method').removeClass('is-active');
            $('#button-verify-payment_method').prop('disabled', false);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * Merchant withdraw from InPlayer platform
     */
    $('.platform-payout').click(function (e) {
        e.preventDefault();

        $('.spinner', $(this).parent('form')).addClass('is-active');
        $(this).prop('disabled', true);

        var $notice = $('.notice'),
            $amount = $('input[name="amount"]', $(this).parents('form')).val();

        if (!$amount) {
            showErrorNotice($notice, 'Value for withdrawal cannot be empty.');

            $('.spinner', $(this).parent('form')).removeClass('is-active');
            $(this).prop('disabled', false);

            return false;
        }

        if (parseFloat($amount) > parseFloat($(this).data('amount'))) {
            showErrorNotice($notice, 'Value for withdrawal cannot be more than the available balance.');

            $('.spinner', $(this).parent('form')).removeClass('is-active');
            $(this).prop('disabled', false);

            return false;
        }

        $.ajax({
            action: 'inplayer_platform_payout',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: {
                'action': 'inplayer_platform_payout',
                'gateway': $(this).data('gateway'),
                'amount': $amount,
                'currency_iso': $(this).data('currency')

            }
        }).done(function (data, res) {

            if ('0' === res) {
                $('.platform-payout').prop('disabled', false);
                $('.spinner').removeClass('is-active');

                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                showSuccessNotice($notice, data.data);
            } else {
                $('.platform-payout').prop('disabled', false);
                $('.spinner').removeClass('is-active');

                showErrorNotice($notice, data.data);
            }
        }).fail(function (xhr) {
            var $errors = [];

            $('.platform-payout').prop('disabled', false);
            $('.spinner').removeClass('is-active');

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice($notice, $errors.join(', '));
            }
        });
    });

    /**
     * Upload Image for InPlayer Asset
     */
    $('#upload_image_button').click(function (e) {
        e.preventDefault();
        var media_uploader;

        if (media_uploader) {
            media_uploader.open();
            return;
        }

        media_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            }, multiple: false
        });

        media_uploader.on('select', function () {
            var attachment = media_uploader.state().get('selection').first().toJSON();
            $('#asset-image').val(attachment.url);
        });

        media_uploader.open();
    });


    /**
     * Create a new access fee entry from the InPlayer asset editor.
     */
    $('.add', '#access-fees').on('click', function (event) {
        event.preventDefault();
        $(this).next().clone(true).show().appendTo($('#access-fees-list'));
    });

    /**
     * Remove the access fee entry from the InPlayer asset editor.
     */
    $('#access-fees-list').on('click', '.remove', function (event) {
        event.preventDefault();
        var notice = $('.notice');
        var li = $(this).closest("li");
        var feeID = $('input[name^=\'access_fee[id]\']', li).val();

        if (feeID == '0') {
            $(this).parent().remove();
        } else {
            $.ajax({
                action: 'inplayer_remove_access_fee',
                url: inplayer.ajaxUrl,
                type: 'POST',
                data: {
                    'action': 'inplayer_remove_access_fee',
                    'application_uuid': '1',
                    'id': feeID
                }
            }).done(function (data, res) {
                if ('0' === res) {
                    showErrorNotice(notice, '<b>No response</b> from the InPlayer API');
                } else if (data.success === true) {
                    li.remove();
                    // showSuccessNotice(notice, 'Access Fee successfully removed.');
                } else {
                    showErrorNotice(notice, 'Failed to remove Access Fee');
                }
            }).fail(function (xhr) {
                var $errors = [];

                try {
                    $.each(xhr.responseJSON.errors, function (k, v) {
                        $errors.push(v);
                    });
                } catch (e) {
                    $errors.push(xhr.status + ' ' + xhr.statusText);
                    $errors.push(xhr.responseJSON.errors);
                }

                if ($errors.length) {
                    showErrorNotice(notice, $errors.join(', '));
                }
            });
        }
    });

    /**
     *  Publish InPlayer Asset
     */
    $('#save-asset').on('click', function (event) {
        event.preventDefault();
        var $dataToSend = $('form'),
            $notice = $('.notice'),
            $content = '',
            $ooyala = '',
            msg = '',
            clicked = $(this);

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        if( document.getElementById('ooyala-video') ) {
            $ooyala = $('#ooyala-video').find(":selected").data('content');
            $ooyala.pcode = $('#pcode').val(); // Add the pcode to the Ooyala asset JSON

            $content = JSON.stringify($ooyala);
        } else {
            $content = $('#html-editor').val();
        }

        $('#asset-content').val(encodeURIComponent($content)); // set the content in the hidden input placeholder

        $.ajax({
            action: 'inplayer_save_asset',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, 'Your Asset is protected by InPlayer. To start using it, copy the Shortcode generated below in the top right corner and paste it into the post you want to publish.');
                resetSpinner(clicked);
                window.setTimeout(function() {
                    window.location.href = '?page=inplayer-asset' + data.data;
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);

                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     * Delete InPlayer Asset
     */
    $('#delete-asset').on('click', function (event) {
        event.preventDefault();
        var $assetId = $(this).attr("data-asset"),
            $notice = $('.notice'),
            clicked = $(this);

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        $.ajax({
            action: 'inplayer_delete_asset',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: { 'action' : 'inplayer_delete_asset', 'id' : $assetId }
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, data.data);
                resetSpinner(clicked);
                window.setTimeout(function() {
                    location.reload();
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, data.data);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     * Rreactivate InPlayer Asset
     */
    $('.reactivate-asset').on('click', function (event) {
        event.preventDefault();
        var $assetId = $(this).attr("data-asset"),
            $notice = $('.notice'),
            msg = '',
            clicked = $(this);

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        $.ajax({
            action: 'inplayer_reactivate_asset',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: { 'action' : 'inplayer_reactivate_asset', 'id' : $assetId }
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, data.data);
                resetSpinner(clicked);
                window.setTimeout(function() {
                    location.reload();
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);

                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     *  Publish InPlayer Package
     */
    $('#save-package').on('click', function (event) {
        event.preventDefault();
        var $dataToSend = $('form'),
            $items = '',
            $notice = $('.notice'),
            msg = '',
            clicked = $(this);

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        $( "#ip-package-assets li" ).each(function() {
            $items += $( this ).data('id') + ',';
        });
        $('#new-assets').val($items); // set the content in the hidden input placeholder

        $.ajax({
            action: 'inplayer_save_package',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, 'Your Package was successfully created');
                resetSpinner(clicked);
                window.setTimeout(function() {
                    window.location.href = '?page=inplayer-package' + data.data;
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);

                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     *  Update InPlayer Package
     */
    $('#update-package').on('click', function (event) {
        event.preventDefault();
        var $dataToSend = $('form'),
            $notice = $('.notice'),
            $items = '',
            msg = '',
            clicked = $(this);

        $( "#ip-package-assets li" ).each(function() {
            $items += $( this ).data('id') + ',';
        });
        $('#new-assets').val($items); // set the content in the hidden input placeholder

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        $.ajax({
            action: 'inplayer_update_package',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: $dataToSend.serializeArray()
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, 'Your Package was successfully updated');
                resetSpinner(clicked);
                window.setTimeout(function() {
                    window.location.href = '?page=inplayer-package' + data.data;
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);

                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     * Delete InPlayer Package
     */
    $('.delete-package').on('click', function (event) {
        event.preventDefault();
        var $packageId = $(this).attr("data-asset"),
            $notice = $('.notice'),
            clicked = $(this);

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        $.ajax({
            action: 'inplayer_delete_package',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: { 'action' : 'inplayer_delete_package', 'id' : $packageId }
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, data.data);
                resetSpinner(clicked);
                window.setTimeout(function() {
                    location.reload();
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, data.data);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     * Rreactivate InPlayer Package
     */
    $('.reactivate-package').on('click', function (event) {
        event.preventDefault();
        var $packageId = $(this).attr("data-asset"),
            $notice = $('.notice'),
            msg = '',
            clicked = $(this);

        $('.inplayer-spinner').addClass('is-active');
        clicked.prop('disabled', true);

        $.ajax({
            action: 'inplayer_reactivate_package',
            url: inplayer.ajaxUrl,
            type: 'POST',
            data: { 'action' : 'inplayer_reactivate_package', 'id' : $packageId }
        }).done(function (data, res) {
            if ('0' === res) {
                scrollToTop();
                resetSpinner(clicked);
                showErrorNotice($notice, '<b>No response</b> from the InPlayer API');
            } else if (data.success === true) {
                scrollToTop();
                showSuccessNotice($notice, data.data);
                resetSpinner(clicked);
                window.setTimeout(function() {
                    location.reload();
                }, 2000)
            } else {
                scrollToTop();
                resetSpinner(clicked);

                $.map(data.data, function (value) {
                    msg += value + '<br>';
                });
                showErrorNotice($notice, msg);
            }
        }).fail(function (xhr) {
            var $errors = [];
            resetSpinner(clicked);

            try {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    $errors.push(v);
                });
            } catch (e) {
                $errors.push(xhr.status + ' ' + xhr.statusText);
                $errors.push(xhr.responseJSON.errors);
            }

            if ($errors.length) {
                showErrorNotice(notice, $errors.join(', '));
            }
        });
    });

    /**
     * All Assets filter by published/deleted
     */
    $('#asset-selection').on('change', function (event) {
        event.preventDefault();

        if($('#asset-selection option:selected').val() == '0') {
            var search = window.location.search + (window.location.search ? "&" : "?");
            search += "inactive";
            window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname + search;
        } else {
            location.search = location.search.replace(/&inactive*/,'');
        }
    });

    // Changes the previous input type password element to input type text and vice versa
    $('.show-input').on('click', function(event){
        event.preventDefault();

        if ( $(this).data('toggle') == 'on' ) {
            $(this).data('toggle', 'off');
            $('.ip-sp-show', $(this)).toggle();
            $('.ip-sp-hide', $(this)).toggle();
            $(this).prev('input').attr('type', 'text');
        } else {
            $(this).data('toggle', 'on');
            $('.ip-sp-show', $(this)).toggle();
            $('.ip-sp-hide', $(this)).toggle();
            $(this).prev('input').attr('type', 'password');
        }
    });

    /**
     * InPlayer notifications.
     *
     * @param {Object} body
     */
    var handleInplayerNotification = function (body) {
        switch (body.state) {
            case 'processing':
                break;

            case 'completed':
                break;

            case 'failed':
                break;
        }
    };

    /**
     * Reset the state of the element and the associated spinner.
     *
     * @param {Object} $element
     */
    var resetSpinner = function ($element) {
        $element.prop('disabled', false);
        $element.parent().find('.spinner').removeClass('is-active');
    };

    /**
     * @param $notice
     * @param message
     */
    function showErrorNotice($notice, message) {
        resetNotice($notice).addClass('notice-error').removeClass('hidden').find('p').html(message);
    }

    /**
     * @param $notice
     * @param message
     */
    function showSuccessNotice($notice, message) {
        resetNotice($notice).addClass('notice-success').removeClass('hidden').find('p').html(message);
    }

    /**
     * Sets the admin notice div to default style.
     * @param $notice
     */
    function resetNotice($notice) {
        $notice.addClass('hidden').removeClass(function (i, css) {
            return (css.match(/(^|\s)notice-\S+/g) || []).join(' ');
        });

        $notice.find('p').text('');

        return $notice;
    }

    /**
     * Returns browser view back to top
     */
    function scrollToTop() {
        $('html,body').animate({
            scrollTop: 0
        }, 100);
    }

}(jQuery);
