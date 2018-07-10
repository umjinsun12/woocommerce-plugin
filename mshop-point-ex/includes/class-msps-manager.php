<?php

/*
=====================================================================================
                엠샵 프리미엄 포인트 시스템 / Copyright 2014-2015 by CodeM(c)
=====================================================================================

  [ 우커머스 버전 지원 안내 ]

   워드프레스 버전 : WordPress 4.3.1 이상

   우커머스 버전 : WooCommerce 2.4 이상


  [ 코드엠 플러그인 라이센스 규정 ]

   (주)코드엠에서 개발된 워드프레스  플러그인을 사용하시는 분들에게는 다음 사항에 대한 동의가 있는 것으로 간주합니다.

   1. 코드엠에서 개발한 워드프레스 우커머스용 엠샵 프리미엄 포인트 시스템 플러그인의 저작권은 (주)코드엠에게 있습니다.
   
   2. 플러그인은 사용권을 구매하는 것이며, 프로그램 저작권에 대한 구매가 아닙니다.

   3. 플러그인을 구입하여 다수의 사이트에 복사하여 사용할 수 없으며, 1개의 라이센스는 1개의 사이트에만 사용할 수 있습니다. 
      이를 위반 시 지적 재산권에 대한 손해 배상 의무를 갖습니다.

   4. 플러그인은 구입 후 1년간 업데이트를 지원합니다.

   5. 플러그인은 워드프레스, 테마, 플러그인과의 호환성에 대한 책임이 없습니다.

   6. 플러그인 설치 후 버전에 관련한 운용 및 관리의 책임은 사이트 당사자에게 있습니다.

   7. 다운로드한 플러그인은 환불되지 않습니다.

=====================================================================================
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MSPS_Manager' ) ) {

	class MSPS_Manager {
		const MODE_CHECKOUT_POINT = 'checkout_point';
		const MODE_PAYMENT_GATEWAY = 'payment_gateway';
		const PAYMENT_GATEWAY_POINT = 'mshop-point';
		protected static $_enabled = null;
		protected static $_use_purchase_point_rule = null;

		protected static $_use_print_notice = null;

		protected static $_use_print_notice_product = null;

		protected static $_use_print_guide_notice_product = null;

		protected static $_use_print_notice_cart = null;

		protected static $_use_print_notice_checkout = null;

		protected static $_use_print_notice_post = null;

		protected static $_use_print_notice_comment = null;
		protected static $point_rules = null;
		protected static $point_rule_ids = array ();
		protected static $point_seperate_rules = null;
		protected static $point_always_rules = null;
		protected static $terms = array ( 'product', 'category', 'shipping-class', 'common' );
		public static function enabled() {
			if ( null == self::$_enabled ) {
				self::$_enabled = 'yes' == get_option( 'mshop_point_system_enabled', 'no' );
			}

			return self::$_enabled;
		}
		public static function use_purchase_point_rule() {
			if ( null == self::$_use_purchase_point_rule ) {
				self::$_use_purchase_point_rule = 'yes' == get_option( 'mshop_point_system_use_purchase_point_rule', 'no' );
			}

			return self::$_use_purchase_point_rule;
		}

		public static function use_print_notice( $type ) {
			if ( null == self::$_use_print_notice ) {
				self::$_use_print_notice = ( 'yes' == get_option( 'mshop_point_system_use_print_notice', 'no' ) );
			}

			switch ( $type ) {
				case 'product' :
					if ( null == self::$_use_print_notice_product ) {
						self::$_use_print_notice_product = ( 'yes' == get_option( 'mshop_point_system_use_print_notice_product', 'no' ) );
					}

					return ( self::$_use_print_notice && self::$_use_print_notice_product );
				case 'product_guide' :
					if ( null == self::$_use_print_guide_notice_product ) {
						self::$_use_print_guide_notice_product = ( 'yes' == get_option( 'mshop_point_system_use_print_guide_notice_product', 'no' ) );
					}

					return ( self::$_use_print_notice && self::$_use_print_guide_notice_product );
				case 'cart' :
					if ( null == self::$_use_print_notice_cart ) {
						self::$_use_print_notice_cart = ( 'yes' == get_option( 'mshop_point_system_use_print_notice_cart', 'no' ) );
					}

					return self::$_use_print_notice && self::$_use_print_notice_cart;
				case 'checkout' :
					if ( null == self::$_use_print_notice_checkout ) {
						self::$_use_print_notice_checkout = ( 'yes' == get_option( 'mshop_point_system_use_print_notice_checkout', 'no' ) );
					}

					return self::$_use_print_notice && self::$_use_print_notice_checkout;
				case 'comment' :
					if ( null == self::$_use_print_notice_comment ) {
						self::$_use_print_notice_comment = ( 'yes' == get_option( 'mshop_point_system_use_print_notice_comment', 'no' ) );
					}

					return self::$_use_print_notice && self::$_use_print_notice_comment;
				case 'post' :
					if ( null == self::$_use_print_notice_post ) {
						self::$_use_print_notice_post = ( 'yes' == get_option( 'mshop_point_system_use_print_notice_post', 'no' ) );
					}

					return self::$_use_print_notice && self::$_use_print_notice_post;
			}

			return false;
		}
		public static function purchase_maximum_ratio() {
			return get_option( 'mshop_point_system_purchase_maximum_ratio', '100' );
		}
		public static function point_exchange_ratio() {
			return apply_filters( 'wcml_raw_price_amount', get_option( 'mshop_point_system_point_exchange_ratio', '1' ) );
		}
		public static function purchase_minimum_amount() {
			return get_option( 'mshop_point_system_purchase_minimum_amount', 0 );
		}
		public static function purchase_minimum_point() {
			return get_option( 'mshop_point_system_purchase_minimum_point', 0 );
		}
		public static function allow_pay_shipping() {
			return 'yes' === get_option( 'mshop_point_system_allow_devilery', 'no' );
		}
		public static function allow_coupon() {
			return 'yes' === get_option( 'mshop_point_system_allow_coupon', 'no' );
		}
		public static function support_redeposit_when_refunded() {
			return 'yes' === get_option( 'mshop_point_system_use_purchase_point_refund', 'no' );
		}

		public static function support_earn_point_for_point_discount() {
			return 'yes' === get_option( 'mshop_point_system_support_earn_point_for_point_discount', 'no' );
		}

		public static function support_earn_point_for_point_payment() {
			return 'yes' === get_option( 'mshop_point_system_support_earn_point_for_point_payment', 'no' );
		}
		public static function purchase_method() {
			return get_option( 'mshop_point_system_purchase_method', 'checkout_point' );
		}
		public static function is_purchase_method_checkout_point() {
			return self::MODE_CHECKOUT_POINT == self:: purchase_method();
		}
		public static function is_purchase_method_payment_gateway() {
			return self::MODE_PAYMENT_GATEWAY == self:: purchase_method();
		}
		public static function use_user_register_rule() {
			return 'yes' === get_option( 'mshop_point_system_use_user_point_rule', 'no' );
		}

		public static function mshop_membership_skip_filter( $skip ) {
			return false;
		}
		public static function max_useable_amount( $cart ) {
			if ( wc_tax_enabled() && wc_prices_include_tax() ) {
				$useable_amount = $cart->subtotal;
			} else {
				$useable_amount = $cart->cart_contents_total;
			}

			$fees = is_callable( array( $cart, 'get_fees') ) ? $cart->get_fees() : $cart->fees;

			foreach ( $fees as $fee ) {
				if ( $fee->name != __( '포인트 할인', 'mshop-point-ex' ) || $fee->name != __( '포인트 할인 (비과세)', 'mshop-point-ex' ) || $fee->name != __( '포인트 할인 (과세)', 'mshop-point-ex' ) ) {
					$useable_amount += $fee->amount;
				}
			}

			if ( self::allow_pay_shipping() ) {
				$useable_amount += $cart->shipping_total;
			}

			foreach ( $cart->get_cart() as $cart_item ) {
				$product = new MSPS_Product( $cart_item['data'] );
				if ( ! $product->is_point_purchasable() ) {
					if ( wc_tax_enabled() && wc_prices_include_tax() ) {
						$useable_amount -= ( $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'] );
					} else {
						$useable_amount -= $cart_item['line_subtotal'];
					}
				}
			}

			return $useable_amount;
		}
		public static function max_useable_point( $cart ) {
			$useable_amount = self::max_useable_amount( $cart );

			return $useable_amount / 100 * self::purchase_maximum_ratio() / self::point_exchange_ratio();
		}
		public static function get_point_rules( $reload = false ) {
			if ( empty( self::$point_rules ) || $reload ) {
				self::$point_rules    = array ();
				self::$point_rule_ids = array ();

				// Query Point Rules Data
				$args = array (
					'post_type'      => 'point_rule',
					'posts_per_page' => - 1,
					'post_status'    => 'publish',
					'meta_key'       => '_order',
					'orderby'        => 'meta_value_num',
					'order'          => 'ASC',
					'tax_query'      => array (
						array (
							'taxonomy' => 'point_rule_type',
							'field'    => 'slug',
							'terms'    => self::$terms
						),
					),
				);

				$query = new WP_Query( $args );

				// Generate Point Rules
				foreach ( $query->posts as $post ) {
					$point_rule = mshop_get_point_rule( $post );

					if ( ! is_wp_error( $point_rule ) ) {
						self::$point_rule_ids[] = $point_rule->id;
						self::$point_rules[]    = $point_rule;
					}
				}

				self::$point_seperate_rules = array_filter( self::$point_rules, function ( $rule ) {
					return 'yes' != $rule->always && $rule->is_valid();
				} );
				self::$point_always_rules   = array_filter( self::$point_rules, function ( $rule ) {
					return 'yes' == $rule->always && $rule->is_valid();
				} );
			}

			return self::$point_rules;
		}
		public static function update_point_rule_meta( $point_rule_id, $data ) {
			$point_rule_type = null;
			if ( isset( $data['type'] ) ) {
				$point_rule_type = sanitize_text_field( $data['type'] );
				wp_set_object_terms( $point_rule_id, $point_rule_type, 'point_rule_type' );
			} else {
				$_point_rule_type = get_the_terms( $point_rule_id, 'point_rule_type' );
				if ( is_array( $_point_rule_type ) ) {
					$_point_rule_type = current( $_point_rule_type );
					$point_rule_type  = $_point_rule_type->slug;
				}
			}

			update_post_meta( $point_rule_id, '_order', $data['order'] );
			update_post_meta( $point_rule_id, '_type', $data['type'] );
			update_post_meta( $point_rule_id, '_use_valid_term', $data['use_valid_term'] );
			update_post_meta( $point_rule_id, '_valid_term', $data['valid_term'] );
			update_post_meta( $point_rule_id, '_always', $data['always'] );
			if ( ! empty( $data['price_rules'] ) ) {
				update_post_meta( $point_rule_id, '_price_rules', json_decode( json_encode( $data['price_rules'] ), true ) );
			} else {
				delete_post_meta( $point_rule_id, '_price_rules' );
			}

			if ( ! empty( $data['object'] ) ) {
				update_post_meta( $point_rule_id, '_object', $data['object'] );
			} else {
				delete_post_meta( $point_rule_id, '_object' );
			}
		}
		public static function update_point_rules( $point_rules ) {
			self::get_point_rules();
			$new_point_rule_ids = array ();

			// Update point rule's info
			foreach ( $point_rules as $index => $rule ) {
				if ( empty( $rule['id'] ) ) {
					$args = array (
						'post_title'  => $rule['type'] . ' [' . ( ! empty( $rule['uuid'] ) ? $rule['uuid'] : '' ) . ']',
						'post_type'   => 'point_rule',
						'post_status' => 'publish'
					);

					$point_rule_id = wp_insert_post( $args );
				} else {
					$point_rule_id = wp_update_post( array (
						'ID'         => $rule['id'],
						'post_title' => $rule['type'] . ' [' . ( ! empty( $rule['uuid'] ) ? $rule['uuid'] : '' ) . ']'
					) );
				}

				// Reset rule's order
				$rule['order'] = $index;

				if ( ! is_wp_error( $point_rule_id ) ) {
					$new_point_rule_ids[] = $point_rule_id;
					self::update_point_rule_meta( $point_rule_id, $rule );
				}
			}

			// Check deleted point rules
			$deleted_point_rule_ids = array_diff( self::$point_rule_ids, $new_point_rule_ids );

			if ( ! empty( $deleted_point_rule_ids ) ) {
				// send to trash
				foreach ( $deleted_point_rule_ids as $point_rule_id ) {
					wp_trash_post( $point_rule_id );
				}
			}

			self::get_point_rules( true );
		}
		public static function is_valid_user( $user_role ) {
			$roles = get_option( 'mshop_point_system_role_filter', array () );

			$result = array_filter( $roles, function ( $role ) use ( $user_role ) {
				return 'yes' == $role['enabled'] && $user_role === $role['role'];
			} );

			return ! empty( $result );
		}
		public static function get_expected_point_from_product_rule( &$cart_items, $user_role ) {
			$total       = 0;
			$applied_ids = array ();

			foreach ( $cart_items as $cart_item ) {
				$quantity = $cart_item['quantity'];

				$product = new MSPS_Product( $cart_item['data'] );

				if ( $product->enabled() ) {
					$rule = $product->get_matched_rule( $quantity, $user_role );
					if ( $rule ) {
						$total += $product->calculate_point( $quantity, $user_role );
					}

					$applied_ids[] = $product->id;
				}
			}
			$cart_items = array_filter( $cart_items, function ( $cart_item ) use ( $applied_ids ) {
				$product = $cart_item['data'];

				return ! in_array( $product->id, $applied_ids );
			} );

			return $total;
		}
		protected static function get_expected_point_from_global_rule( $rules, $cart_items, $filter, $user_role ) {
			$total = 0;

			if ( ! empty( $rules ) ) {
				foreach ( $rules as $rule ) {
					$rule->clear();
					$applied_ids = array ();
					foreach ( $cart_items as $cart_item ) {
						$product  = $cart_item['data'];
						$quantity = $cart_item['quantity'];

						if ( $rule->is_match( $product ) ) {
							$applied_ids[] = $product->get_id();

							$rule->add_item( $product, $quantity );
						}
					}
					if ( $rule->is_applicable() ) {
						$total += $rule->calculate_point( $user_role );

						if ( $filter ) {
							$cart_items = array_filter( $cart_items, function ( $cart_item ) use ( $applied_ids ) {
								return ! in_array( $cart_item['data']->get_id(), $applied_ids );
							} );
						}
					}

					if ( empty( $cart_items ) ) {
						break;
					}
				}
			}

			return $total;
		}
		public static function get_expected_point( $object, $qty, $user_role ) {
			$total          = 0;
			$purchase_items = array ();
			if ( is_user_logged_in() && self::is_valid_user( $user_role ) ) {
				self::get_point_rules();

				if ( $object instanceof WC_Product ) {
					$purchase_items[] = array (
						'data'     => $object,
						'quantity' => $qty
					);
				} else if ( $object instanceof WC_Cart ) {
					$purchase_items = $object->get_cart();
					foreach ( $purchase_items as &$item ) {
						$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
						$product_id = apply_filters( 'wpml_object_id', $product_id, 'product', true, mshop_wpml_get_default_language() );
						$product    = wc_get_product( $product_id );
						$product->set_price( apply_filters( 'wcml_raw_price_amount', floatval( $item['data']->get_price() ) ) );
						$item['data'] = $product;
					}
				} else if ( $object instanceof WC_Order ) {
					foreach ( $object->get_items() as $item ) {
						$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
						$product_id = apply_filters( 'wpml_object_id', $product_id, 'product', true, mshop_wpml_get_default_language() );

						$product = wc_get_product( $product_id );
						if ( wc_tax_enabled() && wc_prices_include_tax() ) {
							$product->set_price( apply_filters( 'wcml_raw_price_amount', floatval( $item['line_total'] + $item['line_tax'] ) ) / $item['qty'] );
						} else {
							$product->set_price( apply_filters( 'wcml_raw_price_amount', floatval( $item['line_total'] ) ) / $item['qty'] );
						}

						$purchase_items[] = array (
							'data'     => $product,
							'quantity' => $item['qty']
						);
					}
				}
				$purchase_items = array_filter( $purchase_items, function ( $cart_item ) {
					$product       = $cart_item['data'];
					$point_product = new MSPS_Product( $product );

					return $product->get_price() > 0 && ! $point_product->is_except_earn_point();
				} );

				if ( count( $purchase_items ) > 0 ) {
					$items = $purchase_items;
					$total = self::get_expected_point_from_product_rule( $items, $user_role );
					$total += self::get_expected_point_from_global_rule( self::$point_seperate_rules, $items, true, $user_role );
					$total += self::get_expected_point_from_global_rule( self::$point_always_rules, $purchase_items, false, $user_role );
				}
			}

			return $total;
		}
		protected static function get_precedence_ruleset_from_product_rule( $object, $qty, $user_role ) {
			$ruleset = array ();

			$product = new MSPS_Product( $object );
			if ( $product->enabled() ) {
				$rule = $product->get_precedence_rule( $qty, $user_role );
				if ( $rule ) {
					$ruleset[] = array (
						'type'   => 'product',
						'desc'   => $product->product->get_title(),
						'amount' => $rule['amount'],
						'qty'    => $rule['qty'],
						'option' => $product->get_user_point_option( $rule, $user_role )
					);
				}
			}

			return $ruleset;
		}

		protected static function get_ruleset_for_product_from_product_rule( $object, $qty, $user_role ) {
			$rule_infos = array ();
			$ruleset    = array ();

			$product = new MSPS_Product( $object );
			if ( $product->enabled() ) {
				foreach ( $product->active_rules as $price_rule ) {
					$option = array_filter( $price_rule['roles'], function ( $option ) use ( $user_role ) {
						return $option['role'] == $user_role && ( ( isset( $option['fixed'] ) && $option['fixed'] > 0 ) || ( isset( $option['ratio'] ) && $option['ratio'] > 0 ) );

					} );

					if ( ! empty( $option ) ) {
						$option = current( $option );

						$ruleset[] = array (
							'amount'     => apply_filters( 'wcml_raw_price_amount', floatval( $price_rule['amount'] ) ),
							'qty'        => intval( $price_rule['qty'] ),
							'fixed'      => intval( $option['fixed'] ),
							'fixed_text' => number_format_i18n( intval( $option['fixed'] ) ),
							'ratio'      => floatval( $option['ratio'] ),
							'desc'       => $product->product->get_title()
						);
					}
				}

				$rule_infos[] = array_reverse( $ruleset );
			}

			return array_filter( $rule_infos );
		}
		protected static function filter_ruleset_of_always_rules( $object, $qty, $user_role ) {
			$rulesets = array ();

			foreach ( self::$point_always_rules as $rule ) {
				if ( empty( $product ) || $rule->is_match( $product ) ) {
					$options = $rule->get_all_option( $user_role );
					if ( ! empty( $options ) ) {
						$rulesets = array_merge( $rulesets, $options );
					}
				}
			}

			return $rulesets;
		}
		protected static function get_precedence_ruleset_from_global_rule( $rules, $cart_items, $user_role, $filter, $break = false, $type = 'product' ) {
			$ruleset = array ();

			if ( ! empty( $rules ) ) {
				foreach ( $rules as $rule ) {
					$rule->clear();
					$applied_ids = array ();
					foreach ( $cart_items as $cart_item ) {
						$product  = $cart_item['data'];
						$quantity = $cart_item['quantity'];

						if ( $rule->is_match( $product ) ) {
							$applied_ids[] = $product->get_id();

							$rule->add_item( $product, $quantity );
						}
					}
					if ( ! empty( $applied_ids ) ) {
						$price_rule = $rule->get_precedence_rule( $user_role );

						if ( empty( $price_rule ) ) {
							if ( $break ) {
								return $ruleset;
							}
						} else {
							$option = array_filter( $price_rule['roles'], function ( $role ) use ( $user_role ) {
								return $user_role == $role['role'];
							} );

							$ruleset[] = array (
								'type'   => $type,
								'desc'   => implode( ', ', array_values( $rule->object ) ) . ' ' . $rule->rule_description,
								'amount' => $price_rule['amount'],
								'qty'    => $price_rule['qty'],
								'option' => array_shift( $option )
							);

							if ( $break ) {
								return $ruleset;
							}
						}

						if ( $filter ) {
							$cart_items = array_filter( $cart_items, function ( $cart_item ) use ( $applied_ids ) {
								return ! in_array( $cart_item['data']->get_id(), $applied_ids );
							} );
						}
					}

					if ( empty( $cart_items ) ) {
						break;
					}
				}
			}

			return $ruleset;
		}

		protected static function get_ruleset_for_product_from_global_rule( $rules, $product, $user_role ) {
			$rule_infos = array ();

			if ( ! empty( $rules ) ) {
				foreach ( $rules as $rule ) {
					$ruleset = array ();
					$rule->clear();

					if ( $rule->is_match( $product ) ) {
						foreach ( $rule->price_rules as $price_rule ) {
							$option = array_filter( $price_rule['roles'], function ( $option ) use ( $user_role ) {
								return $option['role'] == $user_role && ( ( isset( $option['fixed'] ) && $option['fixed'] > 0 ) || ( isset( $option['ratio'] ) && $option['ratio'] > 0 ) );
							} );

							if ( ! empty( $option ) ) {
								$option = current( $option );

								$ruleset[] = array (
									'amount'     => apply_filters( 'wcml_raw_price_amount', floatval( $price_rule['amount'] ) ),
									'qty'        => intval( $price_rule['qty'] ),
									'fixed'      => intval( $option['fixed'] ),
									'fixed_text' => number_format_i18n( intval( $option['fixed'] ) ),
									'ratio'      => floatval( $option['ratio'] ),
									'desc'       => implode( ', ', array_values( $rule->object ) ) . ' ' . $rule->rule_description,
								);
							}
						}
					}

					$rule_infos[] = array_reverse( $ruleset );
				}
			}

			return array_filter( $rule_infos );
		}
		public static function get_precedence_ruleset( $object, $qty, $user_role ) {
			$ruleset = array ();
			if ( is_user_logged_in() && self::is_valid_user( $user_role ) ) {
				$purchase_items = array ();
				self::get_point_rules();

				if ( $object instanceof WC_Product ) {
					$cart_items[] = array (
						'data'     => $object,
						'quantity' => $qty
					);
					$point_product = new MSPS_Product( $object );

					if ( $point_product->is_except_earn_point() ) {
						return $ruleset;
					}

					if ( $point_product->enabled() ) {
						$ruleset = self::get_precedence_ruleset_from_product_rule( $object, $qty, $user_role );
					} else {
						$ruleset = self::get_precedence_ruleset_from_global_rule( self::$point_seperate_rules, $cart_items, $user_role, true, true, 'product' );
					}

					$ruleset = array_merge( $ruleset, self::get_precedence_ruleset_from_global_rule( self::$point_always_rules, $cart_items, $user_role, false, false, 'common' ) );
				} else if ( $object instanceof WC_Cart ) {
					$cart_items     = $object->get_cart();
					$filtered_items = array ();

					foreach ( $cart_items as $item ) {
						$product       = $item['data'];
						$quantity      = $item['quantity'];
						$point_product = new MSPS_Product( $product );

						if ( ! $point_product->is_except_earn_point() ) {
							if ( $point_product->enabled() ) {
								$product_ruleset = self::get_precedence_ruleset_from_product_rule( $product, $quantity, $user_role );

								if ( ! empty( $product_ruleset ) ) {
									$ruleset = array_merge( $ruleset, $product_ruleset );
								}
							} else {
								$filtered_items[] = $item;
							}
						}
					}
					$ruleset = array_merge( $ruleset, self::get_precedence_ruleset_from_global_rule( self::$point_seperate_rules, $filtered_items, $user_role, true, false, 'product' ) );
					$ruleset = array_merge( $ruleset, self::get_precedence_ruleset_from_global_rule( self::$point_always_rules, $cart_items, $user_role, false, false, 'common' ) );
				}
			}

			return array_filter( $ruleset );
		}

		public static function get_ruleset_for_product( $object, $qty, $user_role ) {
			$ruleset = array ();
			if ( is_user_logged_in() && self::is_valid_user( $user_role ) ) {
				self::get_point_rules();
				$point_product = new MSPS_Product( $object );

				if ( $point_product->is_except_earn_point() ) {
					return $ruleset;
				}

				if ( $point_product->enabled() ) {
					$ruleset['product'] = self::get_ruleset_for_product_from_product_rule( $object, $qty, $user_role );
				} else {
					$ruleset['product'] = self::get_ruleset_for_product_from_global_rule( self::$point_seperate_rules, $object, $user_role );
				}

				$ruleset['common'] = self::get_ruleset_for_product_from_global_rule( self::$point_always_rules, $object, $user_role );
			}

			return $ruleset;
		}

		public static function show_message_for_product( $product, $qty, $user_id, $place = 'product_detail' ) {
			$user_role = mshop_point_get_user_role( $user_id );
			$point     = self::get_expected_point( $product, $qty, $user_role );

			$result = array ();

			if ( $point > 0 ) {
				$message  = apply_filters( 'mshop_point_translate_string', get_option( 'mshop_point_system_notice_at_' . $place ), 'point_message_' . $place );
				$result[] = str_replace( '{point}', number_format_i18n( $point ), $message );
			}

			$rulesets = self::get_precedence_ruleset( $product, $qty, $user_role );

			if ( count( $rulesets ) > 0 ) {
				$guide_msg = array ();

				foreach ( $rulesets as $rule ) {
					if ( ! empty( $rule['option']['fixed'] ) || ! empty( $rule['option']['ratio'] ) ) {
						if ( ! empty( $rule['amount'] ) && $rule['amount'] > 0 && ! empty( $rule['qty'] ) && $rule['qty'] > 0 ) {
							$template = apply_filters( 'mshop_point_translate_string', get_option( 'mshop_point_system_notice_at_product_detail_price_qty', __( '{desc} 상품 {amount} 이상 또는 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ) ), 'point_guide_message_price_qty' );
						} else if ( ! empty( $rule['amount'] ) && $rule['amount'] > 0 ) {
							$template = apply_filters( 'mshop_point_translate_string', get_option( 'mshop_point_system_notice_at_product_detail_price', __( '{desc} 상품 {amount} 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ) ), 'point_guide_message_price' );
						} else if ( ! empty( $rule['qty'] ) && $rule['qty'] > 0 ) {
							$template = apply_filters( 'mshop_point_translate_string', get_option( 'mshop_point_system_notice_at_product_detail_qty', __( '{desc} 상품 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ) ), 'point_guide_message_qty' );
						}

						$amount   = wc_price( apply_filters( 'wcml_raw_price_amount', $rule['amount'] ) );
						$template = str_replace( '{amount}', $amount, $template );
						$template = str_replace( '{qty}', $rule['qty'], $template );
						$template = str_replace( '{desc}', $rule['desc'], $template );

						if ( $rule['option']['ratio'] > 0 ) {
							$point = $rule['option']['ratio'] . '%';
							if ( $rule['option']['fixed'] > 0 ) {
								$point .= ' + ' . number_format_i18n( $rule['option']['fixed'] );
							}
						} else {
							$point = ! empty( $rule['option']['fixed'] ) ? number_format_i18n( $rule['option']['fixed'] ) : '';
						}

						$template = str_replace( '{point}', $point, $template );

						$guide_msg[] = ( 'common' == $rule['type'] ? __( '(추가적립) ', 'mshop-point-ex' ) : '' ) . $template;
					}
				}

				if ( count( $guide_msg ) > 0 ) {
					if ( count( $result ) > 0 ) {
						$result[] = '<br>';
					}
					$result[] = apply_filters( 'mshop_point_translate_string', get_option( 'mshop_point_system_guide_notice_title_at_product_detail', __( '[ 포인트 적립안내 ]', 'mshop-point-ex' ) ), 'point_guide_message_title' );
					$result[] = '<ul>';
					$result[] = '<li>' . implode( '</li><li>', $guide_msg ) . '</li>';
					$result[] = '</ul>';
				}
			}

			return implode( '', $result );
		}

		public static function show_message_for_cart( $cart ) {
			return self::show_message_for_product( $cart, 0, null, 'cart' );
		}

		public static function show_message_for_checkout( $cart ) {
			return self::show_message_for_product( $cart, 0, null, 'checkout' );
		}
	}
}