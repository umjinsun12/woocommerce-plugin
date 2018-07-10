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
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'MSPS_Admin_Meta_Box_Product_Point' ) ) :

	class MSPS_Admin_Meta_Box_Product_Point {

		static function get_role_options( $defaults ){
			$results = array();

			$roles = get_editable_roles();
			$roles[ 'guest' ] = array(
					'name' => __( 'Guest', 'mshop-point-ex' )
			);

			$filters = get_option( 'mshop_point_system_role_filter' );

			foreach( $filters as $role ){
				if( 'yes' === $role['enabled'] && array_key_exists( $role['role'], $roles ) ) {
					$results[] = array_merge( array(
							'role' =>  $role['role'],
							'name' => !empty($role['nickname']) ? $role['nickname'] : $role['name']
					),
							$defaults
					);
				}
			}

			return $results;
		}

		public static function woocommerce_product_data_tabs( $tabs ){
			$tabs['mshop_point_setting'] = array(
				'label'  => __( 'Point Setting', 'mshop-point-ex' ),
				'class' => array( '' ),
				'target' => 'mshop_point_setting'
			);

			return $tabs;
		}

		public static function get_setting_fields(){
			return array(
                'type' => 'Page',
                'title' => __( '정책설정', 'mshop-point-ex '),
                'elements' => array(
                    array(
                        'type' 	=> 'Section',
                        'title' => __( '포인트 구매 정책', 'mshop-point-ex' ),
                        'elements' => array(
                            array(
                                "id" 		=> "_mshop_point_not_purchasable",
                                "title" 	=> __( "포인트 구매 불가", 'mshop-point-ex' ),
                                "className" => "",
                                "type" 	 	=> "Toggle",
                                "default" 	=> "no",
                                "desc"		=> __( "이 상품은 적립된 포인트로 구매할 수 없습니다.", 'mshop-point-ex' )
                            ),
                            array(
                                "id" 		=> "_mshop_point_except_earn_point",
                                "title" 	=> __( "포인트 적립 불가", 'mshop-point-ex'),
                                "className" => "",
                                "type" 		=> "Toggle",
                                "default" 	=> "no",
                                "desc" 		=> __( "이 상품은 포인트가 적립되지 않습니다.", 'mshop-point-ex' )
                            )
                        )
                    ),
                    array(
                        'type' 	   => 'Section',
                        'title'    => __('상품별 포인트 정책', 'mshop-point-ex' ),
                        'elements' => array(
                            array(
                                "id" 		=> "_mshop_point_use",
                                "title" 	=> __("사용", 'mshop-point-ex' ),
                                "className" => "",
                                "type" 		=> "Toggle",
                                "default" 	=> "no",
                                "desc" 		=> __("상품에 대한 포인트 정책 설정 기능을 사용합니다.", 'mshop-point-ex' )
                            )
                        )
                    ),
                    array(
                        "id" 				=> "_mshop_point_rules",
						"title" 			=> __("포인트 적립 정책", 'mshop-point-ex' ),
						"type" 				=> "SortableList",
                        "hideSectionHeader" => true,
                        "listItemType" 		=> "MShopPointRuleForProduct",
                        "repeater" 			=> true,
                        'showIf' 			=> array( '_mshop_point_use' => 'yes' ),
                        "template" 			=> array(
							'amount' 	 => '0',
							'qty' 		 => '0',
							'valid_term' => date('Y-m-d') . ',' . date('Y-m-d'),
                            'roles' 	 => self::get_role_options( array( 'fixed' => 0, 'ratio' => 0) ),
                        ),
                        "default" => array(),
                        "tooltip" => array(
                            "title" => array(
                                "title"   => __( "주의사항", 'mshop-point-ex' ),
                                "content" => __( "고정 포인트 적립과 구매비율 적립이 중복 가능함에 주의하세요.", 'mshop-point-ex' )
                            )
                        ),
                        "elements" => array(
                            array(
                                "id" 		=> "amount",
                                "type" 		=> "LabeledInput",
                                "className" => "fluid",
                                'inputType' => 'number',
                                "title" 	=> __( "금액조건", 'mshop-point-ex' ),
                                "leftLabel" => get_woocommerce_currency_symbol(),
                                "label" 	=> __( "이상", 'mshop-point-ex' ),
                                "default" 	=> "0"
                            ),
                            array(
                                "id" 		=> "qty",
                                "type" 		=> "LabeledInput",
                                "className" => "fluid",
                                "title" 	=> __( "수량조건", 'mshop-point-ex' ),
                                'inputType' => 'number',
                                "label"	 	=> __( "개 이상", 'mshop-point-ex' ),
                                "default" 	=> "0"
                            ),
							array(
								"id" 		=> "use_valid_term",
								"type" 		=> "Toggle",
								"title" 	=> __( "기간설정", 'mshop-point-ex' ),
								"className" => "fluid",
								"desc" 		=> __( "규칙을 적용할 기간을 지정합니다.", 'mshop-point-ex' )
							),
							array(
								"id" 		=> "valid_term",
								"showIf" 	=> array( "use_valid_term" => 'yes' ),
								"type" 		=> "DateRange",
								"title" 	=> __( "유효기간", 'mshop-point-ex' ),
								"className" => "mshop-daterange",
							),
                            array(
                                "id" 		 => "roles",
                                "title" 	 => __( "포인트 정책", 'mshop-point-ex' ),
                                "className"  => "",
                                'filterData' => get_option( 'mshop_point_system_role_filter', array() ),
                                "type" 		 => "SortableTable",
                                "tooltip" 	 => array(
                                    "title" => array(
                                        "title"   => __( "주의사항", 'mshop-point-ex' ),
                                        "content" => __( "고정 포인트 적립과 구매비율 적립이 중복 가능함에 주의하세요.", 'mshop-point-ex' )
                                    )
                                ),
                                "elements" => array(
                                    array(
                                        "id" 		=> "name",
                                        "title" 	=> __( "역할", 'mshop-point-ex' ),
                                        "className" => "",
                                        "type" 		=> "Label"
                                    ),
                                    array(
                                        "id" 		=> "ratio",
                                        "title"		=> __( "구매비율 적립", 'mshop-point-ex' ),
                                        "className" => " fluid",
                                        "type" 		=> "LabeledInput",
                                        'inputType' => 'number',
                                        "label" 	=> "%",
                                        "default" 	=> "0%",
                                        "tooltip" 	=> array(
                                            "title" => array(
                                                "title"   => __( "금액비율 포인트 적립", 'mshop-point-ex' ),
                                                "content" => __( "주문금액의 일정 비율을 포인트로 적립합니다.", 'mshop-point-ex' )
                                            )
                                        )
                                    ),
                                    array(
                                        "id" 		=> "fixed",
                                        "title" 	=> __( "고정 포인트 적립", 'mshop-point-ex' ),
                                        "className" => " fluid",
                                        "type" 	 	=> "LabeledInput",
                                        'inputType' => 'number',
                                        "label" 	=> __( "포인트", 'mshop-point-ex' ),
                                        "default" 	=> "0",
                                        "tooltip" 	=> array(
                                            "title" => array(
                                                "title"   => __( "주문당 포인트 적립", 'mshop-point-ex' ),
                                                "content" => __( "주문 건당 일정 포인트를 적립합니다.", 'mshop-point-ex' )
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

        public static function mshop_point_update_product_settings(){
            include_once MSPS()->plugin_path() . '/includes/admin/setting-manager/mshop-setting-helper.php';
			$_REQUEST = array_merge( $_REQUEST, json_decode( stripslashes($_REQUEST['values']), true ) );

			MSSHelper::update_settings( self::get_setting_fields(), $_REQUEST['sid'] );

            wp_send_json_success();
        }

		static function enqueue_scripts(){
			wp_enqueue_style( 'mshop-point-admin', MSPS()->plugin_url()  . '/assets/css/admin.css' );

			wp_enqueue_style( 'mshop-setting-manager', MSPS()->plugin_url() . '/includes/admin/setting-manager/css/setting-manager.min.css' );
			wp_enqueue_script( 'mshop-setting-manager', MSPS()->plugin_url() . '/includes/admin/setting-manager/js/setting-manager.min.js', array ( 'jquery', 'jquery-ui-core', 'underscore' ) );
		}

		public static function woocommerce_product_data_panels(){
			global $thepostid, $post;
			$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

			include_once MSPS()->plugin_path() . '/includes/admin/setting-manager/mshop-setting-helper.php';
			$settings = self::get_setting_fields();

            self::enqueue_scripts();

            wp_localize_script( 'mshop-setting-manager', 'mshop_setting_manager', array(
                'element' => 'mshop-setting-product-wrapper',
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'action' => 'mshop_point_update_product_settings',
                'settings' => $settings,
                'slug' => MSPS()->slug(),
                'domain' => preg_replace('#^https?://#', '', site_url()),
                'licenseInfo' => get_option('msl_license_' . MSPS()->slug(), null)
            ) );

            $values = MSSHelper::get_settings( self::get_setting_fields(), $thepostid );
			?>
			<script>
				jQuery(document).ready(function(){
					jQuery(this).trigger(
						'mshop-setting-manager',
						[
							'mshop-setting-product-wrapper',        /** 설정창을 표시할 DOM Element ID */
							<?php echo $thepostid; ?>,              /** Store ID */
							<?php echo json_encode( $values ); ?>,  /** 설정값 */
							null,                                   /** 라이센스 정보 */
							<?php echo json_encode( $settings); ?>  /** 설정필드 정보 */
						]
					);
				});
			</script>
			<?php

            echo '<div id="mshop_point_setting" class="panel woocommerce_options_panel">';
            echo '<div id="mshop-setting-product-wrapper" class="mshop-setting-product-wrapper"></div>';
			echo '</div>';
		}

        public static function woocommerce_product_after_variable_attributes( $loop, $variation_data, $variation ){
            $values = MSSHelper::get_settings( self::get_setting_fields(), $variation->ID );

            $element = 'mshop-setting-variable-product-' . $loop;
            ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery(this).trigger('mshop-setting-manager', [ '<?php echo $element; ?>', <?php echo $variation->ID; ?>, <?php echo json_encode( $values ); ?> ]);
                });
            </script>

            <div>
                <p class="form-row form-row-full">
                    <label>Point Setting:</label>
                    <div id="<?php echo $element; ?>" class="mshop-setting-product-wrapper variation"></div>
                </p>
            </div>

            <?php
        }
	}
endif;
