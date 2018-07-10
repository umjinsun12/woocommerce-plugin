<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<tr>
	<td class="point-title" style="text-align:left">
		<?php _e('포인트','mshop-point-ex'); ?>
	</td>
	<td class="point-description update_totals_on_change">
		<span style='font-size: 0.9em; color: gray;'><?php echo sprintf( __('%s 이상 구매 시 포인트 사용이 가능합니다.', 'mshop-point-ex'), wc_price( floatval( $purchase_minimum_amount ) ) ); ?></span>
	</td>
</tr>

