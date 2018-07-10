<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class MSPS_Wallet_Item_Free_Point extends MSPS_Wallet_Item {
	public function __construct ( $user_id ) {
		$this->id       = 'free_point';
		$this->label    = __( '무상 포인트', 'mshop-point-ex' );
		$this->meta_key = '_mshop_point';

		parent::__construct( $user_id );
	}

}
