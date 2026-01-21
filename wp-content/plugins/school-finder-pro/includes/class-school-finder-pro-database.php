<?php
/**
 * Database handler for School Finder Pro plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class School_Finder_Pro_Database {
    
    /**
     * Get table name
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'school_finder_pro';
    }
    
    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            urn varchar(20) DEFAULT NULL,
            la_code varchar(10) DEFAULT NULL,
            la_name varchar(255) DEFAULT NULL,
            establishment_number varchar(20) DEFAULT NULL,
            establishment_name varchar(255) NOT NULL,
            type_of_establishment varchar(255) DEFAULT NULL,
            establishment_status varchar(100) DEFAULT NULL,
            open_date varchar(50) DEFAULT NULL,
            phase_of_education varchar(100) DEFAULT NULL,
            statutory_low_age int(11) DEFAULT NULL,
            statutory_high_age int(11) DEFAULT NULL,
            boarders varchar(50) DEFAULT NULL,
            official_sixth_form varchar(50) DEFAULT NULL,
            gender varchar(50) DEFAULT NULL,
            religious_character varchar(255) DEFAULT NULL,
            admissions_policy varchar(255) DEFAULT NULL,
            ukprn varchar(20) DEFAULT NULL,
            street varchar(255) DEFAULT NULL,
            locality varchar(255) DEFAULT NULL,
            address3 varchar(255) DEFAULT NULL,
            town varchar(255) DEFAULT NULL,
            county varchar(255) DEFAULT NULL,
            postcode varchar(20) DEFAULT NULL,
            school_website varchar(255) DEFAULT NULL,
            telephone_num varchar(50) DEFAULT NULL,
            head_title varchar(50) DEFAULT NULL,
            head_first_name varchar(100) DEFAULT NULL,
            head_last_name varchar(100) DEFAULT NULL,
            head_preferred_job_title varchar(255) DEFAULT NULL,
            gor varchar(100) DEFAULT NULL,
            parliamentary_constituency_code varchar(20) DEFAULT NULL,
            parliamentary_constituency_name varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY establishment_name (establishment_name),
            KEY town (town),
            KEY postcode (postcode),
            KEY search_fields (establishment_name(100), street(100), locality(100), town(100), county(100), postcode)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Ensure table exists (create if it doesn't)
     */
    public static function ensure_table_exists() {
        global $wpdb;
        $table_name = self::get_table_name();
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            // Create table if it doesn't exist
            self::create_table();
        }
    }
    
    /**
     * Insert school from CSV row
     */
    public static function insert_school($data) {
        global $wpdb;
        
        // Ensure table exists before inserting
        self::ensure_table_exists();
        
        $table_name = self::get_table_name();
        
        $insert_data = array(
            'urn' => isset($data['URN']) ? sanitize_text_field($data['URN']) : null,
            'la_code' => isset($data['LA (code)']) ? sanitize_text_field($data['LA (code)']) : null,
            'la_name' => isset($data['LA (name)']) ? sanitize_text_field($data['LA (name)']) : null,
            'establishment_number' => isset($data['EstablishmentNumber']) ? sanitize_text_field($data['EstablishmentNumber']) : null,
            'establishment_name' => isset($data['EstablishmentName']) ? sanitize_text_field($data['EstablishmentName']) : '',
            'type_of_establishment' => isset($data['TypeOfEstablishment (name)']) ? sanitize_text_field($data['TypeOfEstablishment (name)']) : null,
            'establishment_status' => isset($data['EstablishmentStatus (name)']) ? sanitize_text_field($data['EstablishmentStatus (name)']) : null,
            'open_date' => isset($data['OpenDate']) ? sanitize_text_field($data['OpenDate']) : null,
            'phase_of_education' => isset($data['PhaseOfEducation (name)']) ? sanitize_text_field($data['PhaseOfEducation (name)']) : null,
            'statutory_low_age' => isset($data['StatutoryLowAge']) && $data['StatutoryLowAge'] !== '' ? intval($data['StatutoryLowAge']) : null,
            'statutory_high_age' => isset($data['StatutoryHighAge']) && $data['StatutoryHighAge'] !== '' ? intval($data['StatutoryHighAge']) : null,
            'boarders' => isset($data['Boarders (name)']) ? sanitize_text_field($data['Boarders (name)']) : null,
            'official_sixth_form' => isset($data['OfficialSixthForm (name)']) ? sanitize_text_field($data['OfficialSixthForm (name)']) : null,
            'gender' => isset($data['Gender (name)']) ? sanitize_text_field($data['Gender (name)']) : null,
            'religious_character' => isset($data['ReligiousCharacter (name)']) ? sanitize_text_field($data['ReligiousCharacter (name)']) : null,
            'admissions_policy' => isset($data['AdmissionsPolicy (name)']) ? sanitize_text_field($data['AdmissionsPolicy (name)']) : null,
            'ukprn' => isset($data['UKPRN']) ? sanitize_text_field($data['UKPRN']) : null,
            'street' => isset($data['Street']) ? sanitize_text_field($data['Street']) : null,
            'locality' => isset($data['Locality']) ? sanitize_text_field($data['Locality']) : null,
            'address3' => isset($data['Address3']) ? sanitize_text_field($data['Address3']) : null,
            'town' => isset($data['Town']) ? sanitize_text_field($data['Town']) : null,
            'county' => isset($data['County (name)']) ? sanitize_text_field($data['County (name)']) : null,
            'postcode' => isset($data['Postcode']) ? sanitize_text_field($data['Postcode']) : null,
            'school_website' => isset($data['SchoolWebsite']) ? esc_url_raw($data['SchoolWebsite']) : null,
            'telephone_num' => isset($data['TelephoneNum']) ? sanitize_text_field($data['TelephoneNum']) : null,
            'head_title' => isset($data['HeadTitle (name)']) ? sanitize_text_field($data['HeadTitle (name)']) : null,
            'head_first_name' => isset($data['HeadFirstName']) ? sanitize_text_field($data['HeadFirstName']) : null,
            'head_last_name' => isset($data['HeadLastName']) ? sanitize_text_field($data['HeadLastName']) : null,
            'head_preferred_job_title' => isset($data['HeadPreferredJobTitle']) ? sanitize_text_field($data['HeadPreferredJobTitle']) : null,
            'gor' => isset($data['GOR (name)']) ? sanitize_text_field($data['GOR (name)']) : null,
            'parliamentary_constituency_code' => isset($data['ParliamentaryConstituency (code)']) ? sanitize_text_field($data['ParliamentaryConstituency (code)']) : null,
            'parliamentary_constituency_name' => isset($data['ParliamentaryConstituency (name)']) ? sanitize_text_field($data['ParliamentaryConstituency (name)']) : null,
        );
        
        // Remove null values to use database defaults
        $insert_data = array_filter($insert_data, function($value) {
            return $value !== null && $value !== '';
        });
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        return $result !== false;
    }
    
    /**
     * Clear all schools
     */
    public static function clear_all_schools() {
        global $wpdb;
        $table_name = self::get_table_name();
        return $wpdb->query("TRUNCATE TABLE $table_name");
    }
    
    /**
     * Get total count of schools
     */
    public static function get_total_count() {
        global $wpdb;
        
        // Ensure table exists before querying
        self::ensure_table_exists();
        
        $table_name = self::get_table_name();
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    /**
     * Get school by ID
     */
    public static function get_school_by_id($school_id) {
        global $wpdb;
        
        // Ensure table exists before querying
        self::ensure_table_exists();
        
        $table_name = self::get_table_name();
        $school = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $school_id), ARRAY_A);
        
        return $school;
    }
    
    /**
     * Search schools
     */
    public static function search_schools($search_term, $limit = 10) {
        global $wpdb;
        
        // Ensure table exists before searching
        self::ensure_table_exists();
        
        $table_name = self::get_table_name();
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';
        
        $query = $wpdb->prepare(
            "SELECT 
                id,
                establishment_name,
                street,
                locality,
                address3,
                town,
                county,
                postcode
            FROM $table_name
            WHERE 
                establishment_name LIKE %s
                OR street LIKE %s
                OR locality LIKE %s
                OR town LIKE %s
                OR county LIKE %s
                OR postcode LIKE %s
            ORDER BY 
                CASE 
                    WHEN establishment_name LIKE %s THEN 1
                    WHEN town LIKE %s THEN 2
                    ELSE 3
                END,
                establishment_name ASC
            LIMIT %d",
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            '%' . $wpdb->esc_like($search_term) . '%',
            '%' . $wpdb->esc_like($search_term) . '%',
            $limit
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Format results
        $formatted_results = array();
        foreach ($results as $school) {
            // Build full address
            $address_parts = array_filter(array(
                $school['street'],
                $school['locality'],
                $school['address3'],
                $school['town'],
                $school['county']
            ));
            $full_address = implode(', ', $address_parts);
            
            $formatted_results[] = array(
                'id' => $school['id'],
                'name' => $school['establishment_name'],
                'address' => $full_address,
                'town' => $school['town'] ? $school['town'] : ($school['county'] ? $school['county'] : ''),
                'postcode' => $school['postcode']
            );
        }
        
        return $formatted_results;
    }
}
