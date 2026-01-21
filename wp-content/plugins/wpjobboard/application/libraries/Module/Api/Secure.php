<?php

class Wpjb_Module_Api_Secure extends Daq_Controller_Abstract
{

    protected function _decypherPath( $path ) {
        if( wpjb_conf( 'fs_hash_path') == "1" ) {
            $filename = basename($path);
            $path = dirname($path);
            list($blog_id, $path) = explode("/", $path, 2);

            if( wpjb_conf( 'fs_hash_key' ) ) {
                $hash_key = wpjb_conf( 'fs_hash_key' );
            } elseif( defined( NONCE_SALT ) ) {
                $hash_key = NONCE_SALT;
            } else {
                $hash_key = "";
            }
    
            $dir = wpjb_decypher($path, $hash_key);
            //print_r($dir);
            //print_r($path);
            return sprintf( "%d/%s/%s", $blog_id, $dir, $filename);
        } else {
            $dir = $path;
        }

        return $dir;
    }

    public function fileAction() {
        $uri = $_SERVER['REQUEST_URI'];
        $find = "/wpjobboard/secure/file/";
        $pos = stripos($uri, $find);
        $length = strlen($find);

        if($pos === false) {
            return;
        }

        $path = trim(substr($uri, $pos+$length), "/");
        $path = $this->_decypherPath($path);

        $parts = explode("/", $path);
        
        $keys = array( "blog_id", "object", "id", "field", "file" );
        $parts = array_combine($keys, $parts);

        list($blog_id, $clean) = explode("/", $path, 2);

        $this->restrict( trim($clean, "/"));
    }

    public function restrict( $clean ) {
        list($type, $id, $path) = explode("/", $clean, 3);

        $file = wpjb_upload_dir($type, "", $id, "basedir")."".$path;
        $finfo = wp_check_filetype_and_ext($file, basename($file));

        $isAllowed = false;
        $adminMenu = new Wpjb_Utility_AdminMenu();
        $menu = $adminMenu->getLeftItems();

        if($type == "application") {
            $application = new Wpjb_Model_Application($id);
            $job = new Wpjb_Model_Job($application->job_id);
            
            if(!is_null($job->employer_id) && $job->employer_id == Wpjb_Model_Company::current()->id) {
                $isAllowed = true;
            }
            if(current_user_can($menu["applications"]["access"])) {
                $isAllowed = true;
            }
            
        } elseif($type == "resume") {
            
            if(wpjb_conf("cv_privacy") == "1" && wpjr_can_browse($id)) {
                $isAllowed = true;
            }

            if(wpjb_conf("cv_privacy") == "0" && ( stripos($path, "image/") === 0 || wpjr_can_browse($id) ) ) {
                $isAllowed = true;
            }
            
            if(current_user_can($menu["resumes_manage"]["access"])) {
                $isAllowed = true;
            }
        }

        if(current_user_can("edit_files")) {
            $isAllowed = true;
        }

        $isAllowed = apply_filters("wpjb_restrict", $isAllowed, $clean, $file);

        if($isAllowed) {
            ob_end_clean();
            
            header('Content-type: '.$finfo["type"]);
            header('Content-Disposition: inline; filename="'.basename($file).'"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($file));
            header('Accept-Ranges: bytes');
            header('X-Robots-Tag: noindex');
            
            @readfile($file);
        } else {
            wp_die(__("You are not allowed to access this file.", "wpjobboard"));
        }

    }
}