<?php
add_thickbox();
$transactionsTable = new TransactionsListTable;
$transactionsTable->prepare_items();

$transactions = new TransactionsReport;
$transactions->execute();
?>

<style>
	#inplayer_transactions_panels table {
		border:none;
	}
</style>

<div class="wrap" id="inplayer-transactions">
	<div class="notice hidden">
		<p></p>
	</div>

	<h2><?php esc_attr_e( 'InPlayer PayWall', INPLAYER_TEXT_DOMAIN ) ?>: <?php esc_attr_e( 'Transactions', INPLAYER_TEXT_DOMAIN ) ?></h2>

	<div id="inplayer_transactions_panels">
		<div class="postbox-container">
			<?php require __DIR__ . '/../views/payments/widget-purchases.php'; ?>
		</div>

		<div class="postbox-container">
			<?php require __DIR__ . '/../views/payments/widget-transactions.php'; ?>
		</div>

		<div class="postbox-container">
			<?php require __DIR__ . '/../views/payments/widget-balance.php'; ?>
		</div>
	</div>

	<h2><?php esc_attr_e( 'Payment History', INPLAYER_TEXT_DOMAIN ) ?></h2>
	<p><?php esc_attr_e( 'text_payment_history_explain', INPLAYER_TEXT_DOMAIN ) ?></p>
	<?php $transactionsTable->display(); ?>
</div>
<?php unset($transactionsTable, $transactions); ?>