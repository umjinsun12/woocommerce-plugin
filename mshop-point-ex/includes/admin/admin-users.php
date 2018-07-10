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

if ( ! class_exists( 'MSPS_Admin_Users' ) ) {

	class MSPS_Admin_Users {

		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
		}

		public function init() {
			add_action( 'pre_user_query', array( $this, 'extended_user_orderby_point' ));
			add_filter( 'manage_users_columns', array( $this, 'manage_users_columns'), 999 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'manage_users_sortable_columns'), 999 );
			add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column'), 10, 3 );
		}

		function manage_users_custom_column( $value, $column_name, $userid ) {
			if ( 'mshop_point_amount' == $column_name ) {
				$tmp = get_user_meta( $userid, '_mshop_point', true );
				if($tmp != '') {
					return sprintf(__('%s 포인트','mshop-point-ex'), number_format($tmp));
				} else {
					return __('0 포인트', 'mshop-point-ex');
				}
			}

			return $value;
		}

		function manage_users_sortable_columns( $users_columns ) {
			$users_columns['mshop_point_amount'] = '_mshop_point';
			return $users_columns;
		}

		function manage_users_columns( $users_columns ) {
			$users_columns['mshop_point_amount'] = __( '포인트', 'mshop-point-ex' );
			return $users_columns;
		}

		function extended_user_orderby_point( $user_query ){
			global $wpdb;
			if( $user_query->query_vars['orderby'] == '_mshop_point' ) {
				$user_query->query_fields .= ', wp_users.*, MF.meta_value as _mshop_point';
				$user_query->query_from .= " LEFT JOIN {$wpdb->usermeta} MF ON MF.user_id = {$wpdb->users}.ID AND MF.meta_key = '_mshop_point'";
				$user_query->query_orderby = "ORDER BY {$user_query->query_vars['orderby']}*1 {$user_query->query_vars['order']} ";
			}
		}
	}

	return new MSPS_Admin_Users();

}

