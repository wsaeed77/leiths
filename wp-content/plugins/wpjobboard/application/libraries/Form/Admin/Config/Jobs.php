<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Config_Jobs extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Job Board Configuration", "wpjobboard");
        
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("jobs-list", __("Jobs List", "wpjobboard"));
        
        $e = $this->create("search_bar", Daq_Form_Element::TYPE_SELECT);
        $e->setValue(Wpjb_Project::getInstance()->conf("search_bar", "disabled"));
        $e->addOption("disabled", "disabled", __("Disabled", "wpjobboard"));
        $e->addOption("enabled", "enabled", __("Enabled", "wpjobboard"));
        $e->addOption("enabled-live", "enabled-live", __("Enabled (with live search)", "wpjobboard"));
        $e->setLabel(__("Search form on jobs list", "wpjobboard"));
        $this->addElement($e, "jobs-list");
        
        $e = $this->create("front_jobs_per_page");
        $e->setRequired(true);
        $e->setValue($instance->getConfig("front_jobs_per_page", 20));
        $e->setLabel(__("Job offers per page", "wpjobboard"));
        $e->setHint(__("Number of listings per page.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(1));
        $this->addElement($e, "jobs-list");
        
        $e = $this->create("front_hide_filled", "checkbox");
        $e->setValue($instance->getConfig("front_hide_filled"));
        $e->setLabel(__("Filled Jobs", "wpjobboard"));
        $e->addOption(1, 1, __("When job is marked as filled, hide it on the jobs list.", "wpjobboard"));
        $this->addElement($e, "jobs-list");
        

        
        $e = $this->create("front_show_expired", "checkbox");
        $e->setValue($instance->getConfig("front_show_expired"));
        $e->setLabel(__("Expired Jobs", "wpjobboard"));
        $e->addOption(1, 1, __("Allow visitors to view expired jobs details pages.", "wpjobboard"));
        $this->addElement($e, "jobs-list");

        $e = $this->create("front_expired_redirect");
        $e->setRequired(false);
        $e->setValue($instance->getConfig("front_expired_redirect", ""));
        $e->setLabel(__("Redirect expired jobs", "wpjobboard"));
        $e->setHint(__("Enter an URL to which users should be redirected when entering expired job details page.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Url());
        $this->addElement($e, "jobs-list");
        
        $this->addGroup("job-details", __("Job Details", "wpjobboard"));
        
        $e = $this->create("front_marked_as_new");
        //$e->setRequired(true);
        $e->setValue($instance->getConfig("front_marked_as_new", 7));
        $e->setLabel(__("Days marked as new", "wpjobboard"));
        $e->setHint(__("Number of days since posting job will be displayed as new.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(0));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_show_related_jobs", "checkbox");
        $e->setValue($instance->getConfig("front_show_related_jobs"));
        $e->setLabel(__("Related Jobs", "wpjobboard"));
        $e->addOption(1, 1, __("Show related jobs on job details page.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_hide_apply_link", "checkbox");
        $e->setValue($instance->getConfig("front_hide_apply_link"));
        $e->setLabel(__("Apply online", "wpjobboard"));
        $e->addOption(1, 1, __("Hide 'Apply Online' button on job details page.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_hide_bookmarks", "checkbox");
        $e->setValue($instance->getConfig("front_hide_bookmarks"));
        $e->setLabel(__("Bookmarks", "wpjobboard"));
        $e->addOption(1, 1, __("Hide 'bookmark' button on job details page.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_apply_members_only", "checkbox");
        $e->setValue($instance->getConfig("front_apply_members_only"));
        $e->setLabel(__("Applications", "wpjobboard"));
        $e->addOption(1, 1, __("Only registered members can apply for jobs.", "wpjobboard"));
        $this->addElement($e, "job-details");

        $e = $this->create("show_expiration_date", "checkbox" );
        $e->setRequired(false);
        $e->setValue($instance->getConfig("show_expiration_date", false));
        $e->setLabel(__("Show Expiration Date", "wpjobboard"));
        $e->addOption( 1, 1, __("Show Expiration Date on the job details page." ) );
        $this->addElement($e, "job-details");

        $this->addGroup("dashboard", __("Employer Dashboard", "wpjobboard"));

        $e = $this->create("employer_dashboard_hide_icons", "checkbox");
        $e->setValue($instance->getConfig("employer_dashboard_hide_icons"));
        $e->setLabel(__("Hide Icons", "wpjobboard"));
        $e->setHint(__("Select which icons should be hidden in candidate dashboard", "wpjobboard"));
        $e->addOption( "job_add", "job_add", __("Post a Job", "wpjobboard") );
        $e->addOption( "employer_panel", "employer_panel", __("Listings", "wpjobboard") );
        $e->addOption( "job_applications", "job_applications", __("Applications", "wpjobboard") );
        $e->addOption( "employer_edit", "employer_edit", __("Edit Profile", "wpjobboard") );
        $e->addOption( "membership", "membership", __("Membership", "wpjobboard") );
        $e->addOption( "payment_history", "payment_history", __("Payment History", "wpjobboard") );
        $e->addOption( "employer_logout", "employer_logout", __("Logout", "wpjobboard") );
        $e->addOption( "employer_password", "employer_password", __("Change Password", "wpjobboard") );
        $e->addOption( "employer_delete", "employer_delete", __("Delete Account", "wpjobboard") );
        $e->setMaxChoices(15);
        $this->addElement($e, "dashboard");

        $e = $this->create("front_allow_edition", "checkbox");
        $e->setValue($instance->getConfig("front_allow_edition"));
        $e->setLabel(__("Job Edition", "wpjobboard"));
        $e->addOption(1, 1, __("Allow Employers to edit their job listings.", "wpjobboard"));
        $this->addElement($e, "dashboard");

        $e = $this->create("allow_set_expiration_date_edit", "checkbox" );
        $e->setRequired(false);
        $e->setValue($instance->getConfig("allow_set_expiration_date_edit", false));
        $e->setLabel(__("Edit Expiration Date", "wpjobboard"));
        $e->addOption( 1, 1, __("Allow employers to change expiration date while editing a job from Employer Panel.", "wpjobboard" ) );
        $this->addElement($e, "dashboard");
        
        $this->addGroup("jobs-add", __("Jobs Publishing", "wpjobboard"));

        $e = $this->create("posting_allow", Daq_Form_Element::TYPE_SELECT);
        $e->setValue(Wpjb_Project::getInstance()->conf("posting_allow"));
        $e->addOption(1, 1, __("Anyone", "wpjobboard"));
        $e->addOption(2, 2, __("Employers", "wpjobboard"));
        $e->addOption(4, 4, __("Employers with Membership", "wpjobboard"));
        $e->addOption(3, 3, __("Administrators", "wpjobboard"));
        $e->setLabel(__("Who Can Post Jobs", "wpjobboard"));
        $this->addElement($e, "jobs-add");

        $e = $this->create("posting_moderation", Daq_Form_Element::TYPE_CHECKBOX);
        $e->setValue($instance->getConfig("posting_moderation"));
        $e->addOption(1, 1, __("Free Jobs.", "wpjobboard"));
        $e->addOption(2, 2, __("Paid Jobs.", "wpjobboard"));
        $e->addOption(3, 3, __("Package Jobs.", "wpjobboard"));
        $e->setLabel(__("Hold For Moderation", "wpjobboard"));
        $this->addElement($e, "jobs-add");

        $e = $this->create("default_job_duration");
        $e->setRequired(false);
        $e->setValue($instance->getConfig("default_job_duration", 30));
        $e->setLabel(__("Default Duration", "wpjobboard"));
        $e->setHint(__("How many days the job will will be displayed (when no listing type selected).", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(0));
        $this->addElement($e, "jobs-add");

        $e = $this->create("allow_set_expiration_date", "checkbox" );
        $e->setRequired(false);
        $e->setValue($instance->getConfig("allow_set_expiration_date", false));
        $e->setLabel(__("Set Expiration Date", "wpjobboard"));
        $e->addOption( 1, 1, __("Allow Employers to set an expiration date while publishing a job." , "wpjobboard") );
        $this->addElement($e, "jobs-add");

        
        $this->addGroup("moderation", __("Employers Moderation", "wpjobboard"));
        
        $e = $this->create("employer_login_only_approved", "checkbox");
        $e->setValue($instance->getConfig("employer_login_only_approved"));
        $e->setLabel(__("Moderation", "wpjobboard"));
        $e->addOption("1", "1", __("Only approved members can login.", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $e = $this->create("employer_approval", "select");
        $e->setValue($instance->getConfig("employer_approval"));
        $e->setLabel(__("Employer Approval", "wpjobboard"));
        $e->setHint("");
        $e->addValidator(new Daq_Validate_InArray(array(0,1)));
        $e->addOption("0", "0", __("Instant", "wpjobboard"));
        $e->addOption(1, 1, __("By Administrator", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $e = $this->create("employer_is_public", "select");
        $e->setValue($instance->getConfig("employer_is_public", 1));
        $e->setLabel(__("Employer Default Visibility", "wpjobboard"));
        $e->setHint("");
        $e->addValidator(new Daq_Validate_InArray(array(0,1)));
        $e->addOption("0", "0", __("Private (not displayed on employers list)", "wpjobboard"));
        $e->addOption("1", "1", __("Public (visible on employers list)", "wpjobboard"));
        $this->addElement($e, "moderation");

        $this->addGroup("membership", __("Membership Defaults", "wpjobboard"));

        $pages = pages_with_shortcode( 'wpjb_employers_list' );
        if( isset( $pages[0]) ) {
            $company_list_page_id = $pages[0]->ID;
        } else {
            $company_list_page_id = 0;
        }
        
        
        $e = $this->create("employer_members_restricted_pages", "select");
        $e->setValue($instance->getConfig("employer_members_restricted_pages"));
        $e->setLabel(__("Restricted Pages", "wpjobboard"));
        $e->setHint(__("What pages should have restricted access for candidates.", "wpjobboard"));
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
        $this->addElement($e, "membership");
        
        $e = $this->create("employer_members_have_access", "select");
        $e->setValue($instance->getConfig("employer_members_have_access"));
        $e->setLabel(__("Access", "wpjobboard"));
        $e->setHint(__("What user types have access to restricted pages", "wpjobboard"));
        $e->addOption("0", "0", __("Anyone", "wpjobboard"));
        $e->addOption("1", "1", __("Only registered employers", "wpjobboard"));
        $e->addOption("2", "2", __("Only premium employers.", "wpjobboard"));
        $this->addElement($e, "membership");

    }
}

?>