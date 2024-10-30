<?php

final class InPlayerAdmin {

	/**
	 * @var InPlayerPlugin
	 */
	private $inplayer;

	/**
	 * @var InPlayerForms
	 */
	private $forms;

	/**
	 * InPlayerAdmin constructor.
	 *
	 * @param InPlayerPlugin $inplayer
	 * @param InPlayerForms  $forms
	 */
	public function __construct( InPlayerPlugin $inplayer, InPlayerForms $forms ) {
		$this->inplayer = $inplayer;
		$this->forms    = $forms;
	}

	/**
	 * Registers the administration settings pages, styles, js, etc.
	 */
	public function register() {
		add_action( 'wp_loaded', function() {
			ob_start();
		} );

		add_action( 'admin_notices', [ $this, 'trigger_flash_message' ] );

		if ( $this->inplayer->is_authenticated() ) {
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'load_admin_menu' ] );
		} else {
			delete_option( InPlayerPlugin::AUTH_KEY );
			add_action( 'admin_menu', [ $this, 'load_setup_menu' ] );
		}

		add_action( 'admin_enqueue_scripts', function( $suffix ) {
			wp_enqueue_style( 'inplayer-admin-style', plugins_url( '/assets/css/inplayer-admin.css', __DIR__ ) );
			wp_enqueue_script( 'inplayer-admin-script', plugins_url( '/assets/js/inplayer-admin.js', __DIR__ ),
				[ 'jquery' ], false, true );

			if ( $suffix == 'inplayer-paywall_page_inplayer-transactions' ) {
				// @todo check if these are already loaded by other plugins or means
				wp_enqueue_script( 'inplayer-admin-sockjs', '//cdnjs.cloudflare.com/ajax/libs/sockjs-client/1.1.1/sockjs.min.js', [ 'jquery' ] );
				wp_enqueue_script( 'inplayer-admin-stomp', '//cdnjs.cloudflare.com/ajax/libs/stomp.js/2.3.3/stomp.min.js', [ 'jquery' ] );
				wp_localize_script( 'inplayer-admin-script', 'notifications', [
					'uuid'  => get_option( InPlayerPlugin::MERCHANT_UUID ),
					'stomp' => [
						'url'      => INPLAYER_STOMP,
						'login'    => 'notifications',
						'password' => 'notifications',
					],
				] );
			}

			wp_localize_script( 'inplayer-admin-script', 'inplayer', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
		} );

		// Ajax form handlers
		add_action( 'wp_ajax_inplayer_register_account', [ $this->forms, 'register_account' ] );
		add_action( 'wp_ajax_inplayer_activate_account', [ $this->forms, 'activate_account' ] );
		add_action( 'wp_ajax_inplayer_resend_activation', [ $this->forms, 'resend_activation' ] );
		add_action( 'wp_ajax_inplayer_platform_login', [ $this->forms, 'platform_login' ] );
		add_action( 'wp_ajax_inplayer_change_password', [ $this->forms, 'platform_change_password' ] );
		add_action( 'wp_ajax_inplayer_forgot_password', [ $this->forms, 'platform_forgot_password' ] );
		add_action( 'wp_ajax_inplayer_reset_password', [ $this->forms, 'platform_reset_password' ] );
		add_action( 'wp_ajax_inplayer_verify_payment_method', [ $this->forms, 'platform_verify_payment_method' ] );
		add_action( 'wp_ajax_inplayer_remove_access_fee', [ $this->forms, 'platform_remove_access_fee' ] );
		add_action( 'wp_ajax_inplayer_transactions', [ $this->forms, 'platform_transactions_history' ] );
		add_action( 'wp_ajax_inplayer_platform_payout', [ $this->forms, 'platform_payout' ] );

		// Save\Delete\Reactivate Asset
		add_action( 'wp_ajax_inplayer_save_asset', [ $this->forms, 'save_asset' ] );
		add_action( 'wp_ajax_inplayer_delete_asset', [ $this->forms, 'delete_asset' ] );
		add_action( 'wp_ajax_inplayer_reactivate_asset', [ $this->forms, 'reactivate_asset' ] );

		// Save\Update\Delete\Reactivate Package
		add_action( 'wp_ajax_inplayer_save_package', [ $this->forms, 'save_package' ] );
		add_action( 'wp_ajax_inplayer_update_package', [ $this->forms, 'update_package' ] );
		add_action( 'wp_ajax_inplayer_delete_package', [ $this->forms, 'delete_package' ] );
		add_action( 'wp_ajax_inplayer_reactivate_package', [ $this->forms, 'reactivate_package' ] );

		// OVP Integrations
		add_action( 'wp_ajax_inplayer_ovp_ooyala', [ $this->forms, 'platform_ovp_ooyala_integration' ] );

		// Add Shortcode options in the WordPres visual editors
		add_action( 'init', function() {
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				return;
			}

			if ( get_user_option( 'rich_editing' ) == 'true' ) {
				add_filter( 'mce_external_plugins', function( $plugin_array ) {
					$plugin_array['inplayer'] = plugins_url( '/assets/js/inplayer-editor.js', __DIR__ );

					return $plugin_array;
				} );

				add_filter( 'mce_buttons', function( $buttons ) {
					array_push( $buttons, "|", "inplayer" );

					return $buttons;
				} );
			}
		} );

		add_action( 'wp_ajax_assets_shortcodes', [ $this->forms, 'assets_shortcodes_list' ] );

		// Add Shortcode button into the WordPress textual editor
		add_action( "admin_print_footer_scripts", function() {
			global $pagenow;

			if ( wp_script_is( 'quicktags') and !($pagenow == 'admin.php') ) {
				include __DIR__ . '/../views/text-editor-shortcode.php';
			}
		} );

		// Add InPlayer widgets on the main dashboard
		add_action( 'wp_dashboard_setup', function() {
			wp_add_dashboard_widget(
				'total-purchases',
				__( 'InPlayer PayWall', INPLAYER_TEXT_DOMAIN ),
				function() {
					$transactions = new TransactionsReport;
					$transactions->execute();
					require __DIR__ . '/../views/payments/widget-purchases.php';
					require __DIR__ . '/../views/payments/widget-transactions.php';
				}
			);
		} );

	}

	/**
	 * Registers the plugin options and the fields for the admin settings page.
	 *
	 * @return void
	 */
	public function admin_init() {
		/*
		 * Payment Settings
		 *
		 */
		add_settings_section(
			'inplayer-section-payments',
			'',
			function() {
				require __DIR__ . '/../views/payments/active-method.php';
			},
			'inplayer-payments'
		);

//		add_settings_field(
//			'receive-payments',
//			__( 'Connect your PayPal account', INPLAYER_TEXT_DOMAIN ),
//			function() {
//				require __DIR__ . '/../views/payments/methods.php';
//			},
//			'inplayer-payments',
//			'inplayer-section-payments'
//		);

		register_setting( 'inplayer-payments', InPlayerPlugin::SETTINGS, [ $this, 'sanitize' ] );

		/*
		 * Account settings
		 *
		 */

		add_settings_section(
			'inplayer-section-account',
			'',
			'',
			'inplayer-account'
		);

		add_settings_field(
			'change-password',
			__( 'Change Password', INPLAYER_TEXT_DOMAIN ),
			function() {
				require __DIR__ . '/../views/account/change-password.php';
			},
			'inplayer-account',
			'inplayer-section-account'
		);

		add_settings_field(
			'ooyala-keys',
			__( 'OVP Accounts', INPLAYER_TEXT_DOMAIN ),
			function() {
				require __DIR__ . '/../views/account/ovp-ooyala.php';
			},
			'inplayer-account',
			'inplayer-section-account'
		);

		register_setting( 'inplayer-account', InPlayerPlugin::SETTINGS, [ $this, 'sanitize' ] );

		/*
		 * About tab
		 *
		 */

		add_settings_section(
			'inplayer-section-about',
			'',
			function() {
				require __DIR__ . '/../views/inplayer-about.php';
			},
			'inplayer-about'
		);
	}

	/**
	 * Registers the administration pages.
	 */
	public function load_admin_menu() {
		add_menu_page(
			__( 'InPlayer Platform Integration', INPLAYER_TEXT_DOMAIN ),
			__( 'InPlayer PayWall', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer',
			function() {
				require __DIR__ . '/../views/settings-index.php';
			}
		);

        add_submenu_page(
            'inplayer',
            __( 'InPlayer Assets' , INPLAYER_TEXT_DOMAIN),
            __( 'All Assets' , INPLAYER_TEXT_DOMAIN),
            'administrator',
            'inplayer-items',
            function() {
            	require __DIR__ . '/AssetsListTable.php';
                require __DIR__ . '/../views/item/asset.php';
            }
        );

        add_submenu_page(
            'inplayer',
            __( 'InPlayer Asset' , INPLAYER_TEXT_DOMAIN),
            __( 'Add Asset' , INPLAYER_TEXT_DOMAIN),
            'administrator',
            'inplayer-asset',
            function() {
                $this->forms->asset_editor();
            }
        );

		add_submenu_page(
			'inplayer',
			__( 'InPlayer Package', INPLAYER_TEXT_DOMAIN ),
			__( 'Manage Packages', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-package',
			function () {
				$this->forms->package_editor();
			}
		);

		// Transactions
		add_submenu_page(
			'inplayer',
			__( 'Transactions', INPLAYER_TEXT_DOMAIN ),
			__( 'Transactions', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-transactions',
			function() {
				require __DIR__ . '/TransactionsListTable.php';
				require __DIR__ . '/../views/transactions.php';
			}
		);

		// Settings
		add_submenu_page(
			'inplayer',
			__( 'InPlayer Paywall Settings', INPLAYER_TEXT_DOMAIN ),
			__( 'Settings', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-settings',
			function() {
				require __DIR__ . '/../views/settings-index.php';
			}
		);
	}

	/**
	 * Registers a menu login page.
	 */
	public function load_login_menu() {
		// Login page
		add_menu_page(
			__( 'InPlayer PayWall', INPLAYER_TEXT_DOMAIN ),
			__( 'InPlayer PayWall', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-login',
			function() {
				require __DIR__ . '/../views/form-login.php';
			}
		);
	}

	/**
	 * Registers a plugin setup pages.
	 */
	public function load_setup_menu() {
		add_menu_page(
			__( 'InPlayer PayWall Setup', INPLAYER_TEXT_DOMAIN ),
			__( 'InPlayer PayWall', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-home',
			function() {
				require __DIR__ . '/../views/setup-welcome.php';
			}
		);

		// Login page
		add_submenu_page(
			'inplayer-home',
			__( 'Login', INPLAYER_TEXT_DOMAIN ),
			__( 'Login', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-login',
			function() {
				require __DIR__ . '/../views/form-login.php';
				require __DIR__ . '/../views/form-forgot-password.php';
				require __DIR__ . '/../views/form-register.php';
			}
		);

		// TOC page
		add_submenu_page(
			'inplayer-home',
			__( 'InPlayer ToC', INPLAYER_TEXT_DOMAIN ),
			__( 'Terms & Conditions', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-terms',
			function() {
				require __DIR__ . '/../views/toc.php';
			}
		);

		// Activate Page
		add_submenu_page(
			null,
			__( 'Activate Your Merchant Account', INPLAYER_TEXT_DOMAIN ),
			__( 'Activate Account', INPLAYER_TEXT_DOMAIN ),
			'administrator',
			'inplayer-activate',
			function() {
				require __DIR__ . '/../views/form-activate.php';
			}
		);
	}

	/**
	 * Sanitize the data array.
	 *
	 * @param array $data
	 *
	 * @return array The sanitized settings
	 */
	public function sanitize( $data ) {
		$output   = [];
		$sanitize = function( $value ) {
			return strip_tags( stripslashes( $value ) );
		};

		foreach ( $data as $k => $v ) {
			$output[ $sanitize( $k ) ] = $sanitize( $v );
		}

		return $output;
	}

	/**
	 * Flash messages function.
	 *
	 * @param string $text
	 * @param string $type
	 * @param string $redirect_url
	 */
	public static function flash_message( $text, $type = 'error', $redirect_url = '' ) {
		$message         = get_option( INPLAYER_FLASH_MESSAGE, [] );
		$message['type'] = $type;
		$message['text'] = $text;
		update_option( INPLAYER_FLASH_MESSAGE, $message );

		if ( ! empty( $redirect_url ) ) {
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

	public function trigger_flash_message() {
		include __DIR__ . '/../views/flash-message.php';
		delete_option( INPLAYER_FLASH_MESSAGE );
	}
}
