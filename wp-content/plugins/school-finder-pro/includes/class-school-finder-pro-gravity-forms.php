<?php
/**
 * Gravity Forms integration for School Finder Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if Gravity Forms is active
if (!class_exists('GF_Field')) {
    return;
}

class GF_Field_School_Finder extends GF_Field {
    
    public $type = 'school_finder';
    
    public function get_form_editor_field_title() {
        return esc_attr__('School Finder', 'school-finder-pro');
    }
    
    public function get_form_editor_field_settings() {
        return array(
            'label_setting',
            'label_placement_setting',
            'admin_label_setting',
            'rules_setting',
            'placeholder_setting',
            'description_setting',
            'css_class_setting',
            'visibility_setting',
            'conditional_logic_field_setting',
            'error_message_setting',
        );
    }
    
    public function get_form_editor_button() {
        return array(
            'group' => 'advanced_fields',
            'text'  => $this->get_form_editor_field_title()
        );
    }
    
    public function get_field_input($form, $value = '', $entry = null) {
        $id              = absint($this->id);
        $form_id         = absint($form['id']);
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();
        
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $size         = $this->size;
        $class_suffix = $is_entry_detail ? '_admin' : '';
        $class        = $size . $class_suffix;
        $class        = esc_attr($class);
        
        $disabled_text = $is_form_editor ? 'disabled="disabled"' : '';
        $required_attribute = $this->isRequired ? 'aria-required="true"' : '';
        $invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
        
        $tabindex = $this->get_tabindex();
        
        $placeholder_attribute = $this->placeholder ? 'placeholder="' . esc_attr($this->placeholder) . '"' : '';
        if (empty($placeholder_attribute)) {
            $placeholder_attribute = 'placeholder="' . esc_attr__('Start typing the name of your school to begin searching.', 'school-finder-pro') . '"';
        }
        
        // Get selected school value
        $selected_value = '';
        if (!empty($value)) {
            $selected_value = esc_attr($value);
        }
        
        $input = sprintf(
            '<div class="ginput_container ginput_container_school_finder">
                <div class="school-finder-pro-gf-wrapper" data-field-id="%s">
                    <input 
                        type="text" 
                        name="input_%s" 
                        id="%s" 
                        value="%s" 
                        class="%s school-finder-pro-gf-input" 
                        %s 
                        %s 
                        %s 
                        %s
                        autocomplete="off"
                        data-min-chars="2"
                        data-max-results="10"
                    >
                    <div class="school-finder-pro-results school-finder-pro-gf-results" id="school-finder-pro-results-%s"></div>
                    <input type="hidden" class="school-finder-pro-gf-selected-id" name="input_%s_id" value="">
                    <input type="hidden" class="school-finder-pro-gf-selected-name" name="input_%s_name" value="">
                </div>
            </div>',
            $id,
            $id,
            $field_id,
            $selected_value,
            $class,
            $tabindex,
            $disabled_text,
            $required_attribute,
            $invalid_attribute,
            $id,
            $id,
            $id
        );
        
        return $input;
    }
    
    public function get_field_label_class() {
        return 'gfield_label gfield_label_before_complex';
    }
    
    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen') {
        if (empty($value)) {
            return '';
        }
        
        // Try to get school name from hidden field or database
        $school_id = $value;
        if (class_exists('School_Finder_Pro_Database')) {
            $school = School_Finder_Pro_Database::get_school_by_id($school_id);
            if ($school) {
                return esc_html($school['establishment_name'] . ' (' . $school['town'] . ', ' . $school['postcode'] . ')');
            }
        }
        
        return esc_html($value);
    }
    
    public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {
        // Get the selected school ID from the hidden field
        $field_id = $this->id;
        $school_id = rgpost("input_{$field_id}_id");
        $school_name = rgpost("input_{$field_id}_name");
        
        if (!empty($school_id)) {
            // Save school ID as the value
            return $school_id;
        }
        
        return $value;
    }
    
    public function validate($value, $form) {
        $field_id = $this->id;
        $school_id = rgpost("input_{$field_id}_id");
        
        if ($this->isRequired && empty($school_id)) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please select a school.', 'school-finder-pro') : $this->errorMessage;
        }
    }
    
    public function get_form_inline_script_on_page_render($form) {
        // Enqueue scripts if not already enqueued
        if (!wp_script_is('school-finder-pro-frontend', 'enqueued')) {
            wp_enqueue_style(
                'school-finder-pro-frontend',
                SCHOOL_FINDER_PRO_PLUGIN_URL . 'assets/css/frontend.css',
                array('gform_theme_framework', 'gform_theme_foundation'),
                SCHOOL_FINDER_PRO_VERSION,
                'all'
            );
            
            wp_enqueue_script(
                'school-finder-pro-frontend',
                SCHOOL_FINDER_PRO_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                SCHOOL_FINDER_PRO_VERSION,
                true
            );
            
            wp_localize_script('school-finder-pro-frontend', 'schoolFinderPro', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('school_finder_pro_search'),
                'i18n' => array(
                    'noResults' => __('No schools found', 'school-finder-pro'),
                    'searching' => __('Searching...', 'school-finder-pro'),
                    'startTyping' => __('Start typing the name of your school to begin searching.', 'school-finder-pro')
                )
            ));
        }
        
        // Initialize Gravity Forms specific JavaScript
        $script = "
        jQuery(document).ready(function($) {
            if (typeof schoolFinderProGF === 'undefined') {
                schoolFinderProGF = {
                    init: function() {
                        $('.school-finder-pro-gf-input').each(function() {
                            var \$input = $(this);
                            var \$wrapper = \$input.closest('.school-finder-pro-gf-wrapper');
                            var fieldId = \$wrapper.data('field-id');
                            var \$results = \$wrapper.find('.school-finder-pro-gf-results');
                            var \$selectedId = \$wrapper.find('.school-finder-pro-gf-selected-id');
                            var \$selectedName = \$wrapper.find('.school-finder-pro-gf-selected-name');
                            
                            // Skip if already initialized
                            if (\$input.data('gf-initialized')) {
                                return;
                            }
                            \$input.data('gf-initialized', true);
                            
                            var minChars = parseInt(\$input.data('min-chars')) || 2;
                            var maxResults = parseInt(\$input.data('max-results')) || 10;
                            var searchTimeout;
                            var isSearching = false;
                            
                            // Hide results when clicking outside
                            $(document).on('click', function(e) {
                                if (!$(e.target).closest('.school-finder-pro-gf-wrapper').length) {
                                    \$results.hide();
                                }
                            });
                            
                            // Handle input
                            \$input.on('input', function() {
                                var searchTerm = $(this).val().trim();
                                
                                clearTimeout(searchTimeout);
                                
                                if (searchTerm.length < minChars) {
                                    \$results.hide().empty();
                                    \$selectedId.val('');
                                    \$selectedName.val('');
                                    return;
                                }
                                
                                searchTimeout = setTimeout(function() {
                                    schoolFinderProGF.search(searchTerm, \$input, \$results, \$selectedId, \$selectedName, maxResults);
                                }, 300);
                            });
                            
                            // Handle keyboard navigation
                            \$input.on('keydown', function(e) {
                                var \$items = \$results.find('.school-finder-pro-item');
                                var \$active = \$items.filter('.active');
                                
                                if (e.key === 'ArrowDown') {
                                    e.preventDefault();
                                    if (\$active.length) {
                                        \$active.removeClass('active').next().addClass('active');
                                    } else {
                                        \$items.first().addClass('active');
                                    }
                                } else if (e.key === 'ArrowUp') {
                                    e.preventDefault();
                                    if (\$active.length) {
                                        \$active.removeClass('active').prev().addClass('active');
                                    } else {
                                        \$items.last().addClass('active');
                                    }
                                } else if (e.key === 'Enter') {
                                    e.preventDefault();
                                    if (\$active.length) {
                                        \$active.click();
                                    }
                                } else if (e.key === 'Escape') {
                                    \$results.hide();
                                }
                            });
                            
                            // Handle item click
                            \$results.on('click', '.school-finder-pro-item', function() {
                                var \$item = $(this);
                                var schoolId = \$item.data('id');
                                var schoolName = \$item.data('name');
                                
                                \$selectedId.val(schoolId);
                                \$selectedName.val(schoolName);
                                \$input.val(schoolName);
                                \$results.hide();
                                
                                // Trigger Gravity Forms validation
                                if (typeof gform != 'undefined') {
                                    \$input.trigger('change');
                                }
                            });
                        });
                    },
                    search: function(searchTerm, \$input, \$results, \$selectedId, \$selectedName, maxResults) {
                        if (typeof schoolFinderPro === 'undefined') {
                            return;
                        }
                        
                        var \$wrapper = \$input.closest('.school-finder-pro-gf-wrapper');
                        \$wrapper.addClass('loading');
                        \$results.html('<div class=\"school-finder-pro-loading\">' + schoolFinderPro.i18n.searching + '</div>').show();
                        
                        $.ajax({
                            url: schoolFinderPro.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'school_finder_pro_search',
                                search: searchTerm,
                                limit: maxResults,
                                nonce: schoolFinderPro.nonce
                            },
                            success: function(response) {
                                \$wrapper.removeClass('loading');
                                
                                if (response.success && response.data.schools.length > 0) {
                                    var html = '';
                                    response.data.schools.forEach(function(school) {
                                        html += '<div class=\"school-finder-pro-item\" data-id=\"' + school.id + '\" data-name=\"' + schoolFinderProGF.escapeHtml(school.name) + '\">';
                                        html += '<div class=\"school-finder-pro-item-name\">' + schoolFinderProGF.escapeHtml(school.name) + '</div>';
                                        html += '<div class=\"school-finder-pro-item-address\">' + schoolFinderProGF.escapeHtml(school.address) + '</div>';
                                        if (school.postcode) {
                                            html += '<div class=\"school-finder-pro-item-postcode\">' + schoolFinderProGF.escapeHtml(school.postcode) + '</div>';
                                        }
                                        html += '<div class=\"school-finder-pro-item-town\">' + schoolFinderProGF.escapeHtml(school.town) + '</div>';
                                        html += '</div>';
                                    });
                                    \$results.html(html).show();
                                    
                                    // Handle hover
                                    \$results.find('.school-finder-pro-item').on('mouseenter', function() {
                                        \$results.find('.school-finder-pro-item').removeClass('active');
                                        $(this).addClass('active');
                                    });
                                } else {
                                    \$results.html('<div class=\"school-finder-pro-no-results\">' + schoolFinderPro.i18n.noResults + '</div>').show();
                                }
                            },
                            error: function() {
                                \$wrapper.removeClass('loading');
                                \$results.html('<div class=\"school-finder-pro-error\">' + schoolFinderPro.i18n.noResults + '</div>').show();
                            }
                        });
                    },
                    escapeHtml: function(text) {
                        var map = {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '\"': '&quot;',
                            \"'\": '&#039;'
                        };
                        return text.replace(/[&<>\"']/g, function(m) { return map[m]; });
                    },
                    applySpacing: function() {
                        // Apply margin-top inline to override Gravity Forms reset
                        $('.ginput_container_school_finder').each(function() {
                            var \$container = $(this);
                            if (!\$container.attr('data-spacing-applied')) {
                                \$container.css({
                                    'margin-top': '12px',
                                    'position': 'relative',
                                    'display': 'block'
                                });
                                \$container.attr('data-spacing-applied', 'true');
                            }
                        });
                        
                        // Apply margin-bottom to labels
                        $('.gfield--type-school_finder .gfield_label').each(function() {
                            var \$label = $(this);
                            if (!\$label.attr('data-spacing-applied')) {
                                \$label.css({
                                    'margin-bottom': '12px',
                                    'display': 'block'
                                });
                                \$label.attr('data-spacing-applied', 'true');
                            }
                        });
                    }
                };
            }
            
            // Initialize on page load
            schoolFinderProGF.init();
            
            // Apply margin-top inline as fallback (overrides Gravity Forms reset)
            schoolFinderProGF.applySpacing();
            
            // Re-initialize after AJAX form submissions (for multi-page forms)
            $(document).on('gform_post_render', function() {
                schoolFinderProGF.init();
                schoolFinderProGF.applySpacing();
            });
        });
        ";
        
        return $script;
    }
}

// Register the field when Gravity Forms is loaded
class GF_Field_School_Finder_Register {
    public static function register() {
        if (method_exists('GF_Fields', 'register')) {
            GF_Fields::register(new GF_Field_School_Finder());
        }
    }
}

// Register on Gravity Forms load
add_action('gform_loaded', array('GF_Field_School_Finder_Register', 'register'), 5);
