<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class MSPS_Rule_Post_taxonomy extends MSPS_Rule {

	public function __construct( $product ) {
		$this->rule_type = 'post-taxonomy';
		parent::__construct( $product );
	}

	public function is_match( $post_id )
	{
        if( empty( $this->object ) ){
            return  true;
        }

        $post = get_post( $post_id );
        $taxonomy = $this->taxonomy;
        $post_id = apply_filters( 'wpml_object_id', $post_id , $post->post_type, true, mshop_wpml_get_default_language() );
        $terms = wp_get_post_terms( $post_id, $this->taxonomy );

        if( !empty( $terms ) ){
            $term_ids = array_flip( array_map(function ($term) use( $taxonomy ) {
                $term_id = apply_filters( 'wpml_object_id', $term->term_id, $taxonomy, true, mshop_wpml_get_default_language() );
                return $term_id;
            }, $terms));
            $result = array_intersect_key( $term_ids, $this->object );
            return !empty( $result  );
        }

        return false;
    }

    public function is_valid(){
        if( 'yes' == $this->use_valid_term ){
            $dates = explode( ',', $this->valid_term );
            $sdate = strtotime( $dates[0] . ' 00:00:00' );
            $edate = strtotime( $dates[1] . ' 23:59:59' );
            $now   = strtotime( date("Y-m-d H:i:s") );

            if( $sdate > $now || $edate < $now ){
                return false;
            }
        }

        return true;
    }

    public function get_post_point( $user_role ){
        $point_option = $this->get_user_point_option( $user_role );

        if( !empty( $point_option ) ){
            return $point_option['post'];
        }

        return 0;
    }

    public function get_comment_point( $user_role ){
        $point_option = $this->get_user_point_option( $user_role );

        if( !empty( $point_option ) ){
            return $point_option['comment'];
        }

        return 0;
    }

}
