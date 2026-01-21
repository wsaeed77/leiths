<?php
/**
 * Frontend interface for School Finder Pro plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class School_Finder_Pro_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('school_finder', array($this, 'render_shortcode'));
        // Enqueue on all frontend pages - use standard priority like JS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue CSS - same approach as JS, always enqueue on frontend
        wp_enqueue_style(
            'school-finder-pro-frontend',
            SCHOOL_FINDER_PRO_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            SCHOOL_FINDER_PRO_VERSION,
            'all'
        );
        
        // Add inline style as fallback
        $inline_css = "
        .ginput_container_school_finder {
            position: relative !important;
            display: block !important;
            overflow: visible !important;
        }
        ";
        wp_add_inline_style('school-finder-pro-frontend', $inline_css);
        
        // Enqueue JS - same approach
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
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => __('Start typing the name of your school to begin searching.', 'school-finder-pro'),
            'min_chars' => 2,
            'max_results' => 10
        ), $atts, 'school_finder');
        
        ob_start();
        ?>
        <div class="school-finder-pro-container">
            <div class="school-finder-pro-card">
                <h2 class="school-finder-pro-title"><?php _e('Select your school', 'school-finder-pro'); ?></h2>
                <p class="school-finder-pro-instructions"><?php echo esc_html($atts['placeholder']); ?></p>
                
                <div class="school-finder-pro-search-wrapper">
                    <input 
                        type="text" 
                        id="school-finder-pro-input" 
                        class="school-finder-pro-input" 
                        placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                        autocomplete="off"
                        data-min-chars="<?php echo esc_attr($atts['min_chars']); ?>"
                        data-max-results="<?php echo esc_attr($atts['max_results']); ?>"
                    >
                    <div class="school-finder-pro-results" id="school-finder-pro-results"></div>
                </div>
                
                <input type="hidden" id="school-finder-pro-selected-id" name="school_id" value="">
                <input type="hidden" id="school-finder-pro-selected-name" name="school_name" value="">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
