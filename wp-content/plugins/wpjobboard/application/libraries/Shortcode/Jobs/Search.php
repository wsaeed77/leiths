<?php

class Wpjb_Shortcode_Jobs_Search extends Wpjb_Shortcode_Abstract 
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_jobs_search] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_jobs_search")) {
            add_shortcode("wpjb_jobs_search", array($this, "main"));
        }
    }
    
    /**
     * Displays advanced jobs search form and results
     * 
     * This function is executed when [wpjb_jobs_search] shortcode is being called.
     * 
     * @link http://wpjobboard.net/kb/wpjb_jobs_search/ documentation
     * 
     * @param array $atts   Shortcode attributes
     * @return void
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

        $params = shortcode_atts(array(
            "form_only" => "0",
            "redirect_to" => wpjb_link_to("search"),
            "page_id" => get_the_ID(),
            "form_code" => null,
            "image" => "50x50",
            "image_default_url" => null,
            "always_show_results" => 0
        ), $atts);

        if( is_numeric( $params["redirect_to"] ) ) {
            $redirect_to = get_permalink( $params["redirect_to"] );
        } else {
            $redirect_to = $params["redirect_to"];
        }

        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job();
        $meta = array();

        foreach($job->meta as $k => $m) {
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
            "query" => $request->get("query"),
            "job_title" => $request->get("job_title"),
            "category" => $request->get("category"),
            "type" => $request->get("type"),
            "page" => $request->get("page", $request->get("pg", $paged)),
            "count" => $request->get("count", wpjb_conf("front_jobs_per_page", 20)),
            "country" => $request->get( "country", $request->get( "job_country" ) ),
            "state" => $request->get( "state", $request->get( "job_state" ) ),
            "zip_code" => $request->get( "zip_code", $request->get( "job_zip_code" ) ),
            "city" => $request->get( "city", $request->get( "job_city" ) ),
            "posted" => $request->get("posted"),
            "location" => $request->get("location"),
            "radius" => $request->get("radius"),
            "is_featured" => $request->get("is_featured"),
            "employer_id" => $request->get("employer_id"),
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

        $this->view = new stdClass();
        $this->view->atts = $atts;
        $this->view->pagination = true;
        $this->view->format = $format;
        $this->view->param = $param;
        $this->view->redirect_to = $redirect_to;
        $this->view->image = $params["image"];
        $this->view->image_default_url = $params["image_default_url"];

        $query = array();
        foreach($request->get() as $k => $v) {
            if(!empty($v) && !in_array($k, array("page", "job_board", "page_id"))) {
                $query[$k] = $v;
            }
        }

        $init = array();
        foreach($param as $k => $v) {
            if(!empty($v) && !in_array($k, array("page", "job_board", "page_id"))) {
                $init[$k] = $v;
            }
        }

        $this->view->query = $query;

        $this->view->search_bar = wpjb_conf("search_bar", "disabled");
        $this->view->search_init = $init;

        $form = new Wpjb_Form_AdvancedSearch(null, apply_filters( "wpjb_form_scheme", array( "custom"=>$params['form_code'] ), "job_search", null ) );
        $form->isValid($request->get());
        $this->view->form = $form;

        if( ( empty($query) || $params["form_only"] == "1" ) && !$params["always_show_results"] ) {
            $this->view->show_results = false;
        } else {
            $this->view->show_results = true;

            $rQuery = wpjb_readable_query($request->get(), $form, new Wpjb_Form_AddJob());
            $readable = array();
            foreach($rQuery as $rk => $data) {

                // Skip param if it is not available in search form
                if( !$form->hasElement( $rk ) && !isset( $param[$rk] ) ) {
                    continue;
                }

                $values = array();

                foreach($data["value"] as $vk => $vv) {
                    $aparam = array(
                        "href"=>"#", 
                        "class"=>"wpjb-glyphs wpjb-icon-cancel wpjb-refine-cancel",
                        "data-wpjb-field-remove" => $rk,
                        "data-wpjb-field-value" => sanitize_text_field( $vv )
                    );


                    if( $rk == "type" || $rk == "category" ) {
                        $aparam["data-wpjb-field-value"] = sanitize_text_field( $vk );
                    }

                    $htmlA = new Daq_Helper_Html("a", $aparam, "");
                    $htmlA->forceLongClosing();

                    $values[] = esc_html($vv)."".$htmlA->render();
                }

                $htmlB = new Daq_Helper_Html("b", array(), esc_html($data["param"]));
                $htmlS = new Daq_Helper_Html("span", array(
                    "class" => "wpjb-tag",
                ), $htmlB->render() ." ". join(" ", $values));

                $readable[] = $htmlS->render();
            }
            if(empty($readable)) {
                $txt = __("No search params provided, showing all active jobs.", "wpjobboard");
                $readable[] = '<span class="wpjb-tag"><em>'.$txt.'</em></span>';
            }
            $this->view->readable = join(" ", $readable);
        }

        wp_enqueue_style("wpjb-css");
        wp_enqueue_script('wpjb-js');

        return $this->render("job-board", "search");
    }
    
    public function __set($name, $value) {
        if($name == "job" && $this->view) {
            $this->view->job = $value;
        }
    }
}
