<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<?php if( !empty( $used_point ) ) : ?>
    <tr style="display:none !important;">
    <td class="point-title" style="text-align:left">
        <?php _e('포인트 할인','mshop-point-ex'); ?>
    </td>
    <td class="point-description">
        <span style='font-size: 0.9em; color: blue;'><?php echo wc_price( floatval( $used_point * $point_exchange_ratio ) ); ?></span>
        <input type="hidden" name="_mshop_point" value="<?php echo $used_point; ?>">
    </td>
    </tr>
<?php endif; ?>
