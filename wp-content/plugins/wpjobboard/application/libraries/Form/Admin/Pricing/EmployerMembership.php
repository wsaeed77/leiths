<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmployerMembership
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Pricing_EmployerMembership extends Wpjb_Form_Admin_Pricing
{

    public function init()
    {
        $this->addGroup( "membership", __( "Membership Features", "wpjobboard" ), 2 );
        $this->addGroup( "recurring", __( "Subscription Settings", "wpjobboard" ), 2 );
        
        $e = $this->create("price_for", "hidden");
        $e->setValue(Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);
        $this->addElement($e);
        
        $e = $this->create("is_trial", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Trial", "wpjobboard"));
        $e->addOption("1", "1", __("Automatically apply this membership to every newly registered Employer.", "wpjobboard"));
        $e->setOrder(101);
        $e->setValue($this->getObject()->meta->is_trial->value());
        $this->addElement($e, "membership");
        
        $e = $this->create("is_featured", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Featured", "wpjobboard"));
        $e->addOption("1", "1", __("Featured membership has additional class on membership pricing page, that allows you to distinguish most popular membership.", "wpjobboard"));
        $e->setOrder(102);
        $e->setValue($this->getObject()->meta->is_featured->value());
        $this->addElement($e, "membership");
        
        $e = $this->create("visible");
        /* @var $e Daq_Form_Element */
        //$e->setRequired(true);
        $e->setValue($this->_object->meta->visible);
        $e->setLabel(__("Recurrence", "wpjobboard"));
        $e->addFilter(new Daq_Filter_NotNegativeInt());
        $e->setHint(__("How many days the membership will be valid (0 for Never Expire).", "wpjobboard"));
        $e->setBuiltin(false);
        //$e->setRequired(true);
        $e->setOrder(104);
        $this->addElement($e, "membership");
        
        $data = maybe_unserialize($this->getObject()->meta->package->value());
        
        $price = array(
            array(
                "title" => __("Job Posting", "wpjobboard"),
                "price_for" => Wpjb_Model_Pricing::PRICE_SINGLE_JOB,
                "hint" => __("Select which Job Postings will be included in this package and how many times Employer will be able to use them.", "wpjobboard"),
                "value" => $data[Wpjb_Model_Pricing::PRICE_SINGLE_JOB] ?? 0,
                
            ),
            array(
                "title" => __("Resumes Access", "wpjobboard"),
                "price_for" => Wpjb_Model_Pricing::PRICE_SINGLE_RESUME,
                "hint" => "",
                "value" => $data[Wpjb_Model_Pricing::PRICE_SINGLE_RESUME] ?? 0,
            ),
        );
        
        foreach($price as $p) {
            
            $pfor = $p["price_for"];
            
            $query = new Daq_Db_Query();
            $query->from("Wpjb_Model_Pricing t");
            $query->where("price_for = ?", $pfor);
            
            
            $e = $this->create("items_".$pfor, "checkbox");
            $e->setLabel($p["title"]);
            $e->setHint($p["hint"]);
            foreach($query->execute() as $item) {
                $e->addOption($item->id, $item->id, $item->title);
            }
            $e->setRenderer("wpjb_admin_pricing_render");
            $e->setOrder(105);
            $e->setMaxChoices(100);
            $e->setValue($p["value"]);
            $this->addElement($e, "membership");
            
            $e = $this->create("items_".$pfor."_usage", "checkbox");
            $this->addElement($e, "_internal");
        }

        $instance = Wpjb_Project::getInstance();
        
        $pages = pages_with_shortcode( 'wpjb_employers_list' );
        if(is_array($pages) && isset($pages[0])) {
            $company_list_page_id = $pages[0]->ID;
        } else {
            $company_list_page_id = 0;
        }

        $e = $this->create("have_access", "select");
        if( isset( $this->getObject()->meta->have_access ) && $this->getObject()->meta->have_access->value() ) {
            $e->setValue( explode( ",", $this->getObject()->meta->have_access->value() ) );
        }
        $e->setLabel(__("Have Access", "wpjobboard"));
        $e->setHint(__("Employer will have access to selected pages (only if restricted by config)", "wpjobboard"));
        $e->addOption( $instance->getConfig("urls_link_job"), $instance->getConfig("urls_link_job"), __("Jobs List", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_job_search"), $instance->getConfig("urls_link_job_search"), __("Jobs Search", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_job_add"), $instance->getConfig("urls_link_job_add"), __("Jobs Add", "wpjobboard" ) );
        $e->addOption( 'job', 'job', __("Job Details", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_resume"), $instance->getConfig("urls_link_resume"), __("Resumes List", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_resume_search"), $instance->getConfig("urls_link_resume_search"), __("Resumes Search", "wpjobboard" ) );
        $e->addOption( 'resume', 'resume', __("Resume Details", "wpjobboard" ) );
        if( $company_list_page_id > 0 ) {
            $e->addOption( $company_list_page_id, $company_list_page_id, __("Company List", "wpjobboard" ) );
        }
        $e->addOption( 'company', 'company', __("Company Details", "wpjobboard" ) );
        $e->setMaxChoices(15);
        $e->setOrder(105);
        $this->addElement($e, "membership");
        
        $e = $this->create( "is_recurring_warning", "label" );
        $e->setLabel( __( "Recurring Warning", "wpjobboard" ) );
        $e->setHint( __( "Recurring plans in WPJobBoard work only with Stripe!", "wpjobboard" ) );
        $this->addElement( $e, "recurring" );
        
        $e = $this->create( "is_recurring_info", "label" );
        $e->setLabel( __( "Recurring Information", "wpjobboard" ) );
        $e->setHint( __( "Recurring plans can't be updated because of Stripe policy. The only way to update a plan is to remove it and create it again. When you remove the plan, all users who subscribed to it will lose their subscription. To remove a subscription, uncheck the 'Recurring' checkbox and save pricing.", "wpjobboard" ) );
        $this->addElement( $e, "recurring" );
        
        $e = $this->create( "is_recurring", "checkbox" );
        $e->setBoolean( true );
        $e->setLabel( __( "Recurring", "wpjobboard" ) );
        $e->addOption( "1", "1", __("Recurring membership will charge the user every selected period until he resigns.", "wpjobboard" ) );
        $e->setOrder(103);
        $e->setValue( $this->getObject()->meta->is_recurring->values() );
        $this->addElement( $e, "recurring" );
        
        /*$e = $this->create( "is_recurring_interval", "select" );
        $e->setLabel( __( "Interval", "wpjobboard" ) );
        $e->setHint( __( "How often user with this membership should be charged", "wpjobboard" ) );
        $e->addOption( "day", "day", __("Day", "wpjobboard" ) );
        $e->addOption( "week", "week", __("Week", "wpjobboard" ) );
        $e->addOption( "month", "month", __("Month", "wpjobboard" ) );
        $e->addOption( "year", "year", __("Year", "wpjobboard" ) );
        $e->setOrder(103);
        $e->setValue( $this->getObject()->meta->is_recurring_interval->value() );
        $this->addElement( $e, "recurring" );
        
        $e = $this->create( "is_recurring_interval_count", "text" );
        $e->setLabel( __( "Interval Count", "wpjobboard" ) );
        $e->setHint( __( "Customize interval period. Default is 1. Max values for: year = 1, month = 12, week = 52", "wpjobboard" ) );
        $e->setOrder(103);
        $e->addValidator( new Daq_Validate_PaymentInterval );
        if( $this->getObject()->meta->is_recurring_interval_count->value() != null ) {
            $e->setValue( $this->getObject()->meta->is_recurring_interval_count->value() );
        } else {
            $e->setValue( 1 );
        }
        $this->addElement( $e, "recurring" );*/
        
        parent::init();
        
        if( isset( $this->getObject()->meta->is_recurring ) && $this->getObject()->meta->is_recurring->value() == 1) {
            $this->getElement('price')->setAttr('readonly', 'readonly');
            $this->getElement('title')->setAttr('readonly', 'readonly');
            $this->getElement('currency')->setAttr('readonly', 'readonly');
            $this->getElement('visible')->setAttr('readonly', 'readonly');
        }
        
    }
    
    public function save($append = array())
    {
        parent::save($append);
        
        $object = $this->getObject();
        
        $data = array(
            Wpjb_Model_Pricing::PRICE_SINGLE_JOB => array(),
            Wpjb_Model_Pricing::PRICE_SINGLE_RESUME => array(),
        );
        
        foreach(array_keys($data) as $key) {
            $post = $this->value("items_".$key);
            foreach((array)$post as $id => $usage) {
                if($usage["status"] != "disabled") {
                    $data[$key][$id] = $usage;
                }
            }
        }
        $data["have_access"] = $this->value( "have_access");
        
        $meta = $object->meta->package->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = serialize($data);
        $meta->save();
        
        $meta = $object->meta->is_trial->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = absint($this->value("is_trial"));
        $meta->save();
        
        $meta = $object->meta->is_featured->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = absint($this->value("is_featured"));
        $meta->save();
        
        $meta = $object->meta->is_recurring->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = absint($this->value("is_recurring"));
        $meta->save();

        $meta = $object->meta->have_access->getFirst();
        $meta->object_id = $object->getId();
        if( is_array( $this->value("have_access") ) ) {
            $meta->value = implode(",", $this->value( "have_access" ) );
        } else {
            $meta->value = $this->value( "have_access" );
        }
        $meta->save();
        
        /*$meta = $object->meta->is_recurring_interval->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = $this->value("is_recurring_interval");
        $meta->save();
        
        $meta = $object->meta->is_recurring_interval_count->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = absint($this->value("is_recurring_interval_count"));
        $meta->save();*/
    }
    
}

?>
