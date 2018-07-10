<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class MSPS_Rule_Factory {
	public function get_point_rule( $the_point_rule = false, $args = array() ) {
		$the_point_rule = $this->get_point_rule_object( $the_point_rule );

		if ( ! $the_point_rule ) {
			return false;
		}

		$classname = $this->get_point_rule_class( $the_point_rule, $args );

		if ( ! class_exists( $classname ) ) {
			$classname = 'MSPS_Rule_Product';
		}

		return new $classname( $the_point_rule, $args );
	}
	private function get_classname_from_point_rule_type( $the_point_rule ) {
		return $the_point_rule ? 'MSPS_Rule_' . implode( '_', array_map( 'ucfirst', explode( '-', $the_point_rule ) ) ) : false;
	}
	private function get_point_rule_class( $the_point_rule, $args = array() ) {
		$point_rule_id = absint( $the_point_rule->ID );
		$post_type  = $the_point_rule->post_type;

		if ( 'point_rule' === $post_type ) {
			if ( isset( $args['point_rule_type'] ) ) {
				$point_rule_type = $args['point_rule_type'];
			} else {
				$terms        = get_the_terms( $point_rule_id, 'point_rule_type' );
				$point_rule_type = ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'product';
			}
		} else {
			$point_rule_type = false;
		}

		$classname = $this->get_classname_from_point_rule_type( $point_rule_type );

		return apply_filters( 'mshop_point_rule_class', $classname, $point_rule_type, $post_type, $point_rule_id );
	}
	private function get_point_rule_object( $the_point_rule ) {
		if( is_numeric( $the_point_rule ) ){
			$the_point_rule = get_post( $the_point_rule );
		} elseif ( $the_point_rule instanceof MSPS_Rule ) {
			$the_point_rule = get_post( $the_point_rule->id );
		} elseif ( ! ( $the_point_rule instanceof WP_Post ) ) {
			$the_point_rule = false;
		}

		return apply_filters( 'mshop_point_rule_object', $the_point_rule );
	}
}
