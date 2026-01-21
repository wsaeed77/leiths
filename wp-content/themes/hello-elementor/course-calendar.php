<?php
/** Template Name: Course Calendar **/
get_header();

$cal_date = $_GET['d'] ?? date('Y-m');

//$excluded_courses = array(pods('course_calendar', array())->field('excluded_courses'));
?>

<style>
    .course-calendar {
        padding: 3rem;
    }

    .cc-content {
    }

    .cc-month-navigation {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 10;
        margin-bottom: 1.5rem;
    }

    .cc-month-navigation .month-title {
        display: inline-flex;
        align-items: center;
        margin: 0;
        font-size: 1.5rem;
        line-height: 1.2;
        font-weight: bold;
        white-space: nowrap;
        text-decoration: none;
        color: #333;
        cursor: pointer;
    }

    .cc-month-navigation .month-title:hover {
        color: rgb(0, 108, 183);
    }

    .cc-month-navigation .month-title img {
        box-shadow: none;
        margin-left: .5rem;
        width: 1.1rem;
        height: auto;
        transition: transform .2s linear;
    }

    .cc-month-navigation .active img {
        transform: rotateZ(-180deg);
    }

    .cc-month-menu {
        position: absolute;
        padding: .5rem;
        border-radius: .5rem;
        background-color: #fff;
        box-shadow: 0 3px 12px 0 rgba(0, 0, 0, .15);
        margin-top: .5rem;
        display: none;
    }

    .cc-month-menu a {
        display: block;
        font-size: 1.1rem;
        line-height: 1.5;
        color: #0a1a3d;
        text-decoration: none;
        padding: .25rem .75rem;
        border-radius: .25rem;
    }

    .cc-month-menu a:hover {
        color: #0a1a3d;
        background-color: rgb(245, 245, 245);
    }

    .cc-content pre {
        font-size: .75rem;
        padding: 1rem;
        margin: .5rem 0;
        background-color: #f0f0f5;
        border-radius: .5rem;
    }

    .cc-content pre.float-right {
        width: 50%;
        float: right;
    }

    .cc-content pre.booking {
        font-size: .75rem;
        padding: 1.5rem;
        border: 1px dashed lightgreen;
        margin: 0 0 2rem 0;
    }

    .cc-content .cc-event-list {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        grid-template-rows: auto;
        grid-gap: .5rem;
    }

    .cc-content .cc-calendar {
        background-color: #f0f0f3;
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(6, 1fr) .33fr;
        grid-template-rows: auto;
        grid-gap: .33rem;
        border-radius: .66rem;
    }

    .cc-content .cc-calendar .cc-day {
        color: rgb(0, 108, 183);
        font-size: .85rem;
        line-height: 1;
        font-weight: bold;
        padding: 0 0 .5rem 0;
    }

    .cc-content .cc-calendar .cc-day.weekend {
        color: lightcoral;
    }

    .cc-content .cc-calendar .cc-date {
        background-color: white;
        padding: .5rem;
        border-radius: .33rem;
        min-height: 10rem;
    }

    .cc-content .cc-calendar .cc-date.sunday {
        opacity: .5;
    }

    .cc-content .cc-calendar .cc-date-empty {
        padding: 0;
        height: 0;
    }

    .cc-content .cc-calendar .cc-course {
        font-size: .85rem;
        line-height: 1.25;
        font-weight: 500;
        margin: .5rem 0;
    }

    .cc-content .cc-calendar .cc-error {
        display: block;
        font-size: .75rem;
        line-height: 1.15;
        font-weight: 500;
        color: red;
        margin: .5rem 0 0 0;
    }

    .cc-content .cc-legend .cc-key {
        display: inline-block;
        font-weight: 500;
        font-size: .75rem;
        line-height: 1;
        padding: .5rem;
        border-radius: .33rem;
        margin-left: .5rem;
        border-left: .33rem solid transparent;
    }

    .cc-content .cc-legend .cc-key.available {
        color: #fff;
        background-color: rgb(0, 108, 183);
        border-left-color: rgb(30, 200, 60);
    }

    .cc-content .cc-legend .cc-key.full {
        color: #505050;
        background-color: rgb(245, 245, 245);
        border-left-color: #ff6962;
    }

    .cc-content .cc-legend .cc-key.event {
        color: #fff;
        background-color: rgb(64, 85, 128);
        border-left-color: rgb(250, 160, 20);
    }

    .cc-content .cc-calendar .cc-available {
        display: block;
        color: #fff;
        background-color: rgb(0, 108, 183);
        text-decoration: none;
        padding: .5rem;
        border-radius: .33rem;
        transition: background-color .25s linear, border-color .25s linear;
        border-left: .33rem solid rgb(30, 200, 60);
    }

    .cc-content .cc-calendar .cc-course.hovered .cc-available {
        background-color: rgb(0, 148, 223);
    }

    .cc-content .cc-calendar .cc-unavailable {
        display: block;
        color: #505050;
        background-color: rgb(245, 245, 245);
        text-decoration: none;
        padding: .5rem;
        border-radius: .33rem;
        transition: background-color .25s linear, border-color .25s linear;
        border-left: .33rem solid #ff6962;
    }

    .cc-content .cc-calendar .cc-course.hovered .cc-unavailable {
        background-color: rgb(230, 230, 230);
    }

    .cc-content .cc-calendar .cc-icon {
        display: inline-block;
        width: 12px;
        height: auto;
        vertical-align: middle;
        position: relative;
        top: -1px;
        margin: 0 .33rem 0 0;
        box-shadow: none;
        background: transparent;
    }

    .cc-content .cc-calendar .cc-time {
        font-size: .75rem;
        line-height: 1.5;
        font-weight: normal;
        display: block;
        margin: .5rem 0 .25rem 0;
        white-space: nowrap;
    }

    .cc-content .cc-calendar .cc-price {
        font-size: .75rem;
        line-height: 1.3;
        font-weight: normal;
        display: block;
        margin: .5rem 0 .25rem 0;
        white-space: nowrap;
    }

    .cc-content .cc-calendar .cc-sessions-times {
        position: relative;
        z-index: 1;
    }

    .cc-content .cc-calendar .cc-sessions {
        font-size: .75rem;
        line-height: 1;
        font-weight: normal;
        display: flex;
        flex: 1 1 auto;
        align-items: center;
        justify-content: space-between;
        margin: .5rem 0 .25rem 0;
        padding: .25rem .5rem .3rem .5rem;
        background-color: #fff;
        color: #0a1a3d;
        border-radius: 1rem;
        z-index: 2;
        cursor: default;
    }

    .cc-content .cc-calendar .cc-sessions b {
        font-weight: normal;
    }

    .cc-content .cc-calendar .cc-sessions i.fa {
        font-size: 1rem;
        color: #484c5a;
    }

    .cc-content .cc-calendar .cc-sessions:hover {
        background-color: #484c5a;
        color: #fff;
    }

    .cc-content .cc-calendar .cc-sessions:hover i.fa {
        color: #fff;
    }

    .cc-content .cc-calendar .cc-times {
        font-size: .75rem;
        line-height: 1.5;
        font-weight: normal;
        display: none;
        margin: 0;
        padding: .5rem .75rem .75rem 2rem;
        color: #0a1a3d;
        background-color: #fff;
        white-space: nowrap;
        border-radius: .25rem;
        position: absolute;
        box-shadow: 0 3px 12px 0 rgba(0, 0, 0, .15);
        background-image: url("/wp-content/themes/hello-elementor/assets/images/icon-ci-time-b.svg");
        background-repeat: no-repeat;
        background-size: 1rem;
        background-position: .55rem .55rem;
        z-index: 3;
    }

    .cc-content .cc-calendar .cc-times b {
        display: block;
        font-weight: 500;
        margin: 0 0 .25rem 0;
    }

    .cc-content .cc-event {
        padding: 1rem;
        border: 1px solid #e0e0e0;
        background-color: #fff;
        border-radius: .5rem;
    }

    .cc-content .cc-event.indent {
        margin-left: 5rem;
        border: 1px dashed #e0e0e0;
    }

    .cc-content .cc-event p {
        font-size: .85rem;
        margin: 0;
        line-height: 1.25;
    }

    .cc-content .cc-event p:not(:last-child) {
        margin-bottom: .5rem;
    }

    .cc-content .cc-event p span {
        margin: 0;
        color: #909090;
        font-size: .6rem;
        text-transform: uppercase;
    }

    .cc-content a.booking-link {
        font-size: .75rem;
        text-transform: uppercase;
        font-weight: bold;
        display: inline-block;
        margin: 1rem 0 0 0;
        text-decoration: none;
        color: rgb(0, 108, 183);
    }

    .cc-content a.booking-link:hover {
        color: lightcoral;
    }

    hr {
        clear: both;
    }

    br.clear {
        clear: both;
    }

    @media screen and (max-width: 480px) {
        .cc-month-navigation {
            display: block;
        }

        .cc-month-menu {
            position: absolute;
            padding: .5rem;
            border-radius: .5rem;
            background-color: #fff;
            box-shadow: 0 3px 12px 0 rgba(0, 0, 0, .15);
            margin-top: .5rem;
            display: none;
            width: 100%;
        }

        .cc-month-menu a {
            display: block;
            width: 100%;
            font-size: 1.2rem;
            line-height: 1.5;
            color: #0a1a3d;
            text-decoration: none;
            padding: .5rem 1.25rem;
            border-radius: .25rem;
        }

        .course-calendar {
            padding: 1rem;
        }

        .cc-content .cc-legend {
            margin-top: 1rem;
        }

        .cc-content .cc-legend .cc-key {
            margin-left: 0;
            margin-right: .5rem;
        }

        .cc-content .cc-calendar {
            grid-template-columns: 1fr;
            grid-template-rows: auto;
        }

        .cc-content .cc-calendar .cc-day {
            display: none;
        }

        .cc-content .cc-calendar .cc-date {
            min-height: 2rem;
        }
    }
</style>

<section class="course-calendar">
    <div class="cc-content">
        <div class="cc-month-navigation">
            <div>
                <?php
                $cal_date_long = new DateTime($cal_date);
                echo '<a class="month-title">' . $cal_date_long->format('F Y') . '<img src="/wp-content/themes/hello-elementor/assets/images/icon-chevron-down.svg""></a>';
                ?>
                <div class="cc-month-menu">
                    <?php
                    for ($m = 0; $m <= 13; $m++) {
                        $year_month = date('Y-m', strtotime('first day of +' . $m . ' months'));
                        $year_month_display = date('F Y', strtotime('first day of +' . $m . ' months'));
                        echo '<a href="/course-calendar?d=' . $year_month . '">' . $year_month_display . '</a>';
                    }
                    ?>
                </div>
            </div>
            <div class="cc-legend">
                <div class="cc-key full">Class Full</div>
                <div class="cc-key available">Sessions Available</div>
                <?php /* <div class="cc-key event">Leiths Event</div> */ ?>
            </div>
        </div>

        <?php
        $actual_date = date('Y-m', strtotime($cal_date));
        $start_date = new DateTime($actual_date);

        $start_day = $start_date->format('D');
        $end_day = $start_date->format('t');

        $bl_data_url = get_theme_file_path() . '/bl-data/' . $cal_date . '.json';
        $booking_data = file_get_contents($bl_data_url);
        $booking_obj = json_decode($booking_data, true);

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

        function compareDates($element1, $element2)
        {
            $datetime1 = strtotime($element1['start_date_time']);
            $datetime2 = strtotime($element2['start_date_time']);
            return $datetime1 - $datetime2;
        }

        usort($booking_entries, 'compareDates');
        ?>

        <?php

        // Print Calendar
        $start_day = strtolower($start_day);

        switch ($start_day) {
            case "mon":
                $week_starts = 1;
                break;
            case "tue":
                $week_starts = 2;
                break;
            case "wed":
                $week_starts = 3;
                break;
            case "thu":
                $week_starts = 4;
                break;
            case "fri":
                $week_starts = 5;
                break;
            case "sat":
                $week_starts = 6;
                break;
            case "sun":
                $week_starts = 7;
                break;
            default:
                $week_starts = 1;
        }

        $day_print = 1;

        echo '<div class="cc-calendar">';

        echo '<div class="cc-day">Monday</div>';
        echo '<div class="cc-day">Tuesday</div>';
        echo '<div class="cc-day">Wednesday</div>';
        echo '<div class="cc-day">Thursday</div>';
        echo '<div class="cc-day">Friday</div>';
        echo '<div class="cc-day weekend">Saturday</div>';
        echo '<div class="cc-day weekend">Sunday</div>';

        for ($day = 1; $day <= 42; $day++) {
            if ($day >= $week_starts && $day_print <= $end_day) {
                if ($day % 7 == 0) {
                    $class = ' sunday';
                } else {
                    $class = '';
                }
                echo '<div class="cc-date' . $class . '">';
                echo '<h6 style="margin:0;"><b>' . $day_print . '</b></h6>';
                foreach ($booking_entries as $booking_entry) :

                    $product_id = $booking_entry['product_id'];

//                    if (in_array($product_id, $excluded_courses)) {
//                        echo '<p>' . $product_id . ' is excluded' . '</p>';
//                    }

                    $booking_event_id = $booking_entry['booking_event_id'];
                    $start_date_time = $booking_entry['start_date_time'];
                    $end_date_time = $booking_entry['end_date_time'];
                    $available_spaces = $booking_entry['available_spaces'];
                    $start = new DateTime($start_date_time);
                    $end = new DateTime($end_date_time);
                    $date = $start->format('d');
                    $time = $start->format('H:i');
                    $end_time = $end->format('H:i');
                    $days = $booking_entry['days'];
                    $times = $booking_entry['times'];

                    $product_wp = new WC_Product(get_product_by_sku($product_id));
                    $product_wp_id = $product_wp->get_id();

                    if ($product_wp_id !== 0) {
                        $product_wp_name = $product_wp->get_title();
                        $product_wp_link = $product_wp->get_permalink();
                        $product_wp_price = $product_wp->get_price();
                    } else {
                        $product_wp_link = '#';
                    }

                    if (intval($date) == $day_print) {

                        if (!isset($product_wp_name)) {
                            $product_wp_name = 'Course not found';
                            $product_wp_price = '0';
                        }

                        if ($days > 1) :

                            if ($available_spaces > 0) {
                                echo '<p class="cc-course cid-' . $product_id . '" data-course="' . $product_id . '">';
                                echo '<a class="cc-available" href="' . $product_wp_link . '" target="_blank">' . $product_wp_name;
                                echo '<span class="cc-time"><img class="cc-icon" src="/wp-content/themes/hello-elementor/assets/images/icon-ci-date.svg">';
                                if ($start->format('M') == $end->format('M')) {
                                    echo $start->format('d') . '&nbsp;&ndash;&nbsp;' . $end->format('d M');
                                } else {
                                    echo $start->format('d M') . '&nbsp;&ndash;&nbsp;' . $end->format('d M');
                                }
                                echo '</span>';
                                echo '<span class="cc-price">&pound;' . $product_wp_price . '</span>';
                                echo '<span class="cc-sessions-times">';
                                echo '<span class="cc-sessions"><b>' . $days . ' session course</b> <i class="fa fa-info-circle"></i></span>';
                                echo '</span>';
                                echo '<span class="cc-times">';
                                echo '<b>Sessions</b>';
                                foreach ($times as $time) {
                                    echo $time . '<br>';
                                }
                                echo '</span>';
                                if ($product_wp_link == '#') {
                                    echo '<span class="cc-error">! ' . $product_id . '</span>';
                                }
                                echo '</a>';
                                echo '</p>';
                            } else {
                                echo '<p class="cc-course cid-' . $product_id . '" data-course="' . $product_id . '">';
                                echo '<a class="cc-unavailable" href="' . $product_wp_link . '" target="_blank">' . $product_wp_name;
                                echo '<span class="cc-time"><img class="cc-icon" src="/wp-content/themes/hello-elementor/assets/images/icon-ci-date-b.svg">';
                                if ($start->format('M') == $end->format('M')) {
                                    echo $start->format('d') . '&nbsp;&ndash;&nbsp;' . $end->format('d M');
                                } else {
                                    echo $start->format('d M') . '&nbsp;&ndash;&nbsp;' . $end->format('d M');
                                }
                                echo '</span>';
                                echo '<span class="cc-price">&pound;' . $product_wp_price . '</span>';
                                echo '<span class="cc-sessions-times">';
                                echo '<span class="cc-sessions"><b>' . $days . ' session course</b> <i class="fa fa-info-circle"></i></span>';
                                echo '</span>';
                                echo '<span class="cc-times">';
                                echo '<b>Sessions</b>';
                                foreach ($times as $time) {
                                    echo $time . '<br>';
                                }
                                echo '</span>';
                                if ($product_wp_link == '#') {
                                    echo '<span class="cc-error">! ' . $product_id . '</span>';
                                }
                                echo '</a>';
                                echo '</p>';
                            }

                        else :

                            if ($available_spaces > 0) {
                                echo '<p class="cc-course cid-' . $product_id . '" data-course="' . $product_id . '">';
                                echo '<a class="cc-available" href="' . $product_wp_link . '" target="_blank">' . $product_wp_name;
                                echo '<span class="cc-time"><img class="cc-icon" src="/wp-content/themes/hello-elementor/assets/images/icon-ci-time.svg">';
                                echo $time . '&ndash;' . $end_time;
                                echo '</span>';
                                echo '<span class="cc-price">&pound;' . $product_wp_price . '</span>';
                                if ($product_wp_link == '#') {
                                    echo '<span class="cc-error">! ' . $product_id . '</span>';
                                }
                                echo '</a>';
                                echo '</p>';
                            } else {
                                echo '<p class="cc-course cid-' . $product_id . '" data-course="' . $product_id . '">';
                                echo '<a class="cc-unavailable" href="' . $product_wp_link . '" target="_blank">' . $product_wp_name;
                                echo '<span class="cc-time"><img class="cc-icon" src="/wp-content/themes/hello-elementor/assets/images/icon-ci-time-b.svg">';
                                echo $time . '&ndash;' . $end_time;
                                echo '</span>';
                                echo '<span class="cc-price">&pound;' . $product_wp_price . '</span>';
                                if ($product_wp_link == '#') {
                                    echo '<span class="cc-error">! ' . $product_id . '</span>';
                                }
                                echo '</a>';
                                echo '</p>';
                            }

                        endif;
                    }

                    unset($product_id,
                        $booking_event_id,
                        $start_date_time,
                        $end_date_time,
                        $available_spaces,
                        $start,
                        $end,
                        $date,
                        $time,
                        $end_time,
                        $days,
                        $product_wp,
                        $product_wp_id,
                        $product_wp_name,
                        $product_wp_link,
                        $product_wp_price,
                        $booking_entry);
                endforeach;
                $day_print++;
            } else {
                echo '<div class="cc-date-empty">';
            }
            echo '</div>';
        }

        echo '</div>';
        ?>

    </div>
</section>

<script>
    (function ($) {
        $(document).ready(function () {

            $('.cc-course').on('mouseenter', function (e) {
                var bookingId = $(this).attr('data-course');
                $('.cid-' + bookingId).addClass('hovered');
            }).on('mouseleave', function (e) {
                $('.cc-course').removeClass('hovered');
            });


            $('.cc-sessions-times').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
            }).on('mouseenter', function (e) {
                e.stopPropagation();
                $(this).closest('a').children('.cc-times').show();
            }).on('mouseleave', function (e) {
                $(this).closest('a').children('.cc-times').hide();
            });

            $('.month-title').on('click', function (e) {
                e.preventDefault();
                $(this).toggleClass('active');
                $('.cc-month-menu').toggle();
            });

        });
    })(jQuery);
</script>

<?php get_footer(); ?>
