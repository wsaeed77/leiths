<?php
/**
 * Description of JobType
 *
 * @author greg
 * @package
 */

class Wpjb_Model_Discount extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_discount";

    protected function _init()
    {

    }

    public function getTextDiscount()
    {
        if($this->type == 1) {
            return $this->discount."%";
        } else {
            $currency = Wpjb_List_Currency::getByCode($this->currency);
            $code = $currency['code'].' ';
            if($currency['symbol'] != null) {
                $code = $currency['symbol'];
            }
            return $code.$this->discount;
        }
    }

    public function delete()
    {

        $request = Daq_Request::getInstance();
        $stripe = new Wpjb_Payment_Stripe();
        $code = $this->code;

        if( $stripe->conf("secret_key") ) {

            if(!class_exists("Stripe")) {
                include_once Wpjb_List_Path::getPath("vendor")."/stripe/init.php";
            }

            \Stripe\Stripe::setApiKey( $stripe->conf("secret_key") );
            try {
                $coupon = \Stripe\Coupon::Retrieve( $this->code );
                $coupon->delete();
            } catch(Exception $e) {

            }
        }

        return parent::delete();
    }
}

?>