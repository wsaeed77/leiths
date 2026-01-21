<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Jobs
 *
 * @author Grzegorz
 */
class Wpjb_Module_Api_Jobs extends Wpjb_Controller_Api
{

    
    public function init()
    {
        return array(
            "allowed_user_actions" => array(
                "public" => array("get"),
                "user" => array("get", "post"),
                "employer" => array("get", "post", "put", "delete"),
                "admin" => array("get", "post", "put", "delete")
            )
        );
    }
    
    public function getInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array("filter", "count_only", "ids_only", "hide_filled"),
                "user" => array("filter"),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(
                    "default" => array("job_modified_at", "job_expires_at", "company_email", "applications", "read", "admin_url"), 
                    "meta" => array("job_source")
                ),
                "user" => array("filter"),
                "employer" => array(),
                "admin" => array()
            )
        );
    }
    
    public function getAction($params) 
    {
        $result = wpjb_find_jobs($params);
        
        $response = array("status"=>200, "total"=>$result->total, "data"=>array());
        
        foreach($result->job as $row) {
            
            $response["data"][] = $this->reduce($row->toArray(), $this->conf("//hidden_fields"));
        }
        
        return $response;
    }

    public function postInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array("shortlisted_at", "user_id"),
                "employer" => array("shortlisted_at", "user_id"),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array("meta"),
                "user" => array("meta"),
                "employer" => array("meta"),
                "admin" => array("meta")
            )
        );
    }

    public function postAction($params = array())
    {
        $import = new stdClass;
        $request = null;

        if($_SERVER['REQUEST_METHOD'] == 'PUT') {
            parse_str(file_get_contents('php://input'), $request); 
            $request["wpjb-job"]["id"] = absint($params["id"][0]);
            $import->id = absint($params["id"][0]);
        } else if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $request = stripslashes_deep( $_POST );
        }

        if(!isset($request["wpjb-job"]) || !is_array($request["wpjb-job"])) {
            throw new Exception("Param wpjb-job not sent or is not an array.");
        }
        
        
        $meta = array();
        $tags = array();
        $files = array();

        foreach( $request["wpjb-job"] as $k => $v ) {
            if(in_array($k, array("meta", "tags", "files"))) {
                $$k = $v;
                continue;
            }
            $import->$k = (string)$v;
        }

        $import->metas = new stdClass();
        $import->metas->meta =  array();

        $import->tags = new stdClass();
        $import->tags->tag = array();
        
        $import->files = new stdClass();
        $import->files->file = array();

        foreach($meta as $k => $v) {
            $meta = new stdClass();
            $meta->name = "";
            $meta->value = "";
            $meta->values = new stdClass;
            $meta->values->value = array();
            foreach($v as $vk => $vv) {
                if($vk == "values") {
                    $meta->$vk->value = $vv;
                } else {
                    $meta->$vk = $vv;
                }
                
            }
            $import->metas->meta[] = $meta;
        }

        foreach($tags as $k => $v) {
            $tag = new stdClass;
            $tag->id = null;
            $tag->type = "";
            $tag->slug = "";
            $tag->title = "";
            foreach($v as $vk => $vv) {
                $tag->$vk = $vv;
            }
            $import->tags->tag[] = $tag;
        }

        foreach($files as $k => $v) {
            $file = new stdClass;
            foreach($v as $vk => $vv) {
                $file->$vk = $vv;
            }
            $import->files->file[] = $file;
        }

        $result = apply_filters( "wpjb_api_job_post", Wpjb_Model_Job::import($import), $import );

        $object = new Wpjb_Model_Job($result["id"]);
        
        return $object->toArray();
    }

    public function putInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array("shortlisted_at", "user_id"),
                "employer" => array("shortlisted_at", "user_id"),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array("meta"),
                "user" => array("meta"),
                "employer" => array("meta"),
                "admin" => array("meta")
            )
        );
    }

    public function putAction($params = array())
    {
        if(isset($params["id"]) && isset($params["id"][0])) {
            $job_id = absint($params["id"][0]);
        } else {
            $job_id = 0;
        }

        if($job_id < 1) {
            throw new Exception("Job ID was not sent.");
        }

        $job = new Wpjb_Model_Job($job_id);

        if(!$job->exists()) {
            throw new Exception(sprintf("Job with ID %d does not exist.",$job_id));
        }

        $user_id = -1;
        $company = $job->getCompany(true);
        if($company) {
            $user_id = $company->user_id;
        }

        if($this->getAccess() != "admin" && $this->getUser()->ID != $user_id) {
            throw new Exception(sprintf("Cannot edit, your do not own job with ID %d.",$job_id));
        }

        return $this->postAction($params);
    }

    public function deleteInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            )
        );
    }

    public function deleteAction($params = array())
    {
        $id = absint($params["id"][0]);
        $job = new Wpjb_Model_Job($id);

        if(!$job->exists()) {
            throw new Exception("Job with ID $id does not exist.");
        }
        
        $user_id = -1;
        $company = $job->getCompany(true);
        if(is_object($company)) {
            $user_id = $company->user_id;
        }

        if($this->getAccess() != "admin" && $this->getUser()->ID != $user_id) {
            throw new Exception("Cannot delete, your do not own job with ID $id.");
        }

        $job->delete();
        
        return array("status"=>200, "deleted"=>$id);
    }
}

?>
