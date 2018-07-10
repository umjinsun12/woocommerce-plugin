<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MSPS_Wpml {
    static $package = array(
        'kind' => 'MShop Point',
        'name' => 'mshop-point',
        'title' => 'MShop Point'
        );
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_string_package' ) );
		add_filter( 'mshop_point_translate_string', array( __CLASS__, 'translate_string' ), 10, 2 );
	}

    static function translate_string( $value, $name ){
        return apply_filters( 'wpml_translate_string', $value, $name, self::$package );
    }
	public static function register_string_package() {
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_product_detail', '상품 구매시 {point} 포인트가 적립됩니다.'),
            'point_message',
            self::$package,
            '포인트 메시지 (상품상세 페이지)',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_cart', '주문이 완료되면 {point} 포인트가 적립됩니다.'),
            'point_message_cart',
            self::$package,
            '포인트 메시지 (장바구니 페이지)',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_checkout', '주문이 완료되면 {point} 포인트가 적립됩니다.'),
            'point_message_checkout',
            self::$package,
            '포인트 메시지 (체크아웃 페이지)',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_guide_notice_info_at_product_detail', '포인트 적립에 대한 추가 안내문구를 입력합니다.'),
            'guide_notice_info_at_product_detail',
            self::$package,
            '포인트 메시지',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_product_detail_price_qty', '{desc} 상품 {amount} 이상 또는 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.'),
            'point_guide_message_price_qty',
            self::$package,
            '포인트 적립안내 메시지 (금액 + 수량)',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_product_detail_price', '{desc} 상품 {amount} 이상 구매시 {point} 포인트가 적립됩니다.' ),
            'point_guide_message_price',
            self::$package,
            '포인트 적립안내 메시지 (금액)',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_product_detail_qty', '{desc} 상품 {qty}개 이상 구매시 {point} 포인트가 적립됩니다.' ),
            'point_guide_message_qty',
            self::$package,
            '포인트 적립안내 메시지 (수량)',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_guide_notice_title_at_product_detail', '[ 포인트 적립안내 ]'),
            'point_guide_message_title',
            self::$package,
            '포인트 적립안내',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_shortage_point_message', '보유하신 포인트($point)가 부족해서 포인트로 결제하실 수 없습니다.'),
            'point_shortage_message',
            self::$package,
            '포인트 부족시 안내메시지',
            'LINE'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_recommender_point_register_description', '회원가입 안내문구를 입력하세요.'),
            'point_register_message',
            self::$package,
            '회원가입 시 안내문구',
            'AREA'
        );
        do_action(
            'wpml_register_string',
            get_option('mshop_point_system_notice_at_comment', __( '댓글을 작성하시면 {point} 포인트가 적립됩니다.', 'mshop-point-ex' )),
            'comment_message',
            self::$package,
            '코멘트작성 적립 메시지',
            'AREA'
        );
	}
}

MSPS_Wpml::init();
