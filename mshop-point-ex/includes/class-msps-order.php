<?php
class MSPS_Order {

	private static $earn_point_statuses = null;

	private static function get_point_param() {
		$point = 0;

		if ( ! empty( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $params );

			if ( isset( $params['mshop_point'] ) && '' != $params['mshop_point'] ) {
				$point = intval( $params['mshop_point'] );
			} else if ( isset( $params['_mshop_point'] ) ) {
				$point = $params['_mshop_point'];
			}
		} else if ( isset( $_POST['_mshop_point'] ) ) {
			$point = intval( $_POST['_mshop_point'] );
		}

		return $point;
	}

	public static function get_used_point( $order_id ) {
		$deduction_info = self::get_deduction_info( $order_id );

		return array_sum( $deduction_info );
	}

	public static function get_deduction_info( $order_id ) {
		$deduction_info = get_post_meta( $order_id, '_mshop_point', true );
		if ( ! is_array( $deduction_info ) ) {
			$deduction_info = array ( 'free_point' => $deduction_info );
		}

		return $deduction_info;
	}

	public static function is_order_with_point( $order_id, $user_id ) {
		return 'yes' == get_post_meta( $order_id, '_mshop_point_purchase_processed', true ) && $user_id == get_post_meta( $order_id, '_mshop_point_purchase_user_id', true );
	}
	public static function set_earn_point( $order_id, $amount ) {
		if ( $amount > 0 ) {
			update_post_meta( $order_id, '_mshop_point_amount', $amount );
		} else {
			delete_post_meta( $order_id, '_mshop_point_amount' );
		}
	}

	public static function get_earn_point( $order_id ) {
		$earn_point = get_post_meta( $order_id, '_mshop_point_amount', true );

		return is_null( $earn_point ) ? 0 : $earn_point;
	}

	public static function is_earn_processed( $order_id ) {
		return 'yes' == get_post_meta( $order_id, '_mshop_point_processed', true );
	}
	public static function set_earn_processed( $order_id, $flag ) {
		update_post_meta( $order_id, '_mshop_point_processed', $flag ? 'yes' : 'no' );
	}

	public static function is_redeposit_processed( $order_id ) {
		return 'yes' == get_post_meta( $order_id, '_mshop_point_refunded', true );
	}
	public static function set_redeposit_processed( $order_id, $flag ) {
		update_post_meta( $order_id, '_mshop_point_refunded', $flag ? 'yes' : 'no' );
	}
	public static function update_used_point( $order_id, $user_id, $point ) {
		if ( $point > 0 ) {
			update_post_meta( $order_id, '_mshop_point', $point );
			update_post_meta( $order_id, '_mshop_point_purchase_processed', 'yes' );
			update_post_meta( $order_id, '_mshop_point_purchase_user_id', $user_id );
			update_post_meta( $order_id, '_mshop_point_by_fee', 'yes' );
		} else {
			delete_post_meta( $order_id, '_mshop_point' );
			delete_post_meta( $order_id, '_mshop_point_purchase_processed' );
			delete_post_meta( $order_id, '_mshop_point_purchase_user_id' );
			delete_post_meta( $order_id, '_mshop_point_by_fee' );
		}
	}

	public static function woocommerce_admin_order_totals_after_discount( $order_id ) {
		$point_exchange_ratio = MSPS_Manager::point_exchange_ratio();
		$point                = get_post_meta( $order_id, '_mshop_point', true );
		$order                = new WC_Order( $order_id );

		if ( 'yes' !== get_post_meta( $order_id, '_mshop_point_by_fee', 'no' ) ) {
			?>
            <tr>
                <td class="label"><?php _e( '적립금 할인', 'mshop-point-ex' ); ?> <span class="tips" data-tip="<?php _e( 'This is the total discount applied after tax.', 'mshop-point-ex' ); ?>">[?]</span>:</td>
                <td class="total">
                    <div class="view"><?php echo wc_price( $point * $point_exchange_ratio ); ?></div>
                    <div class="edit" style="display: none;">
                        <input type="text" class="wc_input_price" id="_mshop_point" name="_mshop_point" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" value="<?php echo ( isset( $point ) ) ? esc_attr( wc_format_localized_price( $point ) ) : ''; ?>"/>
                        <div class="clear"></div>
                    </div>
                </td>
                <div style="display:none" class="woocommerce_order_items">
                    <input type="hidden" class="line_tax" value="<?php echo ( isset( $point ) ) ? esc_attr( wc_format_localized_price( - 1 * $point ) ) : ''; ?>"/>
                </div>
                <td><?php if ( $order->is_editable() ) : ?>
                        <div class="wc-order-edit-line-item-actions"><a class="edit-order-item" href="#"></a></div><?php endif; ?></td>
            </tr>
			<?php
		}
	}
	public static function woocommerce_get_order_item_totals( $total_rows, $order ) {
		$point_exchange_ratio = MSPS_Manager::point_exchange_ratio();

		$used_point   = MSPS_Order::get_used_point( msps_get_object_property( $order, 'id' ) );
		$point_by_fee = get_post_meta( msps_get_object_property( $order, 'id' ), '_mshop_point_by_fee', 'no' );

		if ( $used_point > 0 && 'yes' !== $point_by_fee ) {
			$point_row = array (
				'label' => __( '포인트 할인:', 'mshop-point-ex' ),
				'value' => wc_price( floatval( $used_point ) * $point_exchange_ratio )
			);

			return array_merge( array_slice( $total_rows, 0, sizeof( $total_rows ) - 1 ), array ( $point_row ), array_slice( $total_rows, sizeof( $total_rows ) - 1 ) );
		} else {
			return $total_rows;
		}
	}

	public static function woocommerce_saved_order_items( $order_id, $items ) {
//        $prev_point = get_post_meta( $order_id, '_mshop_point', true );
//        $current_point = wc_format_decimal( $items['_mshop_point'] );
//        $order_total = wc_format_decimal( $items['_order_total'] );
//        $order_total = $order_total + $prev_point - $current_point;
//
//        update_post_meta( $order_id, '_mshop_point', $current_point );
//        update_post_meta( $order_id, '_order_total', $order_total );
	}
	static function update_order( $order, $used_point ) {
		$fee_total = 0;
		$fees      = $order->get_fees();

		foreach ( $order->get_items( 'fee' ) as $item_id => $item ) {
			if ( 0 === strpos( $item['name'], '포인트 할인' ) ) {
				if ( wc_prices_include_tax() ) {
					$fee_total += absint( round( $item['line_total'] + $item['line_tax'], wc_get_price_decimals() ) );
				} else {
					$fee_total += absint( $item['line_total'] );
				}
			}
		}

		if ( $fee_total > 0 && $fee_total == $used_point * MSPS_Manager::point_exchange_ratio() ) {
			foreach ( $fees as $item_id => $item ) {
				if ( 0 === strpos( $item['name'], __( '포인트 할인', 'mshop-mcommerce-premium' ) ) ) {
					$item['line_total'] = 0;
					$order->update_fee( $item_id, $item );
				}
			}

			$order->calculate_totals( wc_tax_enabled() );

			return true;
		}

		return false;
	}

	static function process_redeposit( $order_id, $old_status, $new_status ) {
		$order = wc_get_order( $order_id );
		if ( ! empty( $order->get_user_id() ) ) {
			$user       = new MSPS_User( $order->get_user_id() );
			$prev_point = $user->get_point();

			$p_payment_method = msps_get_object_property( $order, 'payment_method' );

			if ( MSPS_Manager::PAYMENT_GATEWAY_POINT != $p_payment_method ) {

				// Check redeposit feature is supported
				if ( MSPS_Manager::support_redeposit_when_refunded() ) {

					// Check order status and already not redeposited
					if ( in_array( $new_status, array (
							'cancelled',
							'refunded',
							'failed'
						) ) && ! self::is_redeposit_processed( $order_id )
					) {

						$deduction_info = MSPS_Order::get_deduction_info( $order_id );
						$used_point     = MSPS_Order::get_used_point( $order_id );

						if ( $used_point > 0 && self::update_order( $order, $used_point ) ) {
							$user->wallet->redeposit( $deduction_info );
							$remain_point = $user->get_point();
							self::set_redeposit_processed( $order_id, true );
							$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>)이 취소되어 결제에 사용된 %3$s포인트가 재적립되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
							do_action( 'mshop_add_point_history', $order->get_user_id(), $used_point, $message, false );

							$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>)이 취소되어 결제에 사용된 %3$s포인트가 재적립되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
							do_action( 'mshop_add_point_history', $order->get_user_id(), $used_point, $message, true );

							$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );
						}
					}
				}
			} else if ( MSPS_Manager::PAYMENT_GATEWAY_POINT == $p_payment_method ) {

				if ( in_array( $new_status, array (
						'cancelled',
						'refunded',
						'failed'
					) ) && ! self::is_redeposit_processed( $order_id )
				) {
					$prev_point   = $user->get_point();
					$used_point   = $order->get_total() / MSPS_Manager::point_exchange_ratio();
					$remain_point = $user->earn_point( $used_point );
					self::set_redeposit_processed( $order_id, true );

					$message = sprintf( __( '주문(<a href="%s">#%s</a>) 결제 취소로 %s포인트가 재적립 되었습니다.<br>보유포인트가 %s포인트에서 %s포인트로 변경되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), $used_point, $message, false );

					$message = sprintf( __( '주문(<a href="%s">#%s</a>) 결제 취소로 %s포인트가 재적립 되었습니다.<br>보유포인트가 %s포인트에서 %s포인트로 변경되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), $used_point, $message, true );

					$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );

					$order->calculate_totals();

				} else if ( in_array( $old_status, array (
						'cancelled',
						'refunded',
						'failed'
					) ) && self::is_redeposit_processed( $order_id )
				) {
					$prev_point   = $user->get_point();
					$used_point   = $order->get_total() / MSPS_Manager::point_exchange_ratio();
					$remain_point = $user->deduct_point( $used_point );
					self::set_redeposit_processed( $order_id, false );

					$message = sprintf( __( '주문(<a href="%s">#%s</a>) 결제시 포인트 사용으로 %s포인트가 차감되었습니다.<br>보유포인트가 %s포인트에서 %s포인트로 변경되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( - 1 * $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), - 1 * $used_point, $message, false );

					$message = sprintf( __( '주문(<a href="%s">#%s</a>) 결제시 포인트 사용으로 %s포인트가 차감되었습니다.<br>보유포인트가 %s포인트에서 %s포인트로 변경되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( - 1 * $used_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), - 1 * $used_point, $message, true );

					$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );
				}
			}
		}
	}

	protected static function get_earn_point_status( $order_id ) {
		if ( is_null( self::$earn_point_statuses ) ) {
			$order_statuses = explode( ',', get_option( 'msps_point_eran_status', 'completed' ) );

			self::$earn_point_statuses = apply_filters( 'msps_get_earn_point_status', $order_statuses, $order_id );
		}

		return self::$earn_point_statuses;
	}

	static function process_point( $order_id, $old_status, $new_status ) {
		$order = new WC_Order( $order_id );

		if ( ! empty( $order->get_user_id() ) ) {
			$user       = new MSPS_User( $order->get_user_id() );
			$used_point = MSPS_Order::get_used_point( $order_id );
			$prev_point = $user->get_point();
			if ( ! MSPS_Manager::allow_coupon() && count( $order->get_used_coupons() ) > 0 ) {
				return;
			}

			$p_payment_method = msps_get_object_property( $order, 'payment_method' );

			if ( MSPS_Manager::PAYMENT_GATEWAY_POINT != $p_payment_method ) {
				if ( $used_point > 0 && ! MSPS_Manager::support_earn_point_for_point_discount() ) {
					return;
				}
			} else if ( MSPS_Manager::PAYMENT_GATEWAY_POINT == $p_payment_method ) {
				if ( ! MSPS_Manager::support_earn_point_for_point_payment() ) {
					return;
				}
			}

			$earn_point = self::get_earn_point( $order_id );

			if ( $earn_point > 0 ) {
				if ( in_array( $new_status, self::get_earn_point_status( $order_id ) ) && ! self::is_earn_processed( $order_id ) ) {
					$remain_point = $user->earn_point( $earn_point );
					self::set_earn_processed( $order_id, true );
					$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>)이 완료되어 %3$s포인트가 적립되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( $earn_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), floatval( $earn_point ), $message, false );

					$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>)이 완료되어 %3$s포인트가 적립되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( $earn_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), floatval( $earn_point ), $message, true );

					$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );

					do_action( 'msps_earn_point', $earn_point, $order_id );

				} else if ( ! in_array( $new_status, self::get_earn_point_status( $order_id ) ) && in_array( $old_status, self::get_earn_point_status( $order_id ) ) && self::is_earn_processed( $order_id ) ) {
					$remain_point = $user->deduct_point( $earn_point );
					self::set_earn_processed( $order_id, false );
					$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>)이 취소되어 %3$s포인트가 차감되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), $order->get_view_order_url(), $order_id, number_format( floatval( $earn_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), floatval( - 1 * $earn_point ), $message, false );

					$message = sprintf( __( '주문(<a href="%1$s">#%2$s</a>)이 취소되어 %3$s포인트가 차감되었습니다.<br>보유포인트가 %4$s포인트에서 %5$s포인트로 변경되었습니다.', 'mshop-point-ex' ), get_edit_post_link( $order_id ), $order_id, number_format( floatval( $earn_point ) ), number_format( $prev_point ), number_format( $remain_point ) );
					do_action( 'mshop_add_point_history', $order->get_user_id(), floatval( - 1 * $earn_point ), $message, true );

					$order->add_order_note( __( '[포인트 알림] ', 'mshop-point-ex' ) . $message );

					do_action( 'msps_deduct_point', $earn_point, $order_id );
				}
			}
		}
	}
	public static function woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {

		self::process_redeposit( $order_id, $old_status, $new_status );

		self::process_point( $order_id, $old_status, $new_status );

	}
	public static function woocommerce_checkout_order_processed( $order_id, $post ) {
		$order      = wc_get_order( $order_id );
		$user_role  = mshop_point_get_user_role( $order->get_user_id() );
		$earn_point = MSPS_Manager::get_expected_point( $order, 0, $user_role );
		$used_point = MSPS_Order::get_used_point( $order_id );

		$p_payment_method = msps_get_object_property( $order, 'payment_method' );

		if ( MSPS_Manager::PAYMENT_GATEWAY_POINT != $p_payment_method ) {
			if ( $used_point > 0 && ! MSPS_Manager::support_earn_point_for_point_discount() ) {
				$order->add_order_note( __( '[포인트 알림] 포인트 할인을 받은 주문건으로 포인트가 적립되지 않습니다.', 'mshop-point-ex' ) );

				return;
			}
		}

		if ( MSPS_Manager::PAYMENT_GATEWAY_POINT == $p_payment_method ) {
			if ( ! MSPS_Manager::support_earn_point_for_point_payment() ) {
				$order->add_order_note( __( '[포인트 알림] 포인트 결제건으로 포인트가 적립되지 않습니다.', 'mshop-point-ex' ) );

				return;
			}
		}

		self::set_earn_point( $order_id, $earn_point );

		if ( $earn_point > 0 ) {
			$order->add_order_note( sprintf( __( '[포인트 알림] 주문처리가 완료되면 고객에게 %s 포인트가 적립됩니다.', 'mshop-point-ex' ), number_format( $earn_point ) ) );
		}
	}
}