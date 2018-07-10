<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MSPS_Post_types {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
	}
	public static function register_taxonomies() {
		if ( taxonomy_exists( 'point_rule_type' ) ) {
			return;
		}

		register_taxonomy( 'point_rule_type',
			apply_filters( 'mshop_point_taxonomy_objects_point_rule_type', array( 'point_rule' ) ),
			apply_filters( 'mshop_point_taxonomy_args_point_rule_type', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false
			) )
		);
	}
	public static function register_post_types() {
		if ( post_type_exists('point_rule') ) {
			return;
		}

		register_post_type(
			'point_rule',
			apply_filters( 'mshop_point_register_post_type_point_rule',
				array(
					'labels'              => array(
							'name'               => __( 'Point Rules', 'mshop-point-ex' ),
							'singular_name'      => _x( 'Point Rule', 'mshop_point_rule post type singular name', 'mshop-point-ex' ),
							'add_new'            => __( 'Add Point Rule', 'mshop-point-ex' ),
							'add_new_item'       => __( 'Add New Point Rule', 'mshop-point-ex' ),
							'edit'               => __( 'Edit', 'mshop-point-ex' ),
							'edit_item'          => __( 'Edit Point Rule', 'mshop-point-ex' ),
							'new_item'           => __( 'New Point Rule', 'mshop-point-ex' ),
							'view'               => __( 'View Point Rule', 'mshop-point-ex' ),
							'view_item'          => __( 'View Point Rule', 'mshop-point-ex' ),
							'search_items'       => __( 'Search Point Rules', 'mshop-point-ex' ),
							'not_found'          => __( 'No Point Rules found', 'mshop-point-ex' ),
							'not_found_in_trash' => __( 'No Point Rules found in trash', 'mshop-point-ex' ),
							'parent'             => __( 'Parent Point Rules', 'mshop-point-ex' ),
							'menu_name'          => _x( 'Point Rules', 'Admin menu name', 'mshop-point-ex' )
						),
					'description'         => __( 'This is where store point rules are stored.', 'mshop-point-ex' ),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'point_rule',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'show_in_menu'        => current_user_can( 'manage_woocommerce' ) ? 'mshop-point-ex' : true,
					'hierarchical'        => false,
					'show_in_nav_menus'   => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title', 'comments', 'custom-fields' ),
					'has_archive'         => false,
				)
			)
		);

	}
}

MSPS_Post_types::init();
