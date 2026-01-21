<?php

defined( 'ABSPATH' ) || exit;

/**
 * WC_Gateway_Opayo_Pi_Refunds class.
 *
 * @extends WC_Gateway_Opayo_Pi
 */
class WC_Gateway_Opayo_Pi_Refunds extends WC_Gateway_Opayo_Pi {

    private $order_id;
    private $amount;
    private $reason;

    public function __construct( $order_id, $amount, $reason ) {

        parent::__construct();

        $this->order_id     = $order_id;
        $this->amount       = $amount;
        $this->reason       = $reason;
        $this->settings     = get_option( 'woocommerce_opayopi_settings' );

    }

    /**
     * [refund description]
     * @return [type] [description]
     */
    function refund() {

        $order          = wc_get_order( $this->order_id );
        $VendorTxCode   = 'Refund-' . $this->order_id . '-' . time();

        // New API Request for refunds
        $data    = array(
            "transactionType"       => 'REFUND',
            "referenceTransactionId"=> $this->get_meta_item( '_transaction_id', $order ),
            "vendorTxCode"          => $VendorTxCode,
            "amount"                => $this->get_total( $this->amount ),
            "description"           => 'Refund for order ' . $this->order_id . '. ' . $this->reason
        );

        $result = $this->remote_post( $data, $this->transaction_url, NULL, 'Basic' );

        if ( 'OK' != strtoupper( $result['status'] ) ) {

                $content = 'There was a problem refunding this payment for order ' . $this->order_id . '. The Transaction ID is ' . $data['referenceTransactionId'] . '. The API Request is <pre>' . 
                    print_r( $api_request, TRUE ) . '</pre>. Opayo returned the error <pre>' . 
                    print_r( $result['statusDetail'], TRUE ) . '</pre> The full returned array is <pre>' . 
                    print_r( $result, TRUE ) . '</pre>. ';
                
                wp_mail( $this->notification ,'Opayo Refund Error ' . $result['status'] . ' ' . time(), $content );

                $order->add_order_note( __('Refund failed', 'woocommerce-gateway-sagepay-form') . '<br />' . 
                                    $result['statusDetail'] );

            /**
             * Debugging
             */
            WC_Sagepay_Common_Functions::sagepay_debug( $content, $this->id, __('Opayo Response : ', 'woocommerce-gateway-sagepay-form'), TRUE );

            return new WP_Error( 'error', __('Refund failed ', 'woocommerce-gateway-sagepay-form')  . "\r\n" . $result['statusDetail'] );

        } else {

            $refund_ordernote = '';

            // Flatten the return
            $result = $this->array_flatten( $result );

            foreach ( $result as $key => $value ) {
                $refund_ordernote .= $key . ' : ' . $value . "\r\n";
            }

            $order->add_order_note( __('Refund successful', 'woocommerce-gateway-sagepay-form') . '<br />' . 
                                    __('Refund Amount : ', 'woocommerce-gateway-sagepay-form') . $this->amount . '<br />' .
                                    __('Refund Reason : ', 'woocommerce-gateway-sagepay-form') . $this->reason . '<br />' .
                                    __('Full return from Opayo', 'woocommerce-gateway-sagepay-form') . '<br />' .
                                    $refund_ordernote
                                );

            return true;
    
        }

    }

    /**
     * [array_flatten description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    function array_flatten( $array ) { 

        if ( !is_array($array) ) { 
            return FALSE; 
        }

        $result = array(); 

        foreach ( $array as $key => $value ) {

            if ( is_array($value) ) { 
                $result = array_merge( $result, $this->array_flatten($value) ); 
            } else { 
                $result[$key] = $value; 
            }

        }

        return $result; 

    }

    /**
     * [get_total description]
     * @param  [type] $total [description]
     * @return [type]        [description]
     */
    function get_total( $total ) {
        $total = $total * 100;
        $total = explode( '.', $total );
        return intval( $total[0] );
    }

    /**
     * [get_meta_item description]
     * @param  [type] $meta  [description]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    function get_meta_item( $meta, $order ) {

        $order_meta = $order->get_meta( $meta, TRUE );
        if( !empty( $order_meta ) ) {
            return $order_meta;
        }

        $post_meta = get_post_meta( $order->get_id(), $meta, TRUE );
        if( !empty( $post_meta ) ) {
            return $post_meta;
        }

        return '';
    }

} // END CLASS
