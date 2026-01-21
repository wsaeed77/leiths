<?php
/** Template Name: Booking Confirmation **/
session_start();
get_header();

$prod_sku = $_GET["ref"] ?? null;
?>

<style>
    .content-inner {
        max-width: 1024px;
        margin: 0 auto;
        padding: 4rem;
    }

    p {
        font-family: "Open Sans", sans-serif;
        color: #5f6a7e;
    }

    h1 {
        font-family: "Libre Baskerville", sans-serif;
        color: #2a364d;
        margin: 0 0 2rem 0;
    }

    h2, h3, h4, h5 {
        font-family: "Open Sans", sans-serif;
        color: #2a364d;
    }

    h3 {
        font-size: 1.3em;
        font-weight: bold;
        letter-spacing: -.025em;
        margin: 1.5rem 0 .75rem 0;
    }

    .header-icon {
        display: block;
        margin: 0 auto 1rem auto;
        text-align: center;
    }

    .booking-details {
        max-width: 560px;
        margin: 3rem auto 0 auto;
        border: 1px solid #e6e6e6;
        display: none !important;
    }

    .booking-details img {
        display: block;
        width: 100%;
        height: auto;
        box-shadow: none;
    }

    .booking-summary {
        padding: 2rem;
    }

    .booking-summary h4 {
        font-weight: bold;
        letter-spacing: -.025em;
        margin: 0 0 1rem 0;
    }

    .booking-summary p {
        display: grid;
        grid-template-columns: 1.5fr 3fr;
        grid-gap: 1rem;
        margin: .25rem 0;
    }

    .booking-summary span.label {
        color: #808080;
    }

    .booking-summary span.value {
        font-weight: 500;
    }

    .booking-summary a.button {
        display: inline-block;
        font-size: .85rem;
        font-weight: 600;
        text-align: center;
        line-height: 1.2;
        color: #fff;
        background-color: rgb(0, 108, 183);
        border-radius: .33rem;
        padding: .5rem 1rem;
        margin-top: 1.5rem;
        transition: background-color .25s ease-in-out, color .25s ease-in-out;
    }

    .booking-summary a.button:hover {
        background-color: rgb(0, 68, 143);
    }

    p.small {
        font-size: .9em;
        line-height: 1.2;
    }

    @media screen and (max-width: 767px) {
        .content-inner {
            max-width: none;
            padding: 1.25rem;
        }

        .booking-summary {
            padding: 1.5rem;
        }

        .booking-summary p {
            grid-template-columns: 2fr 3fr;
        }
    }
</style>

<section class="content-container">
    <div class="content-inner">

        <div class="header-icon">
            <svg width="48" height="76" viewBox="0 0 48 76" fill="none" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink">
                <rect width="48" height="76" fill="url(#pattern0)"/>
                <defs>
                    <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_396_9922" transform="matrix(0.0137681 0 0 0.00869565 -0.00253623 0)"/>
                    </pattern>
                    <image id="image0_396_9922" width="73" height="115"
                           xlink:href="data:image/png;base64,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"/>
                </defs>
            </svg>
        </div>

        <?php the_content(); ?>

        <?php if ($prod_sku) : ?>

            <?php
            $product = new WC_Product(get_product_by_sku($prod_sku));
            $product_id = $product->get_id();

            if ($product_id) {
                $product_name = $product->get_title();
                $product_price = sprintf('%.2f', $product->get_price());
                $product_price_html = $product->get_price_html();

                $transaction_id = 'BL_' . $prod_sku . '-' . date("ymd") . '-' . date("hms");
                $product_currency = "GBP";
                $product_category = "Course";
                $product_quantity = 1;

                $product_image = wp_get_attachment_url($product->get_image_id());

                $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
                $product_categories = implode(', ', $categories);
                ?>

                <div class="booking-details">
                    <img src="<?php echo $product_image; ?>" alt="<?php echo $product_name; ?>"/>
                    <div class="booking-summary">
                        <p>You have booked...</p>
                        <h4><?php echo $product_name; ?></h4>

                        <p>
                            <span class="label">Transaction ID</span>
                            <span class="value"><?php echo $transaction_id; ?></span>
                        </p>
                        <p>
                            <span class="label">Course Price</span>
                            <span class="value"><?php echo $product_price_html; ?></span>
                        </p>
                        <a class="button" href="<?php echo get_permalink($product_id); ?>">View Course Details</a>
                    </div>
                </div>

                <script>
                    dataLayer.push({ecommerce: null});
                    dataLayer.push({
                        event: "purchase",
                        ecommerce: {
                            transaction_id: "<?php echo $transaction_id; ?>",
                            value: <?php echo $product_price; ?>,
                            currency: "<?php echo $product_currency; ?>",
                            items: [
                                {
                                    item_id: "<?php echo $prod_sku; ?>",
                                    item_name: "<?php echo $product_name; ?>",
                                    <?php
                                    $i = 0;
                                    $cn = '';
                                    $ts = '';
                                    $c = count($categories);
                                    while ($i < 5 && $i < $c) {
                                        if ($i > 0) {
                                            $cn = $i + 1;
                                            $ts = "\t\t\t\t";
                                        }
                                        echo $ts . "item_category" . $cn . ": \"" . htmlspecialchars_decode($categories[$i]) . "\"," . "\r\n";
                                        ++$i;
                                    }
                                    ?>
                                    price: <?php echo $product_price; ?>,
                                    quantity: <?php echo $product_quantity; ?>,
                                }
                            ]
                        }
                    });
                </script>

                <script>
                    (function ($) {
                        $(document).ready(function () {
                            eraseCookie("Leiths_PJS");
                        });
                    })(jQuery);
                </script>

            <?php } else { ?>

                <div class="booking-details">
                    <div class="booking-summary">
                        <p>No product found!</p>
                        <p>
                            <span class="label">Product ID</span>
                            <span class="value"><?php echo $prod_sku; ?></span>
                        </p>
                    </div>
                </div>

                <script>
                    (function ($) {
                        $(document).ready(function () {
                            eraseCookie("Leiths_PJS");
                        });
                    })(jQuery);
                </script>

            <?php } ?>

        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
