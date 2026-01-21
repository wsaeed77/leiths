<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// HPOS
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Process the payment form
 */
class Opayo_Direct_Process_Threeds extends WC_Gateway_Sagepay_Direct {

	public function __construct() {

		parent::__construct();

		$this->settings 	= get_option( 'woocommerce_sagepaydirect_settings' );

	}

	function process_threeds( $order_id ) {
    	global $wpdb;

        // woocommerce order object
        $order    = wc_get_order( $order_id );

        try {

        	if( isset( $_REQUEST['threedsecure'] ) ) {

        		// Get meta table name
        		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$meta_table_name =  'wc_orders_meta';
				} else {
					$meta_table_name = 'postmeta';
				}

        		$threedsecure = wc_clean( $_REQUEST["threedsecure"] );
            	$stored_value = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->prefix$meta_table_name WHERE meta_key = '_VendorTxCode' AND meta_value = %s;", $threedsecure ) );

            	// Get correct value from object
            	if ( null !== $stored_value && OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$stored_id = $stored_value->order_id;
				} elseif( null !== $stored_value ) {
					$stored_id = $stored_value->post_id;
				}

                if ( null !== $stored_value && $order_id == $stored_id ) {

	        		// Get the stored values from the order
	        		$opayoresult = $order->get_meta( '_sage_3ds', TRUE );

	        		if( isset( $_REQUEST["cres"] ) ) {

		        		$data = array(
                            "CRes" 		=> wc_clean( $_REQUEST["cres"] ),
                            "VPSTxId" 	=> $opayoresult["VPSTxId"]
                        );

	        		} else {

		        		$data = array(
                            "PARes" 	=> wc_clean( $_REQUEST["PaRes"] ),
                            "MD" 	=> $opayoresult["MD"]
                        );

	        		}

	        		// Send the 3D Secure response to Opayo
	        		$result = $this->sagepay_post( $data, $this->callbackURL );

	        		// Check $result for API errors
					if( is_wp_error( $result ) ) {

						$sageresult = $result->get_error_message();
						throw new Exception( __( 'Processing error <pre>' . print_r( $sageresult, TRUE ) . '</pre>', 'woocommerce-gateway-sagepay-form' ) );
					
					} else {
						// Process the response from Opayo
						$sageresult = $this->sageresponse( $result['body'] );

						switch( strtoupper( $sageresult['Status'] ) ) {
			                case 'OK':
			                case 'REGISTERED':
	                		case 'AUTHENTICATED':

			                	// Old payment method
		                        $old_method = $order->get_payment_method();

		                        // Remove the token, just in case
			                    $order->delete_meta_data( '_SagePayDirectToken' );

		                        // Payment method change has been successfull, if the old payment method was PayPal then it needs to be cancelled at PayPal
		                        if( class_exists( 'WCS_PayPal_Status_Manager' ) && $old_method == 'paypal') {
		                        	$subscription       			= new WC_Subscription( $order_id );
		                            $payal_subscription_cancelled 	= WCS_PayPal_Status_Manager::cancel_subscription( $subscription );
		                        }

		                        // Update the order meta with the token, if there is one
			                	if( isset( $sageresult["Token"] ) && strlen( $sageresult["Token"] ) != 0) {

			                		if( $opayoresult["opayo_card_token"] === 'new' ) {
				                		// Save the new token

										$token = new WC_Payment_Token_CC();

										$token->set_token( str_replace( array('{','}'),'',$sageresult["Token"] ) );
										$token->set_gateway_id( $this->id );
										$token->set_card_type( $opayoresult["opayo_card_type"] );
										$token->set_last4( $opayoresult["opayo_masked_card"] );
										$token->set_expiry_month( $opayoresult["opayo_card_expiry_mon"] );
										$token->set_expiry_year( 2000 + $opayoresult["opayo_card_expiry_year"] );

										$token->set_user_id( $order->get_customer_id()  );
										
										$token->save();
									}

									$order->update_meta_data( '_SagePayDirectToken' , $sageresult["Token"] );

								}

		                    	// Delete related transaction details from subscription to force the new method
                        		$order->delete_meta_data( '_RelatedVPSTxId' );
                        		$order->delete_meta_data( '_RelatedSecurityKey' );
                        		$order->delete_meta_data( '_RelatedTxAuthNo' );
                        		$order->delete_meta_data( '_RelatedVendorTxCode' );

								// Delete _sage_3ds
	                        	$order->delete_meta_data( '_sage_3ds' );

								// Create success message for customer
	                        	if( is_callable( 'wc_add_notice') ) {
									wc_add_notice( __('Your payment method has been updated.', 'woocommerce-gateway-sagepay-form'), 'success' );
								}

								// Save the order
        						$order->save();

								// Redirect customer
								wp_redirect( wc_get_endpoint_url( 'view-subscription', $order_id, wc_get_page_permalink( 'myaccount' ) ) );
	                			exit;

			                break;

			                default :

		                        $update_order_status = apply_filters( 'woocommerce_opayo_direct_failed_order_status', 'failed', $order, $sageresult );
		                      
		                        // Add Order Note
		                        $order->add_order_note( __('Payment failed', 'woocommerce-gateway-sagepay-form'), $sageresult, $order );

		                        // Update the order status
		                        $order->update_status( $update_order_status );

		                        // Soft Decline
		                        if( isset( $sageresult['DeclineCode'] ) && in_array( $sageresult['DeclineCode'], array('65','1A') ) ) {
		                            $order->update_meta_data( '_opayo_soft_decline', $sageresult['DeclineCode'] );
		                        }

		                        // Update Order Meta
		                        $this->update_order_meta( $sageresult, $order_id );

		                        // Save the order
        						$order->save();

			                    throw new Exception( __('Payment error. Please try again, your card has not been charged.', 'woocommerce-gateway-sagepay-form') . ': ' . $sageresult['StatusDetail'] );

			            }

					}

					exit;

	        	} else {
	        		// Order ID can not be verified against stored _VendorTxCode
	        		throw new Exception( __( 'There was an error. Your card has not been charged, please try again.', 'woocommerce-gateway-sagepay-form' ) );
	        	}

			} elseif( isset( $_GET['process_threedsecure'] ) ) {

				$sage_3dsecure  = $order->get_meta( '_sage_3ds', TRUE );

		        $key      = 'CRes';
		        $value    = '';

		        $redirect_url = $this->get_return_url( $order );

		        // Get ready to set form fields for 3DS 1.0/2.0
		        $p = $this->pareq_or_creq ( $sage_3dsecure );
		        $m = $this->md_or_vpstxid ( $sage_3dsecure );

		        $sage_3dsecure['Complete3d'] = $this->append_url( $order->get_checkout_payment_url( true ) );
		        $sage_3dsecure['Complete3d'] = add_query_arg( 'threedsecure', $order->get_meta( '_VendorTxCode', TRUE ), $sage_3dsecure['Complete3d'] );

		        $iframe_args = array( 
		                            "name_one"      => $p["field_name"],
		                            "value_one"     => $p["field_value"],
		                            "name_two"      => $m["field_name"],
		                            "value_two"     => $m["field_value"],
		                            "termUrl"       => $sage_3dsecure['Complete3d'],
		                            "ACSURL"        => $sage_3dsecure['ACSURL'],
		                        );

		        // Add some order notes for tracing problems
				// $order->add_order_note( sprintf( __( 'Processing 3D Secure, directing customer to: %s', 'woocommerce-gateway-sagepay-form' ), $sage_3dsecure['ACSURL'] ) );

	            $form  = '<p>Your card issuer has requested additional authorisation for this transaction, please wait while you are redirected.</p>';
	            $form .= '<form id="submitForm" method="post" action="' . $iframe_args['ACSURL'] . '">';
	            $form .= '<input type="hidden" name="' . $iframe_args['name_one'] . '" value="' . $iframe_args['value_one'] . '"/>';
	            $form .= '<input type="hidden" name="' . $iframe_args['name_two'] . '" value="' . $iframe_args['value_two'] . '"/>';
	            $form .= '<input type="hidden" id="termUrl" name="TermUrl" value="' . $iframe_args['termUrl'] . '"/>';
	            $form .= '<noscript><p>You are seeing this message because JavaScript is disabled in your browser. Please click to authenticate your card</p><p><input type="submit" value="Submit"></p></noscript>';
	            $form .= '<script>document.getElementById("submitForm").submit();</script>';
	            $form .= '</form>';

	            echo $form;

	            return;
/*
				$sage_3dsecure  = $order->get_meta( '_sage_3ds', TRUE );

		        $key      = 'CRes';
		        $value    = '';

		        $redirect_url = $this->get_return_url( $order );

		        // Get ready to set form fields for 3DS 1.0/2.0
		        $p = $this->pareq_or_creq ( $sage_3dsecure );
		        $m = $this->md_or_vpstxid ( $sage_3dsecure );

		        $sage_3dsecure['Complete3d'] = $this->append_url( $order->get_checkout_payment_url( true ) );
		        $sage_3dsecure['Complete3d'] = add_query_arg( 'threedsecure', $order->get_meta( '_VendorTxCode', TRUE ), $sage_3dsecure['Complete3d'] );

		        $iframe_args = array( 
		                            "name_one"      => $p["field_name"],
		                            "value_one"     => $p["field_value"],
		                            "name_two"      => $m["field_name"],
		                            "value_two"     => $m["field_value"],
		                            "termUrl"       => $sage_3dsecure['Complete3d'],
		                            "ACSURL"        => $sage_3dsecure['ACSURL'],
		                        );

		        $iframe_url = $sage_3dsecure['Complete3d'];

// Remove iFrame option temporarily
		        $display_method = $this->get_threeDSMethod();

		        if( isset( $display_method ) && $display_method === "0" ) {

		        	// iFrame Method
		            $form  = '<p>Your card issuer has requested additional authorisation for this transaction, please wait while you are redirected.</p>';
		            $form .= '<form id="submitForm" method="post" action="' . $iframe_args['ACSURL'] . '">';
		            $form .= '<input type="hidden" name="' . $iframe_args['name_one'] . '" value="' . $iframe_args['value_one'] . '"/>';
		            $form .= '<input type="hidden" name="' . $iframe_args['name_two'] . '" value="' . $iframe_args['value_two'] . '"/>';
		            $form .= '<input type="hidden" id="termUrl" name="TermUrl" value="' . $iframe_args['termUrl'] . '"/>';
		            $form .= '<noscript><p>Authenticate your card</p><p><input type="submit" value="Submit"></p></noscript>';
		            // $form .= '<script>document.getElementById("submitForm").submit();</script>';
		            $form .= '</form>';

		            $redirect_page = 
		                '<!--Non-IFRAME browser support-->' .
		                '<html><head><title>3D Secure Verification</title></head>' . 
		                '<body>' .
		                $form . 
		                '</body></html>';

		            $iframe_page = 
		                '<noscript><h3>You are seeing this message because JavaScript is disabled in your browser. Please consider enabling JavaScript for this website before continuing. Please do not refresh the page.</h3></noscript>' .
		                '<iframe src=\''. $iframe_url .'\' name=\'3diframe\' width=\'100%\' height=\'500px\' frameBorder=\'0\' sandbox=\'allow-top-navigation allow-scripts allow-forms allow-same-origin\'>' .
		                $redirect_page .
		                '</iframe>';
		                
		                
		            echo $iframe_page;
		            // Use return for iFrame method to make sure website footer shows
		            return;

		        } else {

		            $form  = '<p>Your card issuer has requested additional authorisation for this transaction, please wait while you are redirected.</p>';
		            $form .= '<form id="submitForm" method="post" action="' . $iframe_args['ACSURL'] . '">';
		            $form .= '<input type="hidden" name="' . $iframe_args['name_one'] . '" value="' . $iframe_args['value_one'] . '"/>';
		            $form .= '<input type="hidden" name="' . $iframe_args['name_two'] . '" value="' . $iframe_args['value_two'] . '"/>';
		            $form .= '<input type="hidden" id="termUrl" name="TermUrl" value="' . $iframe_args['termUrl'] . '"/>';
		            $form .= '<noscript><p>You are seeing this message because JavaScript is disabled in your browser. Please click to authenticate your card</p><p><input type="submit" value="Submit"></p></noscript>';
		            $form .= '<script>document.getElementById("submitForm").submit();</script>';
		            $form .= '</form>';

		            echo $form;

		            exit;

			        }
*/
	        } else {
	        	// We should not be here :/
	        	throw new Exception( __( 'There was an error. Your card has not been charged, please try again. 3D Secure could not be validated', 'woocommerce-gateway-sagepay-form' ) );
	        }

        } catch( Exception $e ) {

        	// Clear any stored values, necessary for the retries
    		$order->delete_meta_data( '_sage_3ds' );
    		$order->delete_meta_data( '_VendorTxCode' );
    		$order->delete_meta_data( '_RelatedVendorTxCode' );

        	// Add the error message
			if( is_callable( 'wc_add_notice' ) ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}

			// Redirect for a retry
            wp_redirect( wc_get_endpoint_url( 'view-subscription', $order_id, wc_get_page_permalink( 'myaccount' ) ) );
            exit;

		}
	}

} // End class
