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

class MSPS_Endpoint {
    public static $endpoint = 'mshop-point';
    public function __construct() {
        // Actions used to insert a new endpoint in the WordPress.
        add_action( 'init', array( $this, 'add_endpoints' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

        // Change the My Accout page title.
        add_filter( 'the_title', array( $this, 'endpoint_title' ) );
    }
    public function add_endpoints() {
        add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
    }
    public function add_query_vars( $vars ) {
        $vars[] = self::$endpoint;

        return $vars;
    }
    public function endpoint_title( $title ) {
        global $wp_query;

        $is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

        if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
            // New page title.
            $title = __( 'My Points', 'mshop-point-ex' );

            remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
        }

        return $title;
    }
    public static function install() {
        add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
        flush_rewrite_rules();
    }
}

new MSPS_Endpoint();
