<?php
$massPayment = $this->viewData['pplog'];
$affiliates = $this->viewData['affiliates'];
?>
<div class="wrap">

	<h2>PayPal Mass Pay</h2>
	<h3>Manual Reconciliation</h3>
	<table class="widefat" style="width: 700px">

		<thead>
		<tr><th width="150">&nbsp;</th><th width="500">&nbsp;</th></tr>
		</thead>

		<tr><th>Database ID</th><td><?php echo esc_html($massPayment->paypalLogId)?></td></tr>
		<tr><th>Date Occurred</th><td><?php echo esc_html(date("m/d/Y H:i:s",$massPayment->dateOccurred))?></td></tr>
		<tr><th>PayPal Timestamp</th><td><?php echo esc_html(date("m/d/Y H:i:s", $massPayment->responseTimestamp))?></td></tr>
		<tr><th>PayPal Correlation ID</th> <td><?php echo esc_html($massPayment->correlationId)?></td></tr>
		<tr><th>Amount</th><td><?php echo esc_html($massPayment->amount)?></td></tr>
		<tr><th>Fee</th><td><?php echo esc_html($massPayment->fee)?></td></tr>
		<tr><th>Total Amount</th><td><?php echo esc_html($massPayment->totalAmount)?></td></tr>
		<tr class="transaction-<?php echo esc_attr($massPayment->status)?>"><th>Status</th><td><?php echo esc_html($massPayment->status)?></td></tr>
		
	</table>


	<h3>Please confirm the results of the file</h3>

<form id="mainform" method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpam-payments&step=reconcile_with_file&substep=confirm_ok&id='.$massPayment->paypalLogId))?>">
        <?php wp_nonce_field('wpam_payments_rwfco_nonce'); ?>
	<table class="widefat" style="width: auto">
		<thead>
		<tr>

			<th width="25">ID</th>
			<th width="100">Date Occurred</th>
			<th width="150">Affiliate</th>
			<th width="150">PayPal E-Mail</th>
			<th width="100">Status</th>
			<th>Description</th>
			<th width="100">Amount</th>
			<th width="150" colspan="3">Outcome</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->viewData['transactions_modified'] as $transaction) {?>
		<?php $affiliate = $affiliates[$transaction->affiliateId]; ?>
		<tr class="transaction-<?php echo esc_attr($transaction->newStatus)?>">
			<td>
				<input type="hidden" name="transactions[<?php echo esc_attr($transaction->transactionId)?>][transactionId]" value="<?php echo esc_attr($transaction->transactionId)?>" />
				<input type="hidden" name="transactions[<?php echo esc_attr($transaction->transactionId)?>][newStatus]" value="<?php echo esc_attr($transaction->newStatus)?>" />
				<?php echo esc_html($transaction->transactionId)?>
			</td>
			<td><?php echo esc_html(date("m/d/Y", $transaction->dateCreated))?></td>
			<td><?php echo esc_html($affiliate->firstName)?> <?php echo esc_html($affiliate->lastName)?></td>
			<td><?php echo esc_html($affiliate->paypalEmail)?></td>
			<td><?php echo esc_html($transaction->status)?></td>
			<td><?php echo esc_html($transaction->description)?></td>
			<td style="text-align: right"><?php echo wpam_format_money($transaction->amount)?></td>
			<td><?php echo esc_html($transaction->status)?></td>
			<td><img src="<?php echo WPAM_URL . "/images/icon_arrow_next.png"?>" /></td>
			<td><?php echo esc_html($transaction->newStatus)?></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="100" style="text-align: right; padding: 20px;">
				<input type="submit" value="<?php _e('Reconcile Mass Payment', 'affiliates-manager');?>" class="button-primary" id="btnSubmit" />
			</td>
		</tr>
		</tbody>
	</table>
</form>
</div>