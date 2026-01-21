<?php
/**
 * Frontend interface for School Lookup plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class School_Lookup_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('school_lookup', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'school-lookup-frontend',
            SCHOOL_LOOKUP_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            SCHOOL_LOOKUP_VERSION
        );
        
        wp_enqueue_script(
            'school-lookup-frontend',
            SCHOOL_LOOKUP_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            SCHOOL_LOOKUP_VERSION,
            true
        );
        
        wp_localize_script('school-lookup-frontend', 'schoolLookup', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('school_lookup_search'),
            'i18n' => array(
                'noResults' => __('No schools found', 'school-lookup'),
                'searching' => __('Searching...', 'school-lookup'),
                'startTyping' => __('Start typing the name of your school to begin searching.', 'school-lookup')
            )
        ));
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => __('Start typing the name of your school to begin searching.', 'school-lookup'),
            'min_chars' => 2,
            'max_results' => 10
        ), $atts, 'school_lookup');
        
        ob_start();
        ?>
        <div class="school-lookup-container">
            <div class="school-lookup-card">
                <h2 class="school-lookup-title"><?php _e('Select your school', 'school-lookup'); ?></h2>
                <p class="school-lookup-instructions"><?php echo esc_html($atts['placeholder']); ?></p>
                
                <div class="school-lookup-search-wrapper">
                    <input 
                        type="text" 
                        id="school-lookup-input" 
                        class="school-lookup-input" 
                        placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                        autocomplete="off"
                        data-min-chars="<?php echo esc_attr($atts['min_chars']); ?>"
                        data-max-results="<?php echo esc_attr($atts['max_results']); ?>"
                    >
                    <div class="school-lookup-results" id="school-lookup-results"></div>
                </div>
                
                <input type="hidden" id="school-lookup-selected-id" name="school_id" value="">
                <input type="hidden" id="school-lookup-selected-name" name="school_name" value="">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
