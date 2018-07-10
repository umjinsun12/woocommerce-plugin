<?php

/*
=====================================================================================
                엠샵 프리미엄 포인트 시스템 / Copyright 2014-2015 by CodeM(c)
=====================================================================================

  [ 우커머스 버전 지원 안내 ]

   워드프레스 버전 : WordPress 4.3.1 이상

   우커머스 버전 : WooCommerce 2.4 이상


  [ 코드엠 플러그인 라이센스 규정 ]

   (주)코드엠에서 개발된 워드프레스  플러그인을 사용하시는 분들에게는 다음 사항에 대한 동의가 있는 것으로 간주합니다.

   1. 코드엠에서 개발한 워드프레스 우커머스용 엠샵 프리미엄 포인트 시스템 플러그인의 저작권은 (주)코드엠에게 있습니다.
   
   2. 플러그인은 사용권을 구매하는 것이며, 프로그램 저작권에 대한 구매가 아닙니다.

   3. 플러그인을 구입하여 다수의 사이트에 복사하여 사용할 수 없으며, 1개의 라이센스는 1개의 사이트에만 사용할 수 있습니다. 
      이를 위반 시 지적 재산권에 대한 손해 배상 의무를 갖습니다.

   4. 플러그인은 구입 후 1년간 업데이트를 지원합니다.

   5. 플러그인은 워드프레스, 테마, 플러그인과의 호환성에 대한 책임이 없습니다.

   6. 플러그인 설치 후 버전에 관련한 운용 및 관리의 책임은 사이트 당사자에게 있습니다.

   7. 다운로드한 플러그인은 환불되지 않습니다.

=====================================================================================
*/

if ( ! defined( 'ABSPATH' ) ){
	exit;
}

if ( ! class_exists( 'MSPS_Post_Manager' ) ) {

	class MSPS_Post_Manager {
        protected static $_enabled = null;
        protected static $_use_post_point_rule = null;
        protected static $_use_earn_limit = null;
        protected static $_use_earn_condition = null;
        protected static $point_rules = null;
        protected static $point_rule_ids = array();
        protected static $_earn_limits = null;
        protected static $_count_limits = null;
        protected static $_earn_conditions = null;
        protected static $terms = array( 'post-taxonomy' );
        public static function enabled(){
            if( null == self::$_enabled ){
                self::$_enabled = 'yes' == get_option( 'mshop_point_system_enabled', 'no' );
            }

            return self::$_enabled;
        }
        public static function use_post_point_rule(){
            if( null == self::$_use_post_point_rule ){
                self::$_use_post_point_rule = 'yes' == get_option( 'mshop_point_system_use_post_rule', 'no' );
            }

            return self::$_use_post_point_rule;
        }
        public static function use_earn_limit(){
            if( null == self::$_use_earn_limit ){
                self::$_use_earn_limit = 'yes' == get_option( 'mshop_point_system_use_post_earn_limit', 'no' );
            }

            return self::$_use_earn_limit;
        }
        public static function use_earn_condition(){
            if( null == self::$_use_earn_condition ){
                self::$_use_earn_condition = 'yes' == get_option( 'mshop_point_system_post_use_earn_condition', 'no' );
            }

            return self::$_use_earn_condition;
        }

        public static function get_earn_limit( $user_role ){
            if( empty( self::$_earn_limits ) ){
                self::$_earn_limits = get_option( 'mshop_point_system_post_earn_limit', array() );
            }

            $option = array_filter( self::$_earn_limits, function( $earn_limit ) use( $user_role){
                return $user_role == $earn_limit['role'];
            } );

            return is_array( $option ) ? array_shift( $option ) : $option;
        }

        public static function get_count_limit( $user_role ){
            if( empty( self::$_count_limits ) ){
                self::$_count_limits = get_option( 'mshop_point_system_post_count_limit', array() );
            }

            $option = array_filter( self::$_count_limits, function( $count_limit ) use( $user_role){
                return $user_role == $count_limit['role'];
            } );

            return is_array( $option ) ? array_shift( $option ) : $option;
        }

        public static function get_earn_condition( $user_role ){
            if( empty( self::$_earn_conditions ) ){
                self::$_earn_conditions = get_option( 'mshop_point_system_post_earn_condition', array() );
            }

            $option = array_filter( self::$_earn_conditions, function( $earn_condition ) use( $user_role){
                return $user_role == $earn_condition['role'];
            } );

            return is_array( $option ) ? array_shift( $option ) : $option;
        }
        public static function get_point_rules( $reload = false ){
            if( empty( self::$point_rules ) || $reload ){
                self::$point_rules = array();
                self::$point_rule_ids = array();

                // Query Point Rules Data
                $args = array(
                    'post_type'  => 'point_rule',
                    'meta_key'   => '_order',
                    'orderby'    => 'meta_value_num',
                    'order'      => 'ASC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'point_rule_type',
                            'field'    => 'slug',
                            'terms'    => self::$terms
                        ),
                    ),
                );

                $query = new WP_Query( $args );

                // Generate Point Rules
                foreach( $query->posts as $post ){
                    $point_rule = mshop_get_point_rule( $post );

                    if( !is_wp_error( $point_rule ) ){
                        self::$point_rule_ids[] = $point_rule->id;
                        self::$point_rules[] = $point_rule;
                    }
                }
            }

            return self::$point_rules;
        }
        protected static function update_point_rule_meta( $point_rule_id, $data ){
            $point_rule_type = null;
            if ( isset( $data['type'] ) ) {
                $point_rule_type = sanitize_text_field( $data['type'] );
                wp_set_object_terms( $point_rule_id, $point_rule_type, 'point_rule_type' );
            } else {
                $_point_rule_type = get_the_terms( $point_rule_id, 'point_rule_type' );
                if ( is_array( $_point_rule_type ) ) {
                    $_point_rule_type = current( $_point_rule_type );
                    $point_rule_type  = $_point_rule_type->slug;
                }
            }

            update_post_meta($point_rule_id, '_order'      , $data['order']);
//            update_post_meta($point_rule_id, '_uuid'       , $data['uuid']);
            update_post_meta($point_rule_id, '_type'       , $data['type']);
            update_post_meta($point_rule_id, '_taxonomy'   , $data['taxonomy']);
            update_post_meta($point_rule_id, '_use_valid_term' , $data['use_valid_term']);
            update_post_meta($point_rule_id, '_valid_term'     , $data['valid_term']);

            if( !empty( $data['roles'] ) ){
                update_post_meta($point_rule_id, '_roles' , $data['roles']);
            }

            if( !empty( $data['object'] ) ) {
                update_post_meta($point_rule_id, '_object', $data['object']);
            }
        }
        public static function update_point_rules( $point_rules ){
            self::get_point_rules();
            $new_point_rule_ids = array();

            // Update point rule's info
            foreach( $point_rules as $index => $rule ){
                if( empty( $rule['id'] ) ) {
                    $args = array(
                        'post_title'  => $rule['type'] . ' [' . ( !empty( $rule['uuid'] ) ? $rule['uuid'] : '' ) . ']',
                        'post_type'   => 'point_rule',
                        'post_status' => 'publish'
                    );

                    $point_rule_id = wp_insert_post($args);
                }else {
                    $point_rule_id = wp_update_post( array(
                        'ID'         => $rule['id'],
                        'post_title' => $rule['type'] . ' [' . ( !empty( $rule['uuid'] ) ? $rule['uuid'] : '' ) . ']'
                    ) );
                }

                // Reset rule's order
                $rule['order'] = $index;

                if( !is_wp_error( $point_rule_id ) ) {
                    $new_point_rule_ids[] = $point_rule_id;
                    self::update_point_rule_meta( $point_rule_id, $rule );
                }
            }

            // Check deleted point rules
            $deleted_point_rule_ids = array_diff( self::$point_rule_ids, $new_point_rule_ids );

            if( !empty( $deleted_point_rule_ids ) ){
                // send to trash
                foreach( $deleted_point_rule_ids as $point_rule_id ){
                    wp_trash_post( $point_rule_id );
                }
            }

            self::get_point_rules( true );
        }
        public static function is_valid_user( $user_role ){
            $roles = get_option( 'mshop_point_system_role_filter', array() );

            $result = array_filter( $roles, function( $role ) use( $user_role){
                return 'yes' == $role['enabled'] && $user_role === $role['role'];
            });

            return !empty( $result );
        }

        public static function is_applicable( $post_id ){
            self::get_point_rules();

            foreach( self::$point_rules as $rule ){
                if( $rule->is_valid() && $rule->is_match( $post_id ) ){
                    return true;
                }
            }

            return false;
        }

        public static function get_expected_comment_point( $post_id, $user_role ){
            self::get_point_rules();

            foreach( self::$point_rules as $rule ){
                if( $rule->is_valid() && $rule->is_match( $post_id ) ){
                    return $rule->get_comment_point( $user_role );
                }
            }

            return 0;
        }

        public static function get_expected_post_point( $post_id, $user_role ){
            self::get_point_rules();

            foreach( self::$point_rules as $rule ){
                if( $rule->is_valid() && $rule->is_match( $post_id ) ){
                    return $rule->get_post_point( $user_role );
                }
            }

            return 0;
        }

        public static function comment_form( $post_id ){
            if( is_user_logged_in() && MSPS_Manager::use_print_notice( 'comment' ) ) {
                $user_role = mshop_point_get_user_role();
                if (self::is_valid_user($user_role)) {
                    self::get_point_rules();

                    foreach (self::$point_rules as $rule) {
                        if ( $rule->is_valid() && $rule->is_match($post_id) && self::can_earn_point( get_current_user_id(), $post_id, 'comment' ) ) {

                            $point = $rule->get_comment_point($user_role);

                            if (!empty($point) && intval($point) > 0) {

                                $message = apply_filters( 'mshop_point_translate_string', get_option('mshop_point_system_notice_at_comment', __( '댓글을 작성하시면 {point} 포인트가 적립됩니다.', 'mshop-point-ex' )), 'comment_message' );
                                $message = str_replace('{point}', number_format_i18n($point), $message);
                                wp_enqueue_style('mshop-point', MSPS()->plugin_url() . '/assets/css/mshop-point.css');
                                mshop_point_print_post_notice($message);
                                return;
                            }
                        }
                    }
                }
            }
        }

        public static function get_earn_point( $user_id, $from, $to ){
            return self::get_comment_earn_point( $user_id, $from, $to ) + self::get_post_earn_point( $user_id, $from, $to );
        }
        public static function get_post_earn_point( $user_id, $from, $to ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT sum(meta1.meta_value)
                FROM {$wpdb->posts} posts
                INNER JOIN {$wpdb->postmeta} AS meta1 ON posts.ID = meta1.post_id AND meta1.meta_key='_mshop_point_post_amount'
                INNER JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id AND meta2.meta_key='_mshop_point_post_processed' AND meta2.meta_value = 'yes'
                WHERE
                    posts.post_author = '%s'
                    AND posts.post_date BETWEEN '%s' AND '%s'
            ", $user_id, $from, $to);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }
        public static function get_comment_earn_point( $user_id, $from, $to ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT sum(meta1.meta_value)
                FROM {$wpdb->comments} comments
                INNER JOIN {$wpdb->commentmeta} AS meta1 ON comments.comment_ID = meta1.comment_id AND meta1.meta_key='_mshop_point_post_amount'
                INNER JOIN {$wpdb->commentmeta} AS meta2 ON comments.comment_ID = meta2.comment_id AND meta2.meta_key='_mshop_point_post_processed' AND meta2.meta_value = 'yes'
                WHERE
                    comments.user_id = '%s'
                    AND comments.comment_date BETWEEN '%s' AND '%s'
            ", $user_id, $from, $to);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }
        public static function get_post_count( $user_id ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT COUNT(ID)
                FROM {$wpdb->posts}
                WHERE
                    post_author = '%s'
                    AND post_status = 'publish'
            ", $user_id);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }
        public static function get_comment_count( $user_id ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT COUNT(comment_ID)
                FROM {$wpdb->comments}
                WHERE
                    user_id = '%s'
                    AND comment_approved = 1
            ", $user_id);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }
        public static function get_days_after_register( $user_id ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT DATEDIFF( NOW(), user_registered )
                FROM {$wpdb->users}
                WHERE
                    id = '%s'
            ", $user_id);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }

        public static function can_earn_point( $user_id, $post_id, $type ){
            $user_role = mshop_point_get_user_role( $user_id );

            if( MSPS_Post_Manager::use_earn_limit() ){

                $rule = MSPS_Post_Manager::get_earn_limit( $user_role );

                if( !empty( $rule['day'] ) && $rule['day'] > 0 ){
                    $amount = self::get_today_earn_point( $user_id );

                    if( $amount >= $rule['day'] ){
                        return false;
                    }
                }

                if( !empty( $rule['week'] ) && $rule['week'] > 0 ){
                    $amount = self::get_weekly_earn_point( $user_id );

                    if( $amount >= $rule['week'] ){
                        return false;
                    }
                }

                if( !empty( $rule['month'] ) && $rule['month'] > 0 ){
                    $amount = self::get_monthly_earn_point( $user_id );

                    if( $amount >= $rule['month'] ){
                        return false;
                    }
                }

                $rule = MSPS_Post_Manager::get_count_limit( $user_role );

                if( 'post' === $type ){
                    if( !empty( $rule['post'] ) && $rule['post'] > 0 ){
                        $count = self::get_post_today_earn_count( $user_id );

                        if( $count >= $rule['post'] ){
                            return false;
                        }
                    }

                }else{
                    if( !empty( $rule['comment'] ) && $rule['comment'] > 0 ){
                        $count = self::get_comment_today_earn_count( $user_id, $post_id );

                        if( $count >= $rule['comment'] ){
                            return false;
                        }
                    }
                }
            }

            if( MSPS_Post_Manager::use_earn_condition() ){
                $rule = MSPS_Post_Manager::get_earn_condition( $user_role );

                if( !empty( $rule['post'] ) && $rule['post'] > 0 ){
                    $count = self::get_post_count( $user_id );

                    if( $count < $rule['post'] ){
                        return false;
                    }
                }
                if( !empty( $rule['comment'] ) && $rule['comment'] > 0 ){
                    $count = self::get_comment_count( $user_id );

                    if( $count < $rule['comment'] ){
                        return false;
                    }
                }
                if( !empty( $rule['register'] ) && $rule['register'] > 0 ){
                    $days = self::get_days_after_register( $user_id );

                    if( $days < $rule['register'] ){
                        return false;
                    }
                }
            }

            return true;
        }
        static function get_post_today_earn_count( $user_id ){
            return self::get_post_earn_count( $user_id, date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"));
        }
        protected static function get_comment_today_earn_count( $user_id, $post_id ){
            return self::get_comment_earn_count( $user_id, $post_id, date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"));
        }
        protected static function get_today_earn_point( $user_id ){
            return MSPS_Post_Manager::get_earn_point( $user_id, date("Y-m-d 00:00:00"), date("Y-m-d 23:59:59"));
        }
        protected static function get_weekly_earn_point( $user_id ){
            $day_of_week = date('N');
            $from_date = new DateTime( date("Y-m-d") . " -" . ( $day_of_week - 1 ) . " day");

            return MSPS_Post_Manager::get_earn_point( $user_id, $from_date->format('Y-m-d 00:00:00'), date("Y-m-d 23:59:59"));
        }
        protected static function get_monthly_earn_point( $user_id ){
            return MSPS_Post_Manager::get_earn_point( $user_id, date("Y-m-01 00:00:00"), date("Y-m-d 23:59:59"));
        }
        public static function get_comment_earn_count( $user_id, $post_id, $from, $to ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT count(comments.comment_ID)
                FROM {$wpdb->comments} comments
                INNER JOIN {$wpdb->commentmeta} AS meta1 ON comments.comment_ID = meta1.comment_id AND meta1.meta_key='_mshop_point_post_amount'
                INNER JOIN {$wpdb->commentmeta} AS meta2 ON comments.comment_ID = meta2.comment_id AND meta2.meta_key='_mshop_point_post_processed' AND meta2.meta_value = 'yes'
                WHERE
                    comments.user_id = '%s'
                    AND comments.comment_post_ID = '%s'
                    AND comments.comment_date BETWEEN '%s' AND '%s'
            ", $user_id, $post_id, $from, $to);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }
        public static function get_post_earn_count( $user_id, $from, $to ){
            global $wpdb;

            $sql = $wpdb->prepare("
                SELECT count(comments.comment_ID)
                FROM {$wpdb->comments} comments
                INNER JOIN {$wpdb->commentmeta} AS meta1 ON comments.comment_ID = meta1.comment_id AND meta1.meta_key='_mshop_point_post_amount'
                INNER JOIN {$wpdb->commentmeta} AS meta2 ON comments.comment_ID = meta2.comment_id AND meta2.meta_key='_mshop_point_post_processed' AND meta2.meta_value = 'yes'
                WHERE
                    comments.user_id = '%s'
                    AND comments.comment_date BETWEEN '%s' AND '%s'
            ", $user_id, $from, $to);

            $result = $wpdb->get_var( $sql );

            return is_null( $result ) ? 0 : $result;
        }
	}

}