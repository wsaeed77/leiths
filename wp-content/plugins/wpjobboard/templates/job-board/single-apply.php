<?php if(!wpjb_conf("front_hide_apply_link")): ?>
    <div id="wpjb-form-job-apply" class="wpjb-form-slider wpjb-layer-inside <?php if(!$show->apply): ?>wpjb-none<?php endif;?>">
            
        <?php if($form_error): ?>
        <div class="wpjb-flash-error wpjb-flash-small">
            <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
        </div>
        <?php endif; ?>
            
        <form id="wpjb-apply-form" action="<?php echo esc_attr(wpjb_link_to("job", $job, array("form"=>"apply"))) ?>#wpjb-scroll" method="post" enctype="multipart/form-data" class="wpjb-form wpjb-form-nolines">
            <?php echo $form->renderHidden() ?>
            <?php foreach($form->getReordered() as $group): ?>
            <?php /* @var $group stdClass */ ?> 
                
            <?php if($group->title): ?>
            <div class="wpjb-legend"><?php esc_html_e($group->title) ?></div>
            <?php endif; ?>
                
            <fieldset class="wpjb-fieldset-<?php esc_attr_e($group->getName()) ?>">

                <?php foreach($group->getReordered() as $name => $field): ?>
                <?php /* @var $field Daq_Form_Element */ ?>
                <div class="<?php wpjb_form_input_features($field) ?>">

                    <label class="wpjb-label">
                        <?php esc_html_e($field->getLabel()) ?>
                        <?php if($field->isRequired()): ?><span class="wpjb-required">*</span><?php endif; ?>
                    </label>

                    <div class="wpjb-field">
                        <?php wpjb_form_render_input($form, $field) ?>
                        <?php wpjb_form_input_hint($field) ?>
                        <?php wpjb_form_input_errors($field) ?>
                    </div>

                </div>
                <?php endforeach; ?>
            </fieldset>
            <?php endforeach; ?>
                
            <div class="wpjb-legend"></div>
                
            <fieldset>
                <input type="submit" class="wpjb-submit" id="wpjb_submit" value="<?php _e("Send Application", "wpjobboard") ?>" />
            </fieldset>
        </form>
    </div>
<?php endif; ?>