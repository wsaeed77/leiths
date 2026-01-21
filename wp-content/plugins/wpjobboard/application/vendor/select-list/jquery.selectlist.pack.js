jQuery(function($) {
    
    $(".daq-multiselect-input").focus(function(e) {
        $(this).blur();
        
        if($(this).hasClass("daq-multiselect-open")) {
            $(this).removeClass("daq-multiselect-open");
            $(this).parent().find(".daq-multiselect-options").hide();
        } else {
            $(this).addClass("daq-multiselect-open");
            $(this).parent().find(".daq-multiselect-options").css("width", $(this).outerWidth()-1);
            $(this).parent().find(".daq-multiselect-options").show();
        }

        e.stopPropagation();
    });  
    
    $(".daq-multiselect-options input[type=checkbox]").change(function() {
        var owner = $("#"+$(this).data("wpjb-owner"));
        var all = $(this).closest(".daq-multiselect-options").find("input");
        var checked = [];

        all.each(function(j, c) {
            if($(c).is(":checked")) {
                checked.push($(c).parent().text().trim());
            }
        });

        owner.attr("value", checked.join(", "));
    });

    $(document).mouseup(function(e) {
        var container = $(".daq-multiselect-options");

        if ($(e.target).hasClass("daq-multiselect-input")) {
            return;
        }

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
            container.parent().find("input").removeClass("daq-multiselect-open");
        }
    });

});