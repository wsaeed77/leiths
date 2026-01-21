jQuery(function($) {
    //alert("OK.");
    $("#wpjb-admin-more-filters").on("click", function(e) {
        e.preventDefault();

        var psi = $("#post-search-input");
        if(psi.is(":disabled")) {
            psi.attr("disabled", false);
            $(".wpjb-admin-filter-search-box").show();
            $(".wpjb-admin-filters-advanced").hide();

        } else {
            psi.attr("disabled", "disabled");
            $(".wpjb-admin-filter-search-box").hide();
            $(".wpjb-admin-filters-advanced").show();
 
        }

    });

    $(".wpjb-admin-filter-clear-all").on("click", function(e) {
        $(".wpjb-admin-filters-advanced input[type=text]").attr("name", "");
        $(".wpjb-admin-filters-advanced input[type=hidden]").attr("name", "");
        $(".wpjb-admin-filters-advanced select").val("");
        $(this).closest("form").submit();
    });

    var user_link = $('#ac--search--user_id').autocomplete({
        source: ajaxurl + "?action=wpjb_main_users",
        minLength: 3,
        select: function(event, ui) {
            $("#search--user_id").val(ui.item.id);
        }
    })

    var job_link = $('#ac--search--job').autocomplete({
        source: ajaxurl + "?action=wpjb_main_jobs",
        minLength: 3,
        select: function(event, ui) {
            $("#search--job").val(ui.item.id);
        }
    })

});