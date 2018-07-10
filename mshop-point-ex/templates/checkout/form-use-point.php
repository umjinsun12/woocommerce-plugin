<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$ratio = explode( '.', $point_exchange_ratio );
if( count( $ratio) > 1 ){
    $ratio = strlen( $ratio[1] ) >  wc_get_price_decimals() ? strlen( $ratio[1] ) : wc_get_price_decimals();
}else{
    $ratio = wc_get_price_decimals();
}

$args = array(
    'decimals' => $ratio
);

?>
<script>
    jQuery( document ).ready(function( $ ) {
        $('input.mshop_point.input-text').on( 'keydown', function(e){
            if(e.keyCode==13 && e.srcElement.type != 'textarea') {
                return false;
            }
        });
    });
</script>
<style>
    .point-title p{
        padding-bottom: 0px;
    }
</style>

<tr>
    <td class="point-title" style="text-align:left">
	    <p><?php _e('포인트','mshop-point-ex'); ?></p>
        <p style='font-size: 0.9em; color: gray;'><?php echo sprintf( __('보유 포인트 : %s','mshop-point-ex'), number_format( floatval( $user_point ) ) ); ?></p>
        <p style='font-size: 0.9em; color: gray;'><?php echo sprintf( __('결제 가용 포인트 : %s', 'mshop-point-ex'), $user_point > $max_useable_point ? number_format( floatval( $max_useable_point ) ) : number_format( floatval( $user_point ) ) ); ?></p>
        <?php
        echo sprintf( __('<p style=\'font-size: 0.9em; color: gray;\'>(1 포인트 = %s)</p>', 'mshop-point-ex'), wc_price( $point_exchange_ratio, $args ) );
	    $point_unit = get_option('mshop_point_system_point_unit_number');
	    if(! empty($point_unit) ){
		    echo sprintf( __('<p style=\'font-size: 0.9em; color: gray;\'>%s 포인트 단위로 사용할 수 있습니다.</p>', 'mshop-point-ex'), $point_unit );
	    } ?>
    </td>
    <td class="point-description <?php echo $update_by_wc ? 'update_totals_on_change' : 'msps_update_totals_on_change'; ?>">
        <input type="text" name="mshop_point" class="mshop_point input-text" value="">
    </td>
</tr>
