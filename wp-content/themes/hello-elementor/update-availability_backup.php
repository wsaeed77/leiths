<?php

ini_set('display_errors', true);
set_time_limit(0);
//ini_set("log_errors", 1);
//ini_set("error_log", "php-error.log");
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

echo '<h1>Availability List</h1>';

$query = new WP_Query(array(
    'post_type' => 'product',
    'posts_per_page' => 25,
    'product_cat' => 'cc'
));

echo $query->found_posts;
echo '<hr>';

while ($query->have_posts()) : $query->the_post();
    $product = wc_get_product(get_the_ID());

    $p_name = $product->get_name();
    $p_slug = $product->get_slug();
    $p_status = $product->get_status();
    $p_sku = $product->get_sku();
    $p_menu = $product->get_menu_order();

    if (!empty($p_sku)) :
        $booking_feed_url = 'https://leiths.bookinglive.com/jsonfeed/productavailability/' . $p_sku;
        $booking_data = file_get_contents($booking_feed_url, true);
        $booking_obj = json_decode($booking_data, true);

        if ($booking_obj) :
            foreach ($booking_obj as $entries) :
                foreach ($entries as $key_entry => $entry) :

                    if (!empty($entry)) :
                        $product_id = $key_entry;
                        $product_id = str_replace('product_id_', '', $product_id);
                        $product_id = str_replace('_availability', '', $product_id);

                        if (!empty($product_id)) :
                            foreach ($entry as $eval) :
                                if ($eval['product_id']) {
                                    $entry_id = $eval['id'];
                                    $booking_entry['product_id'] = $eval['product_id'];
                                    $start_date = new DateTime($eval['start_date_time']);
                                    $booking_entry['start_date_time'] = $start_date->format('ymd');
                                    $booking_entries[$entry_id] = $booking_entry;
                                }
                            endforeach;
                        endif;

                    endif;

                endforeach;
            endforeach;
        endif;

//    echo '<p>Product: <b>' . $p_name . '</b><br>';
//    echo 'Slug: ' . $p_slug . '<br>';
//    echo 'Status: ' . $p_status . '<br>';
//    echo 'SKU: ' . $p_sku . '<br>';
//    echo 'Menu: ' . $p_menu . '</p>';
//        echo '<pre>';
//        if (!empty($entry)) {
//            print_r($entry);
//            $product->set_menu_order(10);
//        } else {
//            echo 'No availability.';
//            $product->set_menu_order(0);
//        }
//        $product->save();
//        echo '</pre>';

//    else :
//        echo '<pre>';
//        echo 'No SKU.';
//        echo '</pre>';
    endif;

endwhile;

if (!empty($booking_entries)) :
    usort($booking_entries, 'compareDates');

    echo '<pre>';
    foreach ($booking_entries as $booking_entry) :
        print_r($booking_entry);
    endforeach;
    echo '<hr>';
    echo '</pre>';
endif;

echo 'Done';

function compareDates($element1, $element2)
{
    $datetime1 = strtotime($element1['start_date_time']);
    $datetime2 = strtotime($element2['start_date_time']);
    return $datetime1 - $datetime2;
}

wp_reset_query();

