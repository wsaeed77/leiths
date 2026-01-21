<?php

use Google\Service\TrafficDirectorService\NullMatch;

/**
 * Description of CarrerBuilder
 *
 * @author greg
 * @package 
 */

class Wpjb_Service_RssImport
{
    protected $_id = null;

    protected $_job_source_id = null;

    protected $_url = null;

    public function __construct() {

    }
    
    public function prepare($item, $import) {
        return $item;
    }
    
    public function find($param = array())  {
        $result = new stdClass();
        $result->item = array();
        
        $this->_id = $param["id"];

        $posted = $param["posted"];
        $url = $param["keyword"];
        $max = $param["add_max"];

        $content = wp_remote_get($url, array(
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36'
        ));

        if($content instanceof WP_Error) {
            $this->log(sprintf("Error: %s", $content->get_error_message()));
            return array();
        }

        $xml = simplexml_load_string($content["body"]);

        if($xml === false) {
            $this->log(sprintf("Error: %s", implode("; ", libxml_get_errors())));
            return array();
        }

        $this->log(sprintf("Found %d items to import.", count($xml->channel->item)));

        $already_imported = 0;
        $too_old = 0;
        $imported = 0;

        foreach($xml->channel->item as $r) {

            if($imported >= $max) {
                break;
            }

            $job_created_at = strtotime((string)$r->pubDate);

            if($job_created_at < time() - $posted*24*3600) {
                $too_old++;
                continue;
            }

            if($this->isDuplicate((string)$r->link)) {
                $already_imported++;
                continue;
            }

            $dd = wpjb_conf("default_job_duration", 30);
            if( $dd == 0) {
                $job_expires_at = WPJB_MAX_DATE;
            } else {
                $job_expires_at = $job_created_at + $dd * 3600 * 24;
            }

            $item = new stdClass;
            $item->job_title = (string)$r->title;
            $item->job_description = (string)$r->description;
            $item->job_created_at = date("Y-m-d", $job_created_at );
            $item->job_expires_at = date("Y-m-d", $job_expires_at);
            $item->job_country = wpjb_locale();

            $item->metas = new stdClass;
            $item->metas->meta = array();

            $m1 = new stdClass();
            $m1->name = "job_description_format";
            $m1->value = "html";
            
            $m2 = new stdClass();
            $m2->name = "job_source";
            $m2->value = (string)$r->link;

            $item->metas->meta[] = $m1;
            $item->metas->meta[] = $m2;

            $item = apply_filters( "wpjb_import_rss_find", $item, $r );

            if( $item ) {
                $result->item[] = $item;
                $imported++;
            }
        }
        
        $t = "Can Import %d (max %d); Too old %d; Already imported %d.";
        $this->log(sprintf($t, $imported, $max, $too_old, $already_imported));

        return $result;
    
    }

    public function findJobSourceId() {
        if($this->_job_source_id) {
            return $this->_job_source_id;
        }

        $select = new Daq_Db_Query;
        $select->from("Wpjb_Model_Meta t");
        $select->where("name = ?", "job_source");
        $select->where("meta_object = ?", "job");
        $select->limit(1);

        $this->_job_source_id = $select->fetchColumn();

        return $this->_job_source_id;
    }

    public function isDuplicate($job_source) {

        $select = new Daq_Db_Query;
        $select->from("Wpjb_Model_MetaValue t");
        $select->where("meta_id = ?", $this->findJobSourceId());
        $select->where("value = ?", $job_source );
        $select->limit(1);

        $result = $select->fetchColumn();

        if($result !== null) {
            return true;
        } else {
            return false;
        }

    }

    public function log( $text ) {
        Wpjb_Model_Import::log($this->_id, $text);
    }
    

}

?>