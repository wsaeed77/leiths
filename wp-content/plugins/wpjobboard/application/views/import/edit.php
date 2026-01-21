<div class="wrap wpjb">
    
<h1>
    <?php if($form->getObject()->id): ?>
    <?php _e("Edit Import | ID: ", "wpjobboard"); echo intval( $form->getObject()->id ); ?> 
    <?php else: ?>
    <?php _e("Schedule Import", "wpjobboard"); ?>
    <?php endif; ?>
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("import"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
</h1>
    
<?php $this->_include("flash.php"); ?>

<div class="wpjb-admin-import-wrap">

<form action="" method="post" class="wpjb-form">
    <table class="form-table">
        <tbody>
            <?php echo daq_form_layout_config($form) ?>
        </tbody>
    </table>

    <p class="submit">
    
    <?php if(!$form->getId()): ?>
        <input type="submit" value="<?php _e("Schedule", "wpjobboard") ?>" class="button-primary" name="Schedule" />
        <input type="submit" value="<?php _e("Import Once", "wpjobboard") ?>" class="button-secondary" name="Once" />
    <?php else: ?>
        <input type="submit" value="<?php _e("Update Schedule", "wpjobboard") ?>" class="button-primary" name="Schedule" />
    <?php endif; ?>
    </p>

</form>

<div class="wpjb-admin-import-log">
<?php if($form->getObject()->id): ?>
    <h3>
        <?php _e("Import Logs (last 100 lines)", "wpjobboard" ) ?>

        <a href="#" class="wpjb-import-run-now" data-id="<?php echo esc_attr($form->getId()) ?>" data-nonce="<?php echo wp_create_nonce( sprintf( "wpjb-import-now-%d", $form->getId() ) ) ?>" title="<?php esc_attr_e("Run import now", "wpjobboard" ) ?>">
            <span class="dashicons dashicons-update"></span>
        </a>
        <span class="spinner wpjb-import-run-spinner"></span>
    </h3>
    <div class="wpjb-import-log-box">
    <pre class="wpjb-import-log"><?php echo esc_html($form->getObject()->logs) ?></pre>
    </div>
<?php endif; ?>
</div>

    </div>

</div>

<style type="text/css">
.wpjb-admin-import-wrap {
    display:flex;
    box-sizing:content-box
}
.wpjb-admin-import-log {
    flex-grow:1
}
.wpjb-admin-import-log > h3 {
    padding:0 1rem 0 1rem
}
.wpjb-admin-import-log .spinner.wpjb-import-run-spinner {
    line-height: 20px !important;
    float: none;
    margin: 0;
}
.wpjb-admin-import-log .wpjb-import-run-now {
    display: inline-block;
}
.wpjb-admin-import-log .wpjb-import-run-now > .dashicons {
    vertical-align: middle;
    text-decoration: none;
}
.wpjb-import-log-box {
    max-width:100%;
    margin-left:1rem;
    padding:0 10px 0 5px; 
    background: whitesmoke; 
    border:1px solid gray;
}
</style>