<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class MSPS_Rule_Shipping_class extends MSPS_Rule {
	public function __construct( $product ) {
		$this->rule_type = 'class';
		$this->rule_description = __( '배송클래스', 'mshop-point-ex' );
		parent::__construct( $product );
	}
	public function is_match($product)
	{
		$product_id = apply_filters( 'wpml_object_id', $product->get_id() , 'product', true, mshop_wpml_get_default_language() );

		$product = wc_get_product( $product_id );

		$class_id = $product->get_shipping_class_id();
		$class_id = apply_filters( 'wpml_object_id', $class_id , 'product_shipping_class', true, mshop_wpml_get_default_language() );

		return array_key_exists( $class_id, $this->object );
	}

	function get_description(){
		$default_language = mshop_wpml_get_default_language();
		$current_language = mshop_wpml_get_current_language();

		if( empty( $current_language ) || $current_language == $default_language ){
			return array_values( $this->object );
		}else{
			$desc = array();
			foreach( $this->object as $id => $value ){
				$term_id = apply_filters( 'wpml_object_id', $id, 'product_shipping_class', true, $current_language );
				$term    = get_term( $term_id, 'product_shipping_class');
				$desc[] = $term->name;
			}

			return $desc;
		}
	}
}
