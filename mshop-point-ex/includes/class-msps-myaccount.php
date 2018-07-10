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

if ( ! class_exists( 'MSPS_Myaccount' ) ) {
	class MSPS_Myaccount {

		public static function init() {
			if ( MSPS_Manager::enabled() && self::show_point_menu() ) {
				add_filter( 'woocommerce_account_menu_items', 'MSPS_Myaccount::woocommerce_account_menu_items' );
				add_action( 'woocommerce_account_mshop-point_endpoint', 'MSPS_Myaccount::mshop_point_endpoint' );
			}
		}
		public static function woocommerce_register_form() {
			wc_get_template( 'myaccount/mshop-point-form-register.php', array (), '', MSPS()->template_path() );
		}
		public static function user_register( $user_id ) {
			$amount = get_option( 'mshop_point_system_user_point_register_amount', 0 );
			if ( 'yes' == get_option( 'mshop_point_system_use_user_point_rule' ) && $amount > 0 ) {
				$user = new MSPS_User( $user_id );
				$user->earn_point( $amount );

				$note = sprintf( __( '신규회원가입 포인트(%s)가 적립되었습니다.', 'mshop-point-ex' ), number_format( $amount ) );

				$user->add_comment_note( $amount, $note, true );
				$user->add_comment_note( $amount, $note, false );
			}
		}

		public static function show_point_menu() {
			$roles = get_option( 'msps_myaccount_menu_roles', '' );
			if( ! empty( $roles ) ) {
				$roles = explode( ',', $roles );
			}

			return empty( $roles ) || in_array( mshop_point_get_user_role(), $roles );
		}

		public static function woocommerce_account_menu_items( $items ) {
			//엔드포인트 동작여부 확인하여 동작시에만 동작하도록 처리
			$logout_endpoint = get_option( 'woocommerce_logout_endpoint', 'customer-logout' );
			if ( ! empty( $logout_endpoint ) ) {
				$removed = false;
				if ( isset( $items['customer-logout'] ) ) {
					unset( $items['customer-logout'] );
					$removed = true;
				}
				$items['mshop-point'] = __( '내 포인트', 'mshop-point-ex' );

				if ( $removed ) {
					$items['customer-logout'] = __( 'Logout', 'woocommerce' );
				}
			} else {
				$items['mshop-point'] = __( '내 포인트', 'mshop-point-ex' );
			}

			return $items;
		}

		public static function mshop_point_endpoint() {
			wc_get_template( 'myaccount/mshop-point.php', array (), '', MSPS()->template_path() );
		}

		public static function mshop_myaccount_show_point() {
			wc_get_template( 'myaccount/mshop-point-25.php', array (), '', MSPS()->template_path() );
		}

		public static function mshop_myaccount_show_point_avada() {
			?>
			<style>
				body {
					height: auto !important;;
				}

				#mshop-point-myaccount-point-log .ui.segment {
					padding: 4px;
					border-radius: 0;
					box-shadow: none;
				}

				#mshop-point-myaccount-point-log table {
					border-radius: 0;
				}

				#mshop-point-myaccount-point-log table.page-navigation,
				#mshop-point-myaccount-point-log table.page-navigation th {
					border: none;
				}

				#mshop-point-myaccount-point-log table th {
					padding-top: 5px;
					padding-bottom: 5px;
				}

				#mshop-point-myaccount-point-log .ui.loader:before {
					font-size: 1em;
					background: none;
				}
			</style>
			<script>
				jQuery(document).ready(function ($) {
					$('.mshop_point_myaccount_log_wrapper').detach().insertAfter('.avada_myaccount_user');
					$('.mshop_point_myaccount_log_wrapper').css('display', 'block');
				});
			</script>
			<?php

			wc_get_template( 'myaccount/mshop-point-avada.php', array (), '', MSPS()->template_path() );
		}

	}

}