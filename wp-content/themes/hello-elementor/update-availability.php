<?php
set_time_limit(0);

ini_set("log_errors", 1);
ini_set("error_log", "update-availability-error.log");

//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require_once('wp-load.php');

$query = new WP_Query(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'product_cat' => 'cc'
));

$proc_log = fopen('update-availability.txt', 'w');
fwrite($proc_log, 'Total products in category: ' . $query->found_posts . PHP_EOL);
fwrite($proc_log, 'Process start: ' . date('Y-m-d H:i:s') . PHP_EOL);
fwrite($proc_log, '==================================' . PHP_EOL);
fwrite($proc_log, ' ' . PHP_EOL);

while ($query->have_posts()) : $query->the_post();
    $product = wc_get_product(get_the_ID());

    $p_name = $product->get_name();
    $p_sku = $product->get_sku();
    $p_menu = $product->get_menu_order();
    $p_favourite = pods('product', get_the_ID())->field('ci_favourite');

    fwrite($proc_log, 'Product Name: ' . $p_name . PHP_EOL);
    fwrite($proc_log, 'SKU: ' . $p_sku . PHP_EOL);
    fwrite($proc_log, 'Menu Order: ' . $p_menu . PHP_EOL);
    fwrite($proc_log, 'Favourite: ' . $p_favourite . PHP_EOL);

    if ($p_favourite == 1) {
        $product->set_menu_order(0);
        fwrite($proc_log, 'Favourite product.' . PHP_EOL);
    } else {
        if (!empty($p_sku)) :
            $booking_feed_url = 'https://leiths.bookinglive.com/jsonfeed/productavailability/' . $p_sku;
            $booking_data = file_get_contents($booking_feed_url, true);
            $booking_obj = json_decode($booking_data, true);

            if ($booking_obj) :
                foreach ($booking_obj as $entries) :
                    foreach ($entries as $key_entry => $entry) :

                        if (!empty($entry)) :
                            $start_date = new DateTime($entry[0]['start_date_time']);
                            $eval_date = $start_date->format('ymd');
                            if (!empty($eval_date)) {
                                $product->set_menu_order($eval_date);
                                fwrite($proc_log, 'First available date: ' . $start_date->format('d-m-y') . PHP_EOL);
                            } else {
                                $product->set_menu_order(999999);
                                fwrite($proc_log, 'No availability.' . PHP_EOL);
                            }
                        else :
                            $product->set_menu_order(999999);
                            fwrite($proc_log, 'Empty calendar entry.' . PHP_EOL);
                        endif;

                    endforeach;
                endforeach;
            else :
                $product->set_menu_order(999999);
                fwrite($proc_log, 'No calendar entry.' . PHP_EOL);
            endif;

        else :

            $product->set_menu_order(999999);
            fwrite($proc_log, 'No product SKU.' . PHP_EOL);

        endif;
    }

    $product->save();

    fwrite($proc_log, '-------------------------------' . PHP_EOL);

endwhile;

fwrite($proc_log, ' ' . PHP_EOL);
fwrite($proc_log, '================================' . PHP_EOL);
fwrite($proc_log, 'Process end: ' . date('Y-m-d H:i:s') . PHP_EOL);
fwrite($proc_log, 'All done!' . PHP_EOL);

fclose($proc_log);

wp_reset_query();

