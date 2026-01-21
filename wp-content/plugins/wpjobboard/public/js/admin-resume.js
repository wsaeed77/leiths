jQuery(function($) {

    jQuery("#resume_created_at").datepicker({
        dateFormat: wpjb_admin_resume_lang.datepicker_date_format,
        //autoSize: true,
        changeMonth: false,
        changeYear: true,
        onSelect: function( date ){
            jQuery("#resume_created_at").attr("value", date);
            jQuery(".resume_created_date").text(date);
        }
    });

    jQuery("#resume_modified_at").datepicker({
        dateFormat: wpjb_admin_resume_lang.datepicker_date_format,
        //autoSize: true,
        changeMonth: false,
        changeYear: true,
        onSelect: function( date ){
            jQuery("#resume_modified_at").attr("value", date);
            jQuery(".resume_modified_date").text(date);
        }
    });

    $("#resume_created_at_link").click(function() {
        $("#resume_created_at").focus().focus();
        return false;
    }); 

    
    $("#resume_modified_at_link").click(function() {
        $("#resume_modified_at").focus().focus();
        return false;
    }); 
});