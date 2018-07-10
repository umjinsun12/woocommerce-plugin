<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$user = new MSPS_User( get_current_user_id() );
?>

<tr>
	<td class="point-title" style="text-align:left">
		<?php _e('포인트','mshop-point-ex'); ?><br>
		<span style='font-size: 0.9em; color: gray;'>
			<?php echo sprintf( __('보유 포인트 : %s','mshop-point-ex'), number_format( floatval( $user_point ) ) ); ?>
		</span>
	</td>
	<td class="point-description update_totals_on_change">
		<?php if( $user_point > 0 ) : ?>
		<span style='font-size: 0.9em; color: gray;'><?php echo sprintf( __('포인트가 %s 이상 시 결제 사용이 가능합니다.','mshop-point-ex'), number_format( floatval( $purchase_minimum_point )) ); ?></span>
		<?php else: ?>
			<span style='font-size: 0.9em; color: gray;'><?php echo sprintf( __('보유 포인트가 없습니다.','mshop-point-ex') ); ?></span>
		<?php endif; ?>
	</td>
</tr>
