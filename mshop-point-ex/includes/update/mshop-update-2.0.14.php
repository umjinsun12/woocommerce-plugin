<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

global $wpdb;

$table_name = $wpdb->prefix . 'mshop_point_history';
$charset_collate = $wpdb->get_charset_collate();

$sql = "SHOW COLUMNS FROM $table_name LIKE 'is_admin'";
$result = $wpdb->get_var( $sql );

if( is_null( $result ) ){
    $sql = "ALTER TABLE $table_name ADD is_admin bool";
    $wpdb->get_var( $sql );
}

