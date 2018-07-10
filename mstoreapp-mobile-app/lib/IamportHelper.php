<?php

class IamportHelper {

  public static function get_customer_uid($order) {
    $prefix = get_option('_iamport_customer_prefix');
    if ( empty($prefix) ) {
      require_once( ABSPATH . 'wp-includes/class-phpass.php');
      $hasher = new PasswordHash( 8, false );
      $prefix = md5( $hasher->get_random_bytes( 32 ) );

      if ( !add_option( '_iamport_customer_prefix', $prefix ) ) throw new Exception( __( "정기결제 구매자정보 생성에 실패하였습니다.", 'iamport-for-woocommerce' ), 1);
    }

    $user_id = $order->get_user_id(); // wp_cron에서는 get_current_user_id()가 없다.
    if ( empty($user_id) )    throw new Exception( __( "정기결제기능은 로그인된 사용자만 사용하실 수 있습니다.", 'iamport-for-woocommerce' ), 1);

    return $prefix . 'c' . $user_id;
  }

}