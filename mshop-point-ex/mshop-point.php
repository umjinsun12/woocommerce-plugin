<?php

/*
Plugin Name: 엠샵 프리미엄 포인트 시스템
Plugin URI: 
Description: 쇼핑몰의 포인트 적립 및 사용 기능을 지원합니다.
Version: 2.6.0
Author: CodeMShop
Author URI: www.codemshop.com
License: Commercial License
*/

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
if ( ! class_exists( 'MShop_Point' ) ) {

	class MShop_Point {

		protected $slug;

        protected static $_instance = null;
		public $version = '2.6.0';
		public $plugin_url;
		public $plugin_path;
		public $template_url;
        public $point_rule_factory = null;

		protected $update_checker;

		private $_body_classes = array();
		public function __construct() {
			// Define version constant
			define( 'MSHOP_POINT_VERSION', $this->version );
			$this->slug = 'mshop-point-ex';

			$this->init_update();
			register_activation_hook( __FILE__, array( $this, 'activation_process' ) );
			register_activation_hook( __FILE__, array( 'MSPS_Endpoint', 'install' ) );

			add_action( 'plugins_loaded',array($this, 'plugins_loaded') );

			add_action( 'init', array( $this, 'init' ), 10 );

            $this->define( 'MSHOP_POINT_PLUGIN_FILE', __FILE__ );

            require_once( 'includes/class-msps-autoloader.php' );

            require_once( 'includes/abstracts/abstract-msps-rule.php' );
            require_once( 'includes/msps-functions.php' );
			require_once( 'includes/msps-wpml.php' );

            require_once( 'includes/class-msps-post-types.php' );
            require_once( 'includes/class-msps-wpml.php' );
			require_once( 'includes/class-msps-endpoint.php' );
			require_once( 'includes/class-msps-shortcodes.php' );
		}

        private function define( $name, $value ) {
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
        }

		function init_update() {
			require 'includes/admin/update/LicenseManager.php';

			$this->license_manager = new MSPS_LicenseManager( $this->slug, __DIR__, __FILE__ );
		}
		public function create_log_table() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'mshop_point_history';
			$charset_collate = $wpdb->get_charset_collate();

			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				$sql = "CREATE TABLE $table_name (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					userid bigint(20) NOT NULL,
					point numeric(20,2) NOT NULL,
					is_admin bool,
					message varchar(2000) NOT NULL,
					date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (id),
					UNIQUE KEY id (id)
				) $charset_collate;";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta( $sql );
				update_option('mshop_point_db_version', $this->version);
			}
		}

		function activation_process() {
			$this->create_log_table();
		}

		public function slug(){
			return $this->slug;
		}

		public function plugin_url() {
			if ( $this->plugin_url ){
				return $this->plugin_url;
			}

			return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function plugin_path() {
			if ( $this->plugin_path ){
				return $this->plugin_path;
			}

			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		public function template_path() {
            return $this->plugin_path() . '/templates/';
		}

		function includes() {
            $this->point_rule_factory = new MSPS_Rule_Factory();

            if ( is_admin() ) {
				$this->admin_includes();
			}

			if ( defined( 'DOING_AJAX' ) ){
				$this->ajax_includes();
			}

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ){
				$this->frontend_includes();
			}
		}

		public function admin_includes() {
			include_once('includes/admin/admin-users.php');
			include_once('includes/admin/class-mshop-admin-point.php');
        }

        public function ajax_includes() {
			include_once( 'includes/class-msps-ajax.php' );
		}

		public function frontend_includes() {
		}

		public function frontend_scripts() {
		}

		public function init() {
            include_once( 'includes/class-msps-manager.php' );

            $this->init_taxonomy();
			$this->includes();

			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) , 999 );

            $this->process_update();

			MSPS_Myaccount::init();
        }

        function process_update(){
            $current_db_version = get_option( 'mshop_point_db_version', null );
            $version = '2.0.14';

            if ( is_null( $current_db_version ) || ( ! is_null( $current_db_version ) && version_compare( $current_db_version, $version, '<' ) ) ) {

                $this->activation_process();

                include_once( 'includes/update/mshop-update-2.0.14.php' );

                $roles = json_decode( get_option( 'mshop_point_rule_for_role' ), true );

                if( !empty( $roles ) ){
                    $rule = array(
                        'type' => 'common',
                        'order' => 999,
                        'use_valid_term' => 'no',
                        'valid_term' => '',
                        'always' => 'no',
                        'price_rules' => array(
                            array(
                                'amount' => 0,
                                'qty' => 0,
                                'roles' => $roles
                            )
                        )
                    );

                    $args = array(
                        'post_title'  => $rule['type'] . ' ' . gmdate('Y-m-d H:i:59'),
                        'post_type'   => 'point_rule',
                        'post_status' => 'publish'
                    );

                    $point_rule_id = wp_insert_post($args);

                    MSPS_Manager::update_point_rule_meta( $point_rule_id, $rule );
                }

                update_option('mshop_point_db_version', $version);
            }

            $current_db_version = get_option( 'mshop_point_db_version', null );
            $version = '2.1.4';
            if ( is_null( $current_db_version ) || ( ! is_null( $current_db_version ) && version_compare( $current_db_version, $version, '<' ) ) ) {
                $rules = $this->get_point_rules();
                MSPS_Manager::update_point_rules($rules);
                update_option('mshop_point_db_version', $version);
            }

        }

        function get_point_rules(){
            $point_rules = array();

            foreach( MSPS_Manager::get_point_rules() as $point_rule ){
                $point_rules[] = array(
                    'id'     => $point_rule->id,
                    'type'   => $point_rule->type,
                    'roles'  => $point_rule->roles,
                    'order'  => $point_rule->order,
                    'object' => $point_rule->object,
                    'amount' => $point_rule->amount,
                    'qty'    => $point_rule->qty,
                    'use_valid_term' => $point_rule->use_valid_term,
                    'valid_term'     => $point_rule->valid_term,
                    'always'         => $point_rule->always,
                    'price_rules'    => $point_rule->price_rules,
                );
            }

            return $point_rules;
        }

		public static function woocommerce_payment_gateways( $load_gateways ){
			include_once( 'includes/gateways/mshop-point/class-mshop-gateway-point.php' );

			$load_gateways[] = 'MShop_Gateway_Point';

			return $load_gateways;
		}

		public function add_body_class( $class ) {
			$this->_body_classes[] = sanitize_html_class( strtolower( $class ) );
		}

		public function output_body_class( $classes ) {
			return $classes;
		}

		public function plugins_loaded() {
            $current_db_version = get_option( 'mshop_point_db_version', null );

			load_plugin_textdomain( 'mshop-point-ex', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		public function init_taxonomy() {
		}

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
	}


    function MSPS() {
        return MShop_Point::instance();
    }


    return MSPS();
}