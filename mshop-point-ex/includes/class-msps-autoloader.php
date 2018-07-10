<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MSPS_Autoloader {
	private $include_path = '';
	public function __construct() {
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( MSHOP_POINT_PLUGIN_FILE ) ) . '/includes/';
	}
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once( $path );
			return true;
		}
		return false;
	}
	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( strpos( $class, 'msps_') === FALSE ){
			return;
		}

		$file  = $this->get_file_name_from_class( $class );
		$path  = '';

		if ( strpos( $class, 'msps_admin_meta_box' ) === 0 ) {
			$path = $this->include_path . 'admin/meta-boxes/';
		}elseif ( strpos( $class, 'msps_admin' ) === 0 ) {
			$path = $this->include_path . 'admin/';
		}elseif ( strpos( $class, 'msps_rule' ) === 0 ) {
			$path = $this->include_path . 'rules/';
		}elseif ( strpos( $class, 'msps_settings' ) === 0 ) {
			$path = $this->include_path . 'admin/settings/';
		}elseif ( strpos( $class, 'msps_wallet_item' ) === 0 ) {
			$path = $this->include_path . 'wallet/';
		}

		if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'msps_' ) === 0 ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new MSPS_Autoloader();
