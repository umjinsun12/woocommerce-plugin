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

if ( ! defined( 'ABSPATH' ) ){
    exit;
}

if ( ! class_exists( 'MSPS_Settings_Manage_Point' ) ) :

    class MSPS_Settings_Manage_Point {
        public static $number_per_page = 20;
        public static $navigation_size = 5;

        public function __construct() {
            add_filter( 'msshelper_get_mshop_manage_point_filter', array( $this, 'get_mshop_manage_point_filter' ) );
        }

        function get_mshop_manage_point_filter(){
            return array(
                'user' => '',
                'term' => ''
            );
        }

        public function get_roles() {
            $results = array();

            if ( !function_exists('get_editable_roles') ) {
                require_once( ABSPATH . '/wp-admin/includes/user.php' );
            }

            $roles = get_editable_roles();

            foreach( $roles as $slug => $role ) {
                $results[ $slug ] = $role['name'];
            }

            return $results;
        }
        public function get_setting_fields() {
	        return array (
		        'type'         => 'ListPage',
		        'title'        => __( '기본설정', 'mshop-point-ex' ),
		        'id'           => 'mshop_manage_point',
		        'searchConfig' => array (
			        'action'   => MSPS()->slug() . '-get_user_point_list',
			        'pageSize' => self::$number_per_page
		        ),
		        'elements'     => array (
			        array (
				        'type'              => 'MShopListTableFilter',
				        'id'                => 'mshop_manage_point_filter',
				        'hideSectionHeader' => true,
				        'elements'          => array (
					        array (
						        "id"          => "user",
						        "title"       => __( "사용자", 'mshop-point-ex' ),
						        "placeHolder" => __( "사용자 선택", 'mshop-point-ex' ),
						        "className"   => "fluid search",
						        'multiple'    => true,
						        'search'      => true,
						        'action'      => mshop_wpml_get_default_language_args() . 'action=' . MSPS()->slug() . '-mshop_point_search_user',
						        "type"        => "SearchSelect",
						        'options'     => array ()
					        ),
					        array (
						        "id"          => "role",
						        "title"       => __( "역할", 'mshop-point-ex' ),
						        "placeHolder" => __( "사용자 역할 선택", 'mshop-point-ex' ),
						        "className"   => "fluid",
						        'multiple'    => true,
						        "type"        => "Select",
						        'options'     => $this->get_roles()
					        )
				        )
			        ),
			        array (
				        'type'              => 'Section',
				        'id'                => 'msps_batch_adjuster',
				        'hideSectionHeader' => true,
				        'elements'          => apply_filters( 'msps_batch_action_settings',
					        array (
						        array (
							        "id"           => "batch_adjust_point",
							        "title"        => __( "포인트 일괄처리", 'mshop-point-ex' ),
							        "className"    => "two wide column",
							        "type"         => "MShopPointBatchAdjuster",
							        "filter"       => "mshop_manage_point_filter",
							        'custom_event' => 'mshop_manage_point',
							        'action'       => MSPS()->slug() . '-batch_adjust_point'
						        ),

					        )
				        )
			        ),
			        array (
				        'type' => 'MShopListTableNavigator'
			        ),
			        array (
				        'type'     => 'MShopListTable',
				        'id'       => 'mshop_manage_point_target',
				        "repeater" => true,
				        'default'  => array (),
				        'elements' => array (
					        'type'        => 'SortableTable',
					        'className'   => 'sortable',
					        'noResultMsg' => __( '검색 결과가 없습니다.', 'mshop-point-ex' ),
					        "repeater"    => true,
					        "elements"    => apply_filters( 'msps_manage_point_settings',
						        array (
							        array (
								        "id"        => "id",
								        "title"     => __( "ID", 'mshop-point-ex' ),
								        "className" => "one wide column",
								        "type"      => "Label",
								        "sortKey"   => "ID"
							        ),
							        array (
								        "id"        => "name",
								        "title"     => __( "사용자명", 'mshop-point-ex' ),
								        "className" => "four wide column",
								        "type"      => "Label",
								        "sortKey"   => "display_name"
							        ),
							        array (
								        "id"           => "point",
								        "title"        => __( "포인트", 'mshop-point-ex' ),
								        "className"    => "two wide column",
								        "type"         => "MShopPointAdjuster",
								        'custom_event' => 'mshop_manage_point',
								        'action'       => MSPS()->slug() . '-adjust_mshop_user_point'
							        ),
							        array (
								        "id"        => "last_date",
								        "title"     => __( "최종적립일", 'mshop-point-ex' ),
								        "className" => "two wide column",
								        "type"      => "Label",
								        "sortKey"   => "last_date"
							        )
						        )
					        )
				        )
			        ),
			        array (
				        'type' => 'MShopListTableNavigator'
			        ),
		        )
	        );
        }

        function enqueue_scripts() {
	        wp_enqueue_style( 'mshop-setting-manager', MSPS()->plugin_url() . '/includes/admin/setting-manager/css/setting-manager.min.css' );
	        wp_enqueue_script( 'mshop-setting-manager', MSPS()->plugin_url() . '/includes/admin/setting-manager/js/setting-manager.min.js', array ( 'jquery', 'jquery-ui-core', 'underscore' ) );
        }
        public function output(){
	        include_once MSPS()->plugin_path() . '/includes/admin/setting-manager/mshop-setting-helper.php';
	        $settings = $this->get_setting_fields();

            $this->enqueue_scripts();

            wp_localize_script( 'mshop-setting-manager', 'mshop_setting_manager', array(
                'element' => 'mshop-setting-wrapper',
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'action' => 'mshop_point_update_settings',
                'settings' => $settings,
                'values' => MSSHelper::get_settings( $settings ),
            ) );

            ?>
            <style>
                .mshop-setting-section .ui.table.sortable td{
                    height: 40px;
                }
            </style>
            <script>
                jQuery(document).ready(function(){
                    jQuery(this).trigger('mshop-setting-manager', [ 'mshop-setting-wrapper', '100', <?php echo json_encode( MSSHelper::get_settings( $settings ) ); ?>  ]);
                });
            </script>

            <div id="mshop-setting-wrapper"></div>

            <?php
        }
    }

endif;

