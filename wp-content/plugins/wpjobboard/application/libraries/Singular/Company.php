<?php

class Wpjb_Singular_Company extends Wpjb_Shortcode_Abstract {
    
    /**
     * Registers singular events
     * 
     * This function is run by Wpjb_Singular_Manager::setupListeners()
     * 
     * @see Wpjb_Singular_Manager::setupListeners()
     * 
     * @return void
     */
    public function listen() {
        add_filter( "init", array($this, "initPreview"), 50);
        add_filter( "the_content", array($this, "theContent"));
        add_filter( "the_title", array($this, "theTitle"));
    }

    public function initPreview() {
        $request = Daq_Request::getInstance();
    
        if($request->get("post_type") == "company" && absint($request->get("p") > 0)) {
            $id = absint($request->get("p"));
        } else {
            $id = null;
        }
        
        if($id) {
            $company_r = wpjb_get_object_from_post_id($id, "company");
            $company_c = Wpjb_Model_Company::current();
            
            if($company_r && $company_c && $company_r->id == $company_c->id) {
                $this->addInfo(__("Your profile is private only you can see this page.", "wpjobboard"));
                remove_filter('template_redirect', 'redirect_canonical');
                register_post_status("wpjb-disabled", array(
                    "public" => true
                ));
            }
        } 
    }
    
    /**
     * Renders Company details HTML 
     * 
     * This function is executed in the the_content filter, if the current page
     * is company details page then it replaces default content with 
     * the companydetails page content.
     * 
     * @param string $content   HTML Content
     * @return string           HTML Content
     */
    public function theContent($content) {
        if( apply_filters( "wpjb_singular_ignore_the_content", false, "company" ) ) {
            return $content;
        }

        if(is_singular('company') && in_the_loop()) {
            return $this->main(get_the_ID());
        } else {
            return $content;
        }
    }

    /**
     * Trim script and html tags from single company page title
     * 
     * @param string $title 
     * @return string
     */
    public function theTitle( $title ) {

        $post = get_post( get_the_ID() );
        
        if( !$post || $post->post_title != $title ) {
            return $title;
        }

        return esc_html( $title ); 
    }
    
    /**
     * Renders job details HTML
     * 
     * @param int $post_id  ID of a post / job to render.
     * @return void
     */
    public function main($post_id) {
        
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
        
        $company = wpjb_get_object_from_post_id($post_id, "company");
        /* @var $company Wpjb_Model_Employer */

        if(Wpjb_Model_Company::current() && Wpjb_Model_Company::current()->id==$company->id) {
            // do nothing
        } elseif($company->is_active == Wpjb_Model_Company::ACCOUNT_INACTIVE) {
            $this->addError(__("Company profile is inactive.", "wpjobboard"));
        } elseif(!$company->is_public) {
            $this->addInfo(__("Company profile is hidden.", "wpjobboard"));
        } elseif(!$company->isVisible()) {
            $this->addError(__("Company profile will be visible once employer will post at least one job.", "wpjobboard"));
        }

        $page = 1;
        if($this->getRequest()->get("pg") > 1) {
            $page = $this->getRequest()->get("pg");
        }
        
        $this->view = new stdClass();
        $this->view->image = "50x50";
        $this->view->company = $company;
        $this->view->param = array(
            "filter" => "active",
            "employer_id" => $company->id,
            "page" => $page
        );
        
        return $this->render("job-board", "company");
    }
}