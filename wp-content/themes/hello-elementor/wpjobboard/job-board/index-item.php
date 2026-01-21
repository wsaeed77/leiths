<?php

/**
 * Job list item
 *
 * This template is responsible for displaying job list item on job list page
 * (template index.php) it is alos used in live search
 *
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 */

/* @var $job Wpjb_Model_Job */

?>

<div class="<?php wpjb_job_features($job); ?> ll-single-job">

    <div>
        <?php if ($image != "none"): ?>
            <?php if ($job->getLogoUrl() && $job->doScheme("company_logo")): ?>
            <?php elseif ($job->getLogoUrl()): ?>
                <img src="<?php echo $job->getLogoUrl($image) ?>" alt="Company Logo"/>
            <?php elseif ($job->getCompany(true)->getLogoUrl()): ?>
                <img src="<?php echo $job->getCompany(true)->getLogoUrl($image) ?>" alt="Company Logo"/>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div>
        <?php if ($job->doScheme("job_title")): else: ?>
            <h3><?php echo esc_html($job->job_title) ?></h3>
        <?php endif; ?>

        <?php if ($job->isNew()): ?>
            <span class="ll-tag-new"><?php _e("new", "wpjobboard") ?></span>
        <?php endif; ?>

        <?php if (isset($job->getTag()->type[0])): ?>
            <span class="ll-job-tag" style="color:#<?php echo $job->getTag()->type[0]->meta->color ?>">
                    <?php echo esc_html($job->getTag()->type[0]->title) ?>
                </span>
        <?php endif; ?>
    </div>

    <div class="ll-job-details">
        <?php if ($job->doScheme("company_name")): else: ?>
            <?php
            $comp = trim($job->company_name);
            if ($comp != "n/a" && $comp != "N/A") {
                echo '<span><strong>';
                echo esc_html($comp);
                echo '</strong></span>';
            }
            ?>
        <?php endif; ?>

        <span><?php echo esc_html($job->locationToString()) ?></span>

        <span class="ll-end-date">Closing <strong><?php echo wpjb_date_display("d F Y", $job->job_expires_at, false); ?></strong></span>

        <?php do_action("wpjb_tpl_index_item", $job->id) ?>
    </div>

    <div class="ll-flex-v-spacer"></div>

    <div>
        <a class="ll-button-apply" href="<?php echo wpjb_link_to("job", $job) ?>">View & Apply</a>
    </div>

</div>
