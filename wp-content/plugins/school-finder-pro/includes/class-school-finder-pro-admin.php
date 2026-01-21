<?php
/**
 * Admin interface for School Finder Pro plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class School_Finder_Pro_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_school_finder_pro_upload_csv', array($this, 'handle_csv_upload'));
        
        // Clear plugin cache on admin page load to fix path issues
        if (isset($_GET['page']) && $_GET['page'] === 'school-finder-pro') {
            wp_cache_delete('plugins', 'plugins');
            delete_transient('plugins');
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('School Finder Pro', 'school-finder-pro'),
            __('School Finder Pro', 'school-finder-pro'),
            'manage_options',
            'school-finder-pro',
            array($this, 'render_admin_page'),
            'dashicons-search',
            30
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_school-finder-pro') {
            return;
        }
        
        wp_enqueue_style(
            'school-finder-pro-admin',
            SCHOOL_FINDER_PRO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SCHOOL_FINDER_PRO_VERSION
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        $total_schools = School_Finder_Pro_Database::get_total_count();
        $upload_message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
        $upload_error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
        ?>
        <div class="wrap school-finder-pro-admin">
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
            
            <div class="school-finder-pro-stats">
                <div class="stat-box">
                    <h3><?php _e('Total Schools', 'school-finder-pro'); ?></h3>
                    <p class="stat-number"><?php echo number_format($total_schools); ?></p>
                </div>
            </div>
            
            <div class="school-finder-pro-upload-section">
                <h2><?php _e('Upload School Data', 'school-finder-pro'); ?></h2>
                <p><?php _e('Upload a CSV file containing school data. The file should include columns: URN, LA (code), LA (name), EstablishmentNumber, EstablishmentName, and other school information.', 'school-finder-pro'); ?></p>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" class="school-finder-pro-upload-form">
                    <?php wp_nonce_field('school_finder_pro_upload_csv', 'school_finder_pro_nonce'); ?>
                    <input type="hidden" name="action" value="school_finder_pro_upload_csv">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="csv_file"><?php _e('CSV File', 'school-finder-pro'); ?></label>
                            </th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                                <p class="description"><?php _e('Select a CSV file to upload. Maximum file size: 50MB', 'school-finder-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="clear_existing"><?php _e('Clear Existing Data', 'school-finder-pro'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="clear_existing" id="clear_existing" value="1">
                                    <?php _e('Clear all existing school data before importing new data', 'school-finder-pro'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Upload CSV', 'school-finder-pro')); ?>
                </form>
            </div>
            
            <div class="school-finder-pro-shortcode-section">
                <h2><?php _e('Usage', 'school-finder-pro'); ?></h2>
                <p><?php _e('To display the school lookup search on your website, use the following shortcode:', 'school-finder-pro'); ?></p>
                <code>[school_finder]</code>
                <p class="description"><?php _e('You can also use it in widgets or directly in your theme templates.', 'school-finder-pro'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle CSV upload
     */
    public function handle_csv_upload() {
        // Check nonce
        if (!isset($_POST['school_finder_pro_nonce']) || !wp_verify_nonce($_POST['school_finder_pro_nonce'], 'school_finder_pro_upload_csv')) {
            wp_die(__('Security check failed', 'school-finder-pro'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action', 'school-finder-pro'));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg(array(
                'page' => 'school-finder-pro',
                'error' => urlencode(__('File upload failed. Please try again.', 'school-finder-pro'))
            ), admin_url('admin.php')));
            exit;
        }
        
        $file = $_FILES['csv_file'];
        
        // Check file type
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            wp_redirect(add_query_arg(array(
                'page' => 'school-finder-pro',
                'error' => urlencode(__('Invalid file type. Please upload a CSV file.', 'school-finder-pro'))
            ), admin_url('admin.php')));
            exit;
        }
        
        // Check file size (50MB max)
        if ($file['size'] > 50 * 1024 * 1024) {
            wp_redirect(add_query_arg(array(
                'page' => 'school-finder-pro',
                'error' => urlencode(__('File is too large. Maximum size is 50MB.', 'school-finder-pro'))
            ), admin_url('admin.php')));
            exit;
        }
        
        // Clear existing data if requested
        if (isset($_POST['clear_existing']) && $_POST['clear_existing'] === '1') {
            School_Finder_Pro_Database::clear_all_schools();
        }
        
        // Process CSV file
        $result = $this->process_csv_file($file['tmp_name']);
        
        if ($result['success']) {
            wp_redirect(add_query_arg(array(
                'page' => 'school-finder-pro',
                'message' => urlencode(sprintf(__('Successfully imported %d schools.', 'school-finder-pro'), $result['count']))
            ), admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(array(
                'page' => 'school-finder-pro',
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
            return array('success' => false, 'message' => __('File could not be read.', 'school-finder-pro'));
        }
        
        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            return array('success' => false, 'message' => __('Could not open file.', 'school-finder-pro'));
        }
        
        // Read header row with proper CSV parsing (handles quotes, commas, etc.)
        $headers = fgetcsv($handle, 0, ',', '"', '"');
        if ($headers === false || empty($headers)) {
            fclose($handle);
            return array('success' => false, 'message' => __('Could not read CSV headers.', 'school-finder-pro'));
        }
        
        // Clean headers (remove BOM, quotes, and trim)
        $headers = array_map(function($header) {
            $header = trim($header);
            $header = preg_replace('/\x{FEFF}/u', '', $header); // Remove BOM
            $header = trim($header, '"'); // Remove surrounding quotes
            return $header;
        }, $headers);
        
        $imported = 0;
        $errors = 0;
        $batch_size = 100;
        $batch = array();
        $row_number = 1; // Start at 1 since we already read header
        
        // Read data rows
        while (($row = fgetcsv($handle, 0, ',', '"', '"')) !== false) {
            $row_number++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Clean row data (remove quotes and trim)
            $row = array_map(function($field) {
                $field = trim($field);
                $field = trim($field, '"');
                return $field;
            }, $row);
            
            // Handle rows with different column counts (pad or truncate)
            $row_count = count($row);
            $header_count = count($headers);
            
            if ($row_count < $header_count) {
                // Pad with empty strings
                $row = array_pad($row, $header_count, '');
            } elseif ($row_count > $header_count) {
                // Truncate to match headers
                $row = array_slice($row, 0, $header_count);
            }
            
            // Combine headers with row data
            $data = array_combine($headers, $row);
            
            // Skip if establishment name is empty
            if (empty($data['EstablishmentName']) || trim($data['EstablishmentName']) === '') {
                $errors++;
                continue;
            }
            
            $batch[] = $data;
            
            // Insert in batches for better performance
            if (count($batch) >= $batch_size) {
                foreach ($batch as $school_data) {
                    if (School_Finder_Pro_Database::insert_school($school_data)) {
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
            if (School_Finder_Pro_Database::insert_school($school_data)) {
                $imported++;
            } else {
                $errors++;
            }
        }
        
        fclose($handle);
        
        $message = sprintf(__('Imported %d schools successfully.', 'school-finder-pro'), $imported);
        if ($errors > 0) {
            $message .= ' ' . sprintf(__('%d rows had errors or were skipped.', 'school-finder-pro'), $errors);
        }
        
        return array(
            'success' => true,
            'count' => $imported,
            'errors' => $errors,
            'message' => $message
        );
    }
}
