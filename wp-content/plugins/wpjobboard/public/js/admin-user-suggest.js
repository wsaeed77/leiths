jQuery(document).ready(function($) {

    $( ".change-assigned-user" ).click( function() {
        $( ".wpjb-mini-profile" ).hide();
        $( ".user-selector" ).show();

    });

    $( ".wpjb-inline-cancel" ).click( function() {
        $( ".wpjb-mini-profile" ).show();
        $( ".user-selector" ).hide();


    });

    var user_link = $('#_user_link').autocomplete({
        source: ajaxurl + "?action=wpjb_main_users&discard="+$('#_user_link').data("discard"),
        minLength: 3,
        select: function(event, ui) {
            $("#_user_id").val(ui.item.id);
            $( ".wpjb-mini-profile" ).show();
            $( ".user-selector" ).hide();

            $(".wpjb-mini-profile-display-name").html( ui.item.label)
            $(".wpjb-mini-profile-user-login").html( ui.item.label)
            $(".wpjb-mini-profile-id").html( ui.item.id)
        }
    })
            
    user_link.data("ui-autocomplete")._renderItem = function (ul, item) {
        var li = $("<li></li>");
        
        li.data("item.autocomplete", item);
        li.append(item.label);
        li.attr("title", item.hint);
        
        if(item.role != "") {
            li.addClass("ui-state-disabled");
            li.css("cursor", "not-allowed");
            li.css("opacity", "0.5");
        }
            
        return li.appendTo(ul);
    };
    
});