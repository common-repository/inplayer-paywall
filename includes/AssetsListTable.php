<?php

/**
 * Assets table.
 */
final class AssetsListTable extends WP_List_Table {

	const LIMIT_PER_PAGE = 15;

	public function ____construct() {
		parent::__construct( [
			'singular' => 'Asset',
			'plural'   => 'Assets',
			'ajax'     => false,

		] );
	}

	public function get_columns() {
		return [
			'title'     => __( 'Title', INPLAYER_TEXT_DOMAIN ),
			'id'        => __( 'Asset ID', INPLAYER_TEXT_DOMAIN ),
			'type'      => __( 'Asset Type', INPLAYER_TEXT_DOMAIN ),
			'shortcode' => __( 'Shortcode', INPLAYER_TEXT_DOMAIN ) .
			               '<span data-tooltip="' . __( 'To display your item, just copy the generated shortcode to any page or post you like',
					INPLAYER_TEXT_DOMAIN ) . '" data-tooltip-position="bottom" class="inplayer-helper"><span class="dashicons dashicons-editor-help"></span></span>',
			'status'    => __( 'Item Status', INPLAYER_TEXT_DOMAIN ),
			'datetime'  => __( 'Date', INPLAYER_TEXT_DOMAIN ),
			'action'    => __( 'Action', INPLAYER_TEXT_DOMAIN ),
		];
	}

	/**
	 * Prepares the table with the items, sorting, pagination, etc.
	 *
	 * @return $this
	 */
	public function prepare_items() {
		// ordering params
		$orderBy     = empty( $_GET['orderby'] ) ? 'datetime' : esc_attr( $_GET['orderby'] );
		$orderBy     = $this->get_inplayer_sortable_field( $orderBy );
		$direction   = empty( $_GET['order'] ) ? 'DESC' : esc_attr( $_GET['order'] );
		$queryString = '?page=' . $this->get_pagenum() . '&limit=' . self::LIMIT_PER_PAGE . '&order=' . $orderBy . '&direction=' . $direction;

		if ( isset($_GET['inactive']) ) {
			$queryString = $queryString . '&is_active=false';
		}
		// get the assets collection
		$response = InPlayerPlugin::request( 'GET', INPLAYER_ASSETS . '/collection' . $queryString );

		if ( $response['response']['code'] === 401 ) {
			delete_option( InPlayerPlugin::AUTH_KEY );
			wp_redirect( 'admin.php?page=inplayer-login', 301 );
			wp_die();
		}

		if ( $response['response']['code'] === 403 ) {
			$message = json_decode( $response['body'], true );
			wp_die( '<div class="error"><p>' . $message['errors'][403] . '</p></div>', 403 );
		}

		if ( ! isset( $response['body']['collection'] ) || ! $response['body']['total'] ) {
			return $this;
		}

		// extract the report data
		$this->items = $this->get_transactions_from_collection( $response['body']['collection'] );

		// the table columns
		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		$this->set_pagination_args( [
			'total_items' => $response['body']['total'],
			'total_pages' => $response['body']['pages'],
			'per_page'    => self::LIMIT_PER_PAGE
		] );

		return $this;
	}

	/**
	 * Text displayed when no transactions exist yet.
	 */
	public function no_items() {
		echo __( 'No Assets has been created yet.', INPLAYER_TEXT_DOMAIN );
	}

	/**
	 * @param string $field The column name in the sortable table
	 *
	 * @return string
	 */
	protected function get_inplayer_sortable_field( $field ) {
		$columns = [
			'datetime' => 'h.createdAt'
		];

		return isset( $columns[ $field ] ) ? $columns[ $field ] : 'h.createdAt';
	}

	protected function get_sortable_columns() {
		return [
			'datetime' => [ 'datetime', false ] // TRUE means it is already sorted
		];
	}

	/**
	 * @param array $collection
	 *
	 * @return array
	 */
	protected function get_transactions_from_collection( array $collection ) {
		$list = [];

		foreach ( $collection as $transaction ) {
			$list[] = [
				'title'    => $transaction['title'],
				'id'       => $transaction['id'],
				'type'     => $transaction['item_type'],
				'datetime' => $transaction['created_at'],
				'active'   => $transaction['is_active']
			];
		}

		return $list;
	}

	/*
	 *
	 * Table data formatting
	 *
	 */
	protected function column_id( $item ) {
		return $item['id'];
	}

	protected function column_title( $item ) {
		return $item['title'];
	}

	protected function column_shortcode( $item ) {
		if ( $item['active'] ) {
			return '<code>[inplayer id="' . $item['id'] . '"]</code>';
		}

		return 'Item has been deleted';
	}

	protected function column_type( $item ) {
		return strtoupper( $item['type'] );
	}

	protected function column_datetime( $item ) {
		return date( 'Y/m/d H:i', $item['datetime'] );
	}

	protected function column_status( $item ) {
		return strtoupper( $item['active'] ? 'published' : 'deleted' );
	}

	protected function column_action( $item ) {
		if ( $item['active'] ) {
			return '<a href="?page=inplayer-asset&asset=' . $item['id'] . '&type=' . $item['type'] . '">Edit Asset</a>';
		} else {
			return '<a href="#reactive" data-asset="' . $item['id'] . '" class="reactivate-asset">Reactivate Asset</a>';
		}
	}

	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}
}