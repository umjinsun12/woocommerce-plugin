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
 
if ( ! class_exists( 'MSPS_User' ) ) {
	class MSPS_User {
		protected $user;

		protected $point = null;

		public $wallet = null;

		public function __construct( $user_id ) {
			if( is_numeric( $user_id ) ){
                $this->user = new WP_User( $user_id );
			}else if( $user_id instanceof WP_User ){
                $this->user = $user_id;
			}

			if( $this->user ) {
				$this->wallet = new MSPS_Point_Wallet( $this->user->ID );
			}
		}

		public function get_user_info( $field ) {
			return $this->user->$field;
		}
		public function get_point( $item_types = array() ){
			return $this->wallet->get_point( $item_types );
		}
		public function earn_point( $amount, $item_type = 'free_point' ) {
			return $this->wallet->earn( $amount, $item_type );
		}
		public function deduct_point( $amount, $item_type = 'free_point' ) {
			return $this->wallet->deduct(array( $item_type => $amount ) );
		}

		public function set_point( $amount, $item_type = 'free_point' ) {
			return $this->wallet->set( $amount, $item_type );
		}
        public function add_comment_note( $point, $note, $is_admin = null ){
            global $wpdb;

            $table_name = $wpdb->prefix . 'mshop_point_history';

            $wpdb->insert(
                $table_name,
                array(
                    'userid' => $this->user->ID,
                    'point' => $point,
					'is_admin' => $is_admin,
                    'message' => $note,
                    'date' => current_time('mysql')
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                )
            );
		}

		public function reset_user_point() {
			$point = $this->get_point();

			if( $point > 0 ) {
				$this->deduct_point( $point );

				$note = sprintf( __( '%s(#%d) 사용자의 회원 탈퇴로 보유중인 %s 포인트가 소멸되었습니다', 'mshop-point-ex' ), $this->user->display_name, $this->user->ID, number_format( $point ) );

				$this->add_comment_note( -1* $point, $note, true );
			}
		}
//
//		/**
//		 * 사용자에게 포인트를 적립/차감/설정한다.
//		 * @param $amount
//		 * @param string $mode
//		 * @return int|mixed
//		 */
//        public function set_point( $amount, $mode = 'set' ){
//            global $wpdb;
//
//            if ( ! is_null( $amount ) ) {
//                add_user_meta( $this->user->ID, '_mshop_point', 0, true );
//
//                switch ( $mode ) {
//                    case 'earn' :
//                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = meta_value + %d WHERE user_id = %d AND meta_key='_mshop_point'", $amount, $this->user->ID ) );
//                        break;
//                    case 'deduction' :
//                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = meta_value - %d WHERE user_id = %d AND meta_key='_mshop_point'", $amount, $this->user->ID ) );
//                        break;
//                    default :
//                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = %d WHERE user_id = %d AND meta_key='_mshop_point'", $amount, $this->user->ID ) );
//                        break;
//                }
//
//                update_user_meta( $this->user->ID, '_mshop_last_date', get_date_from_gmt( date("Y-m-d H:i:s") ) );
//
//                wp_cache_delete( $this->user->ID, 'user_meta' );
//            }
//
//            return $this->get_point();
//        }
	}

}