<?php

class Daq_Form_Element_Select extends Daq_Form_Element_Multi implements Daq_Form_Element_Interface
{
    protected $_maxChoices = 1;
    
    protected $_emptyOption = false; 
    
    protected $_emptyOptionText = null; 
    
    protected $_optgroup = array( "default" => "" );
    
    public final function getType()
    {
        return "select";
    }
    
    public function setEmptyOption($option)
    {
        $this->_emptyOption = (bool)$option;
    }
    
    public function setEmptyOptionText($text) {
        $this->_emptyOptionText = $text;
    }
    
    public function getEmptyOptionText() {
        return $this->_emptyOptionText;
    }
    
    public function hasEmptyOption()
    {
        return $this->_emptyOption;
    }
    
    public function getOptgroup() {
        return $this->_optgroup;
    }
    
    public function addOptgroup( $id, $label ) {
        $this->_optgroup[$id] = $label;
    }
    
    public function render()
    {
        $html = "";
        $name = $this->getName();
        $multiple = false;
        $classes = $this->getClasses();
        
        if($this->isCute()) {
            $classes .= " daq-multiselect-cute "; 
        }
        
        if($this->getMaxChoices()>1) {
            return $this->renderMulti();
        }
        
        $options = array(
            "id" => $this->getName(),
            "name" => $name,
            "class" => $classes,
            "multiple" => $multiple
        );
        
        $options += $this->getAttr();
        
        if($this->hasEmptyOption()) {
            if($this->getEmptyOptionText()) {
                $emptyText = $this->getEmptyOptionText();
            } else {
                $emptyText = "&nbsp;";
            }
            $html .= '<option value="" class="daq-multiselect-empty-option">'.esc_html($emptyText).'</option>'; 
        }
        
        foreach( $this->getOptgroup() as $group_id => $group_label) {
            
            $group_options = "";
            
            foreach($this->getOptions() as $k => $v) {
                
                if( ( !isset($v['param']) && $group_id == "default" ) || $v['param'] == $group_id ) {
                
                    $selected = null;
                    if(in_array($v["value"], (array)$this->getValue())) {
                        $selected = "selected";
                    }
                    $o = new Daq_Helper_Html("option", array(
                        "value" => $v["value"],
                        "selected" => $selected,
                    ), $v["desc"]);

                    $group_options .= $o->render();
                }
            }
            
            $go = new Daq_Helper_Html("optgroup", array(
                    "label" => $group_label,
                ), $group_options);
            
            if( $group_id == "default" ) {
                $html .= $group_options;
            } else {
                $html .= $go->render();
            } 
        }
        
        $input = new Daq_Helper_Html("select", $options, $html);
        
        return $input->render();
    }

    public function renderMulti() {

        wp_enqueue_script("wpjb-vendor-selectlist");

        if($this->getEmptyOptionText()) {
            $emptyText = $this->getEmptyOptionText();
        } else {
            $emptyText = "&nbsp;";
        }

        $val = array();
        $html_list = "";

        foreach( $this->getOptgroup() as $group_id => $group_label) {
            

            foreach($this->getOptions() as $k => $v) {
                
                if( ( !isset($v['param']) && $group_id == "default" ) || $v['param'] == $group_id ) {
                
                    $optName = sprintf( "%s-%d", $this->getName(), $k );
                    $value = $v["value"];
                    $desc = $v["desc"];

                    if(in_array($v["value"], (array)$this->getValue())) {
                        $checked = 'checked="checked"';
                        $val[] = $desc;
                    } else {
                        $checked = "";
                    }

                    $html_list .= sprintf( '<li class="wpjb-input-cols wpjb-input-cols-1">' );
                    $html_list .= sprintf( '<label for="%s">', $optName );
                    $html_list .= sprintf( '<input type="checkbox" id="%s" value="%s" name="%s[]" data-wpjb-owner="%s" %s />', $optName, esc_html( $value ), $this->getName(), $this->getName(), $checked );
                    $html_list .= sprintf( '<span class="wpjb-input-description">%s</span>', esc_html( $desc ) );
                    $html_list .= sprintf( '</label>' );
                    $html_list .= sprintf( '</li>' );

                }
            }

        }

        $html = "";
        $html.= sprintf( '<div class="">');
        $html.= sprintf( '<input type="text" id="%s" placeholder="%s" autocomplete="off" class="daq-multiselect-input" value="%s">', $this->getName(), $emptyText, join( ", ", $val ) );
        $html.= sprintf( '<div class="daq-multiselect-options" style="display:none">' );
        $html.= sprintf( '<ul>' );
        $html.= $html_list;
        $html.= sprintf( '</ul>' );
        $html.= sprintf( '</div>' );
        $html.= sprintf( '</div>' );

        return $html;
    }

    public function overload(array $data)
    {
        parent::overload($data);
            
        if(isset($data["select_choices"]) && $data["select_choices"]) {
           $this->setMaxChoices($data["select_choices"]); 
        }
        if(isset($data["empty_option"]) && $data["empty_option"]) {
            $this->setEmptyOption($data["empty_option"]);
        }
        if(isset($data["empty_option_text"]) && $data["empty_option_text"]) {
            $this->setEmptyOptionText($data["empty_option_text"]);
        }
        if(isset($data["content_display"]) && $data["content_display"]) {
            $this->setContentDisplay($data["content_display"]); 
         }
    }
    
    public function dump()
    {
        $dump = parent::dump();

        if(!is_array($this->_overload)) {
            return $dump;
        }

        $dump->empty_option = $this->_overload["empty_option"];
        if( isset( $this->_overload["empty_option_text"]) ) {
            $dump->empty_option_text = $this->_overload["empty_option_text"];
        }
        
        return $dump;
    }
    
    public function validate()
    {
        return parent::validate();        
    }
}

