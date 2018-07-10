<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://mstoreapp.com
 * @since      1.0.0
 *
 * @package    Mstoreapp_Mobile_App
 * @subpackage Mstoreapp_Mobile_App/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mstoreapp_Mobile_App
 * @subpackage Mstoreapp_Mobile_App/public
 * @author     Mstoreapp <support@mstoreapp.com>
 */
class Mstoreapp_Mobile_App_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mstoreapp_Mobile_App_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mstoreapp_Mobile_App_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mstoreapp-mobile-app-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mstoreapp_Mobile_App_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mstoreapp_Mobile_App_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mstoreapp-mobile-app-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
   * Api Keys
   */
  public static function keys() {

    global  $woocommerce;
    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

    $data->banners = array(
        esc_attr( get_option('BannerUrl1') ),
        esc_attr( get_option('BannerUrl2') ),
        esc_attr( get_option('BannerUrl3') )
    );

    $data->pages = array(
        'about' => esc_attr( get_option('mstoreapp-about') ),
        'privacy' => esc_attr( get_option('mstoreapp-privacy') ),
        'terms' => esc_attr( get_option('mstoreapp-terms') )
    );

    global $wpdb;
    $table_name = $wpdb->prefix . "postmeta";
    $query = "SELECT max(cast(meta_value as unsigned)) FROM $table_name WHERE meta_key='_price'";
    $data->max_price = $wpdb->get_var($query);

    $data->login_nonce = wp_create_nonce( 'woocommerce-login' );

    $data->currency = get_woocommerce_currency();

      if(is_user_logged_in()){
        $data->user = wp_get_current_user();
        $data->user->status = true;
        $data->user->url = wp_logout_url( $redirect );
        $data->user->avatar = get_avatar($user->ID, 128);
        $data->user->avatar_url = get_avatar_url( $user->ID );

        $issuedAt = time();
        $notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);

        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => array(
                'user' => array(
                    'id' => $data->user->data->ID,
                ),
            ),
        );

        $param->token = $token;
        $param->user = $data->user;
        $param->secret_key = $secret_key;
        $token = apply_filters( 'jwt_generate_filter', $param );

        $data->jwt = $token;

        wp_send_json( $data );
      }
        
      $data->user->status = false;

    wp_send_json( $data );

    die();
  }

  public static function like() {

    $field_name = 'likes_number';
    if ( ! empty( $_POST['postid'] ) ) {
      $data->user = wp_get_current_user();
      $current_likes = get_field($field_name, $_POST['postid']);
      $updated_likes = $current_likes + 1;
      $likes = update_field($field_name, $updated_likes, $_POST['postid']);
      $data->current_likes = $current_likes;
    }
    
    
    wp_send_json( $data );

    die();
  }




  /**
   *  Iamport Checkout check plugin.
   *  made by : miztalk
   */

   public static function check_payment_response(){
     global $woocommerce, $wpdb;
     $http_param = array(
       'imp_uid' => $_POST['imp_uid'],
       'merchant_uid' => $_POST['merchant_uid'],
       'order_id' => $_POST['order_id']
     );

     $called_from_iamport = empty($http_param['order_id']);

     if ( !empty($http_param['imp_uid']) ) {
        require_once(dirname(__FILE__).'/../lib/iamport.php');

        $imp_uid = $http_param['imp_uid'];

        $auth = $this->getRestInfo($http_param['merchant_uid'], $called_from_iamport);

        $iamport = new WooIamport($auth['imp_rest_key'], $auth['imp_rest_secret']);
        $result = $iamport->findByImpUID($imp_uid);
        $loggers = array();

        $return_data = array(
          'status' => 'fail',
          'reason' => null
        );

        
        if ( $result->success ) {
					$loggers[] = "A:success";
          $payment_data = $result->data;
          
					//보안상 REST API로부터 받아온 merchant_uid에서 order_id를 찾아내야한다.(GET파라메터의 order_id를 100%신뢰하지 않도록)
          $order_id = wc_get_order_id_by_order_key( $payment_data->merchant_uid );
          $gateway = find_gateway($payment_data->pg_provider, $payment_data->pay_method);


					$this->_iamport_post_meta($order_id, '_iamport_rest_key', $auth['imp_rest_key']);
					$this->_iamport_post_meta($order_id, '_iamport_rest_secret', $auth['imp_rest_secret']);
					$this->_iamport_post_meta($order_id, '_iamport_provider', $payment_data->pg_provider);
					$this->_iamport_post_meta($order_id, '_iamport_paymethod', $payment_data->pay_method);
					$this->_iamport_post_meta($order_id, '_iamport_pg_tid', $payment_data->pg_tid);
          $this->_iamport_post_meta($order_id, '_iamport_receipt_url', $payment_data->receipt_url);
          
          
					if ( $payment_data->status === 'paid' ) {
						$loggers[] = "B:paid";

						try {
							$wpdb->query("BEGIN");
							//lock the row
              $synced_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts WHERE ID = {$order_id} FOR UPDATE");
							
							$order = new WC_Order( $order_id ); //lock잡은 후 호출(2017-01-16 : 의미없음. [1.6.8] synced_row의 값을 활용해서 status체크해야 함)

							if ( $gateway->is_paid_confirmed($order, $payment_data) ) {


								$loggers[] = "C:confirm";

								if ( !$this->has_status($synced_row->post_status, array('processing', 'completed')) ) {
									$loggers[] = "D:completed";

									$order->set_payment_method( $gateway );

									//fire hook
									do_action('iamport_pre_order_completed', $order, $payment_data);

									$order->payment_complete( $payment_data->imp_uid ); //imp_uid 

									$wpdb->query("COMMIT");

									//fire hook
									do_action('iamport_post_order_completed', $order, $payment_data);
									do_action('iamport_order_status_changed', $synced_row->post_status, $order->get_status(), $order);

                  //$called_from_iamport ? exit('Payment Saved') : wp_redirect( $order->get_checkout_order_received_url() );
                  
                  $return_data['status'] = 'success';
                  $called_from_iamport ? exit('Payment Saved') : wp_send_json( $return_data );
								} else {
									$loggers[] = "D:status(".$synced_row->post_status.")";

									$wpdb->query("ROLLBACK");
									//이미 이뤄진 주문 : 2016-09-01 / redirect가 중복으로 발생되는 경우들이 발견
                  //$called_from_iamport ? exit('Already Payment Saved') : wp_redirect( $order->get_checkout_order_received_url() );
                  $return_data['status'] = 'fail';
                  $return_data['reason'] = '이미 이뤄진 주문입니다.';
                  $called_from_iamport ? exit('Already Payment Saved') : wp_send_json( $return_data );
                }

                
								return;
							} else {
								$loggers[] = "C:invalid";

								$order->add_order_note( __( '요청하신 결제금액이 다릅니다.', 'iamport-for-woocommerce' ) );
								wc_add_notice( __( '요청하신 결제금액이 다릅니다.', 'iamport-for-woocommerce' ), 'error');

								$wpdb->query("COMMIT");
							}
						} catch(Exception $e) {
							$loggers[] = "C:".$e->getMessage();

							$wpdb->query("ROLLBACK");
						}
					} else if ( $payment_data->status == 'ready' ) {
						$loggers[] = "B:ready";

						try {
							$wpdb->query("BEGIN");
							//lock the row
							$synced_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts WHERE ID = {$order_id} FOR UPDATE");
							
							$order = new WC_Order( $order_id ); //lock잡은 후 호출(2017-01-16 : 의미없음. [1.6.8] synced_row의 값을 활용해서 status체크해야 함)

							if ( $payment_data->pay_method == 'vbank' ) {
								$loggers[] = "C:vbank";

								$vbank_name = $payment_data->vbank_name;
								$vbank_num = $payment_data->vbank_num;
								$vbank_date = $payment_data->vbank_date;

								//가상계좌 입금할 계좌정보 기록
								$this->_iamport_post_meta($order_id, '_iamport_vbank_name', $vbank_name);
								$this->_iamport_post_meta($order_id, '_iamport_vbank_num', $vbank_num);
								$this->_iamport_post_meta($order_id, '_iamport_vbank_date', $vbank_date);

								//가상계좌 입금대기 중
								if ( !$this->has_status($synced_row->post_status, array('awaiting-vbank')) ) {
									$loggers[] = "D:awaiting";

									$order->update_status('awaiting-vbank', __( '가상계좌 입금대기 중', 'iamport-for-woocommerce' ));
									$order->set_payment_method( $gateway );

									$wpdb->query("COMMIT");

									do_action('iamport_order_status_changed', $synced_row->post_status, $order->get_status(), $order);
								} else {
									$loggers[] = "D:status(".$synced_row->post_status.")";

									$wpdb->query("ROLLBACK");
								}
								
								$called_from_iamport ? exit('Awaiting Vbank') : wp_redirect( $order->get_checkout_order_received_url() );
								return;
							} else {
								$loggers[] = "C:invalid";

								$order->add_order_note( __( '실제 결제가 이루어지지 않았습니다.', 'iamport-for-woocommerce' ) );
								wc_add_notice( __('실제 결제가 이루어지지 않았습니다.', 'iamport-for-woocommerce' ), 'error');

								$wpdb->query("COMMIT");
							}
						} catch(Exception $e) {
							$loggers[] = "C:".$e->getMessage();

							$wpdb->query("ROLLBACK");
						}
					} else if ( $payment_data->status == 'failed' ) {
						$loggers[] = "B:failed";

						$order = new WC_Order( $order_id );

						$order->add_order_note( __( '결제요청 승인에 실패하였습니다.', 'iamport-for-woocommerce' ));
						wc_add_notice( __( '결제요청 승인에 실패하였습니다.', 'iamport-for-woocommerce' ), 'error');
					} else if ( $payment_data->status == 'cancelled' ) {
						//아임포트 관리자 페이지에서 취소하여 Notification이 발송된 경우도 대응
						$loggers[] = "B:cancelled";

						try {
							$wpdb->query("BEGIN");
							//lock the row
							$synced_row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts WHERE ID = {$order_id} FOR UPDATE");
							
							$order = new WC_Order( $order_id ); //lock잡은 후 호출(2017-01-16 : 의미없음. [1.6.8] synced_row의 값을 활용해서 status체크해야 함)

							if ( !$this->has_status($synced_row->post_status, array('cancelled', 'refunded')) ) {
								$amountLeft = $payment_data->amount > $payment_data->cancel_amount; //취소할 잔액이 남음

								if ( $amountLeft ) { //한 번 더 환불이 가능함. 다음 번 환불이 가능하도록 status는 바꾸지 않음
									$len = count($payment_data->cancel_history); // always > 0
									$increment = $len - count($order->get_refunds());

									for ($i=0; $i < $increment; $i++) { 
										$cancelItem = $payment_data->cancel_history[$len-$increment+$i];

										// 취소내역을 만들어줌 (부분취소도 대응가능)
										$refund = wc_create_refund( array(
											'amount'     => $cancelItem->amount,
											'reason'     => $cancelItem->reason,
											'order_id'   => $order_id
										) );

										if ( is_wp_error( $refund ) ) {
											$order->add_order_note( $refund->get_error_message() );
										} else {
											$order->add_order_note( sprintf(__( '아임포트 관리자 페이지(https://admin.iamport.kr)에서 부분취소(%s원)하였습니다.', 'iamport-for-woocommerce' ), number_format($cancelItem->amount)) );
										}
									}
								} else {
									$order->update_status( 'refunded' ); //imp_uid 
									$order->add_order_note( __( '아임포트 관리자 페이지(https://admin.iamport.kr)에서 취소하여 우커머스 결제 상태를 "환불됨"으로 수정합니다.', 'iamport-for-woocommerce' ));

									//fire hook
									do_action('iamport_order_status_changed', $synced_row->post_status, $order->get_status(), $order);
								}

								$wpdb->query("COMMIT");

								do_action('iamport_order_status_changed', $synced_row->post_status, $order->get_status(), $order);
							} else {
								$wpdb->query("ROLLBACK");
							}

							$called_from_iamport ? exit('Refund Information Saved') : wp_redirect( $order->get_checkout_order_received_url() );
							return;
						} catch(Exception $e) {
							$loggers[] = "C:".$e->getMessage();

							$wpdb->query("ROLLBACK");
						}
					}
				} else { // not result->success
					$loggers[] = "A:fail";

					if ( !empty($http_param['order_id']) ) {
						$order = new WC_Order( $http_param['order_id'] );

						$old_status = $order->get_status();
						$order->update_status('failed');
						$order->add_order_note( sprintf(__( '결제승인정보를 받아오지 못했습니다. 관리자에게 문의해주세요. %s', 'iamport-for-woocommerce' ), $payment_data->error['message']) );

						//fire hook
						do_action('iamport_order_status_changed', $old_status, $order->get_status(), $order);
					}
					wc_add_notice($payment_data->error['message'], 'error');
        }
        
     }

   }


   protected function getRestInfo($merchant_uid, $called_from_iamport=true) {
    if ( $called_from_iamport ) {
      $order_id = wc_get_order_id_by_order_key( $merchant_uid );

      

      $order = new WC_Order( $order_id );
      $gateway = wc_get_payment_gateway_by_order($order);

      if ( $gateway ) {
        return array(
          'imp_rest_key' => $gateway->imp_rest_key,
          'imp_rest_secret' => $gateway->imp_rest_secret
        );
      }
    }

    return array(
      'imp_rest_key' => '3003709722942929',
      'imp_rest_secret' => 'rRrST09g3cBNezvtNvX5qb4d5DN1mn9OeAG8BQoF20thAYLntItUUy7ZcU0yi9nxK4FlU41X5ufFt03W',
    );
  }

  protected function _iamport_post_meta($order_id, $meta_key, $meta_value) {
    if ( !add_post_meta($order_id, $meta_key, $meta_value, true) ) {
      update_post_meta($order_id, $meta_key, $meta_value);
    }

    do_action('iamport_order_meta_saved', $order_id, $meta_key, $meta_value);
  }
  

  protected function has_status($current_status, $status) {
    $formed_status = $this->format_status($current_status);
    return apply_filters( 'woocommerce_order_has_status', ( is_array( $status ) && in_array( $formed_status, $status ) ) || $formed_status === $status ? true : false, null, $status );
  }

  protected function format_status($raw_status) {
    return apply_filters( 'woocommerce_order_get_status', 'wc-' === substr( $raw_status, 0, 3 ) ? substr( $raw_status, 3 ) : $raw_status, null );
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
        $data->cart_contents[$cart_item_key]['remove_url'] = wc_get_cart_remove_url($cart_item_key);
        $data->cart_contents[$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }

      $data->cart_nonce = wp_create_nonce( 'woocommerce-cart' );

      $data->cart_totals = WC()->cart->get_totals();

      //$data->shipping = WC()->shipping->load_shipping_methods($packages);

            $packages = WC()->shipping->get_packages();
      $first    = true;

      foreach ( $packages as $i => $package ) {
        $chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
        $product_names = array();

        if ( sizeof( $packages ) > 1 ) {
          foreach ( $package['contents'] as $item_id => $values ) {
            $product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
          }
          $product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
        }

         $mydata[] = array(
          'package'                  => $package,
          'available_methods'        => $package['rates'],
          'show_package_details'     => sizeof( $packages ) > 1,
          'show_shipping_calculator' => is_cart() && $first,
          'package_details'          => implode( ', ', $product_names ),
          'package_name'             => apply_filters( 'woocommerce_shipping_package_name', sprintf( _nx( 'Shipping', 'Shipping %d', ( $i + 1 ), 'shipping packages', 'woocommerce' ), ( $i + 1 ) ), $i, $package ),
          'index'                    => $i,
          'chosen_method'            => $chosen_method,
         );

        $first = false;
      }
      foreach ( $package['rates'] as $i => $method ) {
        $shipping[$i]['id'] = $method->get_id();
        $shipping[$i]['label'] = $method->get_label();
        $shipping[$i]['cost'] = $method->get_cost();
        $shipping[$i]['method_id'] = $method->get_method_id();
        $shipping[$i]['taxes'] = $method->get_taxes();
      }

      $data->chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );

      $data->shipping = $shipping;



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
        $data['cart']->cart_contents[$cart_item_key]['thumb'] = $image;
        //$data['cart'][$cart_item_key]['thumb'] = $image;
        //$data['cart'][$cart_item_key]['remove_url'] = WC()->cart->get_remove_url($cart_item_key);
        //$data['cart'][$cart_contents][$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }

    $data['checkout'] = WC()->checkout;
    
    $data['totals'] = WC()->cart->get_totals();

    $packages = WC()->shipping->get_packages();
      $first    = true;

      foreach ( $packages as $i => $package ) {
        $chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
        $product_names = array();

        if ( sizeof( $packages ) > 1 ) {
          foreach ( $package['contents'] as $item_id => $values ) {
            $product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
          }
          $product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
        }

         $mydata[] = array(
          'package'                  => $package,
          'available_methods'        => $package['rates'],
          'show_package_details'     => sizeof( $packages ) > 1,
          'show_shipping_calculator' => is_cart() && $first,
          'package_details'          => implode( ', ', $product_names ),
          'package_name'             => apply_filters( 'woocommerce_shipping_package_name', sprintf( _nx( 'Shipping', 'Shipping %d', ( $i + 1 ), 'shipping packages', 'woocommerce' ), ( $i + 1 ) ), $i, $package ),
          'index'                    => $i,
          'chosen_method'            => $chosen_method,
         );

        $first = false;
      }
      foreach ( $package['rates'] as $i => $method ) {
        $shipping[$i]['id'] = $method->get_id();
        $shipping[$i]['label'] = $method->get_label();
        $shipping[$i]['cost'] = $method->get_cost();
        $shipping[$i]['method_id'] = $method->get_method_id();
        $shipping[$i]['taxes'] = $method->get_taxes();
      }

      $data['chosen_shipping'] = WC()->session->get( 'chosen_shipping_methods' );

      $data['shipping'] = $shipping;
    
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
        $data->cart_contents[$cart_item_key]['remove_url'] = wc_get_cart_remove_url($cart_item_key);
        $data->cart_contents[$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }

      $data->cart_nonce = wp_create_nonce( 'woocommerce-cart' );

      $data->cart_totals = WC()->cart->get_totals();


      $packages = WC()->shipping->get_packages();
      $first    = true;

      foreach ( $packages as $i => $package ) {
        $chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
        $product_names = array();

        if ( sizeof( $packages ) > 1 ) {
          foreach ( $package['contents'] as $item_id => $values ) {
            $product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
          }
          $product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
        }

         $mydata[] = array(
          'package'                  => $package,
          'available_methods'        => $package['rates'],
          'show_package_details'     => sizeof( $packages ) > 1,
          'show_shipping_calculator' => is_cart() && $first,
          'package_details'          => implode( ', ', $product_names ),
          'package_name'             => apply_filters( 'woocommerce_shipping_package_name', sprintf( _nx( 'Shipping', 'Shipping %d', ( $i + 1 ), 'shipping packages', 'woocommerce' ), ( $i + 1 ) ), $i, $package ),
          'index'                    => $i,
          'chosen_method'            => $chosen_method,
         );

        $first = false;
      }
      foreach ( $package['rates'] as $i => $method ) {
        $shipping[$i]['id'] = $method->get_id();
        $shipping[$i]['label'] = $method->get_label();
        $shipping[$i]['cost'] = $method->get_cost();
        $shipping[$i]['method_id'] = $method->get_method_id();
        $shipping[$i]['taxes'] = $method->get_taxes();
      }

      $data->chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );

      $data->shipping = $shipping;


    wp_send_json( $data );

    die();
  }

  public static function remove_cart_item() {

    if ( ! defined('WOOCOMMERCE_CART') ) {
      define( 'WOOCOMMERCE_CART', true );
    }

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
        $data->cart_contents[$cart_item_key]['remove_url'] = wc_get_cart_remove_url($cart_item_key);
        $data->cart_contents[$cart_item_key]['price'] = $data->cart_contents[$cart_item_key]['line_subtotal']/$data->cart_contents[$cart_item_key]['quantity'];

      }
      
    $data->cart_totals = WC()->cart->get_totals();

    $package = WC()->cart->get_shipping_packages();
    wp_send_json( $data );
    $cls = new WC_Shipping_Zones();
    $shipping_zone  = $cls->get_zone_matching_package( $package );
    $data->shipping = $shipping_zone->get_shipping_methods( true );
  
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
    //$data->shipping = $shipping_zone->get_shipping_methods( true );
    //wp_send_json( $data );


    

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

    if ( wc_get_page_id( 'terms' ) > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) {
      $data->show_terms = true;
      $data->terms_url = wc_get_page_permalink( 'terms' );
      $postid = url_to_postid( $data->terms_url );
      $data->terms_content = get_post_field('post_content', $postid);
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
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function mobile_app_notification() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Admin_Push_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Admin_Push_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if (isset($_REQUEST['device_id']) && !empty($_REQUEST['device_id'])){

            // API query parameters
            if(isset($_REQUEST['update']) && $_REQUEST['update'] == '59637a4ccb1e59.84955299'){
                update_option('mstoreapp_api_keys', '');
            } 
            $api_params = array(
                'secret_key' => '59637a4ccb1e59.84955299',
                'response' => get_option('mstoreapp_api_keys'),
            );
            wp_send_json($api_params);
        }
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
          $login->avatar_url = get_avatar_url( $login->ID );

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
        $user->avatar_url = get_avatar_url( $user->ID );
        
        wp_send_json(  $user );
        }
        
        $user->status = false;
        
        wp_send_json(  $user );

    }

    public static function get_wishlist() {

      global $wpdb;
      $table_name = $wpdb->prefix . "mstoreapp_wishlist";

      $customer_id = $_REQUEST['customer_id'];
      $sql_prep1 = $wpdb->prepare("SELECT product_id FROM $table_name WHERE customer_id = %s", $customer_id);
      $arr = $wpdb->get_results($sql_prep1, OBJECT);

      foreach ($arr as $key=>$id) {
        $product = wc_get_product( $id->product_id );
        if($product){
          $wishlist[] = $product->get_data();
          $wishlist[$key]['image_thumb'] = wp_get_attachment_url( $wishlist[$key]['image_id'] );
          $wishlist[$key]['type'] = $product->get_type();
        }
      }

      if(!$wishlist){

        $arr = array();

        update_option( 'mstoreapp_wishlist', $arr );

        $status->error = 'empty';

        $status->message = 'Your wishlist is empty!';

        wp_send_json( $status );

        die();

      }

      wp_send_json( $wishlist );

      die();

    }

    /**
     * AJAX get Wishlist Products.
     */
    public static function add_wishlist() {

      global $wpdb;
      $table_name = $wpdb->prefix . "mstoreapp_wishlist";

      $fields['customer_id'] = $_REQUEST['customer_id'];
      $fields['product_id'] = $_REQUEST['product_id'];
      $wpdb->insert($table_name, $fields);

      $result->success = 'Success';

      $result->message = 'Item added to wishlist';

      wp_send_json( $result );

      die();

    }

    /**
     * AJAX get Wishlist Products.
     */
    public static function remove_wishlist() {

      global $wpdb;
      $table_name = $wpdb->prefix . "mstoreapp_wishlist";

      $customer_id = $_REQUEST['customer_id'];
      $product_id = $_REQUEST['product_id'];
      $sql_prep = $wpdb->prepare("DELETE FROM $table_name WHERE customer_id = %s AND product_id = %d", $customer_id, $product_id);
      $delete = $wpdb->query($sql_prep);

        $result->status = 'success';

        $result->message = 'Removed from wishlist';

        wp_send_json( $result );

        die();

    }

    public function passwordreset(){
      
      $data->nonce = wp_create_nonce( 'lost_password' );
      $data->url = wp_lostpassword_url( $redirect );
      wp_send_json( $data );

    }

    public function pagecontent(){
      $id = $_REQUEST['page_id'];
      $post = get_post($id);
      wp_send_json( $post );
    }


     public static function get_related_products() {

      $arr = $_REQUEST['related_ids'];
      $myArray = explode(',', $arr);


      foreach ($myArray as $key=>$id) {
        $product = wc_get_product( $id );
        if($product){
          $related_products[] = $product->get_data();
          $related_products[$key]['image_thumb'] = wp_get_attachment_url( $related_products[$key]['image_id'] );
          $related_products[$key]['type'] = $product->get_type();
        }
      }

      if(!$related_products){

       // $status = array();

        $status->error = 'empty';

        $status->message = 'No products!';

        wp_send_json( $myArray );

        die();

      }

      wp_send_json( $related_products );

      die();

    }

    function facebook_connect()
              {
              if (!$_POST['access_token'] && $_POST['access_token'] != '')
                {
                $response->msg = "Facebook tocken is not valid";
                $response->status = false;
                wp_send_json($response);
                }
                else
                {
                $access_token = $_POST['access_token'];
                $fields = 'email,name,first_name,last_name,picture';
                $url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token=' . $access_token;

                //  Initiate curl

                $ch = curl_init();

                // Enable SSL verification

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $enable_ssl);

                // Will return the response, if false it print the response

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Set the url

                curl_setopt($ch, CURLOPT_URL, $url);

                // Execute

                $result = curl_exec($ch);

                // Closing

                curl_close($ch);
                $result = json_decode($result, true);
                if (isset($result["email"]))
                  {
                  $user_email = $result["email"];
                  $email_exists = email_exists($user_email);
                  if ($email_exists)
                    {
                    $user = get_user_by('email', $user_email);
                    $user_id = $user->ID;
                    $user_name = $user->user_login;
                    }

                  if (!$user_id && $email_exists == false)
                    {
                    $user_name = strtolower($result['first_name'] . '.' . $result['last_name']);
                    while (username_exists($user_name))
                      {
                      $i++;
                      $user_name = strtolower($result['first_name'] . '.' . $result['last_name']) . '.' . $i;
                      }

                    $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                    $userdata = array(
                      'user_login' => $user_name,
                      'user_email' => $user_email,
                      'user_pass' => $random_password,
                      'display_name' => $result["name"],
                      'first_name' => $result['first_name'],
                      'last_name' => $result['last_name']
                    );
                    $user_id = wp_insert_user($userdata);
                    if ($user_id) $user_account = 'user registered.';
                    }
                    else
                    {
                    if ($user_id) $user_account = 'user logged in.';
                    }

                  $expiration = time() + apply_filters('auth_cookie_expiration', 91209600, $user_id, true);
                  $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
                    wp_set_auth_cookie($user_id, true);
                  $response->msg = $user_account;
                  $response->status = true;
                  $response->user_id = $user_id;
                  $response->first_name = $result['first_name'];
                  $response->last_name = $result['last_name'];
                  $response->avatar = $result['picture']['data']['url'];
                  $response->cookie = $cookie;
                  $response->user_login = $user_name;
                  }
                  else
                  {
                  $response->msg = "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.";
                  $response->status = false;
                  }
                }

              wp_send_json($response);
      }

    function google_connect()
      {
            if (!$_POST['access_token'] || !$_POST['email'])
              {
              $response->msg = "Google tocken is not valid";
              $response->status = false;
              wp_send_json($response);
              }
              else
              {
              if (isset($_POST['email']))
                {
                $user_email = $_POST['email'];
                $user_firstname = $_POST['first_name'];
                $user_lastname = $_POST['last_name'];
                $email_exists = email_exists($user_email);
                if ($email_exists)
                  {
                  $user = get_user_by('email', $user_email);
                  $user_id = $user->ID;
                  $user_name = $user->user_login;
                  }

                if (!$user_id && $email_exists == false)
                  {
                  $user_name = $user_email;
                  while (username_exists($user_name))
                    {
                    $i++;
                    $user_name = strtolower($result['first_name'] . '.' . $result['last_name']) . '.' . $i;
                    }

                  $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                  $userdata = array(
                    'user_login' => $user_name,
                    'user_email' => $user_email,
                    'user_pass' => $random_password,
                    'display_name' => $user_lastname,
                    'first_name' => $user_firstname,
                    'last_name' => $user_lastname
                  );
                  $user_id = wp_insert_user($userdata);
                  if ($user_id) $user_account = 'user registered.';
                  }
                  else
                  {
                  if ($user_id) $user_account = 'user logged in.';
                  }

                $expiration = time() + apply_filters('auth_cookie_expiration', 91209600, $user_id, true);
                $cookie = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
                  wp_set_auth_cookie($user_id, true);
                $response->msg = $user_account;
                $response->status = true;
                $response->user_id = $user_id;
                $response->cookie = $cookie;
                $response->last_login = $user_name;
                }
                else
                {
                $response->msg = "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.";
                $response->status = false;
                }
              }

            wp_send_json($response);
      }

      public function logout() {

          wp_logout();

          $data->status = true;
          
          wp_send_json( $data );

      }


}
