<?php
/**
 * Description of JobBoardMenu
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_Alerts extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "alerts.php";
        $this->_viewFront = "alerts.php";
        
        $this->_defaults["smart"] = 0;
        
        parent::__construct(
            "wpjb-widget-alerts", 
            __("Job Alerts", "wpjobboard"),
            array("description"=>__("Allows to create new job alert", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
        $instance = $old_instance;
        $instance['title'] = htmlspecialchars($new_instance['title']);
        $instance['frequency_default'] = $new_instance['frequency_default'];
        $instance['frequency_change'] = (int)($new_instance['frequency_change']);
        $instance['hide'] = (int)($new_instance['hide']);
        $instance['smart'] = (int)($new_instance['smart']);
        return $instance;
    }

    public function _filter()
    {
        global $post;
        
        $content = $post->post_content;
        $smart = false;
        if(isset($this->view->param->smart) && $this->view->param->smart) {
            foreach(array("index", "category", "type", "search") as $action) {
                if(is_wpjb() && wpjb_is_routed_to($action, "frontend")) {
                    $smart = true;
                    break;
                }
                if(has_shortcode($content, "wpjb_jobs_list") || has_shortcode($content, "wpjb_jobs_search")) {
                    $smart = true;
                    break;
                }
            }
        }

        add_filter( "wpjb_form_init_alert", [$this, "form_filter"] );
        $form = new Wpjb_Form_Frontend_Alert();
        remove_filter( "wpjb_form_init_alert", [$this, "form_filter"] );
        
        $this->view->form = $form;
        $this->view->is_smart = $smart;
        $this->view->alerts = wpjb_candidate_alert_stats();
    }

    public function form_filter($form) {
        $frequency_default = 1;
        $frequency_change = 0;
        
        if(isset($this->view->param->frequency_default)) {
            $frequency_default = $this->view->param->frequency_default;
        }
        if(isset($this->view->param->frequency_change)) {
            $frequency_change = $this->view->param->frequency_change;
        }

        if($frequency_change == 1) {
            $e = $form->create("frequency", "select");
            $e->setLabel(__("Select alerts frequency", "wpjobboard"));
            $e->addOptions([
                ["key" => "opt-1", "value" => 1, "description" => __("Daily", "wpjobboard")],
                ["key" => "opt-2", "value" => 2, "description" => __("Weekly", "wpjobboard")],
            ]);
            $e->setValue($frequency_default);
            $e->addClass("wpjb-widget-alert-frequency");
            $form->addElement($e, "alert");
        } else {
            $e = $form->create("frequency", "hidden");
            $e->setValue($frequency_default);
            $e->addClass("wpjb-widget-alert-frequency");
            $form->addElement($e, "alert");
        }

        return $form;
    }

}

?>