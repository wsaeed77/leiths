jQuery(function($) {

    var settings_anyway = $(".js-wpjb-admin-show-settings");

    if( settings_anyway.length === 1) {
        $(".wpjb-form-group-security").hide();
        $(".wpjb-form-group-data").hide();
        $(".wpjb-form .submit").hide();

        settings_anyway.on("click", function(e) {
            e.preventDefault();

            $(".wpjb-form-group-security").show();
            $(".wpjb-form-group-data").show();
            $(".wpjb-form .submit").show();

            settings_anyway.hide();
        });
    }

    var hash_input =  $(".js-wpjb-admin-generate-hash-input");
    var hash_button =  $(".js-wpjb-admin-generate-hash-button");
    var hash_loader =  $(".js-wpjb-admin-generate-hash-loader");

    if(hash_input.val() === "") {
        hash_input.hide();
        hash_button.show();
        hash_loader.hide();
    } else {
        hash_input.show();
        hash_button.hide();
        hash_loader.hide();
    }

    hash_button.on("click", function(e) {
        e.preventDefault();

        var hash_loader =  $(".js-wpjb-admin-generate-hash-loader");
        hash_loader.show();

        var data = {
            action: "wpjb_main_hash"
        };

        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: "post",
            dataType: "json",
            success: function(response) {

                var hash_input =  $(".js-wpjb-admin-generate-hash-input");
                var hash_button =  $(".js-wpjb-admin-generate-hash-button");
                var hash_loader =  $(".js-wpjb-admin-generate-hash-loader");

                hash_loader.hide();

                if(typeof response === 'object' && typeof response.hash === 'string') {
                    hash_input.show().val(response.hash);
                    hash_button.hide();
                } else {
                    hash_input.hide();
                    hash_button.show();
                }


            }
        });
    });

    $(".wpjb-form-group-data input[type=checkbox]").each(function(index, item) {
        var $item = $(item);

        if($item.is(":checked")) {
            $item.closest("tr").next().show();
        } else {
            $item.closest("tr").next().hide();
        }

        $item.on("click", function(j, ckbox) {
            var $ckbox = $(j.currentTarget);

            if($ckbox.is(":checked")) {
                $ckbox.closest("tr").next().show();
            } else {
                $ckbox.closest("tr").next().hide();
            }
        });
    });

    //$(".wpjb-form-group-data input[type=checkbox]").trigger("click");

});