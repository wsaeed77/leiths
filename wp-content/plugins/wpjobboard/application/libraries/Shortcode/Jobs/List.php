<?php

class Wpjb_Shortcode_Jobs_List extends Wpjb_Shortcode_Abstract {
    
    /**
     * Class constructor
     * 
     * Registers [wpjb_jobs_list] shortcode is not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_jobs_list")) {
            add_shortcode("wpjb_jobs_list", array($this, "main"));
        }
    }
    
    /**
     * Displays jobs list
     * 
     * This function is executed when [wpjb_jobs_list] shortcode is being called.
     * 
     * @link http://wpjobboard.net/kb/shortcode_wpjb_jobs_list/ documentation
     * 
     * @param array $atts   Shortcode attributes
     * @return void
     */
    public function main($atts) {
        
        $instance = Wpjb_Project::getInstance();
        $request = Daq_Request::getInstance();
        
        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            $candidate = Wpjb_Model_Resume::current();

            if( !$candidate || wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = sprintf( __( 'Only registered candidates have access to this page. You can register <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_reg" ) ) );
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash_get();
        }

        if( !wpjb_employer_have_access( get_the_ID() ) ) {

            $employer = Wpjb_Model_Company::current();
            
            if( !$employer || wpjb_conf( "employer_members_have_access" ) == 1 ) {
                $msg = sprintf( __( 'Only registered employer have access to this page. You can register <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_emp_reg" ) ) );
            } elseif( wpjb_conf( "employer_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium employers have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_membership_pricing" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash_get();
        }

        $slug = get_query_var("wpjb-slug");
        $tag = get_query_var("wpjb-tag");
        
        $category = null;
        $type = null;

        if(!empty($slug)) {

            switch(get_query_var("wpjb-tag")) {
                case "category": $tag = Wpjb_Model_Tag::TYPE_CATEGORY; break;
                case "type": $tag = Wpjb_Model_Tag::TYPE_TYPE; break;
                default: $tag = null;
            }

            $query = new Daq_Db_Query;
            $query->from("Wpjb_Model_Tag t");
            $query->where("slug = ?", $slug);
            $query->where("type = ?", $tag);
            $query->limit(1);

            $result = $query->execute();
            $model = $result[0];

            switch($result[0]->type) {
                case Wpjb_Model_Tag::TYPE_CATEGORY: $category = $result[0]->id; break;
                case Wpjb_Model_Tag::TYPE_TYPE: $type = $result[0]->id; break;
            }
        }

        $page = intval( $request->get( "pg", get_query_var( "paged", 1 ) ) );
        if($page < 1) {
            $page = 1;
        }

        if(is_home() || is_front_page()) {
            $page = get_query_var("page", $page);
        }

        $job = new Wpjb_Model_Job();
        $meta = array();

        $plist = array("query", "location", "country", "state", "city", "type", "category", "job_city", "job_address", "job_state", "job_country", "job_zip_code", "posted", "radius", "employer_id");

        foreach($job->meta as $k => $m) {
            
            if( $request->get($k) && !in_array( $k, $plist ) ) {
                $meta_value = $request->get($k);
                if( is_array( $meta_value ) ) {
                    $meta[$k] = array_map( array( $this, "sanitize_data" ), $meta_value );
                } else {
                    $meta[$k] = $this->sanitize_data( $meta_value );
                }
            }
        }

        $params = shortcode_atts(array(
            "filter"        => "active",
            "query"         => null,
            "category"      => $category,
            "type"          => $type,
            "country"       => null,
            "state"         => null,
            "city"          => null,
            "posted"        => null,
            "location"      => null,
            "is_featured"   => null,
            "employer_id"   => null,
            "meta"          => $meta,
            "hide_filled"   => wpjb_conf("front_hide_filled", false),
            "sort"          => null,
            "order"         => null,
            "sort_order"    => "t1.is_featured DESC, t1.job_created_at DESC, t1.id DESC",
            "search_bar"    => wpjb_conf("search_bar", "disabled"),
            "pagination"    => true,
            "standalone"    => false,
            "id__not_in"    => null,
            'page'          => $page,
            'count'         => wpjb_conf("front_jobs_per_page", 20),
            'page_id'       => get_the_ID(),
            'show_results'  => 1,
            'redirect_to'   => '',
            'backfill'      => null,
            "form_code"     => null,
            "show_map"      => null,
            "center"        => "Europe",
            "auto_locate"   => 0,
            "zoom"          => 12,
            "width"         => "100%",
            "height"        => "400px",
            "image"         => "50x50",
            "image_default_url" => null
        ), $atts);

        $search_only_params = array( 
            'filter' => $params['filter'],
            'count' => $params["count"],
            'page' => $params["page"],
            'is_featured' => $params["is_featured"] 
        );
        
        if( $params["search_bar"] != "enabled-live" ) {
            $search_only_params = $params;
            $unset = array( "page_id" );
            foreach( $unset as $us ) {
                if( isset( $search_only_params[$us] ) ) {
                    unset( $search_only_params[$us] );
                }
            }
            /*$search_only_params = array(
                "filter"        => $params["filter"],
                'count'         => $params["count"],
                'is_featured'   => $params["is_featured"],
                "pagination"    => $params["pagination"],
                "page"          => $params["page"],
                "hide_filled"   => $params["hide_filled"],
                "sort_order"    => $params["sort_order"],
                "show_results"  => $params["show_results"],
                "employer_id"   => $params["employer_id"],
            );*/
        }
        
        if( is_string( $params["type"] ) ) {

            if( strpos( $params["type"], "," ) !== false ) {
                $search_only_params["type"] = array_map( "trim", explode( ",", $params["type"] ) );
                $params["type"] = array_map("trim", explode( ",", $params["type"] ) );
            } else {
                $search_only_params["type"] = $params["type"];
            }
        } 

        if( is_string( $params["category"] ) ) {

            if( strpos( $params["category"], "," ) !== false ) {
                $search_only_params["category"] = array_map( "trim", explode( ",", $params["category"] ) );
                $params["category"] = array_map("trim", explode( ",", $params["category"] ) );
            } else {
                $search_only_params["category"] = $params["category"];
            }
        } 
      

        foreach((array)$atts as $k=>$v) {
            if(stripos($k, "meta__") === 0) {
                $params["meta"][substr($k, 6)] = $v;
                $search_only_params["meta"][substr($k, 6)] = $v;
            }
        }

        //$plist = array("query", "location", "country", "state", "city", "type", "category", "job_city", "job_address", "job_state", "job_country", "posted");
        foreach( $plist as $p ) {
            if( $request->get( $p ) ) {
                $param_p = $request->get( $p );
                
                if( is_array( $param_p ) ) {
                    $search_only_params[$p] = array_map( array( $this, "sanitize_data" ), $param_p );
                    $params[$p] = $search_only_params[$p];
                } else {
                    $search_only_params[$p] = $this->sanitize_data( $param_p );
                    $params[$p] = $search_only_params[$p];
                }
            }
        }

        if( count( $params["meta"] ) > 0 ) {
            $search_only_params["meta"] = $params["meta"];
        }

        $map = null;
        if( $params["show_map"] == 1 ) {


            ob_start();           

                $map_search = $search_only_params;
                if( isset( $map_search["sort_order"] ) ) {
                    unset( $map_search["sort_order"] );
                }
                if( isset( $map_search["pagination"] ) ) {
                    unset( $map_search["pagination"] );
                }
                if( isset( $map_search["hide_filled"] ) ) {
                    unset( $map_search["hide_filled"] );
                }
                if( isset( $map_search["page"] ) ) {
                    unset( $map_search["page"] );
                }
				if( isset( $map_search["count"] ) ) {
                    unset( $map_search["count"] );
                }

                ?>
                <input type="hidden" name="wpjb_map_shortcode_params" id="wpjb_map_shortcode_params" value="<?php echo base64_encode( json_encode( $map_search ) ); ?>" />
                <?php

                echo do_shortcode( '[wpjb_map center="'. $params['center'].'" auto_locate="'. $params['auto_locate'].'" zoom="'. $params['zoom'].'" width="'. $params['width'].'" height="'. $params['height'].'"] ');
                ?>
                    <?php if( $params["search_bar"] == "enabled-live"): ?>
                    <script type="text/javascript">
                        wpjbMapCallbacks.loadData.location = function(data) {
                            <?php foreach( $params as $key => $value ): ?>
                                var key = "<?php echo $key; ?>";
                                var field_value = "<?php echo esc_html( $value ); ?>";

                                // input: text
                                if( jQuery( "#" + key ).is( 'input[type="text"]' ) && jQuery( "#" + key ).val() ) {
                                    field_value = jQuery("#" +key).val();
                                }

                                // input dropdown
                                if( jQuery( "#" + key ).is( 'select' ) ) {
                                    if( jQuery( "#" + key ).children("option:selected").val() !== "undefined" ) {
                                        field_value = jQuery( "#" + key ).children("option:selected").val();
                                    }
                                }

                                // input checkbox/radio
                                if( jQuery( "#" + key ).is( 'input[type="checkbox"]' ) || jQuery( "#" + key ).is( 'input[type="radio"]' ) ) {


                                    var f_name = $(this).attr("name");
                                    var checkedVals = $('"#"' + key + ':checked').map(function() {
                                        return this.value;
                                    }).get();

                                    wpjbMapCallbacks.loadData.location = function(data) {
                                        data[f_name] = checkedVals;
                                    }
                                }

                                data[key] = field_value

                            <?php endforeach; ?>
                        };
                    </script>
                    <?php else: ?>
                    <script type="text/javascript">
                        wpjbMapCallbacks.loadData.location = function(data) {
                            <?php foreach( $map_search as $key => $value ): ?>
                                var key = "<?php echo esc_html( $key ); ?>";
                                <?php if( $key == "meta" ): ?>
                                    <?php foreach( $value as $f => $v ): ?>
                                        var meta_key = "<?php echo esc_html( $f ); ?>";
                                        <?php if( is_array( $v ) ): ?>
                                            data[meta_key] = [];
                                            <?php foreach( $v as $i => $single_value ): ?>
                                                data[meta_key][<?php echo intval( $i ); ?>] = "<?php echo esc_html( $single_value ); ?>"; 
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            data[meta_key] = "<?php echo esc_html( $v ); ?>";
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php if( is_array( $value ) ): ?>
                                        data[key] = [];
                                        <?php foreach( $value as $i => $single_value ): ?>
                                            data[key][<?php echo intval( $i ); ?>] = "<?php echo esc_html( $single_value ); ?>"; 
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        data[key] = "<?php echo esc_html( $value ); ?>"; //jQuery("#" +key).val();
                                    <?php endif; ?>
                                    
                                <?php endif; ?>
                            <?php endforeach; ?>
                        };
                    </script>
                    <?php endif; ?>
                <?php
            $map = ob_get_clean();

        }

        $init = array();
        foreach(array_keys((array)$atts) as $key) {
            if(isset($params[$key]) && !in_array($key, array("search_bar"))) {
                $init[$key] = $params[$key];
            }
        }

        if(!empty($category)) {
            $permalink = wpjb_link_to("category", $model);
        } elseif(!empty($type)) {
            $permalink = wpjb_link_to("type", $model);
        } else {
            $permalink = get_the_permalink();
        }
        
        if( is_numeric( $params['redirect_to'] ) ) {
            $action = get_permalink( $params['redirect_to'] );
        } else {
            $action = $params['redirect_to'];
        }

        $form = new Wpjb_Form_Frontend_ListSearch( null, apply_filters( "wpjb_form_scheme", array( "custom"=>$params['form_code'] ), "job_search_list", null ) );
        $form->isValid($this->getRequest()->get());
        $form->getElement("show_results")->setValue(1);

        if( empty( $this->getRequest()->get() ) ) {
            foreach( $init as $field_name => $val ) {
                if( $form->hasElement( $field_name ) ) {
                    $form->getElement( $field_name )->setValue( $val );
                    $search_only_params[ $field_name ] = $val;
                }
            }
        }
        
        if($request->get('show_results', 0) == 1) {
            $params['show_results'] = 1;
            $search_only_params['show_results'] = 1;
        }

        $this->view = new stdClass();
        $this->view->form = $form;
        $this->view->atts = $atts;
        $this->view->param = apply_filters( "wpjb_jobs_list_search_only_params", $search_only_params ); //$params;
        $this->view->pagination = $params["pagination"];
        $this->view->url = $permalink;
        $this->view->query = "";
        $this->view->shortcode = true;
        $this->view->search_bar = $params["search_bar"];
        $this->view->show_results = $params["show_results"];
        $this->view->search_init = $init;
        $this->view->page_id = $params["page_id"];
        $this->view->action = $action;
        $this->view->image = $params["image"];
        $this->view->image_default_url = $params["image_default_url"];
        $this->view->map = $map;
        if ( get_option('permalink_structure') ) {
            $this->view->format = 'page/%#%/';
        } else {
            $this->view->format = '&paged=%#%';
        }

        wp_enqueue_style("wpjb-css");
        wp_enqueue_script('wpjb-js');

        return $this->render("job-board", "index");
    }
    
    public function __set($name, $value) {
        if($name == "job" && $this->view) {
            $this->view->job = $value;
        }
    }
    
    public function sanitize_data( $data ) {
        return str_replace('"', "", sanitize_text_field( stripslashes( $data ) ) );
    } 
}
