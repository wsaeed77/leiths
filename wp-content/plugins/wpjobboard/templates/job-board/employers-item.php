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

 /* @var $company Wpjb_Model_Company */

?>
    <div class="wpjb-grid-row wpjb-grid-flex">
        <?php if($image != "none"): ?>
        <div class="wpjb-grid-col wpjb-col-logo">
        <?php if($company->doScheme("company_logo")): ?>
            <?php elseif($company->getLogoUrl()): ?>
            <div class="<?php echo apply_filters( "wpjb_tpl_list_col_img", "wpjb-flex-img wpjb-flex-img-company", $company ) ?>">
                <img src="<?php echo $company->getLogoUrl($image) ?>" alt="" class="" />
            </div>
            <?php elseif(isset($image_default_url) && $image_default_url): ?>
            <div class="<?php echo apply_filters( "wpjb_tpl_list_col_img", "wpjb-flex-img wpjb-flex-img-default", $company ) ?>">
                <img src="<?php echo esc_attr($image_default_url) ?>" alt="" class="" />
            </div>
            <?php else: ?>
            <div class="<?php echo apply_filters( "wpjb_tpl_list_col_img", "wpjb-flex-img wpjb-flex-img-bg", $company ) ?>">
                <span class="wpjb-glyphs wpjb-icon-industry"></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="wpjb-grid-col wpjb-col-main wpjb-col-title">
            
            <div class="wpjb-line-major">
                <a href="<?php echo wpjb_link_to("company", $company) ?>" class="wpjb-company_name wpjb-title"><?php echo esc_html($company->company_name) ?></a>
                <span class="wpjb-sub-title wpjb-company_slogan wpjb-sub-opaque"> 
                    <span class="wpjb-glyphs wpjb-icon-suitcase"></span>
                    <?php $employer_jobs = wpjb_find_jobs(array("active"=>true, "count_only"=>true, "employer_id"=>$company->id)) ?>
                    <?php echo sprintf(_n("1 job", "%d jobs", $employer_jobs, "wpjobboard"), $employer_jobs) ?>
                </span>
                <span class="wpjb-sub-title wpjb-sub-opaque">
                    <span class="wpjb-glyphs wpjb-icon-location"><?php echo esc_html($company->locationToString()) ?></span>
                </span>

            </div>
            
            <div class="wpjb-line-minor">
                
                
                <span class="wpjb-sub">
                    <?php if($company->company_slogan): ?>
                    <?php echo esc_html($company->company_slogan) ?>
                    <?php else: ?>
                    —
                    <?php endif ?>
                </span>
                
                
                <span class="wpjb-sub wpjb-sub-right wpjb-company_user_registered">
                    <?php echo wpjb_date_display("M, d", $company->getUser(true)->user_registered, false); ?>
                </span>

                <?php do_action( "wpjb_tpl_index_item__company", $company->id ) ?>
            </div>
        </div>
        
    </div>


