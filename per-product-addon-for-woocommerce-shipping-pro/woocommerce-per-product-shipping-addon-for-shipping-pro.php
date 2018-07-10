<?php
/*
	Plugin Name: WooCommerce Per Product Shipping AddOn For Shipping Pro  
	Plugin URI: https://www.xadapter.com/product/per-product-shipping-plugin-for-woocommerce/
	Description: AddOn Plugin for WooCommerce Shipping Pro. Designed to configure shipping costs at product level.
	Version: 1.0.4
	Author: XAdapter
	Author URI: www.xadapter.com/
	Copyright: 2014-2015 WooForce.
	WC tested up to: 3.4
	*/
class wf_per_product_shipping_addon_setup {
	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'wf_woocommerce_init' ));
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );			
	}
	
	public function wf_woocommerce_init(){		
		// Display Fields
		add_action( 'woocommerce_product_options_shipping', array( $this, 'wf_add_product_field_shipping_unit' ));
 
		// Save Fields
		add_action( 'woocommerce_process_product_meta', array( $this, 'wf_save_product_field_shipping_unit' ));		
		
		add_filter( 'wf_shipping_pro_item_quantity', array( $this, 'wf_shipping_pro_item_quantity' ),10, 2 );			
	}
	
	public function wf_shipping_pro_item_quantity( $quantity, $productid ) {

		$product 	= wc_get_product($productid);
		if( $product instanceof WC_Product_Variation ) {
			$productid = $product->get_parent_id();
		}
		$unit 		= get_post_meta( $productid, '_wf_shipping_unit', true );
		if(!empty($unit) && is_numeric($unit) && !empty($quantity) && is_numeric($quantity)){
			return $quantity * $unit;
		}
		return $quantity;
	}
	
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="https://wordpress.org/support/plugin/per-product-addon-for-woocommerce-shipping-pro" target="_blank">' . __( 'Support', 'wf_per_product_shipping_addon' ) . '</a>'
		);
		return array_merge( $plugin_links, $links );
	}

	public function wf_add_product_field_shipping_unit(){ 
		global $woocommerce, $post;

		echo '<p>';


		woocommerce_wp_text_input(
			array(
				'id' => '_miztalk_shipping_common',
				'label' => __( '일반 배송비', 'wf_per_product_shipping_addon' ),
				'placeholder' => '0',
				'description' => '제주도를 제외한 일반 도서산간지역에 추가될 배송비 금액을 기재합니다.',
				'desc_tip' => true
			)
		);

		woocommerce_wp_text_input(
			array(
				'id' => '_miztalk_shipping_min_price',
				'label' => __( '무료배송 최소비용', 'wf_per_product_shipping_addon' ),
				'placeholder' => '0',
				'description' => '무료 배송을 위한 최소 결제 상품 금액을 기재합니다.',
				'desc_tip' => true
			)
		);
		
		echo '</p>';
	}
	
	public function wf_save_product_field_shipping_unit( $post_id ){
	
		// Text Field
		$shipping_unit = $_POST['_wf_shipping_unit'];
		$miztalk_shipping_common = $_POST['_miztalk_shipping_common'];
		$miztalk_shipping_surcharge_area_island = $_POST['_miztalk_shipping_surcharge_area_island'];
		$miztalk_shipping_min_price = $_POST['_miztalk_shipping_min_price'];

		if( !empty( $shipping_unit ) )
			update_post_meta( $post_id, '_wf_shipping_unit', esc_attr( $shipping_unit ) );
		update_post_meta( $post_id, '_miztalk_shipping_common', esc_attr( $miztalk_shipping_common ) );
		update_post_meta( $post_id, '_miztalk_shipping_surcharge_area_island', esc_attr( $miztalk_shipping_surcharge_area_island ) );
		update_post_meta( $post_id, '_miztalk_shipping_min_price', esc_attr( $miztalk_shipping_min_price ) );
	}	
		
	private function wf_get_settings_url()
	{
		return version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
	}
}	
new wf_per_product_shipping_addon_setup();
