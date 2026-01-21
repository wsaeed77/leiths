<?php
/**
 * Description of Resume
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Resume extends Wpjb_Form_Abstract_Resume
{
    public function init() 
    {
        parent::init();
        
        if($this->isNew()) {
            $slug = "";
        } elseif(Wpjb_Project::getInstance()->env("uses_cpt")) {
            wp_enqueue_script("wpjb-admin-user-suggest");
            $post = get_post($this->_object->post_id);
            $slug = $post->post_name;
        } else {
            wp_enqueue_script("wpjb-admin-user-suggest");
            $slug = $this->_object->candidate_slug;
        }

        $e = $this->create("_user_id", "hidden");
        $e->setValue(Daq_Request::getInstance()->post("_user_id"));
        $this->addElement($e, "_internal");
        
        $e = $this->create("candidate_slug", "hidden");
        $e->addClass("wpjb-slug-base");
        $e->setValue($slug);
        $this->addElement($e, "_internal");
        
        $e = $this->create("_slug_type", "hidden");
        $e->setValue("resume");
        $e->addClass("wpjb-slug-type");
        $this->addElement($e, "_internal");
        
        $this->initDetails();
        
        add_filter("wpja_form_init_resume", array($this, "apply"), 9);
        apply_filters("wpja_form_init_resume", $this);
    }
    
    public function save($append = array())
    {
        if( $this->value("_user_id") > 0 ) {
            $append["user_id"] = $this->value("_user_id");
        }

        parent::save($append);
        
        $this->saveDetails();
        
        $form_code = Daq_Request::getInstance()->post( "form_code", null );
        Wpjb_Model_MetaValue::import( "resume", "form_code", $form_code, $this->getId() );
        
        do_action("wpjb_resume_saved", $this->getObject());
        apply_filters("wpja_form_save_resume", $this);
    }
    
}

?>