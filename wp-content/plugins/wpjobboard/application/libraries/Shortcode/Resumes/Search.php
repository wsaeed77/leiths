<?php

class Wpjb_Shortcode_Resumes_Search extends Wpjb_Shortcode_Abstract {
    
    /**
     * Class constructor
     * 
     * Registers [wpjb_resumes_search] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_resumes_search")) {
            add_shortcode("wpjb_resumes_search", array($this, "main"));
        }
    }
    
    /**
     * Displays Resumes Search
     * 
     * This function is executed when [wpjb_resumes_list] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/shortcode_wpjb_resumes_search/ documentation
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        
        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            $candidate = Wpjb_Model_Resume::current();

            if( !$candidate || wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = sprintf( __( 'Only registered candidates have access to this page. You can register <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_reg" ) ) );
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash_get();
        }

        if( !wpjb_employer_have_access( get_the_ID() ) ) {

            $employer = Wpjb_Model_Company::current();
            
            if( !$employer || wpjb_conf( "employer_members_have_access" ) == 1 ) {
                $msg = sprintf( __( 'Only registered employer have access to this page. You can register <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_emp_reg" ) ) );
            } elseif( wpjb_conf( "employer_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium employers have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_membership_pricing" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash_get();
        }
        
        $request = Daq_Request::getInstance();

        $params = shortcode_atts(array(
            "form_only"         => "0",
            "redirect_to"       => wpjr_link_to("search"),
            "page_id"           => get_the_ID(),
            "form_code"         => null,
            "image"             => "50x50",
            "image_default_url" => null
        ), $atts);

        if( is_numeric( $params["redirect_to"] ) ) {
            $redirect_to = get_permalink( $params["redirect_to"] );
        } else {
            $redirect_to = $params["redirect_to"];
        }

        $this->view = new stdClass();

        if(!wpjr_can_browse()) {

            $error = wpjr_can_browse_err();

            if($error) {
                $this->addError($error);
            }

            if(wpjb_conf("cv_privacy") == 1) {
                return $this->flash();
            }
        }

        $resume = new Wpjb_Model_Resume();
        $meta = array();
        foreach($resume->meta as $k => $m) {
            if($request->get($k)) {
                $meta[$k] = $request->get($k);
            }
        }

        $date_from = $request->get("date_from");
        $date_to = $request->get("date_to");

        if($request->get("posted")>0) {
            $posted = intval($request->get("posted"))-1;
            $date_to = date("Y-m-d");
            $date_from = date("Y-m-d", wpjb_time("$date_to -$posted DAY"));
        }

        $paged = get_query_var("paged", 1);

        if($paged < 1) {
            $paged = 1;
        }

        $param = array(
            "filter" => "active",
            "query" => $request->get("query"),
            "headline" => $request->get("headline"),
            "category" => $request->get("category"),
            "fullname" => $request->get("fullname"),
            "page" =>  $request->get("page", $request->get("pg", $paged)),
            "count" => $request->get("count", wpjb_conf("front_jobs_per_page", 20)),
            "country" => $request->get("country"),
            "location" => $request->get("location"),
            "radius" => $request->get("radius"),
            "candidate_country" => $request->get("candidate_country"),
            "candidate_state" => $request->get("candidate_state"),
            "candidate_zip_code" => $request->get("candidate_zip_code"),
            "candidate_location" => $request->get("candidate_location"),
            "meta" => $meta,
            "sort" => $request->get("sort"),
            "order" => $request->get("order"),
            "date_from" => $date_from,
            "date_to" => $date_to
        );
        
        if ( get_option('permalink_structure') ) {
            $format = 'page/%#%/';
        } else {
            $format = '&paged=%#%';
        }

        $this->view->atts = $atts;
        $this->view->pagination = true;
        $this->view->format = $format;
        $this->view->param = $param;
        $this->view->redirect_to = $redirect_to;
        $this->view->show_results = false;
        $this->view->image = $params["image"];
        $this->view->image_default_url = $params["image_default_url"];

        $query = array();
        foreach($request->get() as $k => $v) {
            if(!empty($v) && !in_array($k, array("page", "job_resumes", "page_id"))) {
                if( is_array( $v ) ) {
                    $query[$k] = array_map( "sanitize_text_field", $v );
                } else {
                    $query[$k] = sanitize_text_field( $v );
                }
            }
        }
        $this->view->query = $query;

        $form = new Wpjb_Form_ResumesSearch(null, apply_filters( "wpjb_form_scheme", array( "custom"=>$params['form_code'] ), "resume_search", null ) );
        $form->isValid($request->get());
        $this->view->form = $form;

        if(empty($query) || $params["form_only"] == "1") {
            $this->view->show_results = false;
        } else {
            $this->view->show_results = true;
            $rQuery = wpjb_readable_query($request->get(), $form, new Wpjb_Form_Resume());
            $readable = array();
            foreach($rQuery as $rk => $data) {

                $values = array();

                foreach($data["value"] as $vk => $vv) {
                    $aparam = array(
                        "href"=>"#", 
                        "class"=>"wpjb-glyphs wpjb-icon-cancel wpjb-refine-cancel",
                        "data-wpjb-field-remove" => $rk,
                        "data-wpjb-field-value" => sanitize_text_field( $vv )
                    );

                    $htmlA = new Daq_Helper_Html("a", $aparam, "");
                    $htmlA->forceLongClosing();

                    $values[] = sanitize_text_field( $vv ) . "" . $htmlA->render();
                }

                $htmlB = new Daq_Helper_Html("b", array(), $data["param"]);
                $htmlS = new Daq_Helper_Html("span", array(
                    "class" => "wpjb-tag",
                ), $htmlB->render() ." ". join(" ", $values));

                $readable[] = $htmlS->render();
            }
            if(empty($readable)) {
                $readable[] = '<span class="wpjb-tag"><em>'.__("No search params provided, showing all public resumes.", "wpjobboard").'</em></span>';
            }
            $this->view->readable = join(" ", $readable);
        }


        wp_enqueue_style("wpjb-css");
        wp_enqueue_script('wpjb-js');

        return $this->render("resumes", "search");
    }
    
    public function __set($name, $value) {
        if($name == "resume" && $this->view) {
            $this->view->resume = $value;
        }
    }
}
