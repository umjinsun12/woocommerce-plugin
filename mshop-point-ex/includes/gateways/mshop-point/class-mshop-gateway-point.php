<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MShop_Gateway_Point extends WC_Payment_Gateway {
	public $locale;
	public function __construct() {
		
		$this->id                 = 'mshop-point';
		$this->icon               = apply_filters('mshop_point_icon', '');
		$this->has_fields         = false;
		$this->method_title       = __( '포인트 결제', 'mshop-point-ex' );
		$this->method_description = __( '보유하신 포인트를 이용해서 결제합니다.', 'mshop-point-ex' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array($this, 'woocommerce_payment_complete_order_status' ), 15, 2 );
	}
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'mshop-point-ex' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Point Purchase', 'mshop-point-ex' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'mshop-point-ex' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'mshop-point-ex' ),
				'default'     => __( 'Point Purchase', 'mshop-point-ex' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'mshop-point-ex' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'mshop-point-ex' ),
				'default'     => __( '사용자에게 적립된 포인트를 이용한 결제를 지원합니다', 'mshop-point-ex' ),
				'desc_tip'    => true,
			),
		);
	}

	public function is_available() {
        $user = new MSPS_User( get_current_user_id() );
        $user_point = $user->get_point();

		$is_available = ( 'yes' === $this->enabled ) ? true : false;

		if( ! empty( WC()->cart ) ) {
			if ( $user_point < $this->get_order_total() / MSPS_Manager::point_exchange_ratio() ) {
				$is_available = false;
				add_action( 'woocommerce_review_order_before_submit', array( $this, 'woocommerce_review_order_before_submit'), 10);
			}else{
				$is_point_purchasable = true;
				foreach( WC()->cart->get_cart() as $cart_item ){
					$product = new MSPS_Product( $cart_item['data'] );
					if( !$product->is_point_purchasable() ){
						$is_point_purchasable = false;
						break;
					}
				}

				if( !$is_point_purchasable ){
					$is_available = false;
					add_action( 'woocommerce_review_order_before_submit', array( $this, 'cannot_purchase_use_point'), 10);
				}
			}
		}

		return $is_available;
	}

    public function cannot_purchase_use_point(){
        wc_get_template( 'checkout/cannot_purchase_use_point.php','' , '', MSPS()->template_path() );
    }

	public function woocommerce_review_order_before_submit(){
        $user = new MSPS_User( get_current_user_id() );
        $point = number_format( $user->get_point() );
		$order_total = WC()->cart->get_total();
        $message = apply_filters( 'mshop_point_translate_string', __('보유하신 포인트( $point )가 부족해서 포인트로 결제하실 수 없습니다.<br>','mshop-point-ex'), 'point_shortage_message' );

		if( !empty( $message ) ){
			eval("\$message = \"$message\";");
			echo $message;
		}
	}

	public function payment_fields( ) {
        $user = new MSPS_User( get_current_user_id() );
		echo '<p>' . $this->description . '</p>';
        echo sprintf( __('<p>보유포인트 : %s</p>', 'mshop-point-ex'), number_format_i18n( $user->get_point() ) );
	}

	public function woocommerce_payment_complete_order_status($new_order_status, $id) {
		$paymethod = get_post_meta($id, '_payment_method', true);

		if($this->id == $paymethod) {
			$order_status = get_option( 'mshop_point_system_order_status_after_payment', $new_order_status );
			if ( !empty($order_status) ) {
				return $order_status;
			} else {
				return $new_order_status;
			}
		} else {
			return $new_order_status;
		}
	}
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
        $user = new MSPS_User( get_current_user_id() );
		$used_point = $order->get_total() / MSPS_Manager::point_exchange_ratio();

        $prev_point = $user->get_point();
        $remain_point = $user->deduct_point( $used_point );

		$order->add_order_note( sprintf(__('포인트(%s)를 사용해서 결제했습니다.','mshop-point-ex'), number_format( $used_point ) ) );

		$message = sprintf(__('주문(<a href="%s">#%s</a>) 결제시 포인트 사용으로 %s포인트가 차감되었습니다.<br>보유포인트가 %s포인트에서 %s포인트로 변경되었습니다.', 'mshop-point-ex'), $order->get_view_order_url(), $order_id, number_format( floatval( $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
		do_action( 'mshop_add_point_history', get_current_user_id(), -1*$used_point, $message, false );

		$message = sprintf(__('주문(<a href="%s">#%s</a>) 결제시 포인트 사용으로 %s포인트가 차감되었습니다.<br>보유포인트가 %s포인트에서 %s포인트로 변경되었습니다.', 'mshop-point-ex'), get_edit_post_link( $order_id ), $order_id, number_format( floatval( $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
		do_action( 'mshop_add_point_history', get_current_user_id(), -1*$used_point, $message, true );

		$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );

		// Reduce stock levels
		if ( version_compare( WOOCOMMERCE_VERSION, '2.7.0', '>=' ) ) {
			wc_reduce_stock_levels($order);
		} else {
			$order->reduce_order_stock();
		}

		// Remove cart
		WC()->cart->empty_cart();


		$order->payment_complete();

		// Return thankyou redirect
		return array(
			'result'    => 'success',
			'redirect'  => $this->get_return_url( $order )
		);

	}
}