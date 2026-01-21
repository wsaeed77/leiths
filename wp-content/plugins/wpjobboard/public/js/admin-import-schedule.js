jQuery(function($) {
    $("#engine").on("change", function(e) {
        var $this = $(this);

        if($this.val() == "rss") {
            $(".wpjb-form-layout-row--keyword").find("label").text(wpjb_admin_import_schedule.url);
            $(".wpjb-form-layout-row--category_id").hide();
            $(".wpjb-form-layout-row--country").hide();
            $(".wpjb-form-layout-row--location").hide();
        } else {
            $(".wpjb-form-layout-row--keyword").find("label").text(wpjb_admin_import_schedule.keyword);
            $(".wpjb-form-layout-row--category_id").show();
            $(".wpjb-form-layout-row--country").show();
            $(".wpjb-form-layout-row--location").show();
        }
    });

    $("#engine").change();

    $(".wpjb-import-run-now").on("click", function(e) {
        e.preventDefault();
        var button = $(".wpjb-import-run-now");

        $(".wpjb-import-run-now").hide();
        $(".wpjb-import-run-spinner").css("visibility", "visible");
        $(".wpjb-import-log-box").css("opacity", "0.65");

        $.ajax({
            type: "POST",
            data: {
                action: 'wpjb_export_importnow',
                import_id: button.data("id"),
                import_nonce: button.data("nonce")
            },
            url: ajaxurl,
            dataType: "json",
            success: function(response) {
                $(".wpjb-import-run-now").show();
                $(".wpjb-import-run-spinner").css("visibility", "hidden");
                $(".wpjb-import-log-box").css("opacity", "1");

                if(response.result != 1) {
                    alert(response.message);
                } else {
                    $(".wpjb-import-log").text(response.logs);
                }
            },
            error: function(response) {
                $(".wpjb-import-run-now").show();
                $(".wpjb-import-run-spinner").css("visibility", "hidden");
                $(".wpjb-import-log-box").css("opacity", "1");
                alert(response.responseText);
            }
        });
    })
});