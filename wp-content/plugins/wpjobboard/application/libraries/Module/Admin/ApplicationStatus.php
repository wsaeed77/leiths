<?php
/**
 * Description of Category
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_ApplicationStatus extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
            "redirectAction" => array(
                "accept" => array(),
                "object" => "applicationStatus"
            ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_ApplicationStatus",
                "info" => __("New application status has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("applicationStatus", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_ApplicationStatus",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
        ), "applicationStatus" );
    }
    
    public function addAction( $param = array() )
    {           
        $form = new Wpjb_Form_Admin_ApplicationStatus();
        $form->addElement($this->_getNonceField(), "hidden");
        $form->addElement($this->_getRefField(), "hidden");
        $id = false;
        
        if($this->isPost()) {
            $nonceName = $this->getNonceName();
            $nonce = wp_verify_nonce($this->_request->post("_wpjb_nonce"), $nonceName);

            if( ! $nonce ) {
                $this->_addError(__("Invalid nonce.", "wpjobboard"));
            } else {
                $isValid = $form->isValid($this->_request->getAll());
                if($isValid) {
                    $this->_addInfo( __("New application status has been created.", "wpjobboard") );
                    $form->save();
                    $id = $form->getElement( "id" )->getValue();
                } else {
                    $this->_addError( __("There are errors in your form.", "wpjobboard") );
                }
            }
        }

        $url = wpjb_admin_url( "applicationStatus", "edit", "%d" );
        $this->redirectIf($id, sprintf(str_replace("%25d", "%d", $url), $id));
        $this->view->form = $form;
    }

    protected function _multiDelete( $id )
    {
        $statuses = wpjb_get_application_status();
        $total = wpjb_count_applications($id);

        if( $total > 0 ) {
            $err = __("Cannot delete application status identified by ID #{id}. There are still applications with this application status.", "wpjobboard");
            $err = str_replace("{id}", $id, $err);
            $this->view->_flash->addError($err);
            return false;
        }

        try {
            unset( $statuses[$id] );
            
            $instance = Wpjb_Project::getInstance();
            $instance->setConfigParam( "wpjb_application_statuses", $statuses );
            $instance->saveConfig();
        } catch(Exception $e) {
            // log error
        }
        
        wp_redirect( wpjb_admin_url( "applicationStatus" ) );
        exit;
    }
    
    public function deleteAction() 
    {
        if( ! wp_verify_nonce( $this->_request->get("_wpjb_nonce"), $this->getNonceName("index") ) ) {
            wp_die(__("Invalid Nonce.", "wpjobboard"));
        }

        $id = $this->_request->getParam("id");
        
        if($this->_multiDelete($id)) {
            $m = sprintf(__("Application Status #%d deleted.", "wpjobboard"), $id);
            $this->view->_flash->addInfo($m);
        }
        wp_redirect(wpjb_admin_url("applicationStatus"));
        exit;
    }
    
    public function indexAction()
    {
        $result = wpjb_get_application_status();

        $this->view->current = 1;
        $this->view->total = 1;
        $this->view->data = $result;

        $this->view->nonce = wp_create_nonce($this->getNonceName("index"));
        $this->view->nonceName = $this->getNonceName("index");
    }
    

}

?>
