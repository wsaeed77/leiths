<div class="wrap wpjb">

    <h1>
    <?php if($form->getObject()->getId()>0): ?>
    <?php  _e("Edit Promotion", "wpjobboard") ?>
    <?php else: ?>
    <?php  _e("Add Promotion", "wpjobboard"); ?>
    <?php endif; ?>
        
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("discount") ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a>  
        
    </h1>

<?php $this->_include("flash.php"); ?>

<form action="" method="post">
    <table class="form-table">
        <tbody>
        <?php echo daq_form_layout_config($form) ?>
        </tbody>
    </table>

    <p class="submit">
    <input type="submit" value="<?php _e("Save Changes", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
    </p>

</form>
    
<script type="text/javascript">
jQuery(function($) {

    jQuery("#expires_at").datepicker({
        dateFormat: wpjb_admin_lang.datepicker_date_format,
        //autoSize: true,
        changeMonth: false,
        changeYear: true,
        yearRange: "c-5:c+95",
        onSelect: function( date ){
            $("#expires_at").attr("value", date);
        }
    });
    
});
</script>


</div>