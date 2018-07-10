<?php
class MSPS_Comment {
    public static function set_earn_point( $comment_id, $amount = 0 ){
        if( $amount > 0 ){
            update_comment_meta( $comment_id, '_mshop_point_post_amount', $amount );
        }else{
            delete_comment_meta( $comment_id, '_mshop_point_post_amount' );
        }
    }

    public static function get_earn_point( $comment_id ){
        $earn_point = get_comment_meta( $comment_id, '_mshop_point_post_amount', true );
        return is_null( $earn_point ) ? 0 : $earn_point;
    }

    public static function is_earn_processed( $comment_id ){
        return 'yes' == get_comment_meta( $comment_id, '_mshop_point_post_processed', true );
    }
    public static function set_earn_processed( $comment_id, $flag ){
        update_comment_meta( $comment_id, '_mshop_point_post_processed', $flag ? 'yes' : 'no' );
    }
    public static function earn_point( $comment ){
        if( !self::is_earn_processed( $comment->comment_ID ) ){
            $user_id = $comment->user_id;
            $post_id = $comment->comment_post_ID;

            if( !empty( $user_id ) && intval( $user_id ) > 0 ) {
                $user = get_user_by('id', $user_id);

                if ($user instanceof WP_User) {
                    $user_role = mshop_point_get_user_role( $user_id );
                    if (MSPS_Post_Manager::is_valid_user($user_role)) {

                        $point = MSPS_Post_Manager::get_expected_comment_point($post_id, $user_role);

                        if (!empty($point) && intval($point) > 0) {
                            $mshop_user   = new MSPS_User($user);
                            $prev_point   = $mshop_user->get_point();
                            $remain_point = $mshop_user->earn_point($point);

                            self::set_earn_point( $comment->comment_ID, $point );
                            self::set_earn_processed( $comment->comment_ID, true );

                            $post = get_post($post_id);
                            $message = sprintf(__('<a href="%1$s">%2$s</a>에 작성한 <a href="%3$s">코멘트</a>가 승인되어 %4$s포인트가 적립되었습니다.<br>보유포인트가 %5$s포인트에서 %6$s포인트로 변경되었습니다.', 'mshop-point-ex'), get_permalink($post_id), $post->post_title, get_comment_link( $comment ), number_format(intval($point)), number_format( $prev_point ), number_format( $remain_point ));
                            $message .= sprintf(__('<p class="meta"><abbr>%s</abbr></p>'), $comment->comment_content);
                            do_action( 'mshop_add_point_history', $user_id, $point, $message, false );

                            $message = sprintf(__('<a href="%1$s">%2$s</a>에 작성한 <a href="%3$s">코멘트</a>가 승인되어 %4$s포인트가 적립되었습니다.<br>보유포인트가 %5$s포인트에서 %6$s포인트로 변경되었습니다.', 'mshop-point-ex'), get_permalink($post_id), $post->post_title, get_edit_comment_link( $comment ), number_format(intval($point)), number_format( $prev_point ), number_format( $remain_point ));
                            $message .= sprintf(__('<p class="meta"><abbr>%s</abbr></p>'), $comment->comment_content);
                            do_action( 'mshop_add_point_history', $user_id, $point, $message, true );

                        }
                    }

                }
            }
        }
    }
    public static function deduct_point( $comment ){
        if( self::is_earn_processed( $comment->comment_ID ) ){
            $user_id = $comment->user_id;
            $post_id = $comment->comment_post_ID;

            if( !empty( $user_id ) && intval( $user_id ) > 0 ) {
                $user = get_user_by('id', $user_id);

                if ($user instanceof WP_User) {
                    $user_role = mshop_point_get_user_role($user_id);

                    $point = self::get_earn_point($comment->comment_ID);

                    $mshop_user = new MSPS_User($user);
                    $prev_point   = $mshop_user->get_point();
                    $remain_point = $mshop_user->deduct_point($point);

                    self::set_earn_processed($comment->comment_ID, false);

                    $post = get_post($post_id);
                    $message = sprintf(__('<a href="%1$s">%2$s</a>에 작성한 <a href="%3$s">코멘트</a>가 승인 취소되어 %4$s포인트가 적립되었습니다.<br>보유포인트가 %5$s포인트에서 %6$s포인트로 변경되었습니다.', 'mshop-point-ex'), get_permalink($post_id), $post->post_title, get_comment_link( $comment ), number_format(intval($point)), number_format( $prev_point ), number_format( $remain_point ));
                    $message .= sprintf(__('<p class="meta"><abbr>%s</abbr></p>'), $comment->comment_content);
                    do_action( 'mshop_add_point_history', $user_id, -1 * $point, $message, false );

                    $message = sprintf(__('<a href="%1$s">%2$s</a>에 작성한 <a href="%3$s">코멘트</a>가 승인 취소되어 %4$s포인트가 적립되었습니다.<br>보유포인트가 %5$s포인트에서 %6$s포인트로 변경되었습니다.', 'mshop-point-ex'), get_permalink($post_id), $post->post_title, get_edit_comment_link( $comment ), number_format(intval($point)), number_format( $prev_point ), number_format( $remain_point ));
                    $message .= sprintf(__('<p class="meta"><abbr>%s</abbr></p>'), $comment->comment_content);
                    do_action( 'mshop_add_point_history', $user_id, -1 * $point, $message, true );
                }
            }
        }
    }
    public static function wp_insert_comment( $id, $comment ){
        if( 'approved' == wp_get_comment_status( $comment ) ){
            $user_role = mshop_point_get_user_role( $comment->user_id );

            if( !empty( $user_role ) &&
                MSPS_Post_Manager::is_valid_user( $user_role ) &&
                MSPS_Post_Manager::is_applicable( $comment->comment_post_ID ) &&
                MSPS_Post_Manager::can_earn_point( $comment->user_id, $comment->comment_post_ID, 'comment' ) ){
                self::earn_point( $comment );
            }
        }
    }
    public static function wp_set_comment_status( $id, $comment_status){
        $comment = get_comment( $id );

        switch ( $comment_status ) {
            case 'approve':
            case '1':
                $user_role = mshop_point_get_user_role( $comment->user_id );

                if( !empty( $user_role ) &&
                    MSPS_Post_Manager::is_valid_user( $user_role ) &&
                    MSPS_Post_Manager::is_applicable( $comment->comment_post_ID ) &&
                    MSPS_Post_Manager::can_earn_point( $comment->user_id, $comment->comment_post_ID, 'comment' ) ){
                    self::earn_point( $comment );
                }
                break;
            case 'unapprove':
            case 'hold':
            case 'spam':
                self::deduct_point( $comment );
                break;
            default:
                break;
        }

    }
}