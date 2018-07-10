<?php
class MSPS_Post {
    public static function set_earn_point( $post_id, $amount = 0 ){
        if( $amount > 0 ){
            update_post_meta( $post_id, '_mshop_point_post_amount', $amount );
        }else{
            delete_post_meta( $post_id, '_mshop_point_post_amount' );
        }
    }

    public static function get_earn_point( $post_id ){
        $earn_point = get_post_meta( $post_id, '_mshop_point_post_amount', true );
        return is_null( $earn_point ) ? 0 : $earn_point;
    }

    public static function is_earn_processed( $post_id ){
        return 'yes' == get_post_meta( $post_id, '_mshop_point_post_processed', true );
    }
    public static function set_earn_processed( $post_id, $flag ){
        update_post_meta( $post_id, '_mshop_point_post_processed', $flag ? 'yes' : 'no' );
    }
    public static function earn_point( $post ){
        if( !self::is_earn_processed( $post->ID ) ){
            $user_id = $post->post_author;
            $post_id = $post->ID;

            if( !empty( $user_id ) && intval( $user_id ) > 0 ) {
                $user = get_user_by('id', $user_id);

                if ($user instanceof WP_User) {
                    $user_role = mshop_point_get_user_role( $user_id );
                    if (MSPS_Post_Manager::is_valid_user($user_role)) {

                        $point = MSPS_Post_Manager::get_expected_post_point( $post_id, $user_role );

                        if (!empty($point) && intval($point) > 0) {
                            $mshop_user = new MSPS_User($user);
                            $mshop_user->earn_point($point);

                            self::set_earn_point( $post_id, $point );
                            self::set_earn_processed( $post_id, true );

                            $post = get_post($post_id);
                            $post_link = get_permalink($post_id);
                            $message = sprintf(__('게시글 <a href="%s">%s</a>가 승인되어 포인트(%s)가 적립되었습니다.', 'mshop-point-ex'), $post_link, $post->post_title, number_format(intval($point)));
                            do_action('mshop_add_point_history', $user_id, $point, $message);
                        }
                    }

                }
            }
        }
    }
    public static function deduct_point( $post ){
        if( self::is_earn_processed( $post->ID ) ){
            $user_id = $post->post_author;
            $post_id = $post->ID;

            if( !empty( $user_id ) && intval( $user_id ) > 0 ) {
                $user = get_user_by('id', $user_id);

                if ($user instanceof WP_User) {
                    $point = self::get_earn_point( $post_id );

                    $mshop_user = new MSPS_User($user);
                    $mshop_user->deduct_point($point);

                    self::set_earn_processed($post_id, false);

                    $post = get_post($post_id);
                    $post_link = get_permalink($post_id);
                    $message = sprintf(__('게시글 <a href="%s">%s</a>의 승인이 취소되어 포인트(%s)가 차감되었습니다.', 'mshop-point-ex'), $post_link, $post->post_title, number_format(intval($point)));
                    do_action('mshop_add_point_history', $user_id, -1 * $point, $message);
                }
            }
        }
    }

    // do_action( 'wp_insert_post', $post_ID, $post, $update );
    // do_action( 'transition_post_status', $new_status, $old_status, $post );
    public static function wp_insert_post( $post_ID, $post, $update ){

//        if( 'approved' == wp_get_post_status( $comment ) ){
//            $user_role = mshop_point_get_user_role( $comment->user_id );
//
//            if( !empty( $user_role ) && MSPS_Post_Manager::is_valid_user( $user_role ) && self::can_earn_point( $comment->user_id, $comment->comment_post_ID ) ){
//                self::earn_point( $comment );
//            }
//        }
    }
    public static function transition_post_status( $new_status, $old_status, $post ){
        switch ( $new_status ) {
            case 'publish':
                $user_role = mshop_point_get_user_role( $post->post_author );

                if( !empty( $user_role ) &&
                    MSPS_Post_Manager::is_valid_user( $user_role ) &&
                    MSPS_Post_Manager::is_applicable( $post->ID ) &&
                    MSPS_Post_Manager::can_earn_point( $post->post_author, $post->ID, 'post' ) ){
                    self::earn_point( $post );
                }
                break;
            default:
                self::deduct_point( $post );
                break;
        }

    }
}