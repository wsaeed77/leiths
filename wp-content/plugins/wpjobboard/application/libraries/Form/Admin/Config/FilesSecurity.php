<?php

class Wpjb_Form_Admin_Config_FilesSecurity extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Files Security", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "Server Information", "wpjobboard" ) );

        $e = $this->create("_server", "label");
        $e->setLabel(__("Server", "wpjobboard"));
        $e->setDescription($this->getServerInfo());
        $this->addElement($e, "default");
        
        $this->addGroup( "security", __( "Secure Folder and Hashing", "wpjobboard" ) );

        $e = $this->create("fs_enabled", "checkbox");
        $e->setValue($instance->getConfig("fs_enabled"));
        $e->setLabel(__("Files Security", "wpjobboard"));
        $e->addOption("1", "1", __("Enable files protection.", "wpjobboard"));
        $e->setBoolean(true);
        $this->addElement($e, "security");

        $e = $this->create("fs_folder");
        $e->setValue($instance->getConfig("fs_folder"));
        $e->setLabel(__("Secure Folder Path", "wpjobboard"));
        $e->setHint(__("Use a custom folder to protect uploaded files.", "wpjobboard"));
        $this->addElement($e, "security");

        $e = $this->create("fs_hash_path", "checkbox");
        $e->setValue($instance->getConfig("fs_hash_path"));
        $e->setLabel(__("Enable Hashing", "wpjobboard"));
        $e->addOption("1", "1", __("Hash files path to obfuscate real paths.", "wpjobboard"));
        $e->setBoolean(true);
        $this->addElement($e, "security");

        $e = $this->create("fs_hash_key");
        $e->setValue($instance->getConfig("fs_hash_key"));
        $e->setLabel(__("Hashing Key", "wpjobboard"));
        $e->addClass("js-wpjb-admin-generate-hash-input");
        $e->setRenderer(array($this, "randomize"));
        $this->addElement($e, "security");

        $this->addGroup( "data", __("Data", "wpjobboard"));

        $e = $this->create("fs_secure_job", "checkbox");
        $e->setValue($instance->getConfig("fs_secure_job"));
        $e->setLabel(__("Jobs", "wpjobboard"));
        $e->addOption("1", "1", __("Secure Job files", "wpjobboard"));
        $e->setBoolean(true);
        $this->addElement($e, "data");

        $e = $this->create("fs_secure_job_exclude");
        $e->setValue($instance->getConfig("fs_secure_job_exclude"));
        $e->setLabel(__("Exclude Job Fields", "wpjobboard"));
        $e->setHint(__("List file upload fields that does not need to be protected (separate field names with coma).", "wpjobboard"));
        $this->addElement($e, "data");        
        
        $e = $this->create("fs_secure_application", "checkbox");
        $e->setValue($instance->getConfig("fs_secure_application"));
        $e->setLabel(__("Applications", "wpjobboard"));
        $e->addOption("1", "1", __("Secure Application files", "wpjobboard"));
        $e->setBoolean(true);
        $this->addElement($e, "data");

        $e = $this->create("fs_secure_application_exclude");
        $e->setValue($instance->getConfig("fs_secure_application_exclude"));
        $e->setLabel(__("Exclude Application Fields", "wpjobboard"));
        $e->setHint(__("List file upload fields that does not need to be protected (separate field names with coma).", "wpjobboard"));
        $this->addElement($e, "data");

        $e = $this->create("fs_secure_resume", "checkbox");
        $e->setValue($instance->getConfig("fs_secure_resume"));
        $e->setLabel(__("Resumes", "wpjobboard"));
        $e->addOption("1", "1", __("Secure Resume files", "wpjobboard"));
        $e->setBoolean(true);
        $this->addElement($e, "data");

        $e = $this->create("fs_secure_resume_exclude");
        $e->setValue($instance->getConfig("fs_secure_resume_exclude"));
        $e->setLabel(__("Exclude Resume Fields", "wpjobboard"));
        $e->setHint(__("List file upload fields that does not need to be protected (separate field names with coma).", "wpjobboard"));
        $this->addElement($e, "data");        
        
        $e = $this->create("fs_secure_company", "checkbox");
        $e->setValue($instance->getConfig("fs_secure_company"));
        $e->setLabel(__("Employers", "wpjobboard"));
        $e->addOption("1", "1", __("Secure Employer files", "wpjobboard"));
        $e->setBoolean(true);
        $this->addElement($e, "data");

        $e = $this->create("fs_secure_company_exclude");
        $e->setValue($instance->getConfig("fs_secure_company_exclude"));
        $e->setLabel(__("Exclude Employer Fields", "wpjobboard"));
        $e->setHint(__("List file upload fields that does not need to be protected (separate field names with coma).", "wpjobboard"));
        $this->addElement($e, "data");

        apply_filters("wpja_form_init_config_files_security", $this);

    }

    public function randomize($field, $form) {
        $html = new Daq_Helper_Html("input", array(
            "type" => "submit",
            "name" => "generate",
            "class" => "button-secondary js-wpjb-admin-generate-hash-button",
            "value" =>  __("Generate", "wpjobboard")
        ));
        $html->forceLongClosing(false);
        
        $loader = '<span class="js-wpjb-admin-generate-hash-loader"><img src="'. admin_url() . '/images/wpspin_light.gif" alt="" /></span>';

        return $field->render() . $html . $loader;
        

    }

    public function getServerInfo() {


        if(isset($_SERVER['SERVER_SOFTWARE'])) {
            $ss = $_SERVER['SERVER_SOFTWARE'];
        } else {
            $ss = "";
        }

        $isApache = stripos( $ss, 'Apache' ) !== false;

        if( $isApache ) {
            $text = __( "<h1>All Good!</h1><p>Your files are being natively protected with the Apache mod-rewrite module.</p><p>You do <strong>not</strong> need to make any changes in the Files Security section.</p>", "wpjobboard");
            $text.= '<p><a href="#" class="button-secondary js-wpjb-admin-show-settings">' . __("Show settings anyway ...", "wpjobboard" ) . '</a></p>';
        } else {
            $text = __( "<h1>File are not being protected!</h1><p>Your application files are not protected, secure them now using fields below.</p>", "wpjobboard" );
            $text.= '<p><a href="https://wpjobboard.net/kb/files-security/" target="_blank" class="button-secondary">' . __("View documentation ...", "wpjobboard" ) . '</a></p>';
        }

        return $text;
    }
}

?>