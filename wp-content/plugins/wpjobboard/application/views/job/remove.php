<div class="wrap wpjb">
    
<h1>
    <?php _e("Delete Jobs", "wpjobboard"); ?>
</h1>

<?php $this->_include("flash.php"); ?>
    

    <form action="<?php esc_attr_e(wpjb_admin_url("job", "remove", null, array("noheader"=>1))) ?>" method="post">
    <?php wp_nonce_field($nonceName, "_wpjb_nonce") ?>
    <p><?php _e("You have specified these jobs for deletion", "wpjobboard") ?>:</p>
    <ul>
        <?php foreach($list as $item): ?>
        <li>
            <input type="checkbox" name="jobs[]" value="<?php esc_attr_e($item->id) ?>" <?php checked(true) ?> />
            ID #<?php esc_attr_e($item->id) ?>:
            <strong><?php esc_html_e(trim($item->job_title)) ?></strong>
        </li>
        <?php endforeach; ?>
    </ul>
	
    <fieldset>
        <p><legend><?php _e("What should be done with applications for this job?", "wpjobboard") ?></legend></p>
	<ul style="list-style:none;">
            <li>
                <label for="application_option0">
                    <input type="radio" id="application_option0" name="application_option" value="unassign" checked="checked" />
                    <?php _e("Unassign applications (will be visible as posted to empty job).", "wpjobboard") ?>
                </label>
            </li>
            <li>
                <label for="application_option1">
                    <input type="radio" id="application_option1" name="application_option" value="full" /> 
                    <?php _e("Delete applications.", "wpjobboard") ?>
                </label>
            </li>
	</ul>
    </fieldset>
    
    <fieldset>
        <p><legend><?php _e("What should be done with payments for this job?", "wpjobboard") ?></legend></p>
	<ul style="list-style:none;">
            <li>
                <label for="payment_option1">
                    <input type="radio" id="payment_option1" name="payment_option" value="unassign" /> 
                    <?php _e("Unassign payment history (will be visible as payment for empty job).", "wpjobboard") ?>
                </label>
            </li>
            
            <li>
                <label for="payment_option0">
                    <input type="radio" id="payment_option0" name="payment_option" value="delete" checked="checked" />
                    <?php _e("Delete payment history.", "wpjobboard") ?>
                </label>
            </li>
            
	</ul>
    </fieldset>
	
    <p class="submit">
        <input type="submit" id="submit" class="button" value="<?php _e("Confirm Deletion", "wpjobboard") ?>" />
    </p>
    </form>

</div>
