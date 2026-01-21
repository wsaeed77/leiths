<?php

/**
 * Job details container
 * 
 * Inside this template job details page is generated (using function 
 * wpjb_job_template)
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 * @var $application_url string
 * @var $job Wpjb_Model_Job
 * @var $related array List of related jobs
 * @var $show_related boolean
 * @var $show stdClass
 */

?>
<div class="wpjb wpjb-job wpjb-page-single">
    <?php do_action( "wpjb_tpl_single_top", $job->id, $job->post_id, "job" ) ?>
    
    <?php wpjb_flash() ?>
    <?php include $this->getTemplate("job-board", "job") ?>
    
    <?php if( $members_only ): ?>
    <div class="wpjb-job-apply" style="margin:24px 0px;">
        <div class="wpjb-flash-error wpjb-flash-small">
            <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
        </div>
        
        <div>
            <a class="wpjb-button" href="<?php esc_attr_e(add_query_arg("goto-job", $job->id, wpjr_link_to("login"))) ?>"><?php _e("Login", "wpjobboard") ?></a>
            <a class="wpjb-button" href="<?php esc_attr_e(add_query_arg("goto-job", $job->id, wpjr_link_to("register"))) ?>"><?php _e("Register", "wpjobboard") ?></a>
            
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
    </div>
    <?php elseif( isset( $premium_members_only) && $premium_members_only ): ?>
    <div class="wpjb-job-apply" style="margin:24px 0px;">
        <div class="wpjb-flash-error wpjb-flash-small">
            <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
        </div>
        
        <div>
            <a class="wpjb-button" href="<?php echo esc_html( wpjr_link_to("mymembership") ); ?>"><?php _e("Buy Membership", "wpjobboard") ?></a>
            
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
    </div>
    <?php elseif( $can_apply ): ?>
    
    <div class="wpjb-job-apply" id="wpjb-scroll" style="margin:12px 0px;">
        <div class="wpjb-job-buttons">

            <?php foreach($application_methods as $am): ?>
                <?php if($am["is_active"]): ?>
                    <?php echo $am["button"] ?>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
        
        <?php foreach($application_methods as $amKey => $am): ?>
            <?php if($am["is_active"] && isset($am["callback"]) && is_callable($am["callback"]) ): ?>
                <?php call_user_func_array( $am["callback"], array($amKey, $am, $job, $can_apply)) ?>
            <?php endif; ?>
        <?php endforeach; ?>


    </div>
    <?php else: ?>
    <div class="wpjb-job-apply" style="margin:24px 0px;">
        <div>
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php do_action( "wpjb_tpl_single_job_content", $job->id, $job->post_id, "job" ) ?>

    <?php $relatedJobs = wpjb_find_jobs($related); ?>
    <?php if($show_related && $relatedJobs->total > 0): ?>
    <div class="wpjb-text">
    <h3><?php _e("Related Jobs", "wpjobboard") ?></h3>
    
    <div class="wpjb-grid wpjb-grid-closed-top wpjb-grid-compact">
    <?php foreach($relatedJobs->job as $relatedJob): ?>
    <?php /* @var $relatedJob Wpjb_Model_Job */ ?>
        <div class="wpjb-grid-row <?php wpjb_job_features($relatedJob); ?>">
            <div class="wpjb-grid-col wpjb-col-70">
                <a href="<?php echo wpjb_link_to("job", $relatedJob); ?>"><?php echo esc_html($relatedJob->job_title) ?></a>
                &nbsp; 
                <?php if($relatedJob->locationToString()): ?>
                <span class="wpjb-glyphs wpjb-icon-location"><?php echo esc_html($relatedJob->locationToString()) ?></span>
                <?php endif; ?>
                <?php if($relatedJob->isNew()): ?><span class="wpjb-bulb"><?php _e("new", "wpjobboard") ?></span><?php endif; ?>
            </div>
            <div class="wpjb-grid-col wpjb-grid-col-right wpjb-col-30 wpjb-glyphs wpjb-icon-clock">
            <?php echo wpjb_date_display(get_option('date_format'), $relatedJob->job_created_at) ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    </div>
    <?php endif; ?>

    <?php do_action( "wpjb_tpl_single_end", $job->id, $job->post_id, "job" ) ?>
</div>

