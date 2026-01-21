<?php

class Wpjb_Shortcode_Single_Company extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_single_company] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_single_company")) {
            add_shortcode("wpjb_single_company", array($this, "main"));
        }
    }
    
    /**
     * Displays details page
     * 
     * This function is executed when [wpjb_single_company] shortcode is being called.
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        $params = shortcode_atts(array(
            'post_id' => get_the_ID(),
        ), $atts);
        return Wpjb_Project::getInstance()->singular->company->main($params["post_id"]);
    }
}
