<?php
/**
 * Description of Plain
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Api_Print extends Daq_Controller_Abstract
{
    public function indexAction() {
        
        $id = absint( $_GET['id'] );
        $application = new Wpjb_Model_Application( $id );
        
        if( !$application->exists() ) {
            throw new Exception( sprintf( "Object with ID %d does not exist.", $id ) );
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        $company = $job->getCompany(true);
        
        $hasCap = current_user_can( wpjb_get_print_capability() );
        if( ! $hasCap && $company->user_id > 0 ) {
            $hasCap = absint($company->user_id) === get_current_user_id();
        }

        if( ! $hasCap ) {
            throw new Exception( __("Your account does not have capabilities required to print Applications.", "wpjobboard" ) );
        }

        $current_status = wpjb_get_application_status($application->status);
        
        $view = new Wpjb_Shortcode_Dynamic();
        $view->view->application = $application;
        $view->view->company = $company;
        $view->view->current_status = $current_status;
        $view->view->job = $job;
        
        $render = $view->render("default", "print");
        
        echo apply_filters( "wpjb_print_application", $render, $application );
        exit;       
    }
    
    public function multipleAction() {
        
        $ids = json_decode( base64_decode( $_GET['id'] ) );

        if( ! current_user_can( wpjb_get_print_capability() ) ) {
            throw new Exception( __("Your account does not have capabilities required to print Applications.", "wpjobboard" ) );
        }

        $view = new Wpjb_Shortcode_Dynamic();
        $view->view->ids = $ids;
        
        $render = $view->render("default", "print_multiple");
        
        echo $render;
        exit;      
    }

}

?>
