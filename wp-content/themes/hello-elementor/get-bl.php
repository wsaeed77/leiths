<?php

set_time_limit(0);
//ini_set('display_errors', true);

for ($year = 2023; $year <= 2024; $year++) {
    for ($month = 1; $month <= 12; $month++) {

        $start_date = $year . '-' . sprintf("%02d", $month);
        $end_date = $year . '-' . sprintf("%02d", $month);
        $date = new DateTime($start_date);
        $now = new DateTime();

        $date = $date->format('Y-m');
        $now = $now->format('Y-m');

        $actual_date = new DateTime($date);
        $end_day = $actual_date->format('t');

        if ($now <= $date) {
            $bl_feed_url = 'https://leiths.bookinglive.com/jsonfeed/productavailability/' . $start_date . '-01/' . $end_date . '-' . $end_day . '/';
            $file = fopen(dirname(__FILE__) . '/bl-data/' . $start_date . '.json', 'w') or die('Permission error!');

            $curl = curl_init($bl_feed_url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'H.H\'s PHP CURL script');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FILE, $file);

            curl_exec($curl);
            curl_close($curl);
        }
    }
}
