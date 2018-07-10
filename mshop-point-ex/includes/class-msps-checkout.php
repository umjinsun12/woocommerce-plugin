<?php
class MSPS_Checkout {
	public static function woocommerce_before_checkout_form() {
		$coupons = array_diff( WC()->cart->get_applied_coupons(), array ( 'msms_discount' ) );

		if ( ! MSPS_Manager::allow_coupon() && count( $coupons ) > 0 ) {
			wp_enqueue_style( 'mshop-point', MSPS()->plugin_url() . '/assets/css/mshop-point.css' );
			mshop_point_print_notice( __( '쿠폰을 사용하여 포인트가 적립되지 않습니다', 'mshop-point-ex' ) );

			return;
		}

		if ( MSPS_Manager::use_print_notice( 'product' ) ) {
			$message = MSPS_Manager::show_message_for_checkout( WC()->cart );
			if ( ! empty( $message ) ) {
				wp_enqueue_style( 'mshop-point', MSPS()->plugin_url() . '/assets/css/mshop-point.css' );
				mshop_point_print_notice( $message );
			}
		}
	}
	public static function woocommerce_checkout_cart_item_quantity( $title, $cart_item, $cart_item_key ) {
		$product = new MSPS_Product( $cart_item['data'] );
		if ( ! $product->is_point_purchasable() ) {
			$title .= '<span style="margin-left: 5px; font-size: 0.9em; color: blue;">' . __( '( 포인트 구매불가 상품 )', 'mshop-point-ex' ) . '</span>';
		}

		return $title;
	}
	public static function woocommerce_review_order_after_shipping() {
		$max_useable_amount      = MSPS_Manager::max_useable_amount( WC()->cart );
		$max_useable_point       = MSPS_Manager::max_useable_point( WC()->cart );
		$purchase_minimum_point  = MSPS_Manager::purchase_minimum_point();
		$purchase_minimum_amount = apply_filters( 'wcml_raw_price_amount', floatval( MSPS_Manager::purchase_minimum_amount() ) );
		$point_exchange_ratio    = MSPS_Manager::point_exchange_ratio();
		$used_point              = isset( WC()->cart->mshop_point ) ? WC()->cart->mshop_point : '';

		$user       = new MSPS_User( get_current_user_id() );
		$user_point = $user->get_point();

		if ( $used_point == '' && $max_useable_amount <= 0 ) {
			wc_get_template( 'checkout/no-purchaseable-product.php', array ( 'user_point' => $user_point ), '', MSPS()->template_path() );
		} else if ( $max_useable_amount < $purchase_minimum_amount ) {
			wc_get_template( 'checkout/minimum-purchase-amount.php', array (
				'user_point'              => $user_point,
				'purchase_minimum_amount' => $purchase_minimum_amount
			), '', MSPS()->template_path() );
		} else if ( $user->get_point() == 0 || $user->get_point() < $purchase_minimum_point ) {
			wc_get_template( 'checkout/shortage-point.php', array (
				'user_point'             => $user_point,
				'purchase_minimum_point' => $purchase_minimum_point
			), '', MSPS()->template_path() );
		} else {
			$update_timeout = get_option( 'msps_update_timeout', 1000 );
			$update_timeout = intval( $update_timeout );
			if ( $update_timeout > 1000 ) {
				wp_enqueue_script( 'msps_checkout', MSPS()->plugin_url() . '/assets/js/msps-checkout.js', array ( 'jquery' ), MSPS()->version );
				wp_localize_script( 'msps_checkout', 'msps_checkout_params', array ( 'update_timeout' => get_option( 'msps_update_timeout' ) ) );
			}

			wc_get_template( 'checkout/form-use-point.php', array (
				'used_point'           => $used_point,
				'user_point'           => $user_point,
				'max_useable_point'    => $used_point * MSPS_Manager::purchase_maximum_ratio() / 100 + $max_useable_point,
				'point_exchange_ratio' => $point_exchange_ratio,
				'update_by_wc'         => $update_timeout <= 1000,
			), '', MSPS()->template_path() );
		}

		wc_get_template( 'checkout/used-point.php', array (
			'used_point'           => WC()->cart->mshop_point,
			'point_exchange_ratio' => $point_exchange_ratio
		), '', MSPS()->template_path() );

	}
	private static function get_point_param() {
		$point = 0;

		if ( ! empty( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $params );

			if ( isset( $params['mshop_point'] ) && '' != $params['mshop_point'] ) {
				$point = intval( $params['mshop_point'] );
			} else if ( isset( $params['_mshop_point'] ) ) {
				$point = intval( $params['_mshop_point'] );
			}
		} else if ( isset( $_POST['_mshop_point'] ) ) {
			$point = intval( $_POST['_mshop_point'] );
		}

		return $point;
	}
	public static function get_used_point_from_order( $order ) {
		$point = 0;
		foreach ( $order->get_items( 'fee' ) as $item_id => $item ) {
			if ( 0 === strpos( $item['name'], '포인트 할인' ) ) {
				if ( wc_prices_include_tax() ) {
					$point += absint( round( $item['line_total'] + $item['line_tax'], wc_get_price_decimals() ) );
				} else {
					$point += absint( $item['line_total'] );
				}
			}
		}

		return $point;
	}
	public static function woocommerce_checkout_order_processed( $order_id, $post ) {
		$point      = self::get_point_param();
		$order      = wc_get_order( $order_id );
		$user       = new MSPS_User( $order->get_user_id() );
		$prev_point = $user->get_point();

		$used_point = self::get_used_point_from_order( $order );

		if ( $point != $used_point || ( $point > 0 && $user->get_point() < $point ) ) {
			throw new Exception( __( '보유하신 포인트가 부족합니다. 페이지를 새로고침 하신 후 다시 시도해주세요.', 'mshop-point-ex' ) );
		}

		if ( $point != 0 && 'yes' != get_post_meta( $order_id, '_mshop_point_purchase_processed', true ) ) {

			$deduction_info = $user->wallet->get_deduction_info( $point );
			MSPS_Order::update_used_point( $order_id, $order->get_user_id(), $deduction_info );
			$user->wallet->deduct( $deduction_info );

			$remain_point = $user->get_point();

			$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>) 결제시 포인트 사용으로 %3$s포인트가 차감되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( $point ) ), number_format( $prev_point ), number_format( $remain_point ) );
			do_action( 'mshop_add_point_history', $order->get_user_id(), - 1 * $point, $message, false );

			$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>) 결제시 포인트 사용으로 %3$s포인트가 차감되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( $point ) ), number_format( $prev_point ), number_format( $remain_point ) );
			do_action( 'mshop_add_point_history', $order->get_user_id(), - 1 * $point, $message, true );

			$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );
		}
	}

	public static function adjust_tax_amount( $tax_amount ) {
		if ( wc_prices_include_tax() ) {
			$tax_rates  = WC_Tax::get_rates();
			$fee_taxes  = WC_Tax::calc_inclusive_tax( $tax_amount, $tax_rates );
			$tax_amount -= array_sum( $fee_taxes );
		}

		return $tax_amount;
	}
	public static function woocommerce_cart_calculate_fees( $cart ) {
		if ( is_checkout() ) {
			$user = new MSPS_User( get_current_user_id() );

			//세션에서 주문정보를 가져옴
			$order_id = absint( WC()->session->order_awaiting_payment );

			// 기존 주문이 있으면 포인트 확인후, 재적립 처리
			if ( $order_id > 0 && ( $order = wc_get_order( $order_id ) ) && $order->has_status( array (
					'pending',
					'failed'
				) )
			) {
				if ( MSPS_Order::is_order_with_point( $order_id, get_current_user_id() ) ) {

					$deduction_info = MSPS_Order::get_deduction_info( $order_id );
					$used_point     = MSPS_Order::get_used_point( $order_id );

					if ( intval( $used_point ) > 0 && MSPS_Order::update_order( $order, $used_point ) ) {
						$user->wallet->redeposit( $deduction_info );

						$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>) 결제시 포인트 사용금액 변경으로 사용된 %3$s포인트가 재적립 되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( ( $used_point ) ) ) );
						do_action( 'mshop_add_point_history', get_current_user_id(), $used_point, $message, false );

						$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>) 결제시 포인트 사용금액 변경으로 사용된 %3$s포인트가 재적립 되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( ( $used_point ) ) ) );
						do_action( 'mshop_add_point_history', get_current_user_id(), $used_point, $message, true );

						$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );

						MSPS_Order::update_used_point( $order_id, get_current_user_id(), 0 );
					}
				}
			}

			$point_exchange_ratio = MSPS_Manager::point_exchange_ratio();
			$max_useable_point    = MSPS_Manager::max_useable_point( WC()->cart );

			$user_point        = $user->get_point();
			$want_to_use_point = self::get_point_param();
			if ( $max_useable_point < $want_to_use_point ) {
				$want_to_use_point = $max_useable_point;
			}
			if ( $user_point < $want_to_use_point ) {
				$want_to_use_point = $user_point;
			}
			$point_unit = get_option( 'mshop_point_system_point_unit_number' );
			if ( ! empty( $point_unit ) ) {
				$want_to_use_point = intval( $want_to_use_point / $point_unit ) * $point_unit;
			}

			if ( $want_to_use_point > 0 ) {
				$cart->mshop_point = $want_to_use_point;
				if ( wc_tax_enabled() && apply_filters( 'msps_apply_tax_calculation', true ) ) {
					$tax_amount    = 0;
					$no_tax_amount = 0;
					foreach ( $cart->cart_contents as $cart_content ) {
						if ( $cart_content['line_tax'] <= 0 ) {
							$no_tax_amount += $cart_content['line_total'];
						} else {
							if ( wc_prices_include_tax() ) {
								$tax_amount += $cart_content['line_total'] + $cart_content['line_tax'];
							} else {
								$tax_amount += $cart_content['line_total'];
							}
						}
					}

					if ( 'lowest' == get_option( 'mshop_point_system_apply_order_for_tax', 'lowest' ) ) {
						if ( $no_tax_amount > 0 && $no_tax_amount >= $want_to_use_point * $point_exchange_ratio ) {
							$cart->add_fee( __( '포인트 할인', 'mshop-point-ex' ), - 1 * $want_to_use_point * $point_exchange_ratio );
						} else {
							$tax_amount = self::adjust_tax_amount( $want_to_use_point * $point_exchange_ratio - $no_tax_amount );
							if ( $no_tax_amount > 0 ) {
								$cart->add_fee( __( '포인트 할인 (비과세)', 'mshop-point-ex' ), - 1 * $no_tax_amount );
							}
							if ( $tax_amount > 0 ) {
								$cart->add_fee( __( '포인트 할인 (과세)', 'mshop-point-ex' ), - 1 * $tax_amount, true );
							}
						}
					} else {
						if ( $tax_amount >= $want_to_use_point * $point_exchange_ratio ) {
							$tax_amount = self::adjust_tax_amount( $want_to_use_point * $point_exchange_ratio );
							$cart->add_fee( __( '포인트 할인', 'mshop-point-ex' ), - 1 * $tax_amount, true );
						} else {
							$no_tax_amount = $want_to_use_point * $point_exchange_ratio - $tax_amount;
							if ( $no_tax_amount > 0 ) {
								$cart->add_fee( __( '포인트 할인 (비과세)', 'mshop-point-ex' ), - 1 * $no_tax_amount );
							}
							$tax_amount = self::adjust_tax_amount( $tax_amount );
							if ( $tax_amount > 0 ) {
								$cart->add_fee( __( '포인트 할인 (과세)', 'mshop-point-ex' ), - 1 * $tax_amount, true );
							}
						}
					}

				} else {
					$cart->add_fee( __( '포인트 할인', 'mshop-point-ex' ), - 1 * $want_to_use_point * $point_exchange_ratio);
				}

			} else {
				unset( $cart->mshop_point );
			}
		}
	}
}