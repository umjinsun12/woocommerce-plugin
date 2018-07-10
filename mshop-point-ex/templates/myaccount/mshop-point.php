<?php
$page = get_query_var('mshop-point');

if( empty( $page ) ) {
	$page = 1;
}

$user = new MSPS_User( get_current_user_id() );
$point = $user->get_point();
$logs = MSPS_Ajax::myaccount_point_logs( $page );

$last_page = ceil( $logs['total_count'] / 10 );
?>

<table class="shop_table responsive my_account_orders">
    <thead>
    <tbody>
    <?php do_action( 'msps_beforer_myaccount_table_row' ); ?>
    <tr class="msps-info">
	    <td class="msps-title">
		    <?php _e( '회원 등급', 'mshop-point-ex' ); ?>
	    </td>
	    <td class="order-desc">
		    <?php echo mshop_point_get_user_role_name(); ?>
	    </td>
    </tr>
    <tr class="msps-info">
	    <td class="msps-title">
		    <?php _e( '보유 포인트', 'mshop-point-ex' ); ?>
	    </td>
	    <td class="order-desc">
		    <?php echo ! empty( $point ) ? number_format( $point ) : 0; ?>
	    </td>
    </tr>
    <?php if( count( $user->wallet->load_wallet_items() ) > 1 ) : ?>
    <?php foreach( $user->wallet->load_wallet_items() as $wallet_item ) : ?>
	    <tr class="msps-info">
		    <td class="msps-title">
			    <?php echo $wallet_item->label; ?>
		    </td>
		    <td class="order-desc">
			    <?php echo number_format( $wallet_item->get_point() ) ?>
		    </td>
	    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    <tr class="msps-info">
	    <td class="msps-title">
		    <?php _e( '최종 적립일', 'mshop-point-ex' ); ?>
	    </td>
	    <td class="order-desc">
		    <?php echo get_user_meta( get_current_user_id(), '_mshop_last_date', true ); ?>
	    </td>
    </tr>
    <?php do_action( 'msps_after_myaccount_table_row' ); ?>
    </tbody>
</table>
<h4><?php echo _e( '포인트 로그', 'mshop-point-ex' ); ?></h4>
<table class="shop_table responsive my_account_orders">
    <thead>
    <tr>
        <th class="msps-no"><span class="nobr"><?php _e('순번', 'mshop-point-ex'); ?></span></th>
        <th class="msps-date"><span class="nobr"><?php _e('날짜', 'mshop-point-ex'); ?></span></th>
        <th class="msps-amount"><span class="nobr"><?php _e('포인트', 'mshop-point-ex'); ?></span></th>
        <th class="msps-desc"><span class="nobr"><?php _e('비고', 'mshop-point-ex'); ?></span></th>
    </tr>
    </thead>

    <tbody>
    <?php foreach( $logs['results'] as $log ) : ?>
    <tr class="msps-log">
        <td class="msps-no">
            <?php echo $log['no']; ?>
        </td>
        <td class="msps-date">
            <?php echo $log['date']; ?>
        </td>
        <td class="msps-amount">
            <?php echo $log['amount']; ?>
        <td class="msps-desc">
            <?php echo $log['desc']; ?>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="mshop-point-pagination">
	<?php if ( $page > 1 ) : ?>
		<a class="button" href="<?php echo wc_get_endpoint_url('mshop-point') . ( $page - 1 ); ?>"><?php _e( '이전','mshop-point-ex' ); ?></a>
	<?php endif; ?>
	<?php if ( $last_page > $page ) : ?>
		<a class="button" href="<?php echo wc_get_endpoint_url('mshop-point') . ( $page + 1 ); ?>"><?php _e( '다음','mshop-point-ex' ); ?></a>
	<?php endif; ?>
</div>