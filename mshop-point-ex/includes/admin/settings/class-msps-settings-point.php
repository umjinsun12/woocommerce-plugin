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

if ( ! class_exists( 'MSPS_Settings_Point' ) ) :

	class MSPS_Settings_Point {
		static function init() {
			add_filter( 'msshelper_get_mshop_point_rules', array ( __CLASS__, 'get_mshop_point_rules' ) );
			add_filter( 'msshelper_get_mshop_point_post_rules', array ( __CLASS__, 'get_mshop_point_post_rules' ) );
			add_filter( 'msshelper_get_mshop_point_system_post_earn_limit', array ( __CLASS__, 'get_mshop_point_system_post_earn_limit' ) );
			add_filter( 'msshelper_get_mshop_point_system_post_count_limit', array ( __CLASS__, 'get_mshop_point_system_post_count_limit' ) );
			add_filter( 'msshelper_get_mshop_point_system_post_earn_condition', array ( __CLASS__, 'get_mshop_point_system_post_earn_condition' ) );
		}

		static function clean_status( $arr_status ) {
			if ( ! empty( $arr_status ) ) {
				$reoder = array ();
				foreach ( $arr_status as $status => $status_name ) {
					$status            = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
					$reoder[ $status ] = $status_name;
				}

				return $reoder;
			} else {
				return $arr_status;
			}
		}

		static function get_order_status_list( $except_list ) {

			$shop_order_status = self::clean_status( wc_get_order_statuses() );

			$reorder = array ();
			foreach ( $shop_order_status as $status => $status_name ) {
				$reorder[ $status ] = $status_name;
			}

			foreach ( $except_list as $val ) {
				unset( $reorder[ $val ] );
			}

			return $reorder;
		}

		static function get_mshop_point_rules( $rules ) {
			$point_rules = array ();

			foreach ( MSPS_Manager::get_point_rules() as $point_rule ) {
				$point_rules[] = array (
					'id'             => $point_rule->id,
					'type'           => $point_rule->type,
					'roles'          => $point_rule->roles,
					'order'          => $point_rule->order,
					'object'         => $point_rule->object,
					'amount'         => $point_rule->amount,
					'qty'            => $point_rule->qty,
					'use_valid_term' => $point_rule->use_valid_term,
					'valid_term'     => $point_rule->valid_term,
					'always'         => $point_rule->always,
					'price_rules'    => $point_rule->price_rules,
				);
			}

			return $point_rules;
		}

		static function get_mshop_point_post_rules( $rules ) {
			$point_rules = array ();

			foreach ( MSPS_Post_Manager::get_point_rules() as $point_rule ) {
				$point_rules[] = array (
					'id'             => $point_rule->id,
					'order'          => $point_rule->order,
					'type'           => $point_rule->type,
					'roles'          => $point_rule->roles,
					'object'         => $point_rule->object,
					'taxonomy'       => $point_rule->taxonomy,
					'use_valid_term' => $point_rule->use_valid_term,
					'valid_term'     => $point_rule->valid_term
				);
			}

			return $point_rules;
		}

		static function get_mshop_point_system_post_earn_limit( $elements ) {
			$filter_options = self::get_role_options( array ( 'day' => '0', 'week' => '0', 'month' => '0' ) );

			$results = array_map( function ( $option ) use ( $elements ) {
				$find = array_filter( $elements, function ( $element ) use ( $option ) {
					return $option['role'] == $element['role'];
				} );

				if ( ! empty( $find ) ) {
					$find            = current( $find );
					$option['day']   = isset( $find['day'] ) ? $find['day'] : 0;
					$option['week']  = isset( $find['week'] ) ? $find['week'] : 0;
					$option['month'] = isset( $find['month'] ) ? $find['month'] : 0;
				}

				return $option;
			}, $filter_options );

			return $results;
		}

		static function get_mshop_point_system_post_count_limit( $elements ) {
			$filter_options = self::get_role_options( array ( 'post' => '0', 'comment' => '0' ) );

			$results = array_map( function ( $option ) use ( $elements ) {
				$find = array_filter( $elements, function ( $element ) use ( $option ) {
					return $option['role'] == $element['role'];
				} );

				if ( ! empty( $find ) ) {
					$find              = current( $find );
					$option['post']    = isset( $find['post'] ) ? $find['post'] : 0;
					$option['comment'] = isset( $find['comment'] ) ? $find['comment'] : 0;
				}

				return $option;
			}, $filter_options );

			return $results;
		}

		static function get_mshop_point_system_post_earn_condition( $elements ) {
			$filter_options = self::get_role_options( array ( 'post' => '0', 'comment' => '0', 'register' => 0 ) );

			$results = array_map( function ( $option ) use ( $elements ) {
				$find = array_filter( $elements, function ( $element ) use ( $option ) {
					return $option['role'] == $element['role'];
				} );

				if ( ! empty( $find ) ) {
					$find               = current( $find );
					$option['post']     = isset( $find['post'] ) ? $find['post'] : 0;
					$option['comment']  = isset( $find['comment'] ) ? $find['comment'] : 0;
					$option['register'] = isset( $find['register'] ) ? $find['register'] : 0;
				}

				return $option;
			}, $filter_options );

			return $results;
		}

		public static function update_settings() {
			self::init();

			include_once MSPS()->plugin_path() . '/includes/admin/setting-manager/mshop-setting-helper.php';
			$_REQUEST = array_merge( $_REQUEST, json_decode( stripslashes( $_REQUEST['values'] ), true ) );

			MSSHelper::update_settings( self::get_setting_fields() );

			// 상품 구매 포인트 적립 정책 업데이트
			MSPS_Manager::update_point_rules( ! empty( $_REQUEST['mshop_point_rules'] ) ? $_REQUEST['mshop_point_rules'] : array () );
			// 게시글 및 댓글 포인트 적립 정책 업데이트
			MSPS_Post_Manager::update_point_rules( ! empty( $_REQUEST['mshop_point_post_rules'] ) ? $_REQUEST['mshop_point_post_rules'] : array () );

			wp_send_json_success();
		}

		static function get_role_options( $defaults ) {
			$results = array ();

			$roles          = get_editable_roles();
			$roles['guest'] = array (
				'name' => __( 'Guest', 'mshop-point-ex' )
			);

			$filters = get_option( 'mshop_point_system_role_filter' );

			foreach ( $filters as $role ) {
				if ( 'yes' === $role['enabled'] && array_key_exists( $role['role'], $roles ) ) {
					$results[] = array_merge( array (
						'role' => $role['role'],
						'name' => ! empty( $role['nickname'] ) ? $role['nickname'] : $role['name']
					),
						$defaults
					);
				}
			}

			return $results;
		}

		static function get_setting_fields() {
			return array (
				'type'     => 'Tab',
				'id'       => 'kcp-setting-tab',
				'elements' => apply_filters( 'msps-settings', array (
					self::get_setting_main_tab(),
					self::get_setting_notice(),
					self::get_setting_rule_tab(),
					self::get_setting_taxonomy_tab(),
					self::get_setting_shortcodes(),
					self::get_setting_myaccount(),
				) )
			);
		}

		static function get_taxonomies() {
			$results    = array ();
			$taxonomies = get_taxonomies( array ( 'show_ui' => true, 'show_in_nav_menus' => true, 'public' => true, 'hierarchical' => true ), 'object' );

			foreach ( $taxonomies as $key => $value ) {
				$results[ $value->name ] = $value->label;
			}

			return $results;
		}

		static function get_setting_notice() {
			return array (
				'type'     => 'Page',
				'title'    => __( '알림 문구 설정', 'mshop-point-ex' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 적립 안내문구 표시 기능', 'mshop-point-ex' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_print_notice",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "포인트 적립 안내 문구 출력 기능을 사용합니다.", 'mshop-point-ex' )
							)
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '상품 구매', 'mshop-point-ex' ),
						'showIf'   => array ( 'mshop_point_system_use_print_notice' => 'yes' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_print_notice_product",
								"title"     => __( "포인트 적립정보 표시", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "yes",
								"desc"      => __( "상품 구매시 적립될 포인트에 대한 안내 문구를 출력합니다.", 'mshop-point-ex' )
							),
							array (
								'id'      => 'mshop_point_system_notice_at_product_detail',
								'showIf'  => array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
								'title'   => __( '상품상세 페이지', 'mshop-point-ex' ),
								'default' => __( '상품 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '기본문구는 "상품 구매시 {point} 포인트가 적립됩니다." 입니다.<br>{point} : 적립포인트', 'mshop-point-ex' )
							),
							array (
								'id'      => 'mshop_point_system_notice_at_cart',
								'showIf'  => array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
								'title'   => __( '장바구니 페이지', 'mshop-point-ex' ),
								'default' => __( '주문이 완료되면 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '기본문구는 "주문이 완료되면 {point} 포인트가 적립됩니다." 입니다.<br>{point} : 적립포인트', 'mshop-point-ex' )
							),
							array (
								'id'      => 'mshop_point_system_notice_at_checkout',
								'showIf'  => array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
								'title'   => __( '체크아웃 페이지', 'mshop-point-ex' ),
								'default' => __( '주문이 완료되면 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '기본문구는 "주문이 완료되면 {point} 포인트가 적립됩니다." 입니다.<br>{point} : 적립포인트', 'mshop-point-ex' )
							),
							array (
								"id"        => "mshop_point_system_use_print_guide_notice_product",
								'showIf'    => array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
								"title"     => __( "포인트 적립혜택 정보 표시", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "yes",
								'tooltip'   => array (
									'title' => array (
										'content' => __( '포인트 적립혜택 안내문구를 출력합니다.', 'mshop-point-ex' )
									)
								)
							),
							array (
								'id'        => 'mshop_point_system_guide_notice_title_at_product_detail',
								'showIf'    => array (
									array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
									array ( 'mshop_point_system_use_print_guide_notice_product' => 'yes' ),
								),
								'title'     => __( '제목', 'mshop-point-ex' ),
								"className" => "fluid",
								'default'   => __( '[ 포인트 적립안내 ]', 'mshop-point-ex' ),
								'type'      => 'Text'
							),
							array (
								'id'      => 'mshop_point_system_guide_notice_info_at_product_detail',
								'showIf'  => array (
									array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
									array ( 'mshop_point_system_use_print_guide_notice_product' => 'yes' ),
								),
								'title'   => __( '추가 안내문구', 'mshop-point-ex' ),
								'default' => __( '포인트 적립에 대한 추가 안내문구를 입력합니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea'
							),
							array (
								'id'      => 'mshop_point_system_notice_at_product_detail_price',
								'showIf'  => array (
									array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
									array ( 'mshop_point_system_use_print_guide_notice_product' => 'yes' ),
								),
								'title'   => __( '금액 조건 적립 문구', 'mshop-point-ex' ),
								'default' => __( '{desc} 상품 {amount} 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '기본문구는 "{desc} 상품 {amount} 이상 구매시 {point} 포인트가 적립됩니다." 입니다.<br>{desc} 상품정보, {amount} : 구매 금액, {point} : 적립포인트', 'mshop-point-ex' )
							),
							array (
								'id'      => 'mshop_point_system_notice_at_product_detail_qty',
								'showIf'  => array (
									array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
									array ( 'mshop_point_system_use_print_guide_notice_product' => 'yes' ),
								),
								'title'   => __( '수량 조건 적립 문구', 'mshop-point-ex' ),
								'default' => __( '{desc} 상품 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '기본문구는 "{desc} 상품 {qty}개 이상 구매시 {point} 포인트가 적립됩니다." 입니다.<br>{desc} 상품정보, {qty} : 구매 수량, {point} : 적립포인트', 'mshop-point-ex' )
							),
							array (
								'id'      => 'mshop_point_system_notice_at_product_detail_price_qty',
								'showIf'  => array (
									array ( 'mshop_point_system_use_print_notice_product' => 'yes' ),
									array ( 'mshop_point_system_use_print_guide_notice_product' => 'yes' ),
								),
								'title'   => __( '금액 & 수량 조건 적립 문구', 'mshop-point-ex' ),
								'default' => __( '{desc} 상품 {amount} 이상 또는 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '기본문구는 "{desc} 상품 {amount} 이상 또는 {qty}개 이상 구매시 {point} 포인트가 적립됩니다." 입니다.<br>{desc} 상품정보, {amount} : 구매 금액, {qty} : 구매 수량, {point} : 적립포인트', 'mshop-point-ex' )
							),
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '댓글', 'mshop-point-ex' ),
						'showIf'   => array ( 'mshop_point_system_use_print_notice' => 'yes' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_print_notice_comment",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "댓글 작성 페이지에서 적립될 포인트에 대한 안내 문구 출력 기능을 사용합니다.", 'mshop-point-ex' )
							),
							array (
								'id'      => 'mshop_point_system_notice_at_comment',
								'showIf'  => array ( 'mshop_point_system_use_print_notice_comment' => 'yes' ),
								'title'   => __( '메시지', 'mshop-point-ex' ),
								'default' => __( '댓글을 작성하시면 {point} 포인트가 적립됩니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea'
							)
						)
					)
				)
			);
		}

		static function get_setting_main_tab() {
			return array (
				'type'     => 'Page',
				'title'    => __( '기본설정', 'mshop-point-ex' ),
				'class'    => 'active',
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 설정', 'mshop-point-ex' ),
						'elements' => array (
							array (
								'id'        => 'mshop_point_system_point_exchange_ratio',
								'title'     => __( '포인트 교환 비율', 'mshop-point-ex' ),
								'style'     => array (
									'width' => '50px'
								),
								'className' => '',
								'type'      => 'ExchangeRate',
								'leftValue' => '1',
								'leftLabel' => __( '포인트', 'mshop-point-ex' ),
								'label'     => get_woocommerce_currency_symbol(),
								'default'   => '1',
								'desc'      => __( '포인트 교환 비율을 지정합니다.', 'mshop-point-ex' )
							),
							array (
								'id'        => 'mshop_point_system_purchase_method',
								'title'     => __( '포인트 이용 방법', 'mshop-point-ex' ),
								'className' => '',
								'type'      => 'Select',
								'default'   => 'checkout_point',
								'options'   => array (
									'checkout_point'  => __( '포인트 할인', 'mshop-point-ex' ),
									'payment_gateway' => __( '포인트 결제', 'mshop-point-ex' )
								),
								'tooltip'   => array (
									'title' => array (
										'title'   => __( '포인트 이용 방법 안내', 'mshop-point-ex' ),
										'content' => __( '<div class="ui bulleted list"> <div class="item">포인트 할인 : 주문시 보유한 포인트를 사용해서 주문금액에 대한 할인을 받습니다.</div><div class="item">포인트 결제 : 보유한 포인트를 이용해서 결제를 합니다.</div></div>', 'mshop-point-ex' )
									)
								)
							),
							array (
								'id'        => 'mshop_point_system_order_status_after_payment',
								'showIf'    => array ( 'mshop_point_system_purchase_method' => 'payment_gateway' ),
								'title'     => __( '결제완료시 변경될 주문상태', 'mshop-point-ex' ),
								'className' => '',
								'type'      => 'Select',
								'default'   => 'processing',
								'options'   => self::get_order_status_list( array ( 'cancelled', 'failed', 'on-hold', 'refunded' ) ),
							),
							array (
								'id'      => 'mshop_point_system_shortage_point_message',
								'showIf'  => array ( 'mshop_point_system_purchase_method' => 'payment_gateway' ),
								'title'   => __( '포인트 부족시 안내메시지', 'mshop-point-ex' ),
								'default' => __( '보유하신 포인트($point)가 부족해서 포인트로 결제하실 수 없습니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'tooltip' => array (
									'title' => array (
										'title'   => __( '포인트 이용 방법 안내', 'mshop-point-ex' ),
										'content' => __( '포인트가 부족해서 포인트 결제가 불가능할경우 보여줄 메시지를 지정하세요. ( 보유포인트 : $point, 주문금액 : $order_total )', 'mshop-point-ex' )
									)
								)
							),
							array (
								"id"        => "msps_update_timeout",
								'showIf'    => array ( 'mshop_point_system_purchase_method' => 'checkout_point' ),
								"title"     => __( "포인트 할인액 적용 시간", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "LabeledInput",
								"label"     => __( "ms", 'mshop-point-ex' ),
								"value"     => "yes",
								"inputType" => "number",
								"valueType" => "unsigned int",
								"default"   => "1000",
								"tooltip"   => array (
									"title" => array (
										"content" => __( "포인트 할인액 입력 후 지정된 시간이 지나면 자동으로 입력한 포인트가 주문에 적용됩니다.<br>최소값은 1000ms(1초) 입니다.", 'mshop-point-ex' )
									)
								)
							),
							array (
								'id'        => 'msps_point_eran_status',
								'title'     => __( '포인트 적립 주문상태', 'mshop-point-ex' ),
								'className' => '',
								'type'      => 'Select',
								'multiple'  => true,
								'default'   => 'completed',
								'options'   => self::get_order_status_list( array ( 'cancelled', 'failed', 'on-hold', 'refunded' ) ),
							),
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 사용 제한', 'mshop-point-ex' ),
						'elements' => array (
							array (
								'id'        => 'mshop_point_system_allow_devilery',
								'title'     => __( '배송비 결제 지원', 'mshop-point-ex' ),
								'className' => '',
								'type'      => 'Toggle',
								'default'   => 'no',
								'tooltip'   => array (
									'title' => array (
										'content' => __( '포인트로 배송비도 함께 결제할 수 있습니다.', 'mshop-point-ex' )
									)
								)
							),
							array (
								"id"        => "mshop_point_system_purchase_minimum_point",
								"title"     => __( "최소 보유 포인트", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "LabeledInput",
								"label"     => __( "포인트", 'mshop-point-ex' ),
								"value"     => "yes",
								"inputType" => "number",
								"valueType" => "unsigned int",
								"default"   => "0",
								"tooltip"   => array (
									"title" => array (
										"content" => __( "사용자의 보유 포인트가 지정된 포인트 이상이어야 결제에 사용할 수 있습니다. (0을 입력하면 항상 사용 가능합니다.)", 'mshop-point-ex' )
									)
								)
							),
							array (
								"id"        => "mshop_point_system_purchase_maximum_ratio",
								"title"     => __( "최대 사용 가능 비율", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "LabeledInput",
								"label"     => "%",
								"value"     => "yes",
								"inputType" => "number",
								"valueType" => "unsigned float",
								"default"   => "100",
								"desc"      => __( "총 구매금액의 몇% 까지 결제에 사용할 수 있는지 지정합니다.", 'mshop-point-ex' )
							),
							array (
								"id"          => "mshop_point_system_purchase_minimum_amount",
								"title"       => __( "최소 주문 구매 금액", 'mshop-point-ex' ),
								"placeholder" => "0",
								"className"   => "",
								"type"        => "LabeledInput",
								"inputType"   => "number",
								"valueType"   => "unsigned int",
								"leftLabel"   => get_woocommerce_currency_symbol(),
								"value"       => "yes",
								"default"     => "0",
								"desc"        => __( "포인트 사용에 필요한 최소 구매 금액을 설정합니다.", 'mshop-point-ex' )
							),
							array (
								"id"        => "mshop_point_system_point_unit_number",
								"title"     => __( "포인트 사용단위", 'mshop-mcommerce-premium' ),
								"className" => "",
								"type"      => "Text",
								"desc"      => __( "포인트 사용량을 제한합니다.", 'mshop-point-ex' )
							)
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 적립 제한', 'mshop-point-ex' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_purchase_point_refund",
								"title"     => __( "주문 취소 및 환불 정책", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								'tooltip'   => array (
									'title' => array (
										'content' => __( '고객이 거래 취소 또는 환불시 결제에 사용된 포인트를 재적립 합니다.', 'mshop-point-ex' )
									)
								),
							),
							array (
								"id"        => "mshop_point_system_support_earn_point_for_point_discount",
								"title"     => __( "포인트 할인시 포인트 적립", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								'tooltip'   => array (
									'title' => array (
										'content' => __( '포인트 할인을 받은 경우에도 포인트를 적립합니다.', 'mshop-point-ex' )
									)
								),
							),
							array (
								"id"        => "mshop_point_system_support_earn_point_for_point_payment",
								"title"     => __( "포인트 결제시 포인트 적립", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								'tooltip'   => array (
									'title' => array (
										'content' => __( '포인트 결제건에 대해서도 포인트를 적립합니다.', 'mshop-point-ex' )
									)
								),
							),
							array (
								'id'        => 'mshop_point_system_allow_coupon',
								'title'     => __( '쿠폰 사용시 포인트 적립', 'mshop-point-ex' ),
								'className' => '',
								'type'      => 'Toggle',
								'default'   => 'no',
								'tooltip'   => array (
									'title' => array (
										'content' => __( '쿠폰을 사용한 주문건에 대해서도 포인트를 적립합니다.', 'mshop-point-ex' )
									)
								),
							)
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 할인 우선순위', 'mshop-point-ex' ),
						'elements' => array (
							array (
								'id'        => 'mshop_point_system_apply_order_for_tax',
								'title'     => __( '세금별 우선순위', 'mshop-point-ex' ),
								'className' => '',
								'type'      => 'Select',
								'default'   => 'lowest',
								'options'   => array (
									'lowest'  => __( '세율이 낮은 상품부터 적용', 'mshop-point-ex' ),
									'highest' => __( '세율이 높은 상품부터 적용', 'mshop-point-ex' )
								)
							)
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '신규회원 포인트 적립 기능', 'mshop-point-ex' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_user_point_rule",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "회원 가입시 사용자에게 포인트를 적립합니다.", 'mshop-point-ex' )
							),
							array (
								"id"        => "mshop_point_system_user_point_register_amount",
								"title"     => __( "적립 포인트", 'mshop-point-ex' ),
								'showIf'    => array ( 'mshop_point_system_use_user_point_rule' => 'yes' ),
								"className" => "",
								"type"      => "LabeledInput",
								"inputType" => "number",
								"valueType" => "unsigned int",
								"label"     => __( "포인트", 'mshop-point-ex' ),
								"default"   => "0",
							),
							array (
								"id"          => "mshop_point_system_recommender_point_register_description",
								"title"       => __( "회원가입 시 안내문구", 'mshop-point-ex' ),
								"placeholder" => __( "신규 회원가입 시 1,000포인트가 적립됩니다", 'mshop-point-ex' ),
								'showIf'      => array ( 'mshop_point_system_use_user_point_rule' => 'yes' ),
								"default"     => "",
								"type"        => "TextArea",
								'tooltip'     => array (
									'title' => array (
										'title'   => __( '적립 안내문구', 'mshop-point-ex' ),
										'content' => __( '회원 가입 화면에 보여줄 포인트 적립에 대한 안내문구를 기재할 수 있습니다.', 'mshop-point-ex' )
									)
								)
							)
						)
					)
				)
			);
		}

		static function get_setting_shortcodes() {
			return array (
				'type'     => 'Page',
				'title'    => __( '숏코드(Shortcode) 설정', 'mshop-point-ex' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 정보', 'mshop-point-ex' ),
						'elements' => array (
							array (
								'id'      => 'mshop_point_system_point_info_template',
								'title'   => __( '보유포인트', 'mshop-point-ex' ),
								'default' => __( '{name} 고객님은 {point} 포인트가 있습니다.', 'mshop-point-ex' ),
								'type'    => 'TextArea',
								'desc2'   => __( '숏코드 : [msps_point_info]<br>기본문구는 "{name} 고객님은 {point} 포인트가 있습니다." 입니다.<br>{name} : 이름, {point} : 적립포인트', 'mshop-point-ex' )
							)
						)
					)
				)
			);
		}

		static function get_setting_myaccount() {
			return array (
				'type'     => 'Page',
				'title'    => __( '메뉴 설정', 'mshop-point-ex' ),
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '내계정 메뉴', 'mshop-point-ex' ),
						'elements' => array (
							array (
								'id'          => 'msps_myaccount_menu_roles',
								'title'       => __( '사용자 등급', 'mshop-point-ex' ),
								'multiple'    => true,
								'type'        => 'Select',
								'placeholder' => '사용자 등급을 선택하세요.',
								'options'     => msps_get_user_roles(),
								"tooltip"     => array (
									"title" => array (
										"title"   => '',
										"content" => __( '내계정 페이지에 "내 포인트" 메뉴를 표시할 사용자 등급을 지정합니다.<br>사용자 등급을 지정하지 않는 경우, 모든 사용자에게 "내 포인트" 메뉴가 표시됩니다. ', 'mshop-point-ex' )
									)
								)
							)
						)
					)
				)
			);
		}

		static function get_setting_rule_tab() {
			return array (
				'type'     => 'Page',
				'title'    => __( '주문 설정', 'mshop-point-ex' ),
				'class'    => '',
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '주문 완료시 포인트 적립 기능', 'mshop-point-ex' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_purchase_point_rule",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "고객의 주문이 완료되면 일정 금액을 포인트로 적립해주는 기능을 사용합니다.", 'mshop-point-ex' )
							)
						)
					),
					array (
						"id"           => "mshop_point_rules",
						"type"         => "SortableList",
						"title"        => __( "포인트 적립 정책", 'mshop-point-ex' ),
						"listItemType" => "MShopPointRule",
						"repeater"     => true,
						'showIf'       => array ( 'mshop_point_system_use_purchase_point_rule' => 'yes' ),
						"template"     => array (
							'type'           => 'product',
							'amount'         => '0',
							'qty'            => '0',
							'use_valid_term' => 'no',
							'always'         => 'no',
							'valid_term'     => date( 'Y-m-d' ) . ',' . date( 'Y-m-d' ),
						),
						"default"      => array (),
						"tooltip"      => array (
							"title" => array (
								"title"   => __( "주의사항", 'mshop-point-ex' ),
								"content" => __( "고정 포인트 적립과 구매비율 적립이 중복 가능함에 주의하세요.", 'mshop-point-ex' )
							)
						),
						"elements"     => array (
							'left'        => array (
								'type'              => 'Section',
								'title'             => __( '구매 포인트 적립 기능', 'mshop-point-ex' ),
								"hideSectionHeader" => true,
								'elements'          => array (
									array (
										"id"          => "type",
										"title"       => __( "규칙종류", 'mshop-point-ex' ),
										"placeHolder" => __( "규칙 종류를 지정하세요.", 'mshop-point-ex' ),
										"className"   => "fluid",
										"type"        => "Select",
										'default'     => 'product',
										'options'     => array (
											'product'        => __( '상품', 'mshop-point-ex' ),
											'category'       => __( '카테고리', 'mshop-point-ex' ),
											'shipping-class' => __( '배송클래스', 'mshop-point-ex' ),
											'common'         => __( '공통', 'mshop-point-ex' ),
										),
									),
									array (
										"id"          => "object",
										"title"       => __( "적용대상", 'mshop-point-ex' ),
										"placeHolder" => __( "규칙을 적용할 대상을 선택하세요.", 'mshop-point-ex' ),
										"showIf"      => array ( "type" => 'product,category,shipping-class' ),
										"className"   => "search fluid",
										'multiple'    => true,
										'search'      => true,
										'action'      => mshop_wpml_get_default_language_args() . 'action=' . MSPS()->slug() . '-target_search&type=',
										"type"        => "SearchSelect",
										'options'     => array (),
									),
									array (
										"id"        => "use_valid_term",
										"type"      => "Toggle",
										"title"     => __( "기간설정", 'mshop-point-ex' ),
										"className" => "fluid",
										"desc"      => __( "규칙을 적용할 기간을 지정합니다.", 'mshop-point-ex' )
									),
									array (
										"id"        => "valid_term",
										"showIf"    => array ( "use_valid_term" => 'yes' ),
										"type"      => "DateRange",
										"title"     => __( "유효기간", 'mshop-point-ex' ),
										"className" => "mshop-daterange",
									),
									array (
										"id"        => "always",
										"type"      => "Toggle",
										"title"     => __( "중복적립", 'mshop-point-ex' ),
										"className" => "fluid",
										"desc"      => __( "다른 정책의 적용여부와 관계없이 항상 적용됩니다.", 'mshop-point-ex' )
									)
								)
							),
							'price_rules' => array (
								"id"           => "price_rules",
								"type"         => "SortableList",
								"title"        => __( "가격 정책", 'mshop-point-ex' ),
								"listItemType" => "MShopPointRulePrice",
								"repeater"     => true,
								"template"     => array (
									'amount' => '0',
									'qty'    => '0',
									'roles'  => self::get_role_options( array ( 'fixed' => 0, 'ratio' => 0 ) ),
								),
								"default"      => array (),
								"elements"     => array (
									'left'  => array (
										'type'              => 'Section',
										"hideSectionHeader" => true,
										'elements'          => array (
											array (
												"id"          => "amount",
												"type"        => "LabeledInput",
												"className"   => "fluid",
												'inputType'   => 'number',
												"valueType"   => "unsigned int",
												"title"       => __( "금액", 'mshop-point-ex' ),
												"leftLabel"   => get_woocommerce_currency_symbol(),
												"label"       => __( "이상", 'mshop-point-ex' ),
												"default"     => "0",
												"placeholder" => "0"
											),
											array (
												"id"          => "qty",
												"type"        => "LabeledInput",
												"className"   => "fluid",
												"title"       => __( "수량", 'mshop-point-ex' ),
												'inputType'   => 'number',
												"valueType"   => "unsigned int",
												"label"       => __( "개 이상", 'mshop-point-ex' ),
												"default"     => "0",
												"placeholder" => "0"
											)
										)
									),
									'roles' => array (
										"id"        => "roles",
										"className" => "",
										"type"      => "SortableTable",
										"elements"  => array (
											array (
												"id"        => "name",
												"title"     => __( "역할", 'mshop-point-ex' ),
												"className" => " three wide column",
												"type"      => "Label"
											),
											array (
												"id"          => "ratio",
												"title"       => __( "비율 적립", 'mshop-point-ex' ),
												"className"   => " four wide column fluid",
												"type"        => "LabeledInput",
												'inputType'   => 'number',
												"valueType"   => "unsigned float",
												"label"       => "%",
												"default"     => "0",
												"placeholder" => "0"
											),
											array (
												"id"          => "fixed",
												"title"       => __( "고정 적립", 'mshop-point-ex' ),
												"className"   => " four wide column fluid",
												"type"        => "LabeledInput",
												'inputType'   => 'number',
												"valueType"   => "unsigned float",
												"label"       => __( '포인트', 'mshop-point-ex' ),
												"default"     => "0",
												"placeholder" => "0"
											)
										)
									)
								)
							)
						)
					)
				)
			);
		}

		static function get_setting_taxonomy_tab() {
			return array (
				'type'     => 'Page',
				'title'    => __( '게시글 및 댓글 설정', 'mshop-point-ex' ),
				'class'    => '',
				'elements' => array (
					array (
						'type'     => 'Section',
						'title'    => __( '게시글 및 댓글 작성시 포인트 적립 기능', 'mshop-point-ex' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_post_rule",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "사용자가 게시글 또는 댓글 작성 시 포인트를 적립해주는 기능을 사용합니다.", 'mshop-point-ex' )
							)
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 적립 제한 기능', 'mshop-point-ex' ),
						'showIf'   => array ( 'mshop_point_system_use_post_rule' => 'yes' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_use_post_earn_limit",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "일정 기간동안 적립 가능한 포인트와 일일 최대 적립 가능 횟수를 제한하는 기능을 사용합니다.", 'mshop-point-ex' )
							),
							array (
								"id"        => "mshop_point_system_post_earn_limit",
								"title"     => __( "기간별 최대 적립 포인트", 'mshop-point-ex' ),
								"className" => "",
								'showIf'    => array ( 'mshop_point_system_use_post_earn_limit' => 'yes' ),
								"type"      => "SortableTable",
								"repeater"  => true,
								"default"   => self::get_role_options( array ( 'day' => '0', 'week' => '0', 'month' => '0' ) ),
								"elements"  => array (
									array (
										"id"        => "name",
										"title"     => __( "역할", 'mshop-point-ex' ),
										"className" => "",
										"type"      => "Label"
									),
									array (
										"id"          => "day",
										"title"       => __( "하루", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "포인트", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									),
									array (
										"id"          => "week",
										"title"       => __( "일주일", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "포인트", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									),
									array (
										"id"          => "month",
										"title"       => __( "한달", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "포인트", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									)
								)
							),
							array (
								"id"        => "mshop_point_system_post_count_limit",
								"title"     => __( "일일 최대 적립 횟수", 'mshop-point-ex' ),
								"className" => "",
								'showIf'    => array ( 'mshop_point_system_use_post_earn_limit' => 'yes' ),
								"type"      => "SortableTable",
								"repeater"  => true,
								"default"   => self::get_role_options( array ( 'post' => '0', 'comment' => '0' ) ),
								"elements"  => array (
									array (
										"id"        => "name",
										"title"     => __( "역할", 'mshop-point-ex' ),
										"className" => "",
										"type"      => "Label"
									),
									array (
										"id"          => "post",
										"title"       => __( "게시글수", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "개", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									),
									array (
										"id"          => "comment",
										"title"       => __( "게시글당 댓글수", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "개", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									)
								)
							)
						)
					),
					array (
						'type'     => 'Section',
						'title'    => __( '포인트 적립 자격 제한 기능', 'mshop-point-ex' ),
						'showIf'   => array ( 'mshop_point_system_use_post_rule' => 'yes' ),
						'elements' => array (
							array (
								"id"        => "mshop_point_system_post_use_earn_condition",
								"title"     => __( "활성화", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "Toggle",
								"default"   => "no",
								"desc"      => __( "포인트를 적립받기 위해 필요한 자격 조건을 제한하는 기능을 사용합니다.", 'mshop-point-ex' )
							),
							array (
								"id"        => "mshop_point_system_post_earn_condition",
								"title"     => __( "적립자격", 'mshop-point-ex' ),
								"className" => "",
								'showIf'    => array ( 'mshop_point_system_post_use_earn_condition' => 'yes' ),
								"type"      => "SortableTable",
								"repeater"  => true,
								"default"   => self::get_role_options( array ( 'post' => '0', 'comment' => '0', 'register' => '0' ) ),
								"elements"  => array (
									array (
										"id"        => "name",
										"title"     => __( "역할", 'mshop-point-ex' ),
										"className" => "",
										"type"      => "Label"
									),
									array (
										"id"          => "post",
										"title"       => __( "게시글", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "개 이상", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									),
									array (
										"id"          => "comment",
										"title"       => __( "댓글", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "개 이상", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									),
									array (
										"id"          => "register",
										"title"       => __( "회원가입", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "일 이후", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									)
								)
							)
						)
					),
					array (
						"id"           => "mshop_point_post_rules",
						"type"         => "SortableList",
						"title"        => __( "포인트 적립 정책", 'mshop-point-ex' ),
						"listItemType" => "MShopPointRuleForPost",
						"repeater"     => true,
						'showIf'       => array ( 'mshop_point_system_use_post_rule' => 'yes' ),
						"template"     => array (
							'type'           => 'post-taxonomy',
							'taxonomy'       => 'category',
							'amount'         => '0',
							'qty'            => '0',
							'use_valid_term' => 'no',
							'always'         => 'no',
							'valid_term'     => date( 'Y-m-d' ) . ',' . date( 'Y-m-d' ),
							'roles'          => self::get_role_options( array ( 'post' => 0, 'comment' => 0 ) ),
						),
						"default"      => array (),
						"tooltip"      => array (
							"title" => array (
								"title"   => __( "주의사항", 'mshop-point-ex' ),
								"content" => __( "고정 포인트 적립과 구매비율 적립이 중복 가능함에 주의하세요.", 'mshop-point-ex' )
							)
						),
						"elements"     => array (
							'left'  => array (
								'type'              => 'Section',
								"hideSectionHeader" => true,
								'elements'          => array (
									array (
										"id"        => "type",
										"title"     => __( "Taxonomy", 'mshop-point-ex' ),
										'showIf'    => array ( 'always_hidden' => 'yes' ),
										"className" => "fluid",
										"type"      => "Select",
										"default"   => "post-taxonomy",
										'options'   => array (
											'post-taxonomy' => __( 'Taxonomy', 'mshop-point-ex' )
										)
									),
									array (
										"id"          => "taxonomy",
										"title"       => __( "Taxonomy", 'mshop-point-ex' ),
										"placeHolder" => __( "Taxonomy를 선택하세요.", 'mshop-point-ex' ),
										"className"   => "fluid",
										"type"        => "Select",
										"default"     => "category",
										'options'     => self::get_taxonomies()
									),
									array (
										"id"          => "object",
										"title"       => __( "카테고리", 'mshop-point-ex' ),
										"placeHolder" => __( "규칙을 적용할 카테고리를 선택하세요.", 'mshop-point-ex' ),
										"className"   => "search fluid",
										'multiple'    => true,
										'action'      => mshop_wpml_get_default_language_args() . 'action=' . MSPS()->slug() . '-target_search&type=taxonomy&taxonomy=',
										"type"        => "SearchSelect",
										'options'     => array (),
										"tooltip"     => array (
											"title" => array (
												"title"   => __( "주의사항", 'mshop-point-ex' ),
												"content" => __( "카테고리를 선택하지 않으면, 선택된 Taxonomy의 모든 카테고리에 적용됩니다.", 'mshop-point-ex' )
											)
										)
									),
									array (
										"id"        => "use_valid_term",
										"type"      => "Toggle",
										"title"     => __( "기간설정", 'mshop-point-ex' ),
										"className" => "fluid",
										"desc"      => __( "규칙을 적용할 기간을 지정합니다.", 'mshop-point-ex' )
									),
									array (
										"id"        => "valid_term",
										"showIf"    => array ( "use_valid_term" => 'yes' ),
										"type"      => "DateRange",
										"title"     => __( "유효기간", 'mshop-point-ex' ),
										"className" => "mshop-daterange",
									)
								)
							),
							'roles' => array (
								"id"        => "roles",
								"title"     => __( "사용자 등급별 포인트", 'mshop-point-ex' ),
								"className" => "",
								"type"      => "SortableTable",
								"tooltip"   => array (
									"title" => array (
										"title"   => __( "주의사항", 'mshop-point-ex' ),
										"content" => __( "고정 포인트 적립과 구매비율 적립이 중복 가능함에 주의하세요.", 'mshop-point-ex' )
									)
								),
								"elements"  => array (
									array (
										"id"        => "name",
										"title"     => __( "역할", 'mshop-point-ex' ),
										"className" => "",
										"type"      => "Label"
									),
									array (
										"id"          => "post",
										"title"       => __( "게시글 포인트", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "포인트", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									),
									array (
										"id"          => "comment",
										"title"       => __( "댓글 포인트", 'mshop-point-ex' ),
										"className"   => " fluid",
										"type"        => "LabeledInput",
										'inputType'   => 'number',
										"valueType"   => "unsigned int",
										"label"       => __( "포인트", 'mshop-point-ex' ),
										"default"     => "0",
										"placeholder" => "0"
									)
								)
							)
						)
					)
				)
			);
		}

		static function enqueue_scripts() {
			wp_enqueue_style( 'mshop-setting-manager', MSPS()->plugin_url() . '/includes/admin/setting-manager/css/setting-manager.min.css' );
			wp_enqueue_script( 'mshop-setting-manager', MSPS()->plugin_url() . '/includes/admin/setting-manager/js/setting-manager.min.js', array ( 'jquery', 'jquery-ui-core', 'underscore' ) );
		}
		public static function output() {
			self::init();

			include_once MSPS()->plugin_path() . '/includes/admin/setting-manager/mshop-setting-helper.php';
			$settings = self::get_setting_fields();

			self::enqueue_scripts();

			wp_localize_script( 'mshop-setting-manager', 'mshop_setting_manager', array (
				'element'  => 'mshop-setting-wrapper',
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'action'   => MSPS()->slug() . '-update_policy_settings',
				'settings' => $settings,
			) );

			?>
            <script>
				jQuery( document ).ready( function () {
					jQuery( this ).trigger( 'mshop-setting-manager', ['mshop-setting-wrapper', '100', <?php echo json_encode( MSSHelper::get_settings( $settings ) ); ?>, null] );
				} );
            </script>

            <div id="mshop-setting-wrapper"></div>
			<?php
		}
	}

endif;

return new MSPS_Settings_Point();


