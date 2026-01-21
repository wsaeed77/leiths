<?php
/**
 * Description of Alert
 *
 * @author greg
 * @package 
 */

class Wpjb_Model_Import extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_import";

    protected function _init()
    {
        
    }
    
    public function save()
    {
        parent::save();
       
        if(!wp_next_scheduled("wpjb_event_import")) {
            wp_schedule_event(current_time('timestamp'), 'hourly', 'wpjb_event_import');
        }
        
    }
    
    public function delete() 
    {
        $result = parent::delete();
        
        $query = new Daq_Db_Query();
        $query->select("COUNT(*) AS cnt");
        $query->from("Wpjb_Model_Import t");
        $c = $query->fetchColumn();
        
        if(!$c && wp_next_scheduled("wpjb_event_import")) {
            wp_clear_scheduled_hook("wpjb_event_import");
        }
        
        return $result;
    }


    protected function _resolve($tag) 
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Tag t");
        $query->where("type = ?", $tag->type);
        $query->where("slug = ?", $tag->slug);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(empty($result)) {
            $t = new Wpjb_Model_Tag;
            $t->type = $tag->type;
            $t->slug = $tag->slug;
            $t->title = $tag->title;
            $t->save();
        } else {
            $t = $result[0];
        }
        
        return $t->id;
    }
    
    public function run()
    {
        $engines = apply_filters("wpjb_import_engines", array(
            "indeed" => "Wpjb_Service_Indeed",
            "careerbuilder" => "Wpjb_Service_CareerBuilder",
            "rss" => "Wpjb_Service_RssImport"
        ));
        
        if(array_key_exists($this->engine, $engines)) {
            $teng = $engines[$this->engine];
            $engine = new $teng;
        } else {
            throw new Exception("Engine not found.");
        }
        
        $result = $engine->find($this->toArray());
        foreach($result->item as $item) {
            
            $query = new Daq_Db_Query();
            $query->select();
            $query->from("Wpjb_Model_MetaValue t");
            $query->where("value = ?", $this->engine."-".$item->external_id);
            $query->limit(1);

            $r = $query->execute();

            if( $r && isset( $r[0] ) && $r[0]->id > 0) {
                continue;
            }

            Wpjb_Model_Job::import($engine->prepare($item, $this));
            
        }
    }

    public function getReadableParams() {
        if($this->engine == "rss" ) {
            return sprintf( "<strong>Import URL</strong>: %s", esc_html( $this->keyword ) );
        } else {
            $data = array();
            $keys = array(
                "keyword" => __("Keyword", "wpjobboard"),
                "country" => __("Country", "wpjobboard"),
                "location" => __("Location", "wpjobboard"),
                "category_id" => __("Category ID", "wpjobboard"),

            );

            foreach($keys as $key => $label ) {
                if($this->$key) {
                    $data[] = sprintf( "<strong>%s</strong>: %s", $label, esc_html( $this->$key ) );
                }
            }

            return join( "; ", $data );
        }
    }

    public static function log($id, $text) {
        $log = new self($id);
        $lines = array();
        if($log->logs) {
            $lines = explode("\r\n", $log->logs);
        }
        $total = count($lines);

        $entry = sprintf( "[%s] %s", date("Y/m/d H:i:s"), $text );
        array_unshift($lines, $entry);
        $lines = array_slice($lines, 0, 100);

        $log->logs = implode( "\r\n", $lines );
        $log->save();
    }
}

?>