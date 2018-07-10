<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


function msps_get_user_roles() {
	$results = array ();

	$roles = get_editable_roles();

	$filters = get_option( 'mshop_point_system_role_filter' );

	foreach ( $filters as $role ) {
		if ( 'yes' === $role['enabled'] && array_key_exists( $role['role'], $roles ) ) {
			$results[ $role['role'] ] = ! empty( $role['nickname'] ) ? $role['nickname'] : $role['name'];
		}
	}

	return $results;
}
function msps_get_product_id( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	if ( $product ) {
		if ( $product->is_type( 'variation' ) ) {
			if ( is_callable( array ( $product, 'get_parent_id' ) ) ) {
				return $product->get_parent_id();
			} else {
				return $product->id;
			}
		} else {
			if ( is_callable( array ( $product, 'get_id' ) ) ) {
				return $product->get_id();
			} else {
				return $product->id;
			}
		}
	}
}
function msps_get_object_property( $object, $property ) {
	$method = 'get_' . $property;

	return is_callable( array ( $object, $method ) ) ? $object->$method() : $object->$property;
}

function mshop_get_point_rule( $the_point_rule = false, $args = array () ) {
	return MSPS()->point_rule_factory->get_point_rule( $the_point_rule, $args );
}

function mshop_point_print_notice( $message, $hidden = false ) {
	wc_get_template( 'notice/product_notice.php', array ( 'message' => $message, 'hidden' => $hidden ), '', MSPS()->template_path() );
}

function mshop_point_print_post_notice( $message ) {
	wc_get_template( 'notice/post_notice.php', array ( 'message' => $message ), '', MSPS()->template_path() );
}

function mshop_point_get_user_role( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = wp_get_current_user();
	}

	if ( is_numeric( $user_id ) ) {
		$user = new WP_User( $user_id );

		return array_shift( $user->roles );
	} else if ( $user_id instanceof WP_User ) {
		$user_roles = $user_id->roles;

		return array_shift( $user_roles );
	} else {
		return null;
	}
}

function mshop_point_get_user_role_name( $user_role = null ) {
	if ( is_null( $user_role ) ) {
		$user_role = mshop_point_get_user_role();
	}

	$roles = get_option( 'mshop_point_system_role_filter', array () );

	foreach ( $roles as $role ) {
		if ( 'yes' === $role['enabled'] && $role['role'] == $user_role ) {
			return ! empty( $role['nickname'] ) ? $role['nickname'] : $role['name'];
		}
	}

	return '';
}
function msps_wcs_renewal_order_items( $items, $new_order, $subscription ) {
	$items = array_filter( $items,
		function ( $item ) {
			return $item['type'] != 'fee' || $item['name'] != __( '포인트 할인', 'mshop-point-ex' );
		}
	);

	return $items;
}
function msps_wcs_resubscribe_order_items( $items, $new_order, $subscription ) {
	$items = array_filter( $items,
		function ( $item ) {
			return $item['type'] != 'fee' || $item['name'] != __( '포인트 할인', 'mshop-point-ex' );
		}
	);

	return $items;
}
function msps_wcs_new_order_created( $new_order, $subscription, $type ) {
	$new_order->calculate_totals( true );

	return $new_order;
}

if ( MSPS_Manager::enabled() ) {
	add_filter( 'wcs_renewal_order_items', 'msps_wcs_renewal_order_items', 10, 3 );
	add_filter( 'wcs_renewal_order_items', 'msps_wcs_resubscribe_order_items', 10, 3 );
	add_filter( 'wcs_new_order_created', 'msps_wcs_new_order_created', 10, 3 );
	if ( MSPS_Manager::is_purchase_method_checkout_point() ) {
		add_action( 'woocommerce_review_order_before_order_total', 'MSPS_Checkout::woocommerce_review_order_after_shipping' );
		add_action( 'woocommerce_checkout_order_processed', 'MSPS_Checkout::woocommerce_checkout_order_processed', 100, 2 );
	}

	if ( MSPS_Manager::is_purchase_method_payment_gateway() ) {
		add_filter( 'woocommerce_payment_gateways', 'MShop_Point::woocommerce_payment_gateways' );
	}
	if ( MSPS_Manager::use_user_register_rule() ) {
		add_action( 'woocommerce_register_form', 'MSPS_Myaccount::woocommerce_register_form' );
		add_action( 'user_register', 'MSPS_Myaccount::user_register' );
	}
	add_filter( 'woocommerce_admin_order_totals_after_discount', 'MSPS_Order::woocommerce_admin_order_totals_after_discount' );
	add_filter( 'woocommerce_get_order_item_totals', 'MSPS_Order::woocommerce_get_order_item_totals', 10, 2 );
	add_action( 'woocommerce_saved_order_items', 'MSPS_Order::woocommerce_saved_order_items', 10, 2 );

	add_action( 'woocommerce_cart_calculate_fees', 'MSPS_Checkout::woocommerce_cart_calculate_fees', 100 );
	add_action( 'woocommerce_order_status_changed', 'MSPS_Order::woocommerce_order_status_changed', 100, 3 );
	add_action( 'woocommerce_checkout_order_processed', 'MSPS_Order::woocommerce_checkout_order_processed', 100, 2 );

	if ( MSPS_Manager::use_purchase_point_rule() ) {

		if ( MSPS_Manager::use_print_notice( 'product' ) ) {
			add_action( 'woocommerce_after_add_to_cart_form', 'MSPS_Cart::woocommerce_after_add_to_cart_form' );
			add_action( 'woocommerce_before_cart_table', 'MSPS_Cart::woocommerce_after_cart_totals' );
			add_action( 'woocommerce_before_checkout_form', 'MSPS_Checkout::woocommerce_before_checkout_form' );
		}

		add_filter( 'woocommerce_checkout_cart_item_quantity', 'MSPS_Checkout::woocommerce_checkout_cart_item_quantity', 10, 3 );
		add_filter( 'woocommerce_product_data_tabs', 'MSPS_Admin_Meta_Box_Product_Point::woocommerce_product_data_tabs' );
		add_action( 'woocommerce_product_data_panels', 'MSPS_Admin_Meta_Box_Product_Point::woocommerce_product_data_panels' );
		add_action( 'wp_ajax_mshop_point_update_product_settings', 'MSPS_Admin_Meta_Box_Product_Point::mshop_point_update_product_settings' );
//        add_action( 'woocommerce_product_after_variable_attributes', 'MSPS_Admin_Meta_Box_Product_Point::woocommerce_product_after_variable_attributes', 10, 3 );

	}

	if ( MSPS_Post_Manager::use_post_point_rule() ) {
		add_action( 'wp_insert_comment', 'MSPS_Comment::wp_insert_comment', 100, 2 );
		add_action( 'wp_set_comment_status', 'MSPS_Comment::wp_set_comment_status', 100, 2 );
		add_action( 'comment_form', 'MSPS_Post_Manager::comment_form', 100, 2 );

		add_action( 'transition_post_status', 'MSPS_Post::transition_post_status', 100, 3 );
	}

	add_action( 'mshop_add_point_history', 'MSPS_History::mshop_add_point_history', 10, 4 );

	if ( defined( 'DOING_AJAX' ) ) {
		add_filter( 'wcml_load_multi_currency', 'mshop_point_wcml_load_multi_currency' );

		function mshop_point_wcml_load_multi_currency( $flag ) {
			return true;
		}
	}
	add_action( 'delete_user', 'msps_reset_user_point', 10, 2 );
	function msps_reset_user_point( $user_id, $reassign ) {
		$user = new MSPS_User( $user_id );

		if ( $user ) {
			$user->reset_user_point();
		}
	}

}