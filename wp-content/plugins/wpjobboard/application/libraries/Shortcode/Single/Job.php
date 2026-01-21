<?php

class Wpjb_Shortcode_Single_Job extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_single_job] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_single_job")) {
            add_shortcode("wpjb_single_job", array($this, "main"));
        }
    }
    
    /**
     * Displays details page
     * 
     * This function is executed when [wpjb_single_job] shortcode is being called.
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        $params = shortcode_atts(array(
            'post_id' => get_the_ID(),
        ), $atts);
        return Wpjb_Project::getInstance()->singular->job->main($params["post_id"]);
    }
}
