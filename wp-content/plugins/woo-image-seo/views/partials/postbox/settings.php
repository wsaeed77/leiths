<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<div class="postbox">
	<h1>Woo Image SEO - <?php _e( 'Global Settings', 'woo-image-seo' ) ?></h1>

	<?php require_once WOO_IMAGE_SEO['views_dir'] . 'partials/form-settings.php' ?>

    <?php do_action( 'woo_image_seo_admin_after_settings' ) ?>
</div>
