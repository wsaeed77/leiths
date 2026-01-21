<?php
/**
 * Admin interface for School Lookup plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class School_Lookup_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_school_lookup_upload_csv', array($this, 'handle_csv_upload'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('School Lookup', 'school-lookup'),
            __('School Lookup', 'school-lookup'),
            'manage_options',
            'school-lookup',
            array($this, 'render_admin_page'),
            'dashicons-search',
            30
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_school-lookup') {
            return;
        }
        
        wp_enqueue_style(
            'school-lookup-admin',
            SCHOOL_LOOKUP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SCHOOL_LOOKUP_VERSION
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $total_schools = School_Lookup_Database::get_total_count();
        $upload_message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
        $upload_error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
        ?>
        <div class="wrap school-lookup-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if ($upload_message): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($upload_message); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($upload_error): ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($upload_error); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="school-lookup-stats">
                <div class="stat-box">
                    <h3><?php _e('Total Schools', 'school-lookup'); ?></h3>
                    <p class="stat-number"><?php echo number_format($total_schools); ?></p>
                </div>
            </div>
            
            <div class="school-lookup-upload-section">
                <h2><?php _e('Upload School Data', 'school-lookup'); ?></h2>
                <p><?php _e('Upload a CSV file containing school data. The file should include columns: URN, LA (code), LA (name), EstablishmentNumber, EstablishmentName, and other school information.', 'school-lookup'); ?></p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" class="school-lookup-upload-form">
                    <?php wp_nonce_field('school_lookup_upload_csv', 'school_lookup_nonce'); ?>
                    <input type="hidden" name="action" value="school_lookup_upload_csv">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="csv_file"><?php _e('CSV File', 'school-lookup'); ?></label>
                            </th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                                <p class="description"><?php _e('Select a CSV file to upload. Maximum file size: 50MB', 'school-lookup'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="clear_existing"><?php _e('Clear Existing Data', 'school-lookup'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="clear_existing" id="clear_existing" value="1">
                                    <?php _e('Clear all existing school data before importing new data', 'school-lookup'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Upload CSV', 'school-lookup')); ?>
                </form>
            </div>
            
            <div class="school-lookup-shortcode-section">
                <h2><?php _e('Usage', 'school-lookup'); ?></h2>
                <p><?php _e('To display the school lookup search on your website, use the following shortcode:', 'school-lookup'); ?></p>
                <code>[school_lookup]</code>
                <p class="description"><?php _e('You can also use it in widgets or directly in your theme templates.', 'school-lookup'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle CSV upload
     */
    public function handle_csv_upload() {
        // Check nonce
        if (!isset($_POST['school_lookup_nonce']) || !wp_verify_nonce($_POST['school_lookup_nonce'], 'school_lookup_upload_csv')) {
            wp_die(__('Security check failed', 'school-lookup'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action', 'school-lookup'));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg(array(
                'page' => 'school-lookup',
                'error' => urlencode(__('File upload failed. Please try again.', 'school-lookup'))
            ), admin_url('admin.php')));
            exit;
        }
        
        $file = $_FILES['csv_file'];
        
        // Check file type
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            wp_redirect(add_query_arg(array(
                'page' => 'school-lookup',
                'error' => urlencode(__('Invalid file type. Please upload a CSV file.', 'school-lookup'))
            ), admin_url('admin.php')));
            exit;
        }
        
        // Check file size (50MB max)
        if ($file['size'] > 50 * 1024 * 1024) {
            wp_redirect(add_query_arg(array(
                'page' => 'school-lookup',
                'error' => urlencode(__('File is too large. Maximum size is 50MB.', 'school-lookup'))
            ), admin_url('admin.php')));
            exit;
        }
        
        // Clear existing data if requested
        if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === '1') {
            School_Lookup_Database::clear_all_schools();
        }
        
        // Process CSV file
        $result = $this->process_csv_file($file['tmp_name']);
        
        if ($result['success']) {
            wp_redirect(add_query_arg(array(
                'page' => 'school-lookup',
                'message' => urlencode(sprintf(__('Successfully imported %d schools.', 'school-lookup'), $result['count']))
            ), admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(array(
                'page' => 'school-lookup',
                'error' => urlencode($result['message'])
            ), admin_url('admin.php')));
        }
        exit;
    }
    
    /**
     * Process CSV file
     */
    private function process_csv_file($file_path) {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return array('success' => false, 'message' => __('File could not be read.', 'school-lookup'));
        }
        
        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            return array('success' => false, 'message' => __('Could not open file.', 'school-lookup'));
        }
        
        // Read header row
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return array('success' => false, 'message' => __('Could not read CSV headers.', 'school-lookup'));
        }
        
        // Clean headers (remove BOM if present)
        $headers = array_map(function($header) {
            return trim(preg_replace('/\x{FEFF}/u', '', $header));
        }, $headers);
        
        $imported = 0;
        $errors = 0;
        $batch_size = 100;
        $batch = array();
        
        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                $errors++;
                continue;
            }
            
            // Combine headers with row data
            $data = array_combine($headers, $row);
            
            // Skip if establishment name is empty
            if (empty($data['EstablishmentName'])) {
                $errors++;
                continue;
            }
            
            $batch[] = $data;
            
            // Insert in batches for better performance
            if (count($batch) >= $batch_size) {
                foreach ($batch as $school_data) {
                    if (School_Lookup_Database::insert_school($school_data)) {
                        $imported++;
                    } else {
                        $errors++;
                    }
                }
                $batch = array();
            }
        }
        
        // Insert remaining batch
        foreach ($batch as $school_data) {
            if (School_Lookup_Database::insert_school($school_data)) {
                $imported++;
            } else {
                $errors++;
            }
        }
        
        fclose($handle);
        
        $message = sprintf(__('Imported %d schools successfully.', 'school-lookup'), $imported);
        if ($errors > 0) {
            $message .= ' ' . sprintf(__('%d rows had errors.', 'school-lookup'), $errors);
        }
        
        return array(
            'success' => true,
            'count' => $imported,
            'errors' => $errors,
            'message' => $message
        );
    }
}
