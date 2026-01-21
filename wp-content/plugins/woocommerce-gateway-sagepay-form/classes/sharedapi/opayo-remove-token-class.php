<?php

defined( 'ABSPATH' ) || exit;

/**
 * Remove expired tokens via Action Scheduler
 */
class WC_Gateway_Opayo_Remove_Token {
	
	public function __construct() {

		// Automatic Token Removal Action scheduler
		add_action( 'admin_init' , array( __CLASS__,'opayo_scheduler_remove_token') );
		add_action( 'woocommerce_opayo_scheduler_remove_token', array( __CLASS__, 'action_scheduler_opayo_scheduler_remove_token' ), 10, 2 );

	}

	/**
	 * [opayo_scheduler_remove_token description]
	 * @return NULL
	 */
	public static function opayo_scheduler_remove_token() {

		$remove_token  = WC_Gateway_Opayo_Remove_Token::get_remove_token_action_scheduler();

		// Update the order status if necessary 
		if( $remove_token == 'yes' ) {
			
			$next = WC()->queue()->get_next( 'woocommerce_opayo_scheduler_remove_token' );

			if ( ! $next ) {

				$date 			= new DateTime('now');
				$nowTimestamp 	= $date->getTimestamp();
				$date->modify('first day of next month');

				$firstDayOfNextMonthTimestamp = $date->getTimestamp();

				WC()->queue()->cancel_all( 'woocommerce_opayo_scheduler_remove_token' );
				WC()->queue()->schedule_single( $firstDayOfNextMonthTimestamp, 'woocommerce_opayo_scheduler_remove_token' );
			}
		} else {
			WC()->queue()->cancel_all( 'woocommerce_opayo_scheduler_remove_token' );
		}
		 
	}

	/**
	 * [action_scheduler_opayo_scheduler_remove_token description]
	 * @param  [type] $args  [description]
	 * @param  string $group [description]
	 * @return [type]        [description]
	 */
	public static function action_scheduler_opayo_scheduler_remove_token( $args = NULL, $group = '' ) {
        global $wpdb;

        // Get the Opayo Direct tokens
        $tokens = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.token_id FROM {$wpdb->prefix}woocommerce_payment_tokens AS t
				LEFT JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta AS tm1 ON tm1.payment_token_id = t.token_id
				LEFT JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta AS tm2 ON tm2.payment_token_id = t.token_id
				WHERE 
				( 
					t.gateway_id = %s
					AND tm1.meta_key = 'expiry_year'
					AND tm1.meta_value < %s
				)
				OR
				(( 
					t.gateway_id = %s
					AND tm1.meta_key = 'expiry_year'
					AND tm1.meta_value = %s
				)
				AND 
				( 
					t.gateway_id = %s
					AND tm2.meta_key = 'expiry_month'
					AND tm2.meta_value < %s
				))
				GROUP BY t.token", 
				'sagepaydirect', date('Y'), 'sagepaydirect', date('Y'), 'sagepaydirect', date('m')
			), ARRAY_A
		);

		// Log expired tokens
		WC_Gateway_Opayo_Remove_Token::log_tokens( $tokens );

        if( !empty( $tokens ) ) {

        	foreach( $tokens as $token ) {

        		// Get the token object
				$t = WC_Payment_Tokens::get( $token['token_id'] );

				// Delete expired tokens
				$t->delete( $token['token_id'] );
			} 

        }

	}

	/**
	 * Gets the remove token action scheduler.
	 *
	 * @return     <type>  The remove token action scheduler.
	 */
	public static function get_remove_token_action_scheduler() {
		// Get settings
    	$settings = get_option( 'woocommerce_sagepaydirect_settings' );
		return isset( $settings['removeTokenActionScheduler'] ) ? $settings['removeTokenActionScheduler'] : 'no';
	}

	/**
	 * Logs tokens.
	 *
	 * @param      <type>  $tokens  The tokens
	 */
	public static function log_tokens( $tokens ) {

		$id 		= "OpayoDirect_DeleteTokens";
		$start 		= __( 'TokenID, GatewayID, CardType, Last4, ExpiryMonth, ExpiryYear, CustomerID', 'woocommerce-gateway-sagepay-form' );

		$t 			= new WC_Payment_Token_CC();


		if( !isset( $logger ) ) {
            $logger      = new stdClass();
            $logger->log = new WC_Logger();
        }

        $logger->log->add( $id, $start );

        foreach( $tokens AS $token ) {

        	$t 		= WC_Payment_Tokens::get( $token['token_id'] );
        	$tolog 	= $token['token_id'] .", ".$t->get_gateway_id() .", ".$t->get_card_type() .", ".$t->get_last4() .", ".$t->get_expiry_month() .", ".$t->get_expiry_year() .", ".$t->get_user_id();

			$logger->log->add( $id, print_r( $tolog, TRUE ) );        	
        }
        
        $logger->log->add( $id, __('=============================================', 'woocommerce-gateway-sagepay-form') );

	}

} // End class

// Load the class
$GLOBALS['WC_Gateway_Opayo_Remove_Token'] = new WC_Gateway_Opayo_Remove_Token();