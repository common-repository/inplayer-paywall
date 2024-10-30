<?php
$response = InPlayerPlugin::request( 'GET', INPLAYER_ACCOUNTING . '/transactions/' . $_GET['id'] );

if ( $response['response']['code'] >= 400 ): ?>
	<b><?php esc_attr_e('Something went wrong while communicating with the InPlayer API', INPLAYER_TEXT_DOMAIN); ?></b>
<?php elseif ( empty( $response['body']['transactions'] ) ): ?>
	<b><?php esc_attr_e('No records found', INPLAYER_TEXT_DOMAIN); ?></b>
<?php else: ?>
	<p><?php esc_attr_e('Bellow you can see more details for all the transactions for this purchase:', INPLAYER_TEXT_DOMAIN); ?></p>

	<h3><?php echo $_GET['title']; ?></h3>
	<ul>
		<li><b><?php echo esc_attr_e('Date', INPLAYER_TEXT_DOMAIN) . '</b> ' . gmdate( 'Y-m-d H:i', $response['body']['transactions'][0]['created_at']); ?></li>
	</ul>
	<table class="wp-list-table widefat fixed striped inplayer-transactions-list">
		<thead>
		<tr>
			<th scope="col" id="datetime" class="manage-column"><?php esc_attr_e('Transaction Description', INPLAYER_TEXT_DOMAIN); ?></th>
			<th scope="col" id="type" class="manage-column"><?php esc_attr_e('Amount', INPLAYER_TEXT_DOMAIN); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $response['body']['transactions'] as $record ): ?>
			<tr>
				<td><?php echo $record['description']; ?></td>
				<td><?php echo $record['currency_iso'] . ' ' . number_format( $record['amount'], 2, '.', '' ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif;
unset( $response, $record );
exit;
?>
