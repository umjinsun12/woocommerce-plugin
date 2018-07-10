<?php
/*
Controller name: Mstoreapp api
Controller description: Mstoreapp Restful Api
*/

class MSTOREAPP_API_Mstoreapp_Controller {

  /**
   * Api Keys
   */
  public static function keys() {

    global  $woocommerce;

    $data->keys = array(
        'consumerKey' => esc_attr( get_option('ConsumerKey') ),
        'consumerSecret' => esc_attr( get_option('ConsumerSecret') )
    );

    $data->banners = array(
        esc_attr( get_option('BannerUrl1') ),
        esc_attr( get_option('BannerUrl2') ),
        esc_attr( get_option('BannerUrl3') )
    );

    $data->login_nonce = wp_create_nonce( 'woocommerce-login' );

    $data->currency = get_woocommerce_currency();

      if(is_user_logged_in()){
        $data->user = wp_get_current_user();
        $data->user->status = true;
        $data->user->url = wp_logout_url( $redirect );
        $data->user->avatar = get_avatar($user->ID, 128);
        
        wp_send_json( $data );
      }
        
      $data->user->status = false;

    wp_send_json( $data );

    die();
  }

  public static function test() {

    $data->status = 'working';

    wp_send_json( $data );

    die();
  }


  /**
   * AJAX apply coupon on checkout page.
   */
  public static function apply_coupon() {

    //check_ajax_referer( 'apply-coupon', 'security' );

    if ( ! empty( $_POST['coupon_code'] ) ) {
      WC()->cart->add_discount( sanitize_text_field( $_POST['coupon_code'] ) );
    } else {
      wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
    }

    wc_print_notices();

    die();
  }

  /**
   * AJAX remove coupon on cart and checkout page.
   */
  public static function remove_coupon() {

    //check_ajax_referer( 'remove-coupon', 'security' );

    $coupon = wc_clean( $_POST['coupon'] );

    if ( ! isset( $coupon ) || empty( $coupon ) ) {
      wc_add_notice( __( 'Sorry there was a problem removing this coupon.', 'woocommerce' ), 'error' );

    } else {

      WC()->cart->remove_coupon( $coupon );

      wc_add_notice( __( 'Coupon has been removed.', 'woocommerce' ) );
    }

    wc_print_notices();

    die();
  }

  /**
   * AJAX update shipping method on cart page.
   */
  public static function update_shipping_method() {

    //check_ajax_referer( 'update-shipping-method', 'security' );

    if ( ! defined('WOOCOMMERCE_CART') ) {
      define( 'WOOCOMMERCE_CART', true );
    }

    $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

    if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
      foreach ( $_POST['shipping_method'] as $i => $value ) {
        $chosen_shipping_methods[ $i ] = wc_clean( $value );
      }
    }

    WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

    
    $data = WC()->cart;
    WC()->cart->calculate_totals();
      
      foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( has_post_thumbnail( $product_id ) ) {
              $image = get_the_post_thumbnail_url( $product_id, $size, $attr );
            } elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
              $image = get_the_post_thumbnail_url( $parent_id, $size, $attr );
            } elseif ( $placeholder ) {
              $image = wc_placeholder_img( $size );
            } else {
              $image = '';
            }

        $data->cart_contents[$cart_item_key]['name'] = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
        $data->cart_contents[$cart_item_key]['thumb'] = $image;
        $data->cart_contents[$cart_item_key]['remove_url'] = WC()->cart->wc_get_cart_remove_url($cart_item_key);
        $data->cart_contents[$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }

      $data->cart_nonce = wp_create_nonce( 'woocommerce-cart' );

      //$data->shipping = WC()->shipping->load_shipping_methods($packages);

      $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
      $data->chosen_shipping = $chosen_methods[0];


    $cls = new WC_Shipping();
    $data->zone_shipping = $cls->get_shipping_methods(false);



    wp_send_json( $data );


    die();
  }

  /**
   * AJAX receive updated cart_totals div.
   */
  public static function get_cart_totals() {

    if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
      define( 'WOOCOMMERCE_CART', true );
    }

    WC()->cart->calculate_totals();

    woocommerce_cart_totals();

    die();
  }

  /**
   * AJAX update order review on checkout.
   */
  public static function update_order_review() {
    ob_start();

    //check_ajax_referer( 'update-order-review', 'security' );

    if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
      define( 'WOOCOMMERCE_CHECKOUT', true );
    }

    if ( WC()->cart->is_empty() ) {
      $data = array(
        'fragments' => apply_filters( 'woocommerce_update_order_review_fragments', array(
          'form.woocommerce-checkout' => '<div class="woocommerce-error">' . __( 'Sorry, your session has expired.', 'woocommerce' ) . ' <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="wc-backward">' . __( 'Return to shop', 'woocommerce' ) . '</a></div>'
        ) )
      );

      wp_send_json( $data );

      die();
    }

    do_action( 'woocommerce_checkout_update_order_review', $_POST['post_data'] );

    $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

    if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
      foreach ( $_POST['shipping_method'] as $i => $value ) {
        $chosen_shipping_methods[ $i ] = wc_clean( $value );
      }
    }

    WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
    WC()->session->set( 'chosen_payment_method', empty( $_POST['payment_method'] ) ? '' : $_POST['payment_method'] );

    if ( isset( $_POST['country'] ) ) {
      WC()->customer->set_country( $_POST['country'] );
    }

    if ( isset( $_POST['state'] ) ) {
      WC()->customer->set_state( $_POST['state'] );
    }

    if ( isset( $_POST['postcode'] ) ) {
      WC()->customer->set_postcode( $_POST['postcode'] );
    }

    if ( isset( $_POST['city'] ) ) {
      WC()->customer->set_city( $_POST['city'] );
    }

    if ( isset( $_POST['address'] ) ) {
      WC()->customer->set_address( $_POST['address'] );
    }

    if ( isset( $_POST['address_2'] ) ) {
      WC()->customer->set_address_2( $_POST['address_2'] );
    }

    if ( wc_ship_to_billing_address_only() ) {

      if ( ! empty( $_POST['country'] ) ) {
        WC()->customer->set_shipping_country( $_POST['country'] );
        WC()->customer->calculated_shipping( true );
      }

      if ( isset( $_POST['state'] ) ) {
        WC()->customer->set_shipping_state( $_POST['state'] );
      }

      if ( isset( $_POST['postcode'] ) ) {
        WC()->customer->set_shipping_postcode( $_POST['postcode'] );
      }

      if ( isset( $_POST['city'] ) ) {
        WC()->customer->set_shipping_city( $_POST['city'] );
      }

      if ( isset( $_POST['address'] ) ) {
        WC()->customer->set_shipping_address( $_POST['address'] );
      }

      if ( isset( $_POST['address_2'] ) ) {
        WC()->customer->set_shipping_address_2( $_POST['address_2'] );
      }
    } else {

      if ( ! empty( $_POST['s_country'] ) ) {
        WC()->customer->set_shipping_country( $_POST['s_country'] );
        WC()->customer->calculated_shipping( true );
      }

      if ( isset( $_POST['s_state'] ) ) {
        WC()->customer->set_shipping_state( $_POST['s_state'] );
      }

      if ( isset( $_POST['s_postcode'] ) ) {
        WC()->customer->set_shipping_postcode( $_POST['s_postcode'] );
      }

      if ( isset( $_POST['s_city'] ) ) {
        WC()->customer->set_shipping_city( $_POST['s_city'] );
      }

      if ( isset( $_POST['s_address'] ) ) {
        WC()->customer->set_shipping_address( $_POST['s_address'] );
      }

      if ( isset( $_POST['s_address_2'] ) ) {
        WC()->customer->set_shipping_address_2( $_POST['s_address_2'] );
      }
    }

    WC()->cart->calculate_totals();

    ob_start();
    woocommerce_order_review();
    $woocommerce_order_review = ob_get_clean();
    

    // Get checkout payment fragment
    ob_start();
    woocommerce_checkout_payment();
    $woocommerce_checkout_payment = ob_get_clean();

    // Get messages if reload checkout is not true
    $messages = '';
    if ( ! isset( WC()->session->reload_checkout ) ) {
      ob_start();
      wc_print_notices();
      $messages = ob_get_clean();
    }

    $data = array(
      'result'    => empty( $messages ) ? 'success' : 'failure',
      'messages'  => $messages,
      'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false',
      'fragments' => apply_filters( 'woocommerce_update_order_review_fragments', array(
        'woocommerce-checkout-review-order-table' => $woocommerce_order_review,
        'woocommerce-checkout-payment'            => $woocommerce_checkout_payment
      ) )
    );
    
    $data['cart'] = WC()->cart;

    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( has_post_thumbnail( $product_id ) ) {
              $image = get_the_post_thumbnail_url( $product_id, $size, $attr );
            } elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
              $image = get_the_post_thumbnail_url( $parent_id, $size, $attr );
            } elseif ( $placeholder ) {
              $image = wc_placeholder_img( $size );
            } else {
              $image = '';
            }

        $data['cart']->cart_contents[$cart_item_key]['name'] = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
        //$data['cart']->cart_contents[$cart_item_key]['test'] = 'test';
        $data['cart'][$cart_item_key]['thumb'] = $image;
        //$data['cart'][$cart_item_key]['test'] = 'test';
        //$data['cart'][$cart_item_key]['remove_url'] = WC()->cart->get_remove_url($cart_item_key);
        //$data['cart'][$cart_contents][$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }

    $data['checkout'] = WC()->checkout;
    

    //$data['total'] = wc_cart_totals_order_total_html();
    
    unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

    wp_send_json( $data );

    die();
  }

  /**
   * AJAX add to cart.
   */
  public static function add_to_cart() {
    ob_start();

    $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
    $quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
    $product_status    = get_post_status( $product_id );

    $variation_id      = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
    $variations         = ! empty( $_POST['variation'] ) ? (array) $_POST['variation'] : '';

    if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations) && 'publish' === $product_status ) {

      do_action( 'woocommerce_ajax_added_to_cart', $product_id );

      if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
        wc_add_to_cart_message( array( $product_id => $quantity ), true );
      }

      // Return fragments
      $data->cart = WC()->cart->get_cart();
      $data->cart_nonce = wp_create_nonce( 'woocommerce-cart' );

      wp_send_json( $data );

    } else {

      // If there was an error adding to the cart, redirect to the product page to show any errors
      $data = array(
        'error'       => true,
        'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
      );

      $data->cart_nonce = wp_create_nonce( 'woocommerce-cart' );

      wp_send_json( $data );

    }

    die();
  }

  public function cart() {

    if ( ! defined('WOOCOMMERCE_CART') ) {
      define( 'WOOCOMMERCE_CART', true );
    }
    

      $data = WC()->cart;
      WC()->cart->calculate_shipping();
      WC()->cart->calculate_totals();

      
      
      foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( has_post_thumbnail( $product_id ) ) {
              $image = get_the_post_thumbnail_url( $product_id, $size, $attr );
            } elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
              $image = get_the_post_thumbnail_url( $parent_id, $size, $attr );
            } elseif ( $placeholder ) {
              $image = wc_placeholder_img( $size );
            } else {
              $image = '';
            }

        //$data->cart_contents[$cart_item_key]['name'] = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
        if($data->cart_contents[$cart_item_key]['data']->post->post_title)
          $data->cart_contents[$cart_item_key]['name'] = $data->cart_contents[$cart_item_key]['data']->post->post_title;
        else
          $data->cart_contents[$cart_item_key]['name'] = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
        $data->cart_contents[$cart_item_key]['thumb'] = $image;
        $data->cart_contents[$cart_item_key]['remove_url'] = WC()->cart->wc_get_cart_remove_url($cart_item_key);
        $data->cart_contents[$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }

      $data->cart_nonce = wp_create_nonce( 'woocommerce-cart' );


      $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
      $data->chosen_shipping = $chosen_methods[0];


    //$cls = new WC_Shipping();
    //Uncommet this for older Woocommerce Version than 2.6.x
    // $cls = new WC_Shipping_Zone();
    //$data->zone_shipping = $cls->get_shipping_methods(false);

    $cls = new WC_Shipping_Zones();
    $package = WC()->cart->get_shipping_packages();
    $shipping_zone  = $cls->get_zone_matching_package( $package );
    $data->zone_shipping = $shipping_zone->get_shipping_methods( true );

    wp_send_json( $data );

    die();
  }

  public static function remove_cart_item() {

    $status = WC()->cart->remove_cart_item($_REQUEST['item_key']);

    $data = WC()->cart;

    $data->remove_status = $status;
      
      foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( has_post_thumbnail( $product_id ) ) {
              $image = get_the_post_thumbnail_url( $product_id, $size, $attr );
            } elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
              $image = get_the_post_thumbnail_url( $parent_id, $size, $attr );
            } elseif ( $placeholder ) {
              $image = wc_placeholder_img( $size );
            } else {
              $image = '';
            }

        $data->cart_contents[$cart_item_key]['name'] = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
        $data->cart_contents[$cart_item_key]['thumb'] = $image;
        $data->cart_contents[$cart_item_key]['remove_url'] = WC()->cart->wc_get_cart_remove_url($cart_item_key);
        $data->cart_contents[$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }
      
      $data->shipping = WC()->shipping->get_shipping_methods();
      
      wp_send_json( $data );
  
  }


  /**
   * Process ajax checkout form.
   */
  public static function checkout() {
    if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
      define( 'WOOCOMMERCE_CHECKOUT', true );
    }

    WC()->checkout()->process_checkout();

    die(0);
  }

  public static function get_checkout_form() {

    if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
      define( 'WOOCOMMERCE_CHECKOUT', true );
    }

    //$data = WC()->checkout()->instance();

    foreach ( WC()->checkout()->checkout_fields['billing'] as $key => $field ) :

     $data->$key = WC()->checkout()->get_value( $key );

    endforeach;

    foreach ( WC()->checkout()->checkout_fields['shipping_method'] as $key => $field ) :

     $data->$key = WC()->checkout()->get_value( $key );

    endforeach;

    $data->country = WC()->countries;

    $data->state = WC()->countries->get_states( $cc );



    $data->payment = WC()->payment_gateways->get_available_payment_gateways();

    $package = WC()->cart->get_shipping_packages();
    $cls = new WC_Shipping_Zones();
    $shipping_zone  = $cls->get_zone_matching_package( $package );
    $data->shipping = $shipping_zone->get_shipping_methods( true );

    $data->nonce = array(
          'ajax_url'                  => WC()->ajax_url(),
          'wc_ajax_url'               => WC_AJAX::get_endpoint( "%%endpoint%%" ),
          'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
          'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
          'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
          'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
          'checkout_url'              => WC_AJAX::get_endpoint( "checkout" ),
          'is_checkout'               => is_page( wc_get_page_id( 'checkout' ) ) && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
          'debug_mode'                => defined('WP_DEBUG') && WP_DEBUG,
          'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
    );

    $data->checkout_nonce = wp_create_nonce( 'woocommerce-process_checkout');
    $data->checkout_login = wp_create_nonce( 'woocommerce-login' );
    $data->save_account_details = wp_create_nonce( 'save_account_details' );


    $data->user_logged = is_user_logged_in();

    if(is_user_logged_in()){
       $data->logout_url = wp_logout_url( $redirect );
       $user = wp_get_current_user();
       $data->user_id = $user->ID;
    }

    wp_send_json( $data );

    die(0);
  }

  public static function get_country() {

  $data->country = WC()->countries;

  $data->state = WC()->countries->get_states( $cc );

    wp_send_json( $data );

    die(0);
  }


  public static function payment() {

    if ( WC()->cart->needs_payment() ) {
        // Payment Method
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

      } else {
        $available_gateways = array();
    }

    wp_send_json( $available_gateways );

    die(0);
  }

  public static function info() {

    $data = WC();

    wp_send_json( $data );

    die(0);
  }

  /**
   * Get a matching variation based on posted attributes.
   */
  public static function get_variation() {
    ob_start();

    if ( empty( $_POST['product_id'] ) || ! ( $variable_product = wc_get_product( absint( $_POST['product_id'] ), array( 'product_type' => 'variable' ) ) ) ) {
      die();
    }

    $variation_id = $variable_product->get_matching_variation( wp_unslash( $_POST ) );

    if ( $variation_id ) {
      $variation = $variable_product->get_available_variation( $variation_id );
    } else {
      $variation = false;
    }

    wp_send_json( $variation );

    die();
  }

  /**
   * Feature a product from admin.
   */
  public static function feature_product() {
    if ( current_user_can( 'edit_products' ) && check_admin_referer( 'woocommerce-feature-product' ) ) {
      $product_id = absint( $_GET['product_id'] );

      if ( 'product' === get_post_type( $product_id ) ) {
        update_post_meta( $product_id, '_featured', get_post_meta( $product_id, '_featured', true ) === 'yes' ? 'no' : 'yes' );

        delete_transient( 'wc_featured_products' );
      }
    }

    wp_safe_redirect( wp_get_referer() ? remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() ) : admin_url( 'edit.php?post_type=product' ) );
    die();
  }



  /**
   * Delete variations via ajax function.
   */
  public static function remove_variations() {
    check_ajax_referer( 'delete-variations', 'security' );

    if ( ! current_user_can( 'edit_products' ) ) {
      die(-1);
    }

    $variation_ids = (array) $_POST['variation_ids'];

    foreach ( $variation_ids as $variation_id ) {
      $variation = get_post( $variation_id );

      if ( $variation && 'product_variation' == $variation->post_type ) {
        wp_delete_post( $variation_id );
      }
    }

    die();
  }


  /**
   * Get customer details via ajax.
   */
  public static function get_customer_details() {
    ob_start();

    check_ajax_referer( 'get-customer-details', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $user_id      = (int) trim(stripslashes($_POST['user_id']));
    $type_to_load = esc_attr(trim(stripslashes($_POST['type_to_load'])));

    $customer_data = array(
      $type_to_load . '_first_name' => get_user_meta( $user_id, $type_to_load . '_first_name', true ),
      $type_to_load . '_last_name'  => get_user_meta( $user_id, $type_to_load . '_last_name', true ),
      $type_to_load . '_company'    => get_user_meta( $user_id, $type_to_load . '_company', true ),
      $type_to_load . '_address_1'  => get_user_meta( $user_id, $type_to_load . '_address_1', true ),
      $type_to_load . '_address_2'  => get_user_meta( $user_id, $type_to_load . '_address_2', true ),
      $type_to_load . '_city'       => get_user_meta( $user_id, $type_to_load . '_city', true ),
      $type_to_load . '_postcode'   => get_user_meta( $user_id, $type_to_load . '_postcode', true ),
      $type_to_load . '_country'    => get_user_meta( $user_id, $type_to_load . '_country', true ),
      $type_to_load . '_state'      => get_user_meta( $user_id, $type_to_load . '_state', true ),
      $type_to_load . '_email'      => get_user_meta( $user_id, $type_to_load . '_email', true ),
      $type_to_load . '_phone'      => get_user_meta( $user_id, $type_to_load . '_phone', true ),
    );

    $customer_data = apply_filters( 'woocommerce_found_customer_details', $customer_data, $user_id, $type_to_load );

    wp_send_json( $customer_data );
  }

  /**
   * Add order item via ajax.
   */
  public static function add_order_item() {
    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $item_to_add = sanitize_text_field( $_POST['item_to_add'] );
    $order_id    = absint( $_POST['order_id'] );

    // Find the item
    if ( ! is_numeric( $item_to_add ) ) {
      die();
    }

    $post = get_post( $item_to_add );

    if ( ! $post || ( 'product' !== $post->post_type && 'product_variation' !== $post->post_type ) ) {
      die();
    }

    $_product    = wc_get_product( $post->ID );
    $order       = wc_get_order( $order_id );
    $order_taxes = $order->get_taxes();
    $class       = 'new_row';

    // Set values
    $item = array();

    $item['product_id']        = $_product->id;
    $item['variation_id']      = isset( $_product->variation_id ) ? $_product->variation_id : '';
    $item['variation_data']    = $item['variation_id'] ? $_product->get_variation_attributes() : '';
    $item['name']              = $_product->get_title();
    $item['tax_class']         = $_product->get_tax_class();
    $item['qty']               = 1;
    $item['line_subtotal']     = wc_format_decimal( $_product->get_price_excluding_tax() );
    $item['line_subtotal_tax'] = '';
    $item['line_total']        = wc_format_decimal( $_product->get_price_excluding_tax() );
    $item['line_tax']          = '';
    $item['type']              = 'line_item';

    // Add line item
    $item_id = wc_add_order_item( $order_id, array(
      'order_item_name'     => $item['name'],
      'order_item_type'     => 'line_item'
    ) );

    // Add line item meta
    if ( $item_id ) {
      wc_add_order_item_meta( $item_id, '_qty', $item['qty'] );
      wc_add_order_item_meta( $item_id, '_tax_class', $item['tax_class'] );
      wc_add_order_item_meta( $item_id, '_product_id', $item['product_id'] );
      wc_add_order_item_meta( $item_id, '_variation_id', $item['variation_id'] );
      wc_add_order_item_meta( $item_id, '_line_subtotal', $item['line_subtotal'] );
      wc_add_order_item_meta( $item_id, '_line_subtotal_tax', $item['line_subtotal_tax'] );
      wc_add_order_item_meta( $item_id, '_line_total', $item['line_total'] );
      wc_add_order_item_meta( $item_id, '_line_tax', $item['line_tax'] );

      // Since 2.2
      wc_add_order_item_meta( $item_id, '_line_tax_data', array( 'total' => array(), 'subtotal' => array() ) );

      // Store variation data in meta
      if ( $item['variation_data'] && is_array( $item['variation_data'] ) ) {
        foreach ( $item['variation_data'] as $key => $value ) {
          wc_add_order_item_meta( $item_id, str_replace( 'attribute_', '', $key ), $value );
        }
      }

      do_action( 'woocommerce_ajax_add_order_item_meta', $item_id, $item );
    }

    $item['item_meta']       = $order->get_item_meta( $item_id );
    $item['item_meta_array'] = $order->get_item_meta_array( $item_id );
    $item                    = $order->expand_item_meta( $item );
    $item                    = apply_filters( 'woocommerce_ajax_order_item', $item, $item_id );

    include( 'admin/meta-boxes/views/html-order-item.php' );

    // Quit out
    die();
  }

  /**
   * Add order fee via ajax.
   */
  public static function add_order_fee() {

    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $order_id      = absint( $_POST['order_id'] );
    $order         = wc_get_order( $order_id );
    $order_taxes   = $order->get_taxes();
    $item          = array();

    // Add new fee
    $fee            = new stdClass();
    $fee->name      = '';
    $fee->tax_class = '';
    $fee->taxable   = $fee->tax_class !== '0';
    $fee->amount    = '';
    $fee->tax       = '';
    $fee->tax_data  = array();
    $item_id        = $order->add_fee( $fee );

    include( 'admin/meta-boxes/views/html-order-fee.php' );

    // Quit out
    die();
  }

  /**
   * Add order shipping cost via ajax.
   */
  public static function add_order_shipping() {

    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $order_id         = absint( $_POST['order_id'] );
    $order            = wc_get_order( $order_id );
    $order_taxes      = $order->get_taxes();
    $shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
    $item             = array();

    // Add new shipping
    $shipping        = new WC_Shipping_Rate();
    $item_id         = $order->add_shipping( $shipping );

    include( 'admin/meta-boxes/views/html-order-shipping.php' );

    // Quit out
    die();
  }

  /**
   * Add order tax column via ajax.
   */
  public static function add_order_tax() {
    global $wpdb;

    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $order_id = absint( $_POST['order_id'] );
    $rate_id  = absint( $_POST['rate_id'] );
    $order    = wc_get_order( $order_id );
    $data     = get_post_meta( $order_id );

    // Add new tax
    $order->add_tax( $rate_id, 0, 0 );

    // Return HTML items
    include( 'admin/meta-boxes/views/html-order-items.php' );

    die();
  }

  /**
   * Remove an order item.
   */
  public static function remove_order_item() {
    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $order_item_ids = $_POST['order_item_ids'];

    if ( ! is_array( $order_item_ids ) && is_numeric( $order_item_ids ) ) {
      $order_item_ids = array( $order_item_ids );
    }

    if ( sizeof( $order_item_ids ) > 0 ) {
      foreach( $order_item_ids as $id ) {
        wc_delete_order_item( absint( $id ) );
      }
    }

    die();
  }

  /**
   * Remove an order tax.
   */
  public static function remove_order_tax() {

    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $order_id = absint( $_POST['order_id'] );
    $rate_id  = absint( $_POST['rate_id'] );

    wc_delete_order_item( $rate_id );

    // Return HTML items
    $order = wc_get_order( $order_id );
    $data  = get_post_meta( $order_id );
    include( 'admin/meta-boxes/views/html-order-items.php' );

    die();
  }

  /**
   * Reduce order item stock.
   */
  public static function reduce_order_item_stock() {
    check_ajax_referer( 'order-item', 'security' );
    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }
    $order_id       = absint( $_POST['order_id'] );
    $order_item_ids = isset( $_POST['order_item_ids'] ) ? $_POST['order_item_ids'] : array();
    $order_item_qty = isset( $_POST['order_item_qty'] ) ? $_POST['order_item_qty'] : array();
    $order          = wc_get_order( $order_id );
    $order_items    = $order->get_items();
    $return         = array();
    if ( $order && ! empty( $order_items ) && sizeof( $order_item_ids ) > 0 ) {
      foreach ( $order_items as $item_id => $order_item ) {
        // Only reduce checked items
        if ( ! in_array( $item_id, $order_item_ids ) ) {
          continue;
        }
        $_product = $order->get_product_from_item( $order_item );
        if ( $_product->exists() && $_product->managing_stock() && isset( $order_item_qty[ $item_id ] ) && $order_item_qty[ $item_id ] > 0 ) {
          $stock_change = apply_filters( 'woocommerce_reduce_order_stock_quantity', $order_item_qty[ $item_id ], $item_id );
          $new_stock    = $_product->reduce_stock( $stock_change );
          $item_name    = $_product->get_sku() ? $_product->get_sku() : $order_item['product_id'];
          $note         = sprintf( __( 'Item %s stock reduced from %s to %s.', 'woocommerce' ), $item_name, $new_stock + $stock_change, $new_stock );
          $return[]     = $note;
          $order->add_order_note( $note );
          $order->send_stock_notifications( $_product, $new_stock, $order_item_qty[ $item_id ] );
        }
      }
      do_action( 'woocommerce_reduce_order_stock', $order );
      if ( empty( $return ) ) {
        $return[] = __( 'No products had their stock reduced - they may not have stock management enabled.', 'woocommerce' );
      }
      echo implode( ', ', $return );
    }
    die();
  }

  /**
   * Increase order item stock.
   */
  public static function increase_order_item_stock() {
    check_ajax_referer( 'order-item', 'security' );
    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }
    $order_id       = absint( $_POST['order_id'] );
    $order_item_ids = isset( $_POST['order_item_ids'] ) ? $_POST['order_item_ids'] : array();
    $order_item_qty = isset( $_POST['order_item_qty'] ) ? $_POST['order_item_qty'] : array();
    $order          = wc_get_order( $order_id );
    $order_items    = $order->get_items();
    $return         = array();
    if ( $order && ! empty( $order_items ) && sizeof( $order_item_ids ) > 0 ) {
      foreach ( $order_items as $item_id => $order_item ) {
        // Only reduce checked items
        if ( ! in_array( $item_id, $order_item_ids ) ) {
          continue;
        }
        $_product = $order->get_product_from_item( $order_item );
        if ( $_product->exists() && $_product->managing_stock() && isset( $order_item_qty[ $item_id ] ) && $order_item_qty[ $item_id ] > 0 ) {
          $old_stock    = $_product->get_stock_quantity();
          $stock_change = apply_filters( 'woocommerce_restore_order_stock_quantity', $order_item_qty[ $item_id ], $item_id );
          $new_quantity = $_product->increase_stock( $stock_change );
          $item_name    = $_product->get_sku() ? $_product->get_sku(): $order_item['product_id'];
          $note         = sprintf( __( 'Item %s stock increased from %s to %s.', 'woocommerce' ), $item_name, $old_stock, $new_quantity );
          $return[]     = $note;
          $order->add_order_note( $note );
        }
      }
      do_action( 'woocommerce_restore_order_stock', $order );
      if ( empty( $return ) ) {
        $return[] = __( 'No products had their stock increased - they may not have stock management enabled.', 'woocommerce' );
      }
      echo implode( ', ', $return );
    }
    die();
  }

  /**
   * Add some meta to a line item.
   */
  public static function add_order_item_meta() {
    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $meta_id = wc_add_order_item_meta( absint( $_POST['order_item_id'] ), __( 'Name', 'woocommerce' ), __( 'Value', 'woocommerce' ) );

    if ( $meta_id ) {
      echo '<tr data-meta_id="' . esc_attr( $meta_id ) . '"><td><input type="text" name="meta_key[' . $meta_id . ']" /><textarea name="meta_value[' . $meta_id . ']"></textarea></td><td width="1%"><button class="remove_order_item_meta button">&times;</button></td></tr>';
    }

    die();
  }

  /**
   * Remove meta from a line item.
   */
  public static function remove_order_item_meta() {
    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    global $wpdb;

    $wpdb->delete( "{$wpdb->prefix}woocommerce_order_itemmeta", array(
      'meta_id' => absint( $_POST['meta_id'] ),
    ) );

    die();
  }

  /**
   * Calc line tax.
   */
  public static function calc_line_taxes() {
    global $wpdb;

    check_ajax_referer( 'calc-totals', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $tax                    = new WC_Tax();
    $tax_based_on           = get_option( 'woocommerce_tax_based_on' );
    $order_id               = absint( $_POST['order_id'] );
    $items                  = array();
    $country                = strtoupper( esc_attr( $_POST['country'] ) );
    $state                  = strtoupper( esc_attr( $_POST['state'] ) );
    $postcode               = strtoupper( esc_attr( $_POST['postcode'] ) );
    $city                   = wc_clean( esc_attr( $_POST['city'] ) );
    $order                  = wc_get_order( $order_id );
    $taxes                  = array();
    $shipping_taxes         = array();
    $order_item_tax_classes = array();

    // Default to base
    if ( 'base' === $tax_based_on || empty( $country ) ) {
      $default  = wc_get_base_location();
      $country  = $default['country'];
      $state    = $default['state'];
      $postcode = '';
      $city     = '';
    }

    // Parse the jQuery serialized items
    parse_str( $_POST['items'], $items );

    // Prevent undefined warnings
    if ( ! isset( $items['line_tax'] ) ) {
      $items['line_tax'] = array();
    }
    if ( ! isset( $items['line_subtotal_tax'] ) ) {
      $items['line_subtotal_tax'] = array();
    }
    $items['order_taxes'] = array();

    // Action
    $items = apply_filters( 'woocommerce_ajax_calc_line_taxes', $items, $order_id, $country, $_POST );

    $is_vat_exempt = get_post_meta( $order_id, '_is_vat_exempt', true );

    // Tax is calculated only if tax is enabled and order is not vat exempted
    if ( wc_tax_enabled() && $is_vat_exempt !== 'yes' ) {

      // Get items and fees taxes
      if ( isset( $items['order_item_id'] ) ) {
        $line_total = $line_subtotal = array();

        foreach ( $items['order_item_id'] as $item_id ) {
          $item_id                            = absint( $item_id );
          $line_total[ $item_id ]             = isset( $items['line_total'][ $item_id ] ) ? wc_format_decimal( $items['line_total'][ $item_id ] ) : 0;
          $line_subtotal[ $item_id ]          = isset( $items['line_subtotal'][ $item_id ] ) ? wc_format_decimal( $items['line_subtotal'][ $item_id ] ) : $line_total[ $item_id ];
          $order_item_tax_classes[ $item_id ] = isset( $items['order_item_tax_class'][ $item_id ] ) ? sanitize_text_field( $items['order_item_tax_class'][ $item_id ] ) : '';
          $product_id                         = $order->get_item_meta( $item_id, '_product_id', true );

          // Get product details
          if ( get_post_type( $product_id ) == 'product' ) {
            $_product        = wc_get_product( $product_id );
            $item_tax_status = $_product->get_tax_status();
          } else {
            $item_tax_status = 'taxable';
          }

          if ( '0' !== $order_item_tax_classes[ $item_id ] && 'taxable' === $item_tax_status ) {
            $tax_rates = WC_Tax::find_rates( array(
              'country'   => $country,
              'state'     => $state,
              'postcode'  => $postcode,
              'city'      => $city,
              'tax_class' => $order_item_tax_classes[ $item_id ]
            ) );

            $line_taxes          = WC_Tax::calc_tax( $line_total[ $item_id ], $tax_rates, false );
            $line_subtotal_taxes = WC_Tax::calc_tax( $line_subtotal[ $item_id ], $tax_rates, false );

            // Set the new line_tax
            foreach ( $line_taxes as $_tax_id => $_tax_value ) {
              $items['line_tax'][ $item_id ][ $_tax_id ] = $_tax_value;
            }

            // Set the new line_subtotal_tax
            foreach ( $line_subtotal_taxes as $_tax_id => $_tax_value ) {
              $items['line_subtotal_tax'][ $item_id ][ $_tax_id ] = $_tax_value;
            }

            // Sum the item taxes
            foreach ( array_keys( $taxes + $line_taxes ) as $key ) {
              $taxes[ $key ] = ( isset( $line_taxes[ $key ] ) ? $line_taxes[ $key ] : 0 ) + ( isset( $taxes[ $key ] ) ? $taxes[ $key ] : 0 );
            }
          }
        }
      }

      // Get shipping taxes
      if ( isset( $items['shipping_method_id'] ) ) {
        $matched_tax_rates      = array();
        $order_item_tax_classes = array_unique( array_values( $order_item_tax_classes ) );

        // If multiple classes are found, use the first one. Don't bother with standard rate, we can get that later.
        if ( sizeof( $order_item_tax_classes ) > 1 && ! in_array( '', $order_item_tax_classes ) ) {
          $tax_classes = WC_Tax::get_tax_classes();

          foreach ( $tax_classes as $tax_class ) {
            $tax_class = sanitize_title( $tax_class );
            if ( in_array( $tax_class, $order_item_tax_classes ) ) {
              $matched_tax_rates = WC_Tax::find_shipping_rates( array(
                'country'   => $country,
                'state'   => $state,
                'postcode'  => $postcode,
                'city'    => $city,
                'tax_class' => $tax_class,
              ) );
              break;
            }
          }
        // If a single tax class is found, use it
        } elseif ( sizeof( $order_item_tax_classes ) === 1 ) {
          $matched_tax_rates = WC_Tax::find_shipping_rates( array(
            'country'   => $country,
            'state'   => $state,
            'postcode'  => $postcode,
            'city'    => $city,
            'tax_class' => $order_item_tax_classes[0]
          ) );
        }

        // Get standard rate if no taxes were found
        if ( ! sizeof( $matched_tax_rates ) ) {
          $matched_tax_rates = WC_Tax::find_shipping_rates( array(
            'country'   => $country,
            'state'   => $state,
            'postcode'  => $postcode,
            'city'    => $city
          ) );
        }

        $shipping_cost = $shipping_taxes = array();

        foreach ( $items['shipping_method_id'] as $item_id ) {
          $item_id                   = absint( $item_id );
          $shipping_cost[ $item_id ] = isset( $items['shipping_cost'][ $item_id ] ) ? wc_format_decimal( $items['shipping_cost'][ $item_id ] ) : 0;
          $_shipping_taxes           = WC_Tax::calc_shipping_tax( $shipping_cost[ $item_id ], $matched_tax_rates );

          // Set the new shipping_taxes
          foreach ( $_shipping_taxes as $_tax_id => $_tax_value ) {
            $items['shipping_taxes'][ $item_id ][ $_tax_id ] = $_tax_value;

            $shipping_taxes[ $_tax_id ] = isset( $shipping_taxes[ $_tax_id ] ) ? $shipping_taxes[ $_tax_id ] + $_tax_value : $_tax_value;
          }
        }
      }
    }

    // Remove old tax rows
    $order->remove_order_items( 'tax' );

    // Add tax rows
    foreach ( array_keys( $taxes + $shipping_taxes ) as $tax_rate_id ) {
      $order->add_tax( $tax_rate_id, isset( $taxes[ $tax_rate_id ] ) ? $taxes[ $tax_rate_id ] : 0, isset( $shipping_taxes[ $tax_rate_id ] ) ? $shipping_taxes[ $tax_rate_id ] : 0 );
    }

    // Create the new order_taxes
    foreach ( $order->get_taxes() as $tax_id => $tax_item ) {
      $items['order_taxes'][ $tax_id ] = absint( $tax_item['rate_id'] );
    }

    $items = apply_filters( 'woocommerce_ajax_after_calc_line_taxes', $items, $order_id, $country, $_POST );

    // Save order items
    wc_save_order_items( $order_id, $items );

    // Return HTML items
    $order = wc_get_order( $order_id );
    $data  = get_post_meta( $order_id );
    include( 'admin/meta-boxes/views/html-order-items.php' );

    die();
  }

  /**
   * Save order items via ajax.
   */
  public static function save_order_items() {
    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    if ( isset( $_POST['order_id'] ) && isset( $_POST['items'] ) ) {
      $order_id = absint( $_POST['order_id'] );

      // Parse the jQuery serialized items
      $items = array();
      parse_str( $_POST['items'], $items );

      // Save order items
      wc_save_order_items( $order_id, $items );

      // Return HTML items
      $order = wc_get_order( $order_id );
      $data  = get_post_meta( $order_id );
      include( 'admin/meta-boxes/views/html-order-items.php' );
    }

    die();
  }

  /**
   * Load order items via ajax.
   */
  public static function load_order_items() {
    check_ajax_referer( 'order-item', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    // Return HTML items
    $order_id = absint( $_POST['order_id'] );
    $order    = wc_get_order( $order_id );
    $data     = get_post_meta( $order_id );
    include( 'admin/meta-boxes/views/html-order-items.php' );

    die();
  }

  /**
   * Add order note via ajax.
   */
  public static function add_order_note() {

    check_ajax_referer( 'add-order-note', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $post_id   = absint( $_POST['post_id'] );
    $note      = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );
    $note_type = $_POST['note_type'];

    $is_customer_note = $note_type == 'customer' ? 1 : 0;

    if ( $post_id > 0 ) {
      $order      = wc_get_order( $post_id );
      $comment_id = $order->add_order_note( $note, $is_customer_note, true );

      echo '<li rel="' . esc_attr( $comment_id ) . '" class="note ';
      if ( $is_customer_note ) {
        echo 'customer-note';
      }
      echo '"><div class="note_content">';
      echo wpautop( wptexturize( $note ) );
      echo '</div><p class="meta"><a href="#" class="delete_note">'.__( 'Delete note', 'woocommerce' ).'</a></p>';
      echo '</li>';
    }

    // Quit out
    die();
  }

  /**
   * Delete order note via ajax.
   */
  public static function delete_order_note() {

    check_ajax_referer( 'delete-order-note', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $note_id = (int) $_POST['note_id'];

    if ( $note_id > 0 ) {
      wp_delete_comment( $note_id );
    }

    // Quit out
    die();
  }


  /**
   * Search for customers and return json.
   */
  public static function json_search_customers() {
    ob_start();

    check_ajax_referer( 'search-customers', 'security' );

    if ( ! current_user_can( 'edit_shop_orders' ) ) {
      die(-1);
    }

    $term    = wc_clean( stripslashes( $_GET['term'] ) );
    $exclude = array();

    if ( empty( $term ) ) {
      die();
    }

    if ( ! empty( $_GET['exclude'] ) ) {
      $exclude = array_map( 'intval', explode( ',', $_GET['exclude'] ) );
    }

    $found_customers = array();

    add_action( 'pre_user_query', array( __CLASS__, 'json_search_customer_name' ) );

    $customers_query = new WP_User_Query( apply_filters( 'woocommerce_json_search_customers_query', array(
      'fields'         => 'all',
      'orderby'        => 'display_name',
      'search'         => '*' . $term . '*',
      'search_columns' => array( 'ID', 'username', 'user_email', 'user_nicename' )
    ) ) );

    remove_action( 'pre_user_query', array( __CLASS__, 'json_search_customer_name' ) );

    $customers = $customers_query->get_results();

    if ( ! empty( $customers ) ) {
      foreach ( $customers as $customer ) {
        if ( ! in_array( $customer->ID, $exclude ) ) {
          $found_customers[ $customer->ID ] = $customer->display_name . ' (#' . $customer->ID . ' &ndash; ' . sanitize_email( $customer->user_email ) . ')';
        }
      }
    }

    $found_customers = apply_filters( 'woocommerce_json_search_found_customers', $found_customers );

    wp_send_json( $found_customers );
  }

  /**
   * When searching using the WP_User_Query, search names (user meta) too.
   * @param  object $query
   * @return object
   */
  public static function json_search_customer_name( $query ) {
    global $wpdb;

    $term = wc_clean( stripslashes( $_GET['term'] ) );
    if ( method_exists( $wpdb, 'esc_like' ) ) {
      $term = $wpdb->esc_like( $term );
    } else {
      $term = like_escape( $term );
    }

    $query->query_from  .= " INNER JOIN {$wpdb->usermeta} AS user_name ON {$wpdb->users}.ID = user_name.user_id AND ( user_name.meta_key = 'first_name' OR user_name.meta_key = 'last_name' ) ";
    $query->query_where .= $wpdb->prepare( " OR user_name.meta_value LIKE %s ", '%' . $term . '%' );
  }

  /**
   * Ajax request handling for categories ordering.
   */
  public static function term_ordering() {

    // check permissions again and make sure we have what we need
    if ( ! current_user_can( 'edit_products' ) || empty( $_POST['id'] ) ) {
      die(-1);
    }

    $id       = (int) $_POST['id'];
    $next_id  = isset( $_POST['nextid'] ) && (int) $_POST['nextid'] ? (int) $_POST['nextid'] : null;
    $taxonomy = isset( $_POST['thetaxonomy'] ) ? esc_attr( $_POST['thetaxonomy'] ) : null;
    $term     = get_term_by( 'id', $id, $taxonomy );

    if ( ! $id || ! $term || ! $taxonomy ) {
      die(0);
    }

    wc_reorder_terms( $term, $next_id, $taxonomy );

    $children = get_terms( $taxonomy, "child_of=$id&menu_order=ASC&hide_empty=0" );

    if ( $term && sizeof( $children ) ) {
      echo 'children';
      die();
    }
  }

  /**
   * Ajax request handling for product ordering.
   *
   * Based on Simple Page Ordering by 10up (https://wordpress.org/extend/plugins/simple-page-ordering/).
   */
  public static function product_ordering() {
    global $wpdb;

    ob_start();

    // check permissions again and make sure we have what we need
    if ( ! current_user_can('edit_products') || empty( $_POST['id'] ) || ( ! isset( $_POST['previd'] ) && ! isset( $_POST['nextid'] ) ) ) {
      die(-1);
    }

    // real post?
    if ( ! $post = get_post( $_POST['id'] ) ) {
      die(-1);
    }

    $previd  = isset( $_POST['previd'] ) ? $_POST['previd'] : false;
    $nextid  = isset( $_POST['nextid'] ) ? $_POST['nextid'] : false;
    $new_pos = array(); // store new positions for ajax

    $siblings = $wpdb->get_results( $wpdb->prepare( "
      SELECT ID, menu_order FROM {$wpdb->posts} AS posts
      WHERE   posts.post_type   = 'product'
      AND   posts.post_status   IN ( 'publish', 'pending', 'draft', 'future', 'private' )
      AND   posts.ID      NOT IN (%d)
      ORDER BY posts.menu_order ASC, posts.ID DESC
    ", $post->ID ) );

    $menu_order = 0;

    foreach ( $siblings as $sibling ) {

      // if this is the post that comes after our repositioned post, set our repositioned post position and increment menu order
      if ( $nextid == $sibling->ID ) {
        $wpdb->update(
          $wpdb->posts,
          array(
            'menu_order' => $menu_order
          ),
          array( 'ID' => $post->ID ),
          array( '%d' ),
          array( '%d' )
        );
        $new_pos[ $post->ID ] = $menu_order;
        $menu_order++;
      }

      // if repositioned post has been set, and new items are already in the right order, we can stop
      if ( isset( $new_pos[ $post->ID ] ) && $sibling->menu_order >= $menu_order ) {
        break;
      }

      // set the menu order of the current sibling and increment the menu order
      $wpdb->update(
        $wpdb->posts,
        array(
          'menu_order' => $menu_order
        ),
        array( 'ID' => $sibling->ID ),
        array( '%d' ),
        array( '%d' )
      );
      $new_pos[ $sibling->ID ] = $menu_order;
      $menu_order++;

      if ( ! $nextid && $previd == $sibling->ID ) {
        $wpdb->update(
          $wpdb->posts,
          array(
            'menu_order' => $menu_order
          ),
          array( 'ID' => $post->ID ),
          array( '%d' ),
          array( '%d' )
        );
        $new_pos[$post->ID] = $menu_order;
        $menu_order++;
      }

    }

    do_action( 'woocommerce_after_product_ordering' );

    wp_send_json( $new_pos );
  }

 
  /**
   * Locate user via AJAX.
   */
  public static function get_customer_location() {
    $location_hash = WC_Cache_Helper::geolocation_ajax_get_location_hash();
    wp_send_json_success( array( 'hash' => $location_hash ) );
  }


    public function nonce()
    {

      $data->country = WC()->countries;
      $data->state = WC()->countries->get_states( $cc );
      $data->checkout_nonce = wp_create_nonce( 'woocommerce-process_checkout');
      $data->checkout_login = wp_create_nonce( 'woocommerce-login' );
      $data->save_account_details = wp_create_nonce( 'save_account_details' );
        wp_send_json(  $data );
    }

    public function login()
    {

        $login = wp_authenticate($_REQUEST['username'], $_REQUEST['password']);
        if (!is_wp_error($login)) {
            
         // $login->status = is_user_logged_in();
          $login->status = true;
          $login->url = wp_logout_url( $redirect );

          wp_send_json( $login );
         
        }
        /* @var $login WP_Error */
        $errorCode = strtoupper("username_" . $login->get_error_code());
        $login->status = false;
        wp_send_json(  $login );
    }

    public function userdata()
    {
        if(is_user_logged_in()){
        $user = wp_get_current_user();
        $user->status = true;
        $user->url = wp_logout_url( $redirect );
        $user->avatar = get_avatar($user->ID, 128);
        
        wp_send_json(  $user );
        }
        
        $user->status = false;
        
        wp_send_json(  $user );

    }


}

?>
