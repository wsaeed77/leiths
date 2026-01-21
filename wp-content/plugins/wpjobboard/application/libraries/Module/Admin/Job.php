<?php
/**
 * Description of Index
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Job extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->view->slot("logo", "job_board.png");
        
        
       $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array("filter", "posted", "query"),
               "object" => "job"
           ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_AddJob",
                "info" => __("New job has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("job", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_AddJob",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Job",
                "info" => __("Job deleted.", "wpjobboard"),
                "error" => __("Job could not be deleted.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted jobs: {success}", "wpjobboard")
                ),
                "activate" => array(
                    "success" => __("Number of activated jobs: {success}", "wpjobboard")
                ),
                "deactivate" => array(
                    "success" => __("Number of deactivated jobs: {success}", "wpjobboard")
                ),
                "approve" => array(
                    "success" => __("Number of approved jobs: {success}", "wpjobboard")
                ),
                "read" => array(
                    "success" => __("Number of jobs marked as read: {success}", "wpjobboard")
                ),
                "unread" => array(
                    "success" => __("Number of jobs marked as unread: {success}", "wpjobboard")
                )
            ),
            /*"_multiDelete" => array(
                "model" => "Wpjb_Model_Job"
            )*/
        ), "job" );
    }

    public function slug($object = null)
    {
        global $wp_rewrite;

        $instance = Wpjb_Project::getInstance();
        $pedit = '<span id="editable-post-name" title="Click to edit this part of the permalink">[slug]</span>';
        $shortlink = null;
        
        if($object) {
            $permalink = $object->url();
        } else {
            $permalink = null;
            $object = new stdClass();
            $object->post_id = null;
            $object->job_slug = null;
        }

        if(!get_option('permalink_structure')) {
            $url = wpjb_link_to("job", $object);
            $slug = null;
        } elseif($instance->env("uses_cpt")) {
            $post = get_post($object->post_id);
            
            if($post) {
                $shortlink = wp_get_shortlink($post->ID, 'post');
                $slug = urldecode($post->post_name);
            } else {
                $slug = null;
            }
            
            $pstruct = $wp_rewrite->get_extra_permastruct("job");
            $purl = home_url( user_trailingslashit($pstruct) );

            $url = str_replace("%job%", $pedit, $purl);
        } else {
            $model = new Wpjb_Model_Job();
            $model->job_slug = $pedit;
            $slug = $object->job_slug;
            $url = wpjb_link_to("job", $model);
        }

        if(stripos($url, "[slug]") !== false) {
            $this->view->url = str_replace("[slug]", (string)$slug, $url);
        } else {
            $this->view->url = $url;
        }
        
        $this->view->permalink = $permalink;
        $this->view->shortlink = $shortlink;
        $this->view->slug = $slug;
    }
    
    public function addAction($param = array())
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Pricing t");
        $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_SINGLE_JOB);
        $query->where("is_active = 1");
        $list = $query->execute();
        $pricing = array(array("id"=>0, "title"=>__("Custom", "wpjobboard")));
        foreach($list as $p) {
            $pricing[] = array(
                "id" => $p->id,
                "title" => $p->title,
                "amount" => $p->amount,
                "currency" => $p->currency,
                "visible" => $p->meta->visible->getFirst()->value,
                "is_featured" => $p->meta->is_featured
            );
        }  

        $this->view->pricing = $pricing;
        extract($this->_virtual[__FUNCTION__]);
        $form = new $form();
        $form->addElement($this->_getNonceField(), "hidden");
        $form->addElement($this->_getRefField(), "hidden");
        $job = new Wpjb_Model_Job();
        
        if($this->_request->getParam("clone_id") > 0) {
            $job = new Wpjb_Model_Job($this->_request->getParam("clone_id") );

            if( isset( $job->meta->form_code ) && $job->meta->form_code->value() ) { 
                $tmpForm = new $form( $this->_request->getParam("clone_id"), array( "custom" => $job->meta->form_code->value() ) );
                $form = new $form( null, array( "custom" => $job->meta->form_code->value() ) );
                $form->addElement($this->_getNonceField(), "hidden");
                $form->addElement($this->_getRefField(), "hidden");
            } else {
                $tmpForm = new $form( $this->_request->getParam("clone_id") );
            }
            $tmpForm->addElement($this->_getNonceField(), "hidden");
            $tmpForm->addElement($this->_getRefField(), "hidden");

            foreach($tmpForm->getFields() as $field) {
                if( !in_array( $field->getName(), array('job_slug', 'id') ) && $form->hasElement($field->getName()) ) {
                    $form->getElement($field->getName())->setValue($field->getValue());
                }
                
                if( $field->getType() == 'file') {
                    // Clone Images
                    
                    $source = wpjb_upload_dir('job', str_replace("_", '-', $field->getName()), $job->id);
                    $dest = wpjb_upload_dir('job', str_replace("_", '-', $field->getName()), null);
                    
                    if(is_dir($source['basedir'])) {
                        $dir = opendir($source['basedir']); 
                        if( !is_dir( $dest['basedir'] ) ) {
                            wp_mkdir_p( $dest['basedir'] );
                        }
                        while(false !== ( $file = readdir($dir)) ) { 
                            if (( $file != '.' ) && ( $file != '..' )) { 
                                copy($source['basedir'] . '/' . $file, $dest['basedir'] . '/' . $file); 
                            } 
                        } 
                        closedir($dir); 
                    }
                }
            }

            if( $job->job_expires_at == "9999-12-31" ) {
                $form->getElement( "job_expires_at" )->setValue( "9999-12-31" );
            }
        }
        
        $id = false;
        
        if($this->isPost()) {
            $nonceName = $this->getNonceName();
            $nonce = wp_verify_nonce($this->_request->post("_wpjb_nonce"), $nonceName);

            if( ! $nonce ) {
                $this->_addError(__("Invalid nonce.", "wpjobboard"));
            } else {
                $isValid = $form->isValid($this->_request->getAll());
                if($isValid) {
                    $this->_addInfo($info);
                    $id = $form->save();
                    if(!$id) {
                        $id = $form->getId();
                    }
                } else {
                    $this->_addError($error);
                }
            }
        }
        
        $this->slug($job);
        $this->view->form = $form;
        $this->redirectIf($id, sprintf(str_replace("%25d", "%d", $url), $id));
    }
    
    public function editAction()
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Pricing t");
        $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_SINGLE_JOB);
        $query->where("is_active = 1");
        $list = $query->execute();
        $pricing = array(array("id"=>0, "title"=>__("Custom", "wpjobboard")));
        foreach($list as $p) {
            $pricing[] = array(
                "id" => $p->id,
                "title" => $p->title,
                "amount" => $p->amount,
                "currency" => $p->currency,
                "visible" => $p->meta->visible->getFirst()->value,
                "is_featured" => $p->meta->is_featured
            );
        }
        $this->view->pricing = $pricing;
        
        extract($this->_virtual[__FUNCTION__]);
        
        $job = new Wpjb_Model_Job($this->_request->getParam("id"));
        $this->view->payment = $job->getPayment(true);
        
        if($job->read != 1) {
            $job->read = 1;
            $job->save();
        }
        if(wpjb_conf("uses_cpt") && !$job->post_id) {
            $job->cpt();
            $job->save();
        }


        $approved = (int)$job->is_approved;

        $form = new $form($this->_request->getParam("id"));
        $form->addElement($this->_getNonceField(), "hidden");
        $form->addElement($this->_getRefField(), "hidden");
        if($this->isPost()) {
            $nonceName = $this->getNonceName();
            $nonce = wp_verify_nonce($this->_request->post("_wpjb_nonce"), $nonceName);

            if( ! $nonce ) {
                $this->_addError(__("Invalid nonce.", "wpjobboard"));
            } else {
                $isValid = $form->isValid($this->_request->getAll());
                if($isValid) {
                    $this->_addInfo($info);
                    $form->save();
                    if(!$approved && $form->getObject()->is_approved) {
                        $this->_approve($form->getObject());
                    }
                    $form = new $form($this->_request->getParam("id")); // Reload form for case where form was changed
                    $form->addElement($this->_getNonceField(), "hidden");
                    $form->addElement($this->_getRefField(), "hidden");
                } else {
                    $this->_addError($error);
                }
            }
        }
        $this->slug($job);
        $this->view->form = $form;
    }

    public function markaspaidAction()
    {
        if( ! wp_verify_nonce( $this->_request->get("_wpjb_nonce"), $this->getNonceName("index") ) ) {
            wp_die(__("Invalid Nonce.", "wpjobboard"));
        }

        $id = $this->_request->getParam("id");
        
        $job = new Wpjb_Model_Job($id);
        
        if(!$job->exists()) {
            $this->view->_flash->addError(__("Job does not exist.", "wpjobboard"));

            wp_redirect(wpjb_admin_url("job", "edit", $job->id));
            exit;
        }
        
        $payment = $job->getPayment(true);
        $payment->payment_paid = $payment->payment_sum;
        $payment->paid_at = date("Y-m-d H:i:s");
        $payment->is_valid = 1;
        $payment->save();
        
        $this->view->_flash->addInfo(__("Job has been marked as paid.", "wpjobboard"));
        
        wp_redirect(wpjb_admin_url("job", "edit", $job->id));
        exit;
    }
    
    public function deleteAction() 
    {
        if( ! wp_verify_nonce( $this->_request->get("_wpjb_nonce"), $this->getNonceName("index") ) ) {
            wp_die(__("Invalid Nonce.", "wpjobboard"));
        }

        $id = $this->_request->getParam("id");
        $job = new Wpjb_Model_Job($id);
        $job_title = $job->job_title;

        if($this->_multiDelete($id)) {
            $m = sprintf(__("Job '%s' deleted.", "wpjobboard"), $job_title);
            $this->view->_flash->addInfo($m);
        }
        
        wp_redirect(wpjb_admin_url("job"));
        exit;
    }

    /**
     * Job list action
     * 
     * Allows to search jobs by:
     * - status
     * - title/description
     * - category
     * - job type
     * - sort and order 
     * - page
     */
    public function indexAction()
    {
        global $wpdb;

        
        $screen = new Wpjb_Utility_ScreenOptions();
        
        $this->view->screen = $screen;
        $q = sanitize_text_field( $this->_request->get("query") );
        
        if($this->_request->get("employer")) {
            $q .= " employer_id:".$this->_request->get("employer");
        }
        
        $param = array(
            "filter" => "all",
            "location" => "",
            "posted" => "",
            "sort" => "",
            "order" => ""
        );

        $this->view->rquery = $this->readableQuery($q);
        
        $query = array_merge($param, $this->deriveParams($q, new Wpjb_Model_Job));
        
        if($this->_request->get("filter")) {
            $query["filter"] = $this->_request->get("filter");
        }

        $sort_is_set = false;
        if( $this->_request->get( "sort" ) && in_array( strtolower( $this->_request->get("sort") ), apply_filters( "wpjb_sort_jobs_allowed_fields", array( "job_title", "job_expires_at", "job_created_at" ) ) ) ) {
            $sort = esc_sql($this->_request->get("sort")  );
            $param["sort"] = $sort;
            $query["sort_order"] = "t1.".$sort;
            $sort_is_set = true;
        } elseif( apply_filters( "wpjb_sort_jobs_allow_unsafe_sort", false ) ) {
            $sort = esc_sql($this->_request->get("sort")  );
            $query["sort_order"] = $sort;
        } else {
            $query["sort_order"] = "t1.job_created_at DESC, t1.id DESC";
        }

        if( $sort_is_set && $this->_request->get("order")  && in_array( strtolower( $this->_request->get("order") ), array( "asc", "desc" ) ) ) {
            $order = esc_sql($this->_request->get("order"));
            $param["order"] = $order;
            $query["sort_order"] .= " ".$order;
        } 

        if($this->_request->get("posted")) {
            $p = $this->_request->get("posted");
            $query["date_from"] = date("Y-m-01", strtotime($p));
            $query["date_to"] = date("Y-m-t", strtotime($query["date_from"]));
            $this->view->posted = $p;
        }

        if($this->_request->get("p")) {
            $query["page"] = $this->_request->get("p");
        } else {
            $query["page"] = 1;
        }

        $name = new Wpjb_Model_Job();
        $name = $name->tableName();
        /* @var $wpdb wpdb */
        $result = $wpdb->get_results("
            SELECT DATE_FORMAT(job_created_at, '%Y-%m') as dt
            FROM $name GROUP BY dt ORDER BY job_created_at DESC
        ");

        $months = array();
        foreach($result as $r) {
            $months[$r->dt] = date("Y, F", strtotime($r->dt));
        }

        $this->view->months = $months;

        foreach($param as $k => $v) {
            $param[$k] = $this->_request->get($k, $v);
        }
        
        $query["count"] = $screen->get("job", "count", 20);
        $query["hide_filled"] = false;
        
        $param["query"] = $q;

        $forms = get_option( "wpjb_forms_list" ); 
        if( is_array( $forms ) && isset( $forms['job'] ) ) {
            $forms = $forms['job'];
        } else {
            $forms = array();
        }
        $default_form = wpjb_get_default_form( "job" );


        $this->view->result = Wpjb_Model_JobSearch::search($query);

        $this->view->param = $param;
        $this->view->filter = $param["filter"];
        $this->view->query = $q;
        $this->view->sort = $param["sort"];
        $this->view->order = $param["order"];
        $this->view->posted = $param["posted"];
        $this->view->forms = $forms;
        $this->view->default_form = $default_form;
        
        $stat = new stdClass();
        foreach(array("all", "active", "unread", "expired", "expiring", "awaiting", "inactive") as $f) {
            $stat->$f = Wpjb_Model_JobSearch::search(array_merge($query, array(
                "filter"=>$f,
                "count_only"=>1
            )));
        }
        $this->view->stat = $stat;
        $this->view->nonce = wp_create_nonce($this->getNonceName("index"));
        $this->view->nonceName = $this->getNonceName("index");
    }
    
    public function exportAction() 
    {
        global $wpdb;

        $q = sanitize_text_field( $this->_request->get("query") );
        
        if($this->_request->get("employer")) {
            $q .= " employer_id:".$this->_request->get("employer");
        }
        
        $param = array(
            "filter" => "all",
            "location" => "",
            "posted" => "",
            "sort" => "",
            "order" => ""
        );

        $this->view->rquery = $this->readableQuery($q);
        
        $query = array_merge($param, $this->deriveParams($q, new Wpjb_Model_Job));
        
        if($this->_request->get("filter")) {
            $query["filter"] = $this->_request->get("filter");
        }
        if($this->_request->get("sort")) {
            $sort = esc_sql($this->_request->get("sort"));
            $query["sort_order"] = "t1.".$sort;
        } else {
            $query["sort_order"] = "t1.job_created_at DESC, t1.id DESC";
        }

        if($this->_request->get("order")) {
            $order = esc_sql($this->_request->get("order"));
            $query["sort_order"] .= " ".$order;
        }

        if($this->_request->get("posted")) {
            $p = $this->_request->get("posted");
            $query["date_from"] = date("Y-m-01", strtotime($p));
            $query["date_to"] = date("Y-m-t", strtotime($query["date_from"]));
        }

        if($this->_request->get("p")) {
            $query["page"] = $this->_request->get("p");
        } else {
            $query["page"] = 1;
        }

        $query["count"] = null;
        $query["hide_filled"] = false;
        $query["ids_only"] = true;
  
        $ids = Wpjb_Model_JobSearch::search($query)->job;
        $order = $this->_request->post("object");
        
        if($this->_request->get("format") == "xml") {
            $this->_exportXml($order, $ids);
        }
        
        exit;
    }
    
    protected function _exportXml($order, $ids) {
        $mapping = array(
            "job" => array(
                "tag" => "jobs",
                "model" => "Wpjb_Model_Job",
                "ids" => array(),
                "links" => array(
                    "employer_id" => "company"
                )
            ),
            "company" => array(
                "tag" => "companies",
                "model" => "Wpjb_Model_Company",
                "ids" => array(),
                "links" => array()
            )
        );
        
        $mapping[$order[0]]["ids"] = $ids;
        
        $xml = new Daq_Helper_Xml();
        $xml->declaration();
        $xml->open("wpjb");
        
        foreach($order as $key) {
            $map = $mapping[$key];
            $xml->open($map["tag"]);
            foreach($map["ids"] as $id) {
                $model = $map["model"];
                $object = new $model($id);
                $object->export();
                
                foreach($map["links"] as $column => $link) {
                    if(isset($mapping[$link])) {
                        $mapping[$link]["ids"][$object->$column] = $object->$column;
                    }
                }
                
                unset($object);
            }
            $xml->close($map["tag"]);
        }
        
        
        
        
        $xml->close("wpjb");
        
        //var_dump($order);
        //var_dump($mapping);
    }
    
    public function introAction()
    {
        
    }

    public function removeAction()
    {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Job t");
        $query->where("t.id IN(?)", $this->_request->get("users"));
        $this->view->list = $query->execute();
        $this->view->nonce = wp_create_nonce($this->getNonceName());
        $this->view->nonceName = $this->getNonceName();
        $i = 0;
        
        if($this->isPost() && $this->_request->post("application_option")) {
            
            $nonceName = $this->getNonceName();
            $nonce = wp_verify_nonce($this->_request->post("_wpjb_nonce"), $nonceName);

            if( ! $nonce ) {
                wp_die(__("Invalid nonce.", "wpjobboard"));
            } else {
                $applications = $this->_request->post( "application_option" );
                $payment = $this->_request->post( "payment_option" );
                
                foreach($this->_request->post("jobs", array()) as $id) {
                    $job = new Wpjb_Model_Job($id);
                    $job->delete( array( "remove_apps" => $applications, "remove_payment_history" => $payment ) );
                    $i++;
                }
                
                if($i > 0) {
                    $msg = _n("One job deleted.", "%d jobs deleted.", $i, "wpjobboard");
                    $this->_addInfo(sprintf($msg, $i));
                } else {
                    $this->_addError(__("No jobs to delete", "wpjobboard"));
                }
                
                wp_redirect(wpjb_admin_url("job"));
                exit;
            }
        }
    }

    public function redirectAction()
    {
        if($this->_request->post("action") == "delete" || $this->_request->post("action2") == "delete") {
            $param = array("users"=>$this->_request->post("item", array()));
            $url = wpjb_admin_url("job", "remove")."&".  http_build_query($param);
            wp_redirect($url);
            exit;
        }

        parent::redirectAction();
    }
    

    protected function _multiActivate($id)
    {
        $object = new Wpjb_Model_Job($id);
        $approved = (int)$object->is_approved;
        
        $object->is_approved = 1;
        $object->is_active = 1;
        $object->save();
        
        do_action("wpjb_job_saved", $object);
        
        if(!$approved) {
            do_action("wpjb_job_published", $object);
            $this->_approve($object);
        }
        
        return true;
    }

    protected function _multiDeactivate($id)
    {
        $object = new Wpjb_Model_Job($id);
        $object->is_active = 0;
        $object->save();
        return true;
    }
    
    protected function _multiRead($id)
    {
        $object = new Wpjb_Model_Job($id);
        $object->read = 1;
        $object->save();
        return true;
    }

    protected function _multiUnread($id)
    {
        $object = new Wpjb_Model_Job($id);
        $object->read = 0;
        $object->save();
        return true;
    }
    
    protected function _approve(Wpjb_Model_Job $job)
    {
        $email = null;
        if($job->company_email) {
            $email = $job->company_email;
        } else if($job->getCompany(true)->user_id) {
            $email = get_user_by('ID', $job->getCompany(true)->user_id )->user_email;
        }
        
        
        $message = Wpjb_Utility_Message::load("notify_employer_job_paid");
        $message->assign("job", $job);
        $message->setTo($email);
        $message->send(); 
    }

    protected function _multiDelete($id)
    {
        $object = new Wpjb_Model_Job($id);
        $object->delete();
        return true;
    }
}

?>