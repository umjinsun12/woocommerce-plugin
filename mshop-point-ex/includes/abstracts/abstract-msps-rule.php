<?php

class MSPS_Rule {
    public $id = 0;
    public $post = null;

    protected $items = array();

    protected $point = 0;
    public function __construct( $rule ) {
        if ( is_numeric( $rule ) ) {
            $this->id   = absint( $rule );
            $this->post = get_post( $this->id );
        } elseif ( $rule instanceof MSPS_Rule ) {
            $this->id   = absint( $rule->id );
            $this->post = $rule->post;
        } elseif ( isset( $rule->ID ) ) {
            $this->id   = absint( $rule->ID );
            $this->post = $rule;
        }
    }
    public function __isset( $key ) {
        return metadata_exists( 'post', $this->id, '_' . $key );
    }
    public function __get( $key ) {
        $value = get_post_meta( $this->id, '_' . $key, true );

        // Get values or default if not set
        if ( in_array( $key, array( 'minimum_amount', 'minimum_qty' ) ) ) {
            $value = $value ? $value : 0;
        } elseif ( in_array( $key, array( 'object', 'rules' ) ) ) {
            $value = $value ? $value : array();
        }

        if ( false !== $value ) {
            $this->$key = $value;
        }

        return $value;
    }
    public function get_post_data() {
        return $this->post;
    }

    public function clear(){
        $this->items = array();
    }
    public function get_id() {
        return $this->id;
    }

    public function is_match( $product ){
        return false;
    }

    public function set_item( $product, $qty ){
        if( is_array( $product ) ){
            $this->items = $product;
        }if( $product instanceof WC_Product ){
            $this->items = array();
            $this->add_item( $product, $qty );
        }
    }

    public function add_item( $product, $qty = 1 ){
        $this->items[] = array(
            'product' => $product,
            'qty' => $qty
        );
    }

    public function calculate_point( $user_role ){
        $this->point = 0;
        $total_amount = 0;
        $total_qty = 0;

        $point_option = $this->get_price_rule( $user_role );
        if( !empty( $point_option ) ){
            foreach( $this->items as $item ) {
                $total_amount += apply_filters( 'mshop_membership_get_discounted_price', $item['product']->get_price(), $item['product'] ) * $item['qty'];
                $total_qty += $item['qty'];
            }

            $fixed_amount = $point_option['fixed'];
            $ratio_amount = $total_amount / 100 * floatval( $point_option['ratio'] );

            $this->point = $fixed_amount + $ratio_amount / MSPS_Manager::point_exchange_ratio();
        }

        return ceil( $this->point );
    }

    public function is_valid(){
        if( empty( $this->price_rules ) || count( $this->price_rules ) == 0 ){
            return false;
        }
        if( 'yes' == $this->use_valid_term ){
            $dates = explode( ',', $this->valid_term );
            $sdate = strtotime( $dates[0] . ' 00:00:00' );
            $edate = strtotime( $dates[1] . ' 23:59:59' );
            $now   = strtotime( date("Y-m-d H:i:s") );

            if( $sdate > $now || $edate < $now ){
                return false;
            }
        }

        return true;
    }
    public function is_applicable(){
        $total_amount = 0;
        $total_qty = 0;
        if( !$this->is_valid() ){
            return false;
        }
        if( !empty( $this->items ) ){
            foreach( $this->items as $item ) {
                $total_amount += $item['product']->get_price() * $item['qty'];
                $total_qty += $item['qty'];
            }

            foreach( $this->price_rules as $rule ){
                $rule_amount = apply_filters( 'wcml_raw_price_amount', floatval( $rule['amount'] ) );
                if( ( $rule_amount == 0 && $rule['qty'] == 0 ) ||
                    ( $rule_amount > 0 && $total_amount >= $rule_amount ) ||
                    ( $rule['qty'] > 0    && $total_qty >= $rule['qty'] ) ){
                    return true;
                }
            }
        }

        return false;
    }

    public function get_point( $user_id = null ){
        return $this->point;
    }

    public function get_user_point_option( $user_role ){
        if( !empty( $this->roles ) ){
            $option = array_filter( $this->roles, function( $role ) use( $user_role){
                return $user_role == $role['role'];
            } );

            return is_array( $option ) ? array_shift( $option ) : $option;
        }else{
            return null;
        }
    }

    public function get_price_rule( $user_role ){
        $total_amount = 0;
        $total_qty = 0;

        if( !empty( $this->price_rules ) ){
            foreach( $this->items as $item ) {
                $total_amount += $item['product']->get_price() * $item['qty'];
                $total_qty += $item['qty'];
            }

            foreach( $this->price_rules as $rule ){
                $rule_amount = apply_filters( 'wcml_raw_price_amount', floatval( $rule['amount'] ) );

                if( ( $rule_amount == 0 && $rule['qty'] == 0 ) ||
                    ( $rule_amount != 0 && $total_amount >= $rule_amount ) ||
                    ( $rule['qty'] != 0    && $total_qty >= $rule['qty'] ) ){
                    $option = array_filter( $rule['roles'], function( $role ) use( $user_role){
                        return $user_role == $role['role'];
                    } );

                    return is_array( $option ) ? array_shift( $option ) : $option;
                }
            }
        }else{
            return null;
        }
    }
    public function get_matched_rule( $qty, $user_role ){
        $rule_index = $this->get_matched_rule_index( $user_role );
        return $rule_index >= 0 ? $this->price_rules[ $rule_index ]  : null;
    }
    public function get_precedence_rule( $user_role ){
        $rule_index = $this->get_matched_rule_index( $user_role );

        if( $rule_index == 0 ){
            // 최상위 정책에 매칭된 경우
            return null;
        }else{
            if( $rule_index == -1 ){
                $rule_index = count( $this->price_rules );
            }

            for( $i = $rule_index-1; $i >= 0 ; $i-- ){
                $rule = $this->price_rules[$i];

                $option = array_filter( $rule['roles'], function( $role ) use( $user_role){
                    return $user_role == $role['role'];
                } );

                if( !empty( $option) ) {
                    $option = array_shift( $option );
                    if( $option['fixed'] > 0 || $option['ratio'] > 0 ){
                        return $rule;
                    }
                }
            }
        }

        return null;
    }
    protected function get_matched_rule_index( $user_role ){
        $total_amount = 0;
        $total_qty    = 0;

        foreach( $this->items as $item ) {
            $total_amount += $item['product']->get_price() * $item['qty'];
            $total_qty += $item['qty'];
        }

        if( $total_amount > 0 && $total_qty > 0 && count( $this->price_rules ) > 0){
            for( $i = 0 ; $i < count( $this->price_rules ) ; $i++ ){
                $rule = $this->price_rules[$i];
                $rule_amount = apply_filters( 'wcml_raw_price_amount', floatval( $rule['amount'] ) );
                if( ( $rule_amount == 0 && $rule['qty'] == 0 ) ||
                    ( $rule_amount > 0 && $rule_amount <= $total_amount) ||
                    ( $rule['qty'] > 0 && $rule['qty'] <= $total_qty ) ){

                    $option = $this->get_price_rule( $user_role );

                    if( !empty( $option) && ( $option['fixed'] > 0 || $option['ratio'] > 0 ) ){
                        return $i;
                    }
                }
            }
        }

        return -1;
    }

    public function get_all_option( $user_role ){
        $all_options = array();

        foreach( $this->price_rules as $rule ){

            $option = array_filter( $rule['roles'], function( $role ) use( $user_role){
                return $user_role == $role['role'];
            } );

            $option = is_array( $option ) ? array_shift( $option ) : $option;

            if( !empty( $option ) && ( $option['fixed'] > 0 || $option['ratio'] > 0 )){
                $all_options[] = array(
                    'type' => 'common',
                    'amount' => $rule['amount'],
                    'qty'    => $rule['qty'],
                    'option' => $option
                );
            }
        }

        return $all_options;
    }
}
