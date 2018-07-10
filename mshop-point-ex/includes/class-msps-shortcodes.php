<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( ! class_exists( 'MSPS_Shortcodes' ) ) :

class MSPS_Shortcodes {
	public static function init () {
		$shortcodes = array (
			'msps_point_info' => __CLASS__ . '::msps_point_info',
			'msps_point_log'  => __CLASS__ . '::msps_point_log',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, $function );
		}
	}

	public static function msps_point_info ( $attrs, $content = null ) {
		$result = '';

		$params = shortcode_atts( array (), $attrs );

		if ( is_user_logged_in() ) {
			$user   = new MSPS_User( get_current_user_id() );
			$result = get_option( 'mshop_point_system_point_info_template', __( '{name} 고객님은 {point} 포인트가 있습니다.', 'mshop-point-ex' ) );

			$result = str_replace( "{name}", $user->get_user_info( 'display_name' ), $result );
			$result = str_replace( "{point}", number_format( $user->get_point() ), $result );
		}

		return $result;
	}

	public static function msps_point_log ( $attrs, $content = null ) {
		ob_start();

		wc_get_template( 'shortcodes/mshop-point.php', array (), '', MSPS()->template_path() );

		return ob_get_clean();
	}
}

MSPS_Shortcodes::init();

endif;