<?php
class MSPS_Cart {
	public static function woocommerce_after_add_to_cart_form() {
		global $post;

        $product_id = apply_filters( 'wpml_object_id', $post->ID , 'product', true, mshop_wpml_get_default_language() );
		$product = wc_get_product( $product_id );
        $price   = ( 'variable' == $product->get_type() ) ? $product->get_variation_price('max') : $product->get_price();

		$apply_point = apply_filters('msp_apply_point', $product->is_purchasable() && $price > 0, $product );

        if( $apply_point ){
            mshop_point_print_notice('', true);

            $language_args = mshop_wpml_get_current_language_args();
            if( !empty( $language_args ) ){
                $language_args = '?' . $language_args;
            }

            $rule_infos = MSPS_Manager::get_ruleset_for_product( $product, 1, mshop_point_get_user_role() );

            $additional_message = '';
            $additional_info = apply_filters( 'mshop_point_translate_string', get_option( 'mshop_point_system_guide_notice_info_at_product_detail'), 'guide_notice_info_at_product_detail' );
            if( !empty( $additional_info ) ){
                $infos = explode("\n", $additional_info );
                $infos = array_filter( $infos );
                $additional_message = '<li>' . implode( '</li><li>', $infos ) . '</li>';
            }

            wp_enqueue_script( 'mshop-point-add-to-cart', MSPS()->plugin_url() . '/assets/js/mshop-point-add-to-cart.js', array( 'jquery', 'jquery-ui-core' ));

	        $product_id = $product->get_id();

	        wp_localize_script( 'mshop-point-add-to-cart', '_mshop_point_add_to_cart', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' . $language_args ),
                'product_id'    => $product_id,
                'regular_price' => $product->get_regular_price(),
                'sale_price'    => $product->get_sale_price(),
                'currency_symbol'               => get_woocommerce_currency_symbol(),
                'thousand_separator'            => wc_get_price_thousand_separator(),
                'decimals'                      => wc_get_price_decimals(),
                'exchange_ratio'                => MSPS_Manager::point_exchange_ratio(),
                'rule_infos'                    => $rule_infos,
                'point_message'                 => apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_notice_at_product_detail', __( '상품 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' )), 'point_message' ),
                'point_guide_message_title'     => apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_guide_notice_title_at_product_detail', __( '[ 포인트 적립안내 ]', 'mshop-point-ex' )), 'point_guide_message_title' ),
                'additional_message'            => $additional_message,
                'point_guide_message_price_qty' => apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_notice_at_product_detail_price_qty', __( '{desc} 상품 {amount} 이상 또는 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' )), 'point_guide_message_price_qty' ),
                'point_guide_message_price'     => apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_notice_at_product_detail_price', __( '{desc} 상품 {amount} 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' )), 'point_guide_message_price' ),
                'point_guide_message_qty'       => apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_notice_at_product_detail_qty', __( '{desc} 상품 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' )), 'point_guide_message_qty' ),
                'additional_earn_message'       => __( '(추가적립) ', 'mshop-point-ex' ),
                'show_guide_message'            => MSPS_Manager::use_print_notice( 'product_guide' ),
                'sold_individually'             => $product->is_sold_individually() ? true : false
            ) );

            wp_enqueue_style( 'mshop-point', MSPS()->plugin_url() . '/assets/css/mshop-point.css' );
	        echo '<input type="hidden" class="mshop-quantity" value="1">';
        }
	}
    public static function woocommerce_after_cart_totals() {
	    $coupons = array_diff( WC()->cart->get_applied_coupons(), array( 'msms_discount' ) );

        if( !MSPS_Manager::allow_coupon() && count( $coupons ) > 0 ){
            wp_enqueue_style( 'mshop-point', MSPS()->plugin_url() . '/assets/css/mshop-point.css' );
            mshop_point_print_notice( __('쿠폰을 사용하여 포인트가 적립되지 않습니다', 'mshop-point-ex') );
            return;
        }

        if( MSPS_Manager::use_print_notice( 'product' ) ){
            $message = MSPS_Manager::show_message_for_cart( WC()->cart );
            if( !empty( $message ) ){
                wp_enqueue_style( 'mshop-point', MSPS()->plugin_url() . '/assets/css/mshop-point.css' );
                mshop_point_print_notice( $message );
            }
        }
	}

}
