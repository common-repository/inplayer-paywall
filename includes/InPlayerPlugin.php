<?php

final class InPlayerPlugin {

    const MERCHANT_UUID       = 'inplayer_uuid';
    const MERCHANT_EMAIL      = 'inplayer_email';
    const SETTINGS            = 'inplayer_settings'; // @todo
    const AUTH_KEY            = 'inplayer_token';
    const COOKIE_NAME         = 'inplayer_cookie';
    const INPLAYER_ASSET      = 'inplayer_asset';

	/**
	 * @var array
	 */
	private $settings = [];

	/**
	 * InPlayerPlugin constructor.
	 */
	public function __construct() {
		$this->settings = get_option( self::SETTINGS ) ?: [];
		$this->settings = array_map( 'trim', $this->settings );
	}

	/**
	 * Loads the plugin classes, register styles, js, etc.
	 * If the merchant account is not activated, it will skip the whole plugin functionality.
	 *
	 * @return InPlayerPlugin instance
	 */
	public function register() {
		add_action( 'plugins_loaded', function() {
			load_plugin_textdomain( INPLAYER_TEXT_DOMAIN, false, 'inplayer-paywall/languages/' );
		});

		if ( is_admin() || ! $this->is_registered() ) {
			// the plugin is not configured yet, bail out
			return $this;
		}

		add_action( 'wp_enqueue_scripts', function() {
			// @todo change the injector.js and app.min.css URL
			wp_enqueue_style( 'inplayer-injector-css',  INPLAYER_INJECTOR . '/css/app.min.css' );
			wp_enqueue_script( 'inplayer-injector-js',  INPLAYER_INJECTOR . '/injector.js' );
		});

		add_shortcode('inplayer', [$this, 'injector_shortcode']);

		return $this;
	}

	/**
	 * Callback for the InPlayer shortcode.
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function injector_shortcode($atts) {
		global $post;

		// used in the partial file
		$atts = shortcode_atts(['id' => get_post_meta($post->ID, INPLAYER_ASSET_ID, true) ?: 0 ], $atts);

		ob_start();
		require __DIR__ . '/../views/inplayer-injector.php';
		return ob_get_clean();
	}

	/**
	 * @param string $key The setting name
	 *
	 * @return mixed
	 */
	public function settings( $key ) {
		if ( empty( $key ) ) {
			return $this->settings;
		}

		if ( array_key_exists( $key, $this->settings ) ) {
			return $this->settings[ $key ];
		}

		return null;
	}

	/**
	 * Plugin activation hook.
	 */
	public function activate() {
		add_option( self::MERCHANT_UUID );
	}

	/**
	 * Plugin deactivation hook.
	 */
	public function deactivate() {
		delete_option( self::AUTH_KEY );
	}

	/**
	 * @todo include ToC agreed flag?
	 *
	 * Checks if the administrator has registered an InPlayer account in WordPress.
	 *
	 * @return bool
	 */
	public function is_registered() {
		$uuid = get_option( self::MERCHANT_UUID );

		return !!filter_var( $uuid, FILTER_VALIDATE_REGEXP, [
			'options' => [ 'regexp' => '/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/' ]
		] );
	}

	/**
	 * @todo use the inplayer token
	 *
	 * Checks if the user is logged in on InPlayer platform.
	 *
	 * @return bool
	 */
	public function is_authenticated() {
		$token = get_option( self::AUTH_KEY );
		$now = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();

		return ! ( empty( $token['key'] ) or ( $token['ttl'] < $now ) );
	}

	public static function request($method, $uri, $auth = true, array $body = [], array $headers = [], $can_redirect = true) {
		$args = [ 'method' => strtoupper($method), 'httpversion' => '1.1' ];

		if (count($body)) {
			$args['body'] = $body;
		}

		if (count($headers)) {
			$args['headers'] = $headers;
		}

		if ($auth) {
			$args['headers']['Authorization'] = 'Bearer ' . get_option( self::AUTH_KEY )['key'];
		}

		$response = wp_remote_request( $uri, $args );

        if ($response instanceof WP_Error) {
            InPlayerAdmin::flash_message($response->get_error_message());

            return;
        }

		if ( (int)$response['response']['code'] === 401 && $can_redirect) {
			delete_option( InPlayerPlugin::AUTH_KEY );
			wp_redirect('admin.php?page=inplayer-login', 302);
			exit;
		}

		$response['body'] = json_decode($response['body'], true);

		return $response;
	}

	/**
	 * Processes the WP response.
	 *
	 * @param array|WP_Error $response
	 * @param                bool [optional] $send_headers
	 *
	 * @return string Optionally returns a string, if $sendHeaders is FALSE
	 */
	public static function handle_error_response( $response, $sendHeaders = false ) {
		// handle WP errors
		if ( is_wp_error( $response ) ) {
			if ( $sendHeaders ) {
				header( 'Content-type: application/json', true, 422 );
				wp_die(
					json_encode( [ 'errors' => $response->get_error_message() ] ),
					$response->get_error_code()
				);
			}

			return $response->get_error_message();
		}

		// empty response body
		if ( ! isset( $response['body'] ) || empty( $response['body'] ) ) {
			$response['response']['code']    = 412;
			$response['response']['message'] = 'Precondition Failed';
			$response['body']                = json_encode( [ 'errors' => [ 412 => 'The response is empty.' ] ] );
		}

		// handle InPlayer API errors
		if ( (int) $response['response']['code'] !== 200 ) {
			if ( $sendHeaders ) {
				header( 'Content-type: application/json', true, $response['response']['code'] );
				wp_die( $response['body'], $response['response']['code'] );
			}

			return $response['body'];
		}
	}
}
