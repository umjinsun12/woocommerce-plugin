<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'MShop_Admin_Point' ) ) :

    class MShop_Admin_Point {

        function __construct(){
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            if( is_admin() && defined( 'DOING_AJAX' ) ){
                require_once MSPS()->plugin_path() . '/includes/admin/settings/class-msps-settings-point.php';
            }
        }

        function admin_menu(){
            add_menu_page( __( '엠샵 포인트', 'mshop-point-ex' ), __( '엠샵 포인트', 'mshop-point-ex' ), 'manage_woocommerce', 'mshop_point_setting', '', MSPS()->plugin_url() . '/assets/images/mshop-icon.png', '20.231123' );
            add_submenu_page('mshop_point_setting', __( '기본설정', 'mshop-point-ex' ), __( '기본설정', 'mshop-point-ex' ), 'manage_woocommerce', 'mshop_point_setting', 'MSPS_Settings_Point_Role::output' );
            if( MSPS_Manager::enabled() ){
                add_submenu_page('mshop_point_setting', __( '정책설정', 'mshop-point-ex' ), __( '정책설정', 'mshop-point-ex' ), 'manage_woocommerce', 'mshop_point_policy_setting', array( 'MSPS_Settings_Point', 'output' ) );
                add_submenu_page('mshop_point_setting', __( '로그보기', 'mshop-point-ex' ), __( '로그보기', 'mshop-point-ex' ), 'manage_woocommerce', 'mshop_point_logs', array( $this, 'mshop_point_logs_page' ) );
                add_submenu_page('mshop_point_setting', __( '사용자 포인트 관리', 'mshop-point-ex' ), __( '사용자 포인트 관리', 'mshop-point-ex' ), 'manage_woocommerce', 'mshop_point_user_point', array( $this, 'mshop_point_user_point_page' ) );
            }
        }

        function mshop_point_logs_page(){
            $settings = new MSPS_Settings_Point_Logs();
            $settings->output();
        }

        function mshop_point_user_point_page(){
            $settings = new MSPS_Settings_Manage_Point();
            $settings->output();
        }
    }

    return new MShop_Admin_Point();

endif;
