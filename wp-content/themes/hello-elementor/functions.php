<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('HELLO_ELEMENTOR_VERSION', '2.8.1');
define('LEITHS_LIST_VERSION', '0.0.1');

if (!isset($content_width)) {
    $content_width = 800; // Pixels.
}

if (!function_exists('hello_elementor_setup')) {
    /**
     * Set up theme support.
     *
     * @return void
     */
    function hello_elementor_setup()
    {
        if (is_admin()) {
            hello_maybe_update_theme_version_in_db();
        }

        if (apply_filters('hello_elementor_register_menus', true)) {
            register_nav_menus(['menu-1' => esc_html__('Header', 'hello-elementor')]);
            register_nav_menus(['menu-2' => esc_html__('Footer', 'hello-elementor')]);
        }

        if (apply_filters('hello_elementor_post_type_support', true)) {
            add_post_type_support('page', 'excerpt');
        }

        if (apply_filters('hello_elementor_add_theme_support', true)) {
            add_theme_support('post-thumbnails');
            add_theme_support('automatic-feed-links');
            add_theme_support('title-tag');
            add_theme_support(
                'html5',
                [
                    'search-form',
                    'comment-form',
                    'comment-list',
                    'gallery',
                    'caption',
                    'script',
                    'style',
                ]
            );
            add_theme_support(
                'custom-logo',
                [
                    'height' => 100,
                    'width' => 350,
                    'flex-height' => true,
                    'flex-width' => true,
                ]
            );

            /*
             * Editor Style.
             */
            add_editor_style('classic-editor.css');

            /*
             * Gutenberg wide images.
             */
            add_theme_support('align-wide');

            /*
             * WooCommerce.
             */
            if (apply_filters('hello_elementor_add_woocommerce_support', true)) {
                // WooCommerce in general.
                add_theme_support('woocommerce');
                // Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
                // zoom.
                add_theme_support('wc-product-gallery-zoom');
                // lightbox.
                add_theme_support('wc-product-gallery-lightbox');
                // swipe.
                add_theme_support('wc-product-gallery-slider');
            }
        }
    }
}
add_action('after_setup_theme', 'hello_elementor_setup');

function hello_maybe_update_theme_version_in_db()
{
    $theme_version_option_name = 'hello_theme_version';
    // The theme version saved in the database.
    $hello_theme_db_version = get_option($theme_version_option_name);

    // If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
    if (!$hello_theme_db_version || version_compare($hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<')) {
        update_option($theme_version_option_name, HELLO_ELEMENTOR_VERSION);
    }
}

if (!function_exists('hello_elementor_scripts_styles')) {
    /**
     * Theme Scripts & Styles.
     *
     * @return void
     */
    function hello_elementor_scripts_styles()
    {
        $min_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        if (apply_filters('hello_elementor_enqueue_style', true)) {
            wp_enqueue_style(
                'hello-elementor',
                get_template_directory_uri() . '/style' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }

        if (apply_filters('hello_elementor_enqueue_theme_style', true)) {
            wp_enqueue_style(
                'hello-elementor-theme-style',
                get_template_directory_uri() . '/theme' . $min_suffix . '.css',
                [],
                HELLO_ELEMENTOR_VERSION
            );
        }

        if (apply_filters('leiths_list_enqueue_style', true)) {
            wp_enqueue_style(
                'leiths-list-style',
                get_template_directory_uri() . '/leiths-list.css',
                [],
                LEITHS_LIST_VERSION
            );
        }

        wp_enqueue_script('leiths-script', get_template_directory_uri() . '/assets/js/leiths.js', [], '202309261115', true);
    }
}
add_action('wp_enqueue_scripts', 'hello_elementor_scripts_styles');

if (!function_exists('hello_elementor_register_elementor_locations')) {
    /**
     * Register Elementor Locations.
     *
     * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
     *
     * @return void
     */
    function hello_elementor_register_elementor_locations($elementor_theme_manager)
    {
        if (apply_filters('hello_elementor_register_elementor_locations', true)) {
            $elementor_theme_manager->register_all_core_location();
        }
    }
}
add_action('elementor/theme/register_locations', 'hello_elementor_register_elementor_locations');

if (!function_exists('hello_elementor_content_width')) {
    /**
     * Set default content width.
     *
     * @return void
     */
    function hello_elementor_content_width()
    {
        $GLOBALS['content_width'] = apply_filters('hello_elementor_content_width', 800);
    }
}
add_action('after_setup_theme', 'hello_elementor_content_width', 0);

if (is_admin()) {
    require get_template_directory() . '/includes/admin-functions.php';
}

/**
 * If Elementor is installed and active, we can load the Elementor-specific Settings & Features
 */

// Allow active/inactive via the Experiments
require get_template_directory() . '/includes/elementor-functions.php';

/**
 * Include customizer registration functions
 */
function hello_register_customizer_functions()
{
    if (is_customize_preview()) {
        require get_template_directory() . '/includes/customizer-functions.php';
    }
}

add_action('init', 'hello_register_customizer_functions');

if (!function_exists('hello_elementor_check_hide_title')) {
    /**
     * Check hide title.
     *
     * @param bool $val default value.
     *
     * @return bool
     */
    function hello_elementor_check_hide_title($val)
    {
        if (defined('ELEMENTOR_VERSION')) {
            $current_doc = Elementor\Plugin::instance()->documents->get(get_the_ID());
            if ($current_doc && 'yes' === $current_doc->get_settings('hide_title')) {
                $val = false;
            }
        }
        return $val;
    }
}
add_filter('hello_elementor_page_title', 'hello_elementor_check_hide_title');

if (!function_exists('hello_elementor_add_description_meta_tag')) {
    /**
     * Add description meta tag with excerpt text.
     *
     * @return void
     */
    function hello_elementor_add_description_meta_tag()
    {
        $post = get_queried_object();

        if (is_singular() && !empty($post->post_excerpt)) {
            echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($post->post_excerpt)) . '">' . "\n";
        }
    }
}
add_action('wp_head', 'hello_elementor_add_description_meta_tag');

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if (!function_exists('hello_elementor_body_open')) {
    function hello_elementor_body_open()
    {
        wp_body_open();
    }
}

/** Begin Courses Accolades Code*/
function generate_title_and_icon_for_product_attributes()
{
    global $product;

    $audience = $product->get_attribute('audience');
    $level = $product->get_attribute('level');
    $time = $product->get_attribute('time');

    $title = '';
    $icon = '';

    // Generate title based on attributes
    if ($audience) {
        $title .= $audience . ' ';
    }
    if ($level) {
        $title .= $level . ' ';
    }
    if ($time) {
        $title .= $time;
    }

    // Generate icon based on attributes
    if ($audience === 'Beginner') {
        $icon = '<i class="fas fa-user"></i>';
    } elseif ($level === 'Advanced') {
        $icon = '<i class="fas fa-rocket"></i>';
    } elseif ($time === 'Full Day') {
        $icon = '<i class="fas fa-clock"></i>';
    }

    echo '<div class="custom-product-info">';
    echo '<h3>' . esc_html($title) . '</h3>';
    echo '<div class="icon">' . $icon . '</div>';
    echo '</div>';
}

/** End Courses Accolades Code */


/**
 * Below is code to display the ACF fields as button links.
 */
function acf_buttons_with_navigation($atts)
{
    // Extract shortcode attributes (if needed)
    extract(shortcode_atts(array(
        'field_name' => 'linked-course-1' // Default ACF field name
    ), $atts));

    // Get the current post ID
    $post_id = get_the_ID();

    // Get the selected product IDs from the ACF field 'linked-course-1'
    $selected_product_ids = get_field($field_name, $post_id);

    if ($selected_product_ids && is_array($selected_product_ids)) {
        $buttons_html = '<div class="acf-buttons">';

        foreach ($selected_product_ids as $product_id) {
            $product = get_post($product_id);

            if ($product) {
                $product_title = esc_html($product->post_title);
                $product_link = get_permalink($product_id);

                // Check if the product is the current WooCommerce product

                echo '<div style="display:none">';
                echo $product->ID;
                echo ' / ';
                echo $post_id;
                echo '</div>';

                if ($product->ID == $post_id) {
                    $active_class = ' active';
                } else {
                    $active_class = '';
                }

                // Replace specific keywords in the product title
                if (stripos($product_title, 'Weekend') !== false) {
                    $product_title = 'Weekend Course';
                } elseif (stripos($product_title, 'Saturday') !== false) {
                    $product_title = 'Weekend Course';
                } elseif (stripos($product_title, 'Weekday') !== false) {
                    $product_title = 'Weekday Course';
                } elseif (stripos($product_title, 'Online') !== false) {
                    $product_title = 'Online Course';
                } elseif (stripos($product_title, 'Daytime') !== false) {
                    $product_title = 'In-person Daytime Course';
                } elseif (stripos($product_title, 'Evening') !== false) {
                    $product_title = 'In-person Evening Course';
                } else {
                    $product_title = 'In-person Course';
                }

                $buttons_html .= '<a class="acf-button' . $active_class . '" href="' . esc_url($product_link) . '">' . $product_title . '</a>';
            }
        }

        $buttons_html .= '</div>';
        return $buttons_html;
    }

    return '';
}

add_shortcode('acf_buttons_navigation', 'acf_buttons_with_navigation');


/**
 * Below is code to display the related products by ACF field
 */
function acf_related_products_filter($atts)
{
    $products = get_field('linked-course-1');

    if ($products) :

        echo '
        <style>
        .acf_related_product_grid {
            grid-gap: 32px;
        }
        
        .acf_related_product_grid .elementor-post__card {
            text-align: center;
        }
        </style>
        ';

        echo '<div class="elementor-element elementor-element-a1b2c30 elementor-grid-3 elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-posts--thumbnail-top elementor-card-shadow-yes elementor-posts__hover-gradient elementor-widget elementor-widget-posts" data-id="a1b2c30" data-element_type="widget" data-settings="{&quot;cards_columns&quot;:&quot;3&quot;,&quot;cards_columns_tablet&quot;:&quot;2&quot;,&quot;cards_columns_mobile&quot;:&quot;1&quot;,&quot;cards_row_gap&quot;:{&quot;unit&quot;:&quot;px&quot;,&quot;size&quot;:35,&quot;sizes&quot;:[]},&quot;cards_row_gap_tablet&quot;:{&quot;unit&quot;:&quot;px&quot;,&quot;size&quot;:&quot;&quot;,&quot;sizes&quot;:[]},&quot;cards_row_gap_mobile&quot;:{&quot;unit&quot;:&quot;px&quot;,&quot;size&quot;:&quot;&quot;,&quot;sizes&quot;:[]}}" data-widget_type="posts.cards">';
        echo '<div class="elementor-widget-container">';
        echo '<div class="acf_related_product_grid elementor-posts-container elementor-posts elementor-posts--skin-cards elementor-grid elementor-has-item-ratio">';

        foreach ($products as $product):
            $product_id = $product->ID;
            $image = get_the_post_thumbnail($product_id, 'medium');
            $title = get_the_title($product_id);
            $url = get_permalink($product_id);

            echo '<article class="elementor-post elementor-grid-item product type-product status-publish has-post-thumbnail">';
            echo '<div class="elementor-post__card">';
            echo '<a class="elementor-post__thumbnail__link" href="' . $url . '" tabindex="-1">';
            echo '<div class="elementor-post__thumbnail">';
            echo $image;
            echo '</div>';
            echo '</a>';
            echo '<div class="elementor-post__text">';
            echo '<h3 class="elementor-post__title">';
            echo '<a href="' . $url . '">';
            echo $title;
            echo '</a>';
            echo '</h3>';
            echo '</div>';
            echo '</div>';
            echo '</article>';
        endforeach;

        echo '</div >';
        echo '</div >';
        echo '</div >';

    endif;
}

add_shortcode('acf_related_products', 'acf_related_products_filter');


/**
 * Below is code to display the product child category
 */
// Shortcode to display current WooCommerce product's child subcategories as links
function current_product_child_subcategory_shortcode()
{
    global $product;

    if (is_a($product, 'WC_Product')) {
        $categories = get_the_terms($product->get_id(), 'product_cat');

        if ($categories && !is_wp_error($categories)) {
            $child_subcategory_links = array();
            foreach ($categories as $category) {
                if ($category->parent != 0) {
                    $child_subcategory_links[] = '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
                }
            }

            if (!empty($child_subcategory_links)) {
                return '<span class="product-subcategory">' . implode(', ', $child_subcategory_links) . '</span>';
            }
        }
    }

    // If no child subcategories found, display a frowning face icon
    return '<span class="product-subcategory-no-category">&#128542;</span>';
}

add_shortcode('wc-subcat', 'current_product_child_subcategory_shortcode');


/* Code to display gift voucher options */
function display_product_options_shortcode()
{
    global $product;

    // Check if it's a WooCommerce product
    if (is_product() && $product->is_type('external')) {
        $options_text = get_field('product_options', $product->get_id());

        // Check if the options_text has content
        if ($options_text) {
            $options = json_decode($options_text, true);

            // Check if $options is an array and not empty
            if (is_array($options) && !empty($options)) {
                $output = '<select id="product_options_select" onchange="updateLink()">';

                foreach ($options as $option) {
                    $output .= '<option value="' . esc_url($option['link']) . '">' . esc_html($option['label']) . '</option>';
                }

                $output .= '</select>';

                $output .= '<style>
                    #add_to_cart_button {
                        background-color: #ffffff; /* White background */
                        color: #006CB7 !important; /* Blue text color */
                        border-radius: 4px; /* Rounded corners */
                        transition: background-color 0.3s ease; /* Smooth transition on hover */
                        display: inline-block !important; /* Display button as inline-block */
                        padding: 10px 20px; /* Adjust padding as needed */
                    }

                    #add_to_cart_button:hover {
                        background-color: #002E5F !important; /* Dark blue background on hover */
                    }
                </style>';

                $output .= '<a href="' . esc_url($options[0]['link']) . '" id="add_to_cart_button" class="button alt">Purchase</a>';
                $output .= '<script>
                    function updateLink() {
                        var select = document.getElementById("product_options_select");
                        var link = select.options[select.selectedIndex].value;
                        var addToCartButton = document.getElementById("add_to_cart_button");
                        addToCartButton.href = link;
                    }
                </script>';

                return $output;
            }
        }
    }

    // Display: none if no options are available
    return '<style>#add_to_cart_button { display: none; }</style>';
}

add_shortcode('product_options', 'display_product_options_shortcode');


/** Course Info Shortcode */
function ec_shortcode_course_info($atts)
{
    $product = wc_get_product(get_the_ID());
    if ($product) {
        $ci_product_price = $product->get_price_html();
    }
    $productPod = pods(get_post_type(), get_the_ID(), false);
    if (!empty($productPod)) :
        $ci_course_type = $productPod->field('ci_course_type');
        $ci_course_level = $productPod->field('ci_course_level');
        $ci_course_certification = $productPod->field('ci_course_certification');
        $ci_deposit = $productPod->field('ci_deposit');
        $ci_consultation = $productPod->field('ci_consultation');
        $ci_online = $productPod->field('ci_online');
        $ci_online_url = $productPod->field('ci_online_url');
    endif;

    switch ($ci_course_type) {
        case "professional":
            $ct_title = "Professional";
            $ct_body = "Elevate your skills to new heights in this professional course";
            $ct_icon = "icon-ci-professional.svg";
            break;
        case "home":
            $ct_title = "Home-Cook";
            $ct_body = "Elevate your skills to new heights in this home-cook course";
            $ct_icon = "icon-ci-homecook.svg";
            break;
        default:
            $ct_title = "";
            $ct_body = "";
            $ct_icon = "";
    }

    switch ($ci_course_level) {
        case "1":
            $cl_title = "Beginner";
            $cl_body = "Best suited for people with little or no culinary experience";
            $cl_icon = "icon-ci-beginner.svg";
            break;
        case "2":
            $cl_title = "Intermediate";
            $cl_body = "Best suited for people with moderate culinary experience";
            $cl_icon = "icon-ci-intermediate.svg";
            break;
        case "3":
            $cl_title = "Advanced";
            $cl_body = "Best suited for people looking to advance professionally";
            $cl_icon = "icon-ci-advanced.svg";
            break;
        default:
            $cl_title = "";
            $cl_body = "";
            $cl_icon = "";
    }

    switch ($ci_course_certification) {
        case "1":
            $cc_title = "No Certification";
            $cc_body = "No certificate of completion";
            $cc_icon = "icon-ci-certificate.svg";
            break;
        case "2":
            $cc_title = "Certification";
            $cc_body = "Earn a certificate of completion and the opportunity for full accreditation";
            $cc_icon = "icon-ci-certificate.svg";
            break;
        case "3":
            $cc_title = "Certification";
            $cc_body = "Externally accredited professional course";
            $cc_icon = "icon-ci-certificate.svg";
            break;
        case "4":
            $cc_title = "Certification";
            $cc_body = "Certified professional course";
            $cc_icon = "icon-ci-certificate.svg";
            break;
        default:
            $cc_title = "";
            $cc_body = "";
            $cc_icon = "";
    }

    if (!empty($ci_course_type) && !empty($ci_course_level)) :

        echo '
        <style>
            .course-info {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-template-rows: auto;
                grid-gap: 1.5em;
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .course-info .booking-button {
                display: inline-block;
                font-size: .85rem;
                font-weight: 600;
                text-align: center;
                line-height: 1.2;
                color: rgb(0, 108, 183);
                background-color: #fff;
                border-radius: .33rem;
                padding: .5rem 1rem;
                margin-top: 1.5rem;
                transition: background-color .25s ease-in-out, color .25s ease-in-out;
            }
            
            .course-info .booking-button:hover {
                color: #fff;
                background-color: rgb(0, 68, 143);
            }
            
            .course-info h3.ci-title {
                font-family: "Libre Baskerville", sans-serif;
                color: #fff;
                margin: 0;
                font-size: 1.25em;
            }
            
            .course-info p.ci-body {
                color: #fff;
                margin: 0;
                font-size: .8em;
            }
            
            .course-info img.ci-img {
                width: 40px;
                height: auto;
                display: inline-block;
                margin-bottom: .5rem;
                box-shadow: none;
            }
            
            @media screen and (max-width: 767px) {
                .course-info {
                    display: grid;
                    grid-template-columns: auto;
                    grid-template-rows: 1fr;
                    grid-gap: 1.5em;
                    width: 100%;
                    margin: 0;
                }
                
                .course-info > div {
                    display: grid;
                    grid-template-columns: 32px auto;
                    grid-gap: 1em;
                    align-items: flex-start;
                }
                
                .course-info img.ci-img {
                    width: 32px;
                    height: auto;
                    display: block;
                    box-shadow: none;
                }
            }
        </style>
        ';

        echo '<div class="course-info">';
        echo '<div>';
        echo '<div>';
        echo '<img class="ci-img" src="/wp-content/themes/hello-elementor/assets/images/' . $ct_icon . '">';
        echo '</div>';
        echo '<div>';
        echo '<h3 class="ci-title">' . $ct_title . '</h3>';
        echo '<p class="ci-body">' . $ct_body . '</p>';
        echo '</div>';
        echo '</div>';

        echo '<div>';
        echo '<div>';
        echo '<img class="ci-img" src="/wp-content/themes/hello-elementor/assets/images/' . $cl_icon . '">';
        echo '</div>';
        echo '<div>';
        echo '<h3 class="ci-title">' . $cl_title . '</h3>';
        echo '<p class="ci-body">' . $cl_body . '</p>';
        echo '</div>';
        echo '</div>';

        if (!empty($ci_course_certification) && $ci_course_certification != 1) {
            echo '<div>';
            echo '<div>';
            echo '<img class="ci-img" src="/wp-content/themes/hello-elementor/assets/images/' . $cc_icon . '">';
            echo '</div>';
            echo '<div>';
            echo '<h3 class="ci-title">' . $cc_title . '</h3>';
            echo '<p class="ci-body">' . $cc_body . '</p>';
            echo '</div>';
            echo '</div>';
        }

        echo '<div>';
        echo '<div>';
        echo '<img class="ci-img" src="/wp-content/themes/hello-elementor/assets/images/icon-ci-price.svg">';
        echo '</div>';
        echo '<div>';
        echo '<h3 class="ci-title"' . $ci_product_price . '</h3>';
        if ($ci_deposit && $ci_deposit > 0) {
            echo '<p class="ci-body">Including your initial &pound;' . $ci_deposit . ' deposit</p>';
        }
        if ($ci_consultation) {
            echo '<p class="ci-body"><strong>Initial 1-on-1 consultation required.</strong></p>';
            echo '<a class="booking-button" href="/forms/one-to-one-request-form/">Book a Consultation</a>';
        }
        if ($ci_online && $ci_online_url !== "") {
            echo '<a class="booking-button" href="' . $ci_online_url . '">Book on Leiths Online</a>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';

    endif;

}

add_shortcode('course_info', 'ec_shortcode_course_info');
/** End Course Info Shortcode */


/** Booking Live integration Shortcode */
function ec_shortcode_bl_integration($atts)
{
    $productPod = pods(get_post_type(), get_the_ID(), false);
    if (!empty($productPod)) :
        $ci_title = $productPod->field('title');
        $ci_booking_dates = $productPod->field('ci_booking_dates');
        $ci_booking_button = $productPod->field('ci_booking_button');
        $ci_booking_message = $productPod->field('ci_booking_message');
    endif;

    if (empty($ci_booking_dates) || $ci_booking_dates != 1) :

        if (!empty($ci_booking_button) && $ci_booking_button == 1) {
            $book_opt = false;
        } else {
            $book_opt = true;
        }

        $product = wc_get_product(get_the_ID());
        if ($product) {
            $bli_product_sku = $product->get_sku();
        }

        if (!empty($bli_product_sku)) :

            echo '<div id="courseid" class="courseid" data-courseid="' . $bli_product_sku . '"></div>';

            $current_date = date('Y-m-d');
            $booking_url = "https://leiths.bookinglive.com/book/add/p";

            $booking_feed_url = 'https://leiths.bookinglive.com/jsonfeed/productavailability/' . $bli_product_sku;
            $booking_data = file_get_contents($booking_feed_url, true);
            $booking_obj = json_decode($booking_data, true);

            if ($booking_obj) :

                foreach ($booking_obj as $entries) :

                    foreach ($entries as $key_entry => $entry) :

                        if (!empty($entry)) :
                            $product_id = $key_entry;
                            $product_id = str_replace('product_id_', '', $product_id);
                            $product_id = str_replace('_availability', '', $product_id);

                            if (!empty($product_id) && $product_id !== "541" && $product_id !== "205" && $product_id !== "627" && $product_id !== "293") :

                                $course_days = 0;

                                foreach ($entry as $eval) :

                                    if (array_key_exists('events', $eval)) {

                                        $entry_events = $eval['events'];
                                        $course_days = count($entry_events);

                                        foreach ($entry_events as $key_event => $entry_event) :
                                            if (!empty($entry_event)) {
                                                if ($key_event === array_key_first($entry_events)) {
                                                    $entry_event_id = $entry_event['id'];
                                                    $booking_entry['product_id'] = $entry_event['product_id'];
                                                    $booking_entry['booking_event_id'] = $entry_event_id;
                                                    $booking_entry['start_date_time'] = $entry_event['start_date_time'];
                                                    $booking_entry['end_date_time'] = $entry_event['end_date_time'];
                                                    $booking_entry['capacity'] = $entry_event['capacity'];
                                                    $booking_entry['available_spaces'] = $entry_event['available_spaces'];
                                                    $booking_entry['days'] = $course_days;
                                                }

                                                $s = new DateTime($entry_event['start_date_time']);
                                                $e = new DateTime($entry_event['end_date_time']);
                                                $d = $s->format('D d M');
                                                $st = $s->format('H:i');
                                                $et = $e->format('H:i');
                                                $set = $d . ', ' . $st . '&ndash;' . $et;

                                                $booking_entry['times'][] = $set;

                                                if ($key_event === array_key_last($entry_events)) {
                                                    $booking_entry['end_date_time'] = $entry_event['end_date_time'];
                                                }

                                                $booking_entries[$entry_event_id] = $booking_entry;
                                            }
                                        endforeach;

                                    } else {

                                        $entry_id = $eval['id'];
                                        $booking_entry['product_id'] = $eval['product_id'];
                                        $booking_entry['booking_event_id'] = $entry_id;
                                        $booking_entry['start_date_time'] = $eval['start_date_time'];
                                        $booking_entry['end_date_time'] = $eval['end_date_time'];
                                        $booking_entry['capacity'] = $eval['capacity'];
                                        $booking_entry['available_spaces'] = $eval['available_spaces'];
                                        $booking_entry['days'] = 1;
                                        $booking_entries[$entry_id] = $booking_entry;

                                    }

                                    unset($entry_events, $entry_event_id, $entry_id, $s, $e, $d, $st, $et, $set, $booking_entry['times']);

                                endforeach;

                            endif;

                        endif;

                    endforeach;

                endforeach;

                if (!empty($booking_entries)) :

                    echo '
                    <style>
                    .cc-courses {
                        max-width: 1200px;
                        margin: 0 auto;
                    }
                    
                    .cc-courses-entries {
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        grid-template-rows: auto;
                        grid-gap: 1em;
                    }
                    
                    .cc-courses h4 {
                        color: #fff;
                        font-size: 1.25em;
                        margin: 0;
                    }
                    
                    .cc-courses p.cc-small {
                        color: #fff;
                        font-size: .8em;
                    }
                    
                    p.cc-course {
                        font-size: .85rem;
                        line-height: 1.25;
                        font-weight: 500;
                        margin: 0;
                        position: relative;
                        z-index: 1000;
                    }
                    
                    .cc-course .cc-available {
                        display: block;
                        color: #202020;
                        background-color: #fff;
                        text-decoration: none;
                        padding: .5rem;
                        border-radius: .33rem;
                        transition: background-color .25s linear, border-color .25s linear;
                        border-left: .33rem solid rgb(30, 200, 60);
                    }
                
                    .cc-course .cc-available:hover {
                        color: #202020;
                    }
                    
                    .cc-course .cc-unavailable {
                        display: block;
                        color: #202020;
                        background-color: #fff;
                        text-decoration: none;
                        padding: .5rem;
                        border-radius: .33rem;
                        transition: background-color .25s linear, border-color .25s linear;
                        border-left: .33rem solid #ff6962;
                        position: relative;
                    }
                    
                    .cc-course .cc-unavailable:after {
                        content: "CLASS FULL";
                        position: absolute;
                        display: block;
                        top: .5rem;
                        right: .5rem;
                        border-radius: .2rem;
                        font-size: .7rem;
                        font-weight: bold;
                        line-height: 1;
                        padding: 5px 8px;
                        background-color: #f2f2f2;
                        color: rgb(215, 70, 40);
                    }
                    
                    .cc-course .cc-unavailable:hover {
                        color: #202020;
                    }
                
                    .cc-course .cc-date {
                        font-size: 1.1rem;
                        line-height: 1;
                        font-weight: bold;
                        display: block;
                        margin: 0 0 .5rem 0;
                        white-space: nowrap;
                    }
                    
                    .cc-course .cc-time {
                        font-size: .75rem;
                        line-height: 1.3;
                        font-weight: normal;
                        display: block;
                        margin: .5rem 0 .25rem 0;
                        white-space: nowrap;
                    }
                
                    .cc-course .cc-price {
                        font-size: .75rem;
                        line-height: 1.3;
                        font-weight: bold;
                        display: block;
                        margin: .5rem 0 .25rem 0;
                        white-space: nowrap;
                    }
                
                    .cc-course .cc-sessions-times {
                        position: relative;
                        z-index: 1;
                    }
                    
                    .cc-course .cc-sessions {
                        font-size: .8rem;
                        line-height: 1.35;
                        font-weight: normal;
                        display: flex;
                        flex: 1 1 auto;
                        align-items: center;
                        justify-content: space-between;
                        margin: 1rem 0 .25rem 0;
                        padding: .25rem .3rem .25rem .75rem;
                        background-color: #cee6ff;
                        color: #0a1a3d;
                        border-radius: 1rem;
                        z-index: 2;
                        cursor: default;
                    }
                
                    .cc-course .cc-sessions b {
                        font-weight: normal;
                    }
                    
                    .cc-course .cc-sessions i.fa {
                        font-size: 1rem;
                        color: #484c5a;
                    }
                
                    .cc-course .cc-sessions:hover {
                        background-color: #484c5a;
                        color: #fff;
                    }
                
                    .cc-course .cc-sessions:hover i.fa {
                        color: #fff;
                    }
                    
                    .cc-course .cc-times {
                        font-size: .8rem;
                        line-height: 1.5;
                        font-weight: normal;
                        display: none;
                        margin: 0;
                        padding: .75rem 1rem .75rem 2.25rem;
                        color: #0a1a3d;
                        background-color: #fff;
                        white-space: nowrap;
                        border-radius: .33rem;
                        position: absolute;
                        left: 50%;
                        bottom: 1.75rem;
                        transform: translateX(-50%);
                        box-shadow: 0 3px 12px 0 rgba(0, 0, 0, .15);
                        background-image: url("/wp-content/themes/hello-elementor/assets/images/icon-ci-time-b.svg");
                        background-repeat: no-repeat;
                        background-size: 1rem;
                        background-position: .9rem .9rem;
                        z-index: 3;
                    }
                
                    .cc-course .cc-times b {
                        display: block;
                        font-weight: 500;
                        margin: 0 0 .25rem 0;
                    }
                    
                    .cc-course .cc-button {
                        font-size: .8rem;
                        font-weight: 500;
                        text-align: center;
                        line-height: 1.2;
                        background-color: rgb(0, 108, 183);
                        color: #fff;
                        border-radius: .33rem;
                        padding: .5rem 1rem;
                        display: block;
                        margin-top: .5rem;
                    }
                    
                    .cc-course .cc-button.cc-full {
                        background-color: #ff6962;
                    }
                    
                    .cc-course:hover .cc-button {
                        background-color: rgb(10, 128, 213);
                    }
                    
                    .cc-course:hover .cc-button.cc-full {
                        background-color: #fe6861;
                    }
                    
                    @media screen and (max-width: 767px) {
                        .cc-courses {
                            max-width: none;
                        }
                        
                        .cc-courses-entries {
                            grid-template-columns: 1fr;
                            grid-gap: 1em;
                        }
                        
                        .cc-course .cc-date {
                            font-size: 1.3rem;
                        }
                        
                        .cc-course .cc-sessions {
                            font-size: 1rem;
                            line-height: 1.5;
                            border-radius: 1.5rem;
                            padding: .33rem .5rem .33rem .75rem;
                        }
                        
                        .cc-course .cc-sessions i.fa {
                            font-size: 1.2rem;
                            color: #484c5a;
                        }
                        
                        .cc-course .cc-times {
                            font-size: 1rem;
                        }
                        
                        .cc-course .cc-button {
                            font-size: 1.1rem;
                            margin-top: 1rem;
                        }
                        
                        .cc-course .cc-available {
                            padding: .75rem;
                        }
                    
                        .cc-course .cc-unavailable {
                            padding: .75rem;
                        }
                    }
                    
                    </style>
                ';

                    // Start courses container
                    echo '<div class="cc-courses">';

                    echo '<div class="cc-courses-entries">';

                    foreach ($booking_entries as $booking_entry) :
                        $start_date_time = $booking_entry['start_date_time'];
                        $end_date_time = $booking_entry['end_date_time'];
                        $available_spaces = $booking_entry['available_spaces'];
                        $start = new DateTime($start_date_time);
                        $end = new DateTime($end_date_time);
                        $time = $start->format('H:i');
                        $end_time = $end->format('H:i');
                        $days = $booking_entry['days'];
                        $times = $booking_entry['times'];

                        if ($start->format('Y-m-d') >= $current_date) {
                            if ($days > 1) :

                                if ($available_spaces > 0) {
                                    echo '<p class="cc-course" data-type="multi">';
                                    if ($book_opt == true) {
                                        echo '<a class="cc-available booknowlink" href="' . $booking_url . '/' . $bli_product_sku . '">';
                                    } else {
                                        echo '<span class="cc-available">';
                                    }
                                    echo '<span class="cc-date">';
                                    if ($start->format('M') == $end->format('M')) {
                                        echo $start->format('d') . '&nbsp;&ndash;&nbsp;' . $end->format('d M Y');
                                    } else {
                                        echo $start->format('d M') . '&nbsp;&ndash;&nbsp;' . $end->format('d M Y');
                                    }
                                    echo '</span>';
                                    echo '<span class="cc-sessions-times">';
                                    echo '<span class="cc-sessions"><b>' . $days . ' session course</b> <i class="fa fa-info-circle"></i></span>';
                                    echo '<span class="cc-times">';
                                    echo '<b>Sessions</b>';
                                    foreach ($times as $time) {
                                        echo $time . '<br>';
                                    }
                                    echo '</span>';
                                    echo '</span>';
                                    if ($book_opt == true) {
                                        echo '<span class="cc-button book_now">Book Now</span>';
                                        echo '</a>';
                                    } else {
                                        echo '</span>';
                                    }
                                    echo '</p>';
                                } else {
                                    echo '<p class="cc-course" data-type="multi">';
                                    if ($book_opt == true) {
                                        echo '<a class="cc-unavailable" href="' . $booking_url . '/' . $bli_product_sku . '">';
                                    } else {
                                        echo '<span class="cc-unavailable">';
                                    }
                                    echo '<span class="cc-date">';
                                    if ($start->format('M') == $end->format('M')) {
                                        echo $start->format('d') . '&nbsp;&ndash;&nbsp;' . $end->format('d M Y');
                                    } else {
                                        echo $start->format('d M') . '&nbsp;&ndash;&nbsp;' . $end->format('d M Y');
                                    }
                                    echo '</span>';
                                    echo '<span class="cc-sessions-times">';
                                    echo '<span class="cc-sessions"><b>' . $days . ' session course</b> <i class="fa fa-info-circle"></i></span>';
                                    echo '<span class="cc-times">';
                                    echo '<b>Sessions</b>';
                                    foreach ($times as $time) {
                                        echo $time . '<br>';
                                    }
                                    echo '</span>';
                                    echo '</span>';
                                    if ($book_opt == true) {
                                        echo '<span class="cc-button cc-full waitinglistlink">Join Waiting List</span>';
                                        echo '</a>';
                                    } else {
                                        echo '</span>';
                                    }
                                    echo '</p>';
                                }

                            else :

                                if ($available_spaces > 0) {
                                    echo '<p class="cc-course" data-type="single">';
                                    if ($book_opt == true) {
                                        echo '<a class="cc-available booknowlink" href="' . $booking_url . '/' . $bli_product_sku . '">';
                                    } else {
                                        echo '<span class="cc-available">';
                                    }
                                    echo '<span class="cc-date">';
                                    echo $start->format('d M Y');
                                    echo '</span>';
                                    echo '<span class="cc-time">';
                                    echo $time . '&ndash;' . $end_time;
                                    echo '</span>';
                                    if ($book_opt == true) {
                                        echo '<span class="cc-button book_now">Book Now</span>';
                                        echo '</a>';
                                    } else {
                                        echo '</span>';
                                    }
                                    echo '</p>';
                                } else {
                                    echo '<p class="cc-course" data-type="single">';
                                    if ($book_opt == true) {
                                        echo '<a class="cc-unavailable" href="' . $booking_url . '/' . $bli_product_sku . '">';
                                    } else {
                                        echo '<span class="cc-unavailable">';
                                    }
                                    echo '<span class="cc-date">';
                                    echo $start->format('d M Y');
                                    echo '</span>';
                                    echo '<span class="cc-time">';
                                    echo $time . '&ndash;' . $end_time;
                                    echo '</span>';
                                    if ($book_opt == true) {
                                        echo '<span class="cc-button cc-full waitinglistlink">Join Waiting List</span>';
                                        echo '</a>';
                                    } else {
                                        echo '</span>';
                                    }
                                    echo '</p>';
                                }

                            endif;
                        }
                    endforeach;

                    // End courses container
                    echo '</div>';
                    echo '</div>';

                    echo '
                    <script>
                    (function ($) {
                        $(document).ready(function () {
                            $(".cc-sessions-times").on("click", function (e) {
                                e.stopPropagation();
                                e.preventDefault();
                            }).on("mouseenter", function (e) {
                                e.stopPropagation();
                                $(this).children(".cc-times").show();
                            }).on("mouseleave", function (e) {
                                $(this).children(".cc-times").hide();
                            });
                        });
                        
                        $("a.booknowlink, a.booknowbutton").click(function(e) {
                            var target = $(this).attr("href");
                            gtag("event", "begin_checkout", {
                                "event_category": "ecommerce",
                                "event_label": target
                            });
                            setCookie("Leiths_PJS", "' . $bli_product_sku . '", 7);
                        });
                    })(jQuery);
                    </script>
                    ';

                else :

                    if (empty($ci_booking_message) || $ci_booking_message != 1) {

                        echo '
                        <style>
                            .cc-no-sessions {
                                position: relative;
                            }
                            
                            .cc-no-sessions-message {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                background-color: rgba(0,0,0,.25);
                                padding: .75rem 1.25rem;
                                border-radius: .33rem;
                            }
                            
                            .cc-no-sessions p {
                                font-size: 1rem;
                                font-weight: bold;
                                color: #fff;
                                text-decoration: none;
                                margin: 0;
                            }
                            
                            .cc-no-sessions .cc-button {
                                font-size: .9rem;
                                font-weight: bold;
                                text-align: center;
                                line-height: 1.35;
                                background-color: #fff;
                                color: #333;
                                border: 1px solid #fff;
                                border-radius: .33rem;
                                padding: .5rem 1rem;
                                display: block;
                            }
                            
                            .cc-no-sessions .cc-button:hover {
                                background-color: rgb(0, 108, 183);
                                color: #fff;
                            }
                            
                            .cc-form {
                                display: none;
                                max-width: 40rem;
                                position: absolute;
                                right: 1rem;
                                background-color: #fff;
                                padding: 1rem 1.5rem;
                                box-shadow: 0 3px 12px 0 rgba(0, 0, 0, .15);
                                z-index: 10;
                            }
                            
                            .cc-form p {
                                font-size: 1rem;
                                font-weight: normal;
                                color: #333;
                                text-decoration: none;
                                margin: 0 0 1rem 0;
                            }
                            
                            .cc-form .cc-form-footer {
                                position: relative;
                                text-align: right;
                            }
                            
                            .cc-form .cc-form-footer a {
                                font-size: .9rem;
                                display: inline;
                                text-decoration: none;
                                font-weight: bold;
                                color: rgb(0, 68, 143);
                            }
                            
                            .cc-form p.cc-close a:hover {
                                text-decoration: underline;
                            }
                            
                            .cc-form .gform_confirmation_wrapper .gform_confirmation_message {
                                font-weight: bold;
                                font-size: 1.2rem;
                            } 
                            
                            @media screen and (max-width: 767px) {
                                .cc-no-sessions-message {
                                    display: block;
                                }
                                
                                .cc-no-sessions p {
                                    text-align: center;
                                }
                                
                                .cc-no-sessions .cc-form p {
                                    text-align: left;
                                }
                                
                                .cc-no-sessions .cc-button {
                                    margin-top: 1rem;
                                }
                            }
                            </style>
                        ';

                        $register_interest_form_id = pods('course_calendar', array())->field('register_interest_form_id');

                        echo '<div class="cc-no-sessions">';
                        echo '<div class="cc-no-sessions-message">';
                        echo '<div>';
                        echo '<p>';
                        echo 'There are no available course sessions. Please check back later for updates.';
                        echo '</p>';
                        echo '</div>';
                        echo '<div>';
                        echo '<a class="cc-button register-interest" href="#">Register Interest</a>';
                        echo '</div>';
                        echo '</div>';
                        if ($register_interest_form_id !== '') {
                            echo '<div class="cc-form" id="register-interest-form">';
                            echo '<p>Please provide your name and email to receive notifications for future sessions of "' . $ci_title . '."';
                            echo '<div>';
                            echo do_shortcode('[gravityform id="' . $register_interest_form_id . '" title="false" description="false" ajax="true"]');
                            echo '</div>';
                            echo '<div class="cc-form-footer"><a class="close-register-interest" href="#">Close Form</a></div>';
                            echo '</div>';
                            echo '
                                <script>
                                (function ($) {
                                    $(document).ready(function () {
                                        $("input[name=input_3]").val("' . $ci_title . '");
                                        $(".register-interest").on("click", function (e) {
                                            e.preventDefault();
                                            $("#register-interest-form").toggle();
                                        });
                                        $(".close-register-interest").on("click", function (e) {
                                            e.preventDefault();
                                            $("#register-interest-form").hide();
                                        });
                                    });
                                })(jQuery);
                                </script>
                            ';
                        }
                        echo '</div>';

                    }

                endif;

            else :
                echo '<div class="cc-no-sessions">';
                echo '<h4>Error getting course data</h4>';
                echo '</div>';
            endif;

        endif;

    endif;
}

add_shortcode('bookinglive_book_course', 'ec_shortcode_bl_integration');
/** End Course Info Shortcode */

/** Begin Get product details by product SKU **/
function get_product_by_sku($sku): ?WC_Product
{
    global $wpdb;
    $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku));
    if ($product_id) return new WC_Product($product_id);
    return null;
}

/** End Get product details by product SKU **/

/** Begin Deposit **/
function display_deposit_shortcode()
{
    // Get the deposit price from the ACF field
    $deposit_price = get_field('deposit_price'); // Replace with your ACF field name

    // Check if the deposit price is not empty
    if ($deposit_price) {
        // Format the deposit price as a currency
        $formatted_deposit = wc_price($deposit_price);

        // Return the deposit price with the desired text and styling
        return "<span class='product-deposit' style='color: white;'>Including your initial $formatted_deposit deposit</span>";
    }

    // If the deposit price is empty, return nothing
    return '';
}

add_shortcode('display_deposit', 'display_deposit_shortcode');

/** End Deposit **/

/** Begin Alum Title**/
// Add custom shortcode to display alumni grid
function display_alumni_grid()
{
    ob_start(); // Start output buffering

    // Query child pages under 'Alumni' page
    $alumni_args = array(
        'post_type' => 'page',
        'post_parent' => get_page_by_path('alumni')->ID,
        'posts_per_page' => -1, // Retrieve all child pages
        'orderby' => 'title', // Order by title
        'order' => 'ASC', // Sort in ascending order
    );

    $alumni_query = new WP_Query($alumni_args);

    if ($alumni_query->have_posts()) :
        echo '<div class="grid-container">';

        while ($alumni_query->have_posts()) : $alumni_query->the_post();

            // Get ACF field value
            $alumni_title = get_field('alumni_title');
            $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');

            // Output card markup
            echo '<a href="' . esc_url(get_permalink()) . '" class="card-link">';
            echo '<div class="card">';
            echo '<div class="image" style="background-image: url(' . esc_url($featured_image[0]) . ');"></div>';
            echo '<div class="content">';
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p class="alumni-title">' . $alumni_title . '</p>';
            echo '</div>'; // close content
            echo '</div>'; // close card
            echo '</a>'; // close card link

        endwhile;

        echo '</div>'; // close grid-container
        wp_reset_postdata();
    endif;

    $output = ob_get_clean(); // Get the buffered output
    return $output;
}

add_shortcode('alumni_grid', 'display_alumni_grid');

/** End Alum Title**/

/** Begin Featured Alum - Careers**/
// Add custom shortcode to display featured alumni grid
function display_featured_alumni_grid()
{
    ob_start(); // Start output buffering

    // Get the ACF field value for 'featured-alumni'
    $featured_alumni_pages = get_field('featured-alumni');

    if (!empty($featured_alumni_pages)) :
        echo '<div class="grid-container">';

        foreach ($featured_alumni_pages as $alumni_post) :
            $alumni_title = get_field('alumni_title', $alumni_post);
            $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($alumni_post), 'large');

            // Output card markup
            echo '<a href="' . esc_url(get_permalink($alumni_post)) . '" class="card-link">';
            echo '<div class="card">';
            echo '<div class="image" style="background-image: url(' . esc_url($featured_image[0]) . ');"></div>';
            echo '<div class="content">';
            echo '<h3>' . get_the_title($alumni_post) . '</h3>';
            echo '<p class="alumni-title">' . $alumni_title . '</p>';
            echo '</div>'; // close content
            echo '</div>'; // close card
            echo '</a>'; // close card link

        endforeach;

        echo '</div>'; // close grid-container
    endif;

    $output = ob_get_clean(); // Get the buffered output
    return $output;
}

add_shortcode('featured_alumni_grid', 'display_featured_alumni_grid');

/** End Featured Alum - Careers **/

/** Begin Print The Content **/
function the_content_shortcode()
{
    the_content();
}

add_shortcode('the_content', 'the_content_shortcode');

/** End Deposit **/

/** Start Theme Builder Jobs Post Add **/
add_action('elementor/theme/register_conditions', 'register_wpjb_category_condition');

function register_wpjb_category_condition($conditions_manager) {
    class WPJB_Category_Condition extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {
        public function get_name() {
            return 'wpjb_category';
        }

        public function get_label() {
            return __('WPJB Job Category', 'your-text-domain');
        }

        public function get_all_label() {
            return __('All WPJB Categories', 'your-text-domain');
        }

        public function register_sub_conditions() {
            $job_categories = get_terms([
                'taxonomy' => 'job_category',
                'hide_empty' => false,
            ]);

            // Debug output
            error_log('WPJB Categories found: ' . print_r($job_categories, true));

            if (!empty($job_categories) && !is_wp_error($job_categories)) {
                foreach ($job_categories as $category) {
                    $this->register_sub_condition(new WPJB_Category_Sub_Condition([
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'parent' => $this->get_name(),
                    ]));
                    // Debug output
                    error_log('Registering category: ' . $category->name . ' (ID: ' . $category->term_id . ')');
                }
            } else {
                error_log('No categories found or error occurred.');
            }
        }

        public function check($args) {
            return is_singular('job');
        }
    }

    class WPJB_Category_Sub_Condition extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {
        protected $category_id;
        protected $category_name;

        public function __construct(array $data) {
            parent::__construct($data);
            $this->category_id = $data['id'];
            $this->category_name = $data['name'];
            // Debug output
            error_log('Sub-condition created: ' . $this->category_name . ' (ID: ' . $this->category_id . ')');
        }

        public function get_name() {
            return 'wpjb_category_' . $this->category_id;
        }

        public function get_label() {
            return $this->category_name;
        }

        public function check($args) {
            if (is_singular('job')) {
                $job_id = get_the_ID();
                $job_categories = wp_get_post_terms($job_id, 'job_category', ['fields' => 'ids']);
                return in_array($this->category_id, $job_categories);
            }
            return false;
        }
    }

    $wpjb_condition = new WPJB_Category_Condition();
    $conditions_manager->get_condition('general')->register_sub_condition($wpjb_condition);
}
/** End Theme Builder Jobs Post Add**/

/** Start Apply WAL Template **/
function custom_job_single_template($template) {
    global $post;

    if (is_singular('job')) {
        $categories = wp_get_post_categories($post->ID);

        // Check if the post belongs to the category with ID 8 (Work at Leiths)
        if (in_array(8, $categories)) {
            $new_template = get_stylesheet_directory() . '/wpjobboard/job-board/single-wal.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
    }

    return $template;
}
add_filter('template_include', 'custom_job_single_template');



/** End Apply WAL Template **/

function enqueue_slick_scripts()
{
    wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/jquery.slick/1.8.1/slick.min.js', array('jquery'), null, true);
}

add_action('wp_enqueue_scripts', 'enqueue_slick_scripts');

