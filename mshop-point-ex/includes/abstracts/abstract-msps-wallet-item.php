<?php

class MSPS_Wallet_Item {

    public $user_id = 0;

	public $id;

	public $label;

	public $meta_key;

    public function __construct( $user_id ) {
        $this->user_id = $user_id;
    }

	public function get_point () {
		$point = get_user_meta( $this->user_id, $this->meta_key, true );

		return ! empty( $point ) ? $point : 0;
	}
	public function earn_point( $amount ){
		return $this->set_point( $amount, 'earn' );
	}
	public function deduct_point( $amount ){
		return $this->set_point( $amount, 'deduction' );
	}

	public function set_point( $amount, $mode = 'set' ){
		global $wpdb;

		if ( ! is_null( $amount ) ) {
			add_user_meta( $this->user_id, $this->meta_key, 0, true );

			switch ( $mode ) {
				case 'earn' :
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = meta_value + %d WHERE user_id = %d AND meta_key='{$this->meta_key}'", $amount, $this->user_id ) );
					break;
				case 'deduction' :
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = meta_value - %d WHERE user_id = %d AND meta_key='{$this->meta_key}'", $amount, $this->user_id ) );
					break;
				default :
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET meta_value = %d WHERE user_id = %d AND meta_key='{$this->meta_key}'", $amount, $this->user_id ) );
					break;
			}

			update_user_meta( $this->user_id, '_mshop_last_date', get_date_from_gmt( date("Y-m-d H:i:s") ) );

			wp_cache_delete( $this->user_id, 'user_meta' );
		}

		return $this->get_point();
	}
}
