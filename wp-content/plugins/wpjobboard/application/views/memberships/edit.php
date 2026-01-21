<div class="wrap wpjb">
    

    <h1>
        <?php if($form->getObject()->id): ?>
        <?php _e("Edit Membership | ID: ", "wpjobboard"); echo intval( $form->getObject()->id ); ?> 
        <?php else: ?>
        <?php _e("Add Membership", "wpjobboard"); ?>
        <?php endif; ?>
        <a class="add-new-h2" href="<?php echo wpjb_admin_url("memberships"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
</script>

<form action="" method="post" class="wpjb-form">
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
    $(".wpjb-membership-usage").change(function() {
        var val = $(this).val();
        if(val != "limited") {
            $(this).next().find("input").attr("readonly", "readonly").val("");
        } else {
            $(this).next().find("input").attr("readonly", null);
            
        }
    });
    
    $(".wpjb-membership-usage").change();

    $("#started_at").datepicker({
        dateFormat: wpjb_admin_lang.datepicker_date_format,
        //autoSize: true,
        changeMonth: false,
        changeYear: true,
        yearRange: "c-5:c+95",
        onSelect: function( date ){
            $("#started_at").attr("value", date);
        }
    });

    $("#expires_at").datepicker({
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