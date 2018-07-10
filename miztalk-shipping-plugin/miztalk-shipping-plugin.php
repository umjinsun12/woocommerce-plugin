<?php
 
/**
 * Plugin Name: Miztalk Shipping
 * Plugin URI: http://code.miztalk.com/tutorials/create-a-custom-shipping-method-for-woocommerce--cms-26098
 * Description: 미즈톡 배송 플러그인
 * Version: 1.0.0
 * Author: Jinseop Eom
 * Author URI: http://miztalk.kr
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: miztalk
 */
 
if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function miztalk_shipping_method() {
        if ( ! class_exists( 'MizTalk_Shipping_Method' ) ) {
            class MizTalk_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'miztalk'; 
                    $this->method_title       = __( 'MizTalk Shipping', 'miztalk' );  
                    $this->method_description = __( 'Custom Shipping Method for MizTalk', 'miztalk' ); 
 
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'US', // Unites States of America
                        'CA', // Canada
                        'DE', // Germany
                        'GB', // United Kingdom
                        'IT',   // Italy
                        'ES', // Spain
                        'HR',  // Croatia
                        'KR'
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'MizTalk Shipping', 'miztalk' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'miztalk' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', 'miztalk' ),
                          'default' => 'yes'
                          ),
 
                     'title' => array(
                        'title' => __( 'Title', 'miztalk' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', 'miztalk' ),
                          'default' => __( 'MizTalk Shipping', 'miztalk' )
                          ),
 
                     'weight' => array(
                        'title' => __( 'Weight (kg)', 'miztalk' ),
                          'type' => 'number',
                          'description' => __( 'Maximum allowed weight', 'miztalk' ),
                          'default' => 100
                          ),
 
                     );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package ) {
                    
                    $weight = 0;
                    $cost = 0;
                    $country = $package["destination"]["country"];
 
                    foreach ( $package['contents'] as $item_id => $values ) 
                    { 
                        $_product = $values['data']; 
                        $weight = $weight + $_product->get_weight() * $values['quantity'];
                        $unit =(int)get_post_meta( $_product->get_id(), '_miztalk_shipping_common', true );
                        $min_ship = (int)get_post_meta( $_product->get_id(), '_miztalk_shipping_min_price', true );
                        if($min_ship == 0)
                            $min_ship = 99999999999;
                        if($unit <= $min_ship)
                            $cost += $unit;
                    }
 
 
                    $countryZones = array(
                        'HR' => 0,
                        'US' => 3,
                        'GB' => 2,
                        'CA' => 3,
                        'ES' => 2,
                        'DE' => 1,
                        'IT' => 1,
                        'KR' => 3
                        );
 
                    $zonePrices = array(
                        0 => 10,
                        1 => 30,
                        2 => 50,
                        3 => 0
                        );
 
                    $zoneFromCountry = $countryZones[ $country ];
                    $priceFromZone = $zonePrices[ $zoneFromCountry ];
 
                    $cost += $priceFromZone;
 
                    $rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => $cost
                    );
 
                    $this->add_rate( $rate );
                    
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'miztalk_shipping_method' );
 
    function add_miztalk_shipping_method( $methods ) {
        $methods[] = 'MizTalk_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_miztalk_shipping_method' );
 
    function miztalk_validate_order( $posted )   {
 
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'miztalk', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "miztalk" ) {
                             
                    continue;
                             
                }
 
                $MizTalk_Shipping_Method = new MizTalk_Shipping_Method();
                $weightLimit = (int) $MizTalk_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'miztalk' ), $weight, $weightLimit, $MizTalk_Shipping_Method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }
                }
            }       
        } 
    }
 
    add_action( 'woocommerce_review_order_before_cart_contents', 'miztalk_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'miztalk_validate_order' , 10 );
}