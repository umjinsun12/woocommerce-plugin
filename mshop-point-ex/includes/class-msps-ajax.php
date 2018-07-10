<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MSPS_Ajax {
	static $slug;

	public static function init() {
		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
			@ini_set( 'display_errors', 0 );
		}
		$GLOBALS['wpdb']->hide_errors();

		self::$slug = MSPS()->slug();

		self::add_ajax_events();
	}
	public static function add_ajax_events() {

		$ajax_events = array (
			'target_search'           => false,
			'myaccount_point_logs'    => false,
			'mshop_point_search_user' => false,
			'get_mypoint' => false,
			'get_mypoint_logs' => false
		);

		if ( is_admin() ) {
			$ajax_events = array_merge( $ajax_events, array (
				'admin_point_logs'        => false,
				'update_role_settings'    => false,
				'update_policy_settings'  => false,
				'adjust_mshop_user_point' => false,
				'get_user_point_list'     => false,
				'batch_adjust_point'      => false,
				'export_logs'             => false
			) );
		}

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . self::$slug . '-' . $ajax_event, array ( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . self::$slug . '_' . $ajax_event, array ( __CLASS__, $ajax_event ) );
			}
		}
	}
	static function target_search_product_posts_title_like( $where, &$wp_query ) {
		global $wpdb;
		if ( $posts_title = $wp_query->get( 'posts_title' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE "%' . $posts_title . '%"';
		}

		return $where;
	}
	static function target_search_product() {
		$keyword = ! empty( $_REQUEST['args'] ) ? $_REQUEST['args'] : '';

		add_filter( 'posts_where', array ( __CLASS__, 'target_search_product_posts_title_like' ), 10, 2 );
		$args = array (
			'post_type'      => 'product',
			'posts_title'    => $keyword,
			'post_status'    => 'publish',
			'posts_per_page' => - 1
		);

		$query = new WP_Query( $args );

		remove_filter( 'posts_where', array ( __CLASS__, 'target_search_product_posts_title_like' ) );

		$results = array ();

		foreach ( $query->posts as $post ) {
			$results[] = array (
				"name"  => $post->post_title,
				"value" => $post->ID
			);
		}
		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );

		die();
	}
	static function make_taxonomy_tree( $taxonomy, $args, $depth = 0, $parent = 0, $paths = array () ) {
		$results = array ();

		$args['parent'] = $parent;
		$terms          = get_terms( $taxonomy, $args );

		foreach ( $terms as $term ) {
			$current_paths = array_merge( $paths, array ( $term->name ) );
			$results[]     = array (
				"name"  => '<span class="tree-indicator-desc">' . implode( '-', $current_paths ) . '</span><span class="tree-indicator" style="margin-left: ' . ( $depth * 8 ) . 'px;">' . $term->name . '</span>',
				"value" => $term->term_id
			);

			$results = array_merge( $results, self::make_taxonomy_tree( $taxonomy, $args, $depth + 1, $term->term_id, $current_paths ) );
		}

		return $results;
	}
	static function target_search_category( $depth = 0, $parent = 0 ) {
		$args = array ();

		if ( ! empty( $_REQUEST['args'] ) ) {
			$args['name__like'] = $_REQUEST['args'];
		}

		$results = self::make_taxonomy_tree( 'product_cat', $args );

		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );
		die();
	}
	static function target_search_taxonomy() {
		$args = array ();

		if ( ! empty( $_REQUEST['args'] ) ) {
			$args['name__like'] = $_REQUEST['args'];
		}

		$results = self::make_taxonomy_tree( $_REQUEST['taxonomy'], $args );

		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );
		die();
	}
	static function target_search_shipping_classes() {
		$results          = array ();
		$shipping_classes = WC_Shipping::instance()->get_shipping_classes();

		foreach ( $shipping_classes as $shipping_classe ) {
			$results[] = array (
				"name"  => $shipping_classe->name,
				"value" => $shipping_classe->term_id
			);
		}

		$respose = array (
			'success' => true,
			'results' => $results
		);

		echo json_encode( $respose );
		die();
	}

	public static function target_search() {
		if ( ! empty( $_REQUEST['type'] ) ) {
			$type = $_REQUEST['type'];

			switch ( $type ) {
				case 'product' :
				case 'product-category' :
					self::target_search_product();
					break;
				case 'category' :
					self::target_search_category();
					break;
				case 'shipping-class' :
					self::target_search_shipping_classes();
					break;
				case 'taxonomy' :
					self::target_search_taxonomy();
					break;
				default:
					die();
					break;
			}
		}
	}
	
	public static function get_mypoint(){
			 	$data->user_id = get_current_user_id();
		if(is_user_logged_in()){
			 	$msps_user  = new MSPS_User( $data->user_id );
			    $data->free_point = $msps_user->get_point( array ( 'free_point' ) );
		 }
		wp_send_json( $data );
		die();
	}

	public static function get_mypoint_logs(){
		global $wpdb;
		$results = array ();
		$user_id = get_current_user_id();
		$page = 1;
		$page_size = 50;

		$tbname = $wpdb->prefix . 'mshop_point_history';

		$limit = 'LIMIT ' . $page_size;

		if ( ! empty( $page ) && $page > 1 ) {
			$limit = 'LIMIT ' . ( ( $page - 1 ) * $page_size ) . ', ' . $page_size;
		}

		$count_query = "
				SELECT COUNT(a.id)
				FROM {$tbname} a
				WHERE a.userid = {$user_id} AND (a.is_admin IS NULL OR a.is_admin = FALSE)";

		$total_count = $wpdb->get_var( $count_query );

		$query = "
				SELECT a.id, a.userid, a.date, a.point, a.message
				FROM {$tbname} a
				WHERE a.userid = {$user_id} AND (a.is_admin IS NULL OR a.is_admin = FALSE)
                ORDER BY a.id DESC
				{$limit}";

		$logs = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $logs as $log ) {
			$results[] = array (
				'no'     => $log['id'],
				'date'   => $log['date'],
				'amount' => number_format( $log['point'] ),
				'desc'   => $log['message'],
			);
		}

		$data = array(
			'total_count' => $total_count,
			'results'     => $results
		);

		return wp_send_json( $data );
	}
	
	public static function myaccount_point_logs( $page, $page_size = 10 ) {
		global $wpdb;
		$results = array ();
		$user_id = get_current_user_id();

		$tbname = $wpdb->prefix . 'mshop_point_history';

		$limit = 'LIMIT ' . $page_size;

		if ( ! empty( $page ) && $page > 1 ) {
			$limit = 'LIMIT ' . ( ( $page - 1 ) * $page_size ) . ', ' . $page_size;
		}

		$count_query = "
				SELECT COUNT(a.id)
				FROM {$tbname} a
				WHERE a.userid = {$user_id} AND (a.is_admin IS NULL OR a.is_admin = FALSE)";

		$total_count = $wpdb->get_var( $count_query );

		$query = "
				SELECT a.id, a.userid, a.date, a.point, a.message
				FROM {$tbname} a
				WHERE a.userid = {$user_id} AND (a.is_admin IS NULL OR a.is_admin = FALSE)
                ORDER BY a.id DESC
				{$limit}";

		$logs = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $logs as $log ) {
			$results[] = array (
				'no'     => $log['id'],
				'date'   => $log['date'],
				'amount' => number_format( $log['point'] ),
				'desc'   => $log['message'],
			);
		}

		return array (
			'total_count' => $total_count,
			'results'     => $results
		);
	}
	public static function mshop_point_search_user() {
		global $wpdb;

		$results = array ();

		add_filter( 'user_search_columns', function ( $search_columns ) {
			$search_columns[] = 'display_name';

			return $search_columns;
		} );

//		$keyword = isset( $_REQUEST['args'] ) ? esc_attr( $_REQUEST['args'] ) : '';
//
//		$sql = "SELECT user.ID
//				FROM {$wpdb->users} user
//				LEFT JOIN {$wpdb->usermeta} meta1 ON meta1.meta_key = 'billing_first_name' AND meta1.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta2 ON meta2.meta_key = 'billing_last_name' AND meta2.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta3 ON meta3.meta_key = 'shipping_first_name' AND meta3.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta4 ON meta4.meta_key = 'shipping_last_name' AND meta4.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta5 ON meta5.meta_key = 'first_name' AND meta5.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta6 ON meta6.meta_key = 'last_name' AND meta6.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta7 ON meta7.meta_key = 'billing_first_name_kr' AND meta7.user_id = user.ID
//				LEFT JOIN {$wpdb->usermeta} meta8 ON meta8.meta_key = 'shipping_first_name_kr' AND meta8.user_id = user.ID
//				WHERE
//				    meta1.meta_value like '%{$keyword}%'
//				    OR meta2.meta_value like '%{$keyword}%'
//				    OR meta3.meta_value like '%{$keyword}%'
//				    OR meta4.meta_value like '%{$keyword}%'
//				    OR meta5.meta_value like '%{$keyword}%'
//				    OR meta6.meta_value like '%{$keyword}%'
//				    OR meta7.meta_value like '%{$keyword}%'
//				    OR meta8.meta_value like '%{$keyword}%'
//				    OR user.user_login like '%{$keyword}%'
//				    OR user.user_nicename like '%{$keyword}%'
//				    OR user.display_name like '%{$keyword}%'
//				    OR user.user_email like '%{$keyword}%'
//				LIMIT 20";
//
//
//		$user_ids = $wpdb->get_col( $sql );
//
//		foreach ( $user_ids as $user_id ) {
//			$user      = get_user_by( 'id', $user_id );
//			$results[] = array (
//				"value" => $user->ID,
//				"name"  => $user->data->display_name . ' ( #' . $user->ID . ' - ' . $user->data->user_email . ', ' . $user->billing_last_name . $user->billing_first_name . ')'
//			);
//		}

		$users = new WP_User_Query( array(
			'search'         => '*'.esc_attr( $_REQUEST['args'] ).'*',
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'display_name',
				'user_email'
			),
			'number' => 20
		) );
		$users_found = $users->get_results();

		if( $users_found instanceof WP_User ){
			$users_found = array( $users_found );
		}
		


		foreach( $users_found as $user ) {
			$msps_user  = new MSPS_User( $user->ID );
			$free_point = $msps_user->get_point( array ( 'free_point' ) );
			
			$results[] = array(
				"value" => $user->ID,
				"free_point" => $free_point,
				"name"  => $user->data->display_name . ' ( #' . $user->ID . ' - ' . $user->data->user_email . ', ' . $user->billing_last_name . $user->billing_first_name . ')'
			);
		}

		$respose = array (
			'success' => true,
			'results' => $results
		);

//		echo json_encode( $respose );
		wp_send_json($respose);

		die();
	}
	public static function adjust_mshop_user_point() {
		if ( is_admin() ) {
			$user   = new MSPS_User( $_REQUEST['id'] );
			$action = $_REQUEST['point_action'];
			$amount = $_REQUEST['amount'];
			$note   = $_REQUEST['note'];

			if ( 'earn' === $action ) {
				$user->earn_point( $amount );
				$note = ! empty( $note ) ? $note : sprintf( __( '관리자에의해 %s포인트가 적립되었습니다.', 'mshop-point-ex' ), number_format( $amount ) );
				$user->add_comment_note( $amount, $note, false );
				$note .= sprintf( __( '<p class="meta"><abbr>%s ( #%d, %s )에 의해 작업됨</abbr></p>' ), wp_get_current_user()->display_name, wp_get_current_user()->ID, wp_get_current_user()->user_email );
				$user->add_comment_note( $amount, $note, true );
			} else if ( 'deduct' == $action ) {
				$user->deduct_point( $amount );
				$note = ! empty( $note ) ? $note : sprintf( __( '관리자에의해 %s포인트가 차감되었습니다.', 'mshop-point-ex' ), number_format( $amount ) );
				$user->add_comment_note( - 1 * $amount, $note, false );
				$note .= sprintf( __( '<p class="meta"><abbr>%s ( #%d, %s )에 의해 작업됨</abbr></p>' ), wp_get_current_user()->display_name, wp_get_current_user()->ID, wp_get_current_user()->user_email );
				$user->add_comment_note( - 1 * $amount, $note, true );
			}
		}

		wp_send_json_success();
	}

	public static function get_user_point_list() {
		$results = array ();

		$args = array (
			'number'      => MSPS_Settings_Manage_Point::$number_per_page,
			'count_total' => true
		);
		if ( empty( $_REQUEST['sortKey'] ) ) {
			$_REQUEST['sortKey'] = 'ID';
		}

		switch ( $_REQUEST['sortKey'] ) {
			case 'point' :
				$args['meta_key'] = '_mshop_point';
				$args['orderby']  = 'meta_value_num';
				break;
			case 'last_date' :
				$args['meta_key'] = '_mshop_last_date';
				$args['orderby']  = 'meta_value';
				break;
			default:
				$args['orderby'] = $_REQUEST['sortKey'];
				break;
		}

		if ( ! empty( $_REQUEST['role'] ) ) {
			$args['role__in'] = explode( ',', $_REQUEST['role'] );
		}

		if ( ! empty( $_REQUEST['sortOrder'] ) ) {
			$args['order'] = $_REQUEST['sortOrder'] == 'ascending' ? 'ASC' : 'DESC';
		} else {
			$args['order'] = 'DESC';
		}

		if ( ! empty( $_REQUEST['user'] ) ) {
			$args['include'] = explode( ',', $_REQUEST['user'] );
		}

		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] > 0 ) {
			$args['offset'] = $_REQUEST['page'] * MSPS_Settings_Manage_Point::$number_per_page;
		}

		$user_query = new WP_User_Query( $args );

		$users_found = $user_query->get_results();

		if ( $users_found instanceof WP_User ) {
			$users_found = array ( $users_found );
		}

		foreach ( $users_found as $user ) {
			$msps_user  = new MSPS_User( $user->ID );
			$free_point = $msps_user->get_point( array ( 'free_point' ) );
			$paid_point = $msps_user->get_point( array ( 'paid_point' ) );
			$edit_link  = sprintf( '<a href="%s">%s</a>(%s)', get_edit_user_link( $user->ID ), $user->data->display_name, $user->data->user_email );

			$results[] = array (
				"id"         => $user->ID,
				"name"       => $edit_link,
//                "point" => number_format( $free_point ),
//                "paid_point" => number_format( $paid_point ),
				'last_date'  => get_user_meta( $user->ID, '_mshop_last_date', true ),
				"point"      => array (
					'point'      => $free_point,
					'point_desc' => number_format( $free_point ),
					'id'         => $user->ID,
					'name'       => $user->data->display_name
				),
				"paid_point" => array (
					'point'      => $paid_point,
					'point_desc' => number_format( $paid_point ),
					'id'         => $user->ID,
					'name'       => $user->data->display_name
				)
			);
		}

		wp_send_json_success( array (
			'total_count' => $user_query->get_total(),
			'results'     => $results
		) );
	}

	public static function admin_point_logs() {
		global $wpdb;
		$results = array ();

		$tbname = $wpdb->prefix . 'mshop_point_history';

		$limit         = 'LIMIT ' . MSPS_Settings_Point_Logs::$number_per_page;
		$terms_between = '';
		$users_in      = ! empty( $_REQUEST['user'] ) ? ' and b.ID IN (' . $_REQUEST['user'] . ')' : '';

		$terms_between = 'WHERE (a.is_admin IS NULL OR a.is_admin = TRUE)';

		if ( ! empty( $_REQUEST['term'] ) ) {
			$terms = explode( ',', $_REQUEST['term'] );
			if ( ! empty( $terms[0] ) && ! empty( $terms[1] ) ) {
				$terms_between .= ' AND a.date between "' . $terms[0] . ' 00:00:00" AND "' . $terms[1] . ' 23:59:59" ';
			}
		}

		if ( ! empty( $_REQUEST['sortKey'] ) ) {
			$order_by = 'ORDER BY ' . $_REQUEST['sortKey'] . ' ' . ( $_REQUEST['sortOrder'] == 'ascending' ? 'ASC' : 'DESC' );
		} else {
			$order_by = 'ORDER BY id DESC';
		}

		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] > 0 ) {
			$limit = 'LIMIT ' . ( $_REQUEST['page'] * MSPS_Settings_Point_Logs::$number_per_page ) . ', ' . MSPS_Settings_Point_Logs::$number_per_page;
		}

		$count_query = "
				SELECT COUNT(a.id)
				FROM {$tbname} a
				JOIN {$wpdb->users} b on b.ID = a.userid {$users_in}
				{$terms_between}";

		$total_count = $wpdb->get_var( $count_query );

		$query = "
				SELECT a.id, a.userid, a.date, a.point, a.message, b.user_email
				FROM {$tbname} a
				JOIN {$wpdb->users} b on b.ID = a.userid {$users_in}
				{$terms_between}
                {$order_by}
				{$limit}";

		$logs = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $logs as $log ) {
			$results[] = array (
				'no'     => $log['id'],
				'date'   => $log['date'],
				'user'   => $log['user_email'],
				'amount' => number_format( $log['point'] ),
				'desc'   => $log['message'],
			);
		}

		wp_send_json_success( array (
			'total_count' => $total_count,
			'results'     => $results
		) );
	}

	static function update_role_settings() {
		MSPS_Settings_Point_Role::update_settings();
	}

	static function update_policy_settings() {
		MSPS_Settings_Point::update_settings();
	}

	public static function batch_adjust_point() {
		$args = array (
			'order' => 'ASC'
		);

		if ( ! empty( $_REQUEST['role'] ) ) {
			$args['role__in'] = explode( ',', $_REQUEST['role'] );
		}

		if ( ! empty( $_REQUEST['user'] ) ) {
			$args['include'] = explode( ',', $_REQUEST['user'] );
		}

		$amount     = $_REQUEST['amount'];
		$action     = $_REQUEST['point_action'];
		$user_query = new WP_User_Query( $args );

		$users_found = $user_query->get_results();

		if ( $users_found instanceof WP_User ) {
			$users_found = array ( $users_found );
		}

		foreach ( $users_found as $user ) {
			$msps_user = new MSPS_User( $user->ID );

			if ( 'earn' == $action ) {
				$msps_user->earn_point( $_REQUEST['amount'] );
				$note = sprintf( __( '관리자에의해 %s포인트가 적립되었습니다.', 'mshop-point-ex' ), number_format( $amount ) );
				$msps_user->add_comment_note( $amount, $note, false );
				$note .= sprintf( __( '<p class="meta"><abbr>%s ( #%d, %s )에 의해 일괄 작업됨</abbr></p>' ), wp_get_current_user()->display_name, wp_get_current_user()->ID, wp_get_current_user()->user_email );
				$msps_user->add_comment_note( $amount, $note, true );
			} else if ( 'deduct' == $action ) {
				$msps_user->deduct_point( $_REQUEST['amount'] );
				$note = sprintf( __( '관리자에의해 %s포인트가 차감되었습니다.', 'mshop-point-ex' ), number_format( $amount ) );
				$msps_user->add_comment_note( - 1 * $amount, $note, false );
				$note .= sprintf( __( '<p class="meta"><abbr>%s ( #%d, %s )에 의해 일괄 작업됨</abbr></p>' ), wp_get_current_user()->display_name, wp_get_current_user()->ID, wp_get_current_user()->user_email );
				$msps_user->add_comment_note( - 1 * $amount, $note, true );
			} else if ( 'set' == $action ) {
				$prev_point = $msps_user->get_point( array ( 'free_point' ) );
				$msps_user->set_point( $_REQUEST['amount'] );
				$note = sprintf( __( '관리자에의해 %s 포인트에서 %s 포인트로 변경되었습니다.', 'mshop-point-ex' ), number_format( $prev_point ), number_format( $amount ) );
				$msps_user->add_comment_note( $amount, $note, false );
				$note .= sprintf( __( '<p class="meta"><abbr>%s ( #%d, %s )에 의해 일괄 작업됨</abbr></p>' ), wp_get_current_user()->display_name, wp_get_current_user()->ID, wp_get_current_user()->user_email );
				$msps_user->add_comment_note( $amount, $note, true );
			}
		}

		wp_send_json_success();
	}

	public static function get_log_data() {
		global $wpdb;

		$tbname = $wpdb->prefix . 'mshop_point_history';

		$terms_between = '';
		$users_in      = ! empty( $_REQUEST['user'] ) ? ' and b.ID IN (' . $_REQUEST['user'] . ')' : '';

		$terms_between = 'WHERE (a.is_admin IS NULL OR a.is_admin = TRUE)';

		if ( ! empty( $_REQUEST['term'] ) ) {
			$terms = explode( ',', $_REQUEST['term'] );
			if ( ! empty( $terms[0] ) && ! empty( $terms[1] ) ) {
				$terms_between .= ' AND a.date between "' . $terms[0] . ' 00:00:00" AND "' . $terms[1] . ' 23:59:59" ';
			}
		}

		if ( ! empty( $_REQUEST['sortKey'] ) ) {
			$order_by = 'ORDER BY ' . $_REQUEST['sortKey'] . ' ' . ( $_REQUEST['sortOrder'] == 'ascending' ? 'ASC' : 'DESC' );
		} else {
			$order_by = 'ORDER BY id DESC';
		}

		$query = "
				SELECT a.id, a.userid, a.date, a.point, a.message, b.user_email
				FROM {$tbname} a
				JOIN {$wpdb->users} b on b.ID = a.userid {$users_in}
				{$terms_between}
                {$order_by}";

		$logs = $wpdb->get_results( $query, ARRAY_A );

		$results = array ();
		$count = count( $logs );
		foreach ( $logs as $log ) {
			$results[] = array (
				'no'     => $count--,
				'date'   => $log['date'],
				'user'   => $log['user_email'],
				'amount' => number_format( $log['point'] ),
				'desc'   => strip_tags( $log['message'] ),
			);
		}

		return $results;
	}

	public static function export_logs() {
		$fileName = 'mshop_point_logs_' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment; filename=' . $fileName );

		$file = fopen( 'php://output', 'w' );
		fputs( $file, "\xEF\xBB\xBF" );

		fputcsv( $file, array ( '순번', '날짜', '사용자', '포인트', '비고' ));

		foreach ( self::get_log_data() as $log ) {
			fputcsv( $file, $log );
		}
		fclose( $file );

		exit;
	}
}

MSPS_Ajax::init();
