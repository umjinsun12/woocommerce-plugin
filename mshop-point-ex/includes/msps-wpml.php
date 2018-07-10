<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !function_exists( 'mshop_wpml_get_active_languages' ) ){
    function mshop_wpml_get_active_languages(){
        if( has_filter( 'wpml_object_id') ) {
            return apply_filters('wpml_active_languages', array());
        }else{
            return array();
        }
    }
}

if( !function_exists( 'mshop_wpml_get_default_language' ) ) {
    function mshop_wpml_get_default_language()
    {
        if( has_filter( 'wpml_object_id') ) {
            global $sitepress;
            return $sitepress->get_default_language();
        }else{
            return '';
        }
    }
}

if( !function_exists( 'mshop_wpml_get_current_language' ) ) {
    function mshop_wpml_get_current_language()
    {
        if( has_filter( 'wpml_object_id') ) {
            global $sitepress;
            return $sitepress->get_current_language();
        }else{
            return '';
        }
    }
}

if( !function_exists( 'mshop_wpml_get_current_language_postfix' ) ) {
    function mshop_wpml_get_current_language_postfix()
    {
        $postfix = mshop_wpml_get_current_language();
        if( !empty( $postfix ) && $postfix != mshop_wpml_get_default_language() ){
            return '_' . $postfix;
        }else {
            return '';
        }
    }
}

if( !function_exists( 'mshop_wpml_get_current_language_args' ) ) {
    function mshop_wpml_get_current_language_args()
    {
        if( has_filter( 'wpml_object_id') ) {
            global $sitepress;
            return 'lang=' . $sitepress->get_current_language() . '&';
        }else{
            return '';
        }
    }
}

if( !function_exists( 'mshop_wpml_get_default_language_args' ) ) {
    function mshop_wpml_get_default_language_args()
    {
        if( has_filter( 'wpml_object_id') ) {
            global $sitepress;
            return 'lang=' . $sitepress->get_default_language() . '&';
        }else{
            return '';
        }
    }
}