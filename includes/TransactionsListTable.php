<?php

/**
 * Transactions table.
 */
final class TransactionsListTable extends WP_List_Table {

	const LIMIT_PER_PAGE = 8;

	public function ____construct() {
		parent::__construct( [
			'singular' => 'transaction',
			'plural'   => 'transactions',
			'ajax'     => false,

		] );
	}

	public function get_columns() {
		return [
			'description'   => __( 'Asset Title', INPLAYER_TEXT_DOMAIN ),
			'type'          => __( 'Type', INPLAYER_TEXT_DOMAIN ),
			'datetime'      => __( 'Date Time', INPLAYER_TEXT_DOMAIN ),
			'item_id'       => __( 'Asset ID', INPLAYER_TEXT_DOMAIN ),
			'gross_amount'  => __( 'Gross Amount', INPLAYER_TEXT_DOMAIN ),
			'net_amount'    => __( 'Earning', INPLAYER_TEXT_DOMAIN ),
			'payout_amount' => __( 'Payout', INPLAYER_TEXT_DOMAIN ),
			'details'       => __( 'Transaction Details', INPLAYER_TEXT_DOMAIN ),
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

		// get the transactions report
		$response = InPlayerPlugin::request( 'GET', INPLAYER_ACCOUNTING . '/report/transactions' . $queryString );

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
		echo __( 'No transactions recorded.', INPLAYER_TEXT_DOMAIN );
	}

	/**
	 * @param string $field The column name in the sortable table
	 *
	 * @return string
	 */
	protected function get_inplayer_sortable_field( $field ) {
		$columns = [
			'datetime' => 'h.createdAt',
			'item_id'  => 'h.ItemId'
		];

		return isset( $columns[ $field ] ) ? $columns[ $field ] : 'h.createdAt';
	}

	protected function get_sortable_columns() {
		return [
			'datetime' => [ 'datetime', false ], // TRUE means it is already sorted
			'item_id'  => [ 'item_id' ]
		];
	}

	/**
	 * @param array $collection
	 *
	 * @return array
	 */
	protected function get_transactions_from_collection( array $collection ) {
		$list      = [];

		foreach ( $collection as $transaction ) {
			$list[] = [
				'id'             => $transaction['id'],
				'description'    => $transaction['description'],
				'type'           => $transaction['action_type'],
				'item_id'        => $transaction['item_id'],
				'currency'       => $transaction['currency_iso'],
				'charged_amount' => $transaction['charged_amount'],
				'net_amount'     => $transaction['net_amount'],
				'datetime'       => $transaction['created_at'],
				'title'          => $transaction['item_title'],
				'item_type'      => $transaction['item_type']
			];
		}

		return $list;
	}

	/*
	 *
	 * Table data formatting
	 *
	 */

	protected function column_description( $item ) {
		if ( 'payout' === $item['type'] ) {
			return __( 'Withdraw', INPLAYER_TEXT_DOMAIN );
		}

		$title = $item['title'];

		if ( empty( $title ) ) {
			return __( '(deleted asset)', INPLAYER_TEXT_DOMAIN );
		}

		$wrapped = explode( '##BREAK##', wordwrap( $title, 20, '##BREAK##', false ) );
		$wrapped = $wrapped[0] . ( isset( $wrapped[1] ) ? ' ...' : '' );

		return '<a href="?page=inplayer-asset&asset=' . $item['item_id'] . '&type=' . $item['item_type']. '">' . $wrapped . '</a>';
	}

	protected function column_type( $item ) {
		return strtoupper( $item['type'] );
	}

	protected function column_item_id( $item ) {
		if ( 'payout' === $item['type'] ) {
			return '';
		}

		return $item['item_id'];
	}

	protected function column_gross_amount( $item ) {
		if ( 'payout' === $item['type'] ) {
			return '';
		}

		return $item['currency'] . ' ' . number_format( $item['charged_amount'], 2, '.', '' );
	}

	protected function column_net_amount( $item ) {
		if ( 'payout' === $item['type'] ) {
			return '';
		}

		return $item['currency'] . ' ' . number_format( $item['net_amount'], 2, '.', '' );
	}

	protected function column_payout_amount( $item ) {
		if ( 'payout' === $item['type'] ) {
			return $item['currency'] . ' ' . number_format( $item['charged_amount'], 2, '.', '' );
		}

		return '';
	}

	protected function column_details( $item ) {
		return '<a href="' . admin_url( 'admin-ajax.php' ) . '?action=inplayer_transactions&width=900&id=' . $item['id']
		       . '&title=' . $item['title'] . '" class="thickbox" title="' . __( 'Transaction Details', INPLAYER_TEXT_DOMAIN ) . '">'
		       . __( 'Click for more', INPLAYER_TEXT_DOMAIN ) . '</a>';
	}

	protected function column_datetime( $item ) {
		return date( 'Y/m/d H:i', strtotime( trim( $item['datetime'], '"' ) ) );
	}

	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}
}