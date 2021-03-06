<?php
$point = get_user_meta( get_current_user_id(), '_mshop_point', true );
$logs = MSPS_Ajax::myaccount_point_logs();

?>

<h2><?php echo _e( '내 포인트', 'mshop-point-ex'); ?></h2>
<ul id="mshop_point_log_tabs" class="mshop_list_table_myaccount">
    <div>
	    <table class="shop_table responsive my_account_orders">
		    <thead>
		    <tbody>
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
		    <tr class="msps-info">
			    <td class="msps-title">
				    <?php _e( '최종 적립일', 'mshop-point-ex' ); ?>
			    </td>
			    <td class="order-desc">
				    <?php echo get_user_meta( get_current_user_id(), '_mshop_last_date', true ); ?>
			    </td>
		    </tr>
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
    </div>
</ul>