(function ($) {
    $(document).ready(function () {
        setTimeout(function () {
            // Hide all description tabs and tab buttons
            $('#tabset_1').find('#tab-description').hide();
            $('#tabset_1').find('#tab-title-description').hide();
            $('#tabset_2').find('#tab-description').hide();
            $('#tabset_2').find('#tab-title-description').hide();
            $('#tabset_3').find('#tab-description').hide();
            $('#tabset_3').find('#tab-title-description').hide();

            // Set active tabs
            $('#tabset_1').find('.overview_tab a').trigger('click');
            $('#tabset_2').find('.suitability_tab a').trigger('click');
            $('#tabset_3').find('.career-opportunities_tab a').trigger('click');

            // Hide tabset_1 unneeded tabs and tab buttons
            $('#tabset_1').find('#tab-suitability').hide();
            $('#tabset_1').find('#tab-title-suitability').hide();
            $('#tabset_1').find('#tab-qualifications').hide();
            $('#tabset_1').find('#tab-title-qualifications').hide();
            $('#tabset_1').find('#tab-booking-and-interview').hide();
            $('#tabset_1').find('#tab-title-booking-and-interview').hide();
            $('#tabset_1').find('#tab-school-facilities').hide();
            $('#tabset_1').find('#tab-title-school-facilities').hide();
            $('#tabset_1').find('#tab-uniform-and-equipment').hide();
            $('#tabset_1').find('#tab-title-uniform-and-equipment').hide();
            $('#tabset_1').find('#tab-career-opportunities').hide();
            $('#tabset_1').find('#tab-title-career-opportunities').hide();
            $('#tabset_1').find('#tab-work-experience').hide();
            $('#tabset_1').find('#tab-title-work-experience').hide();

            // Hide tabset_2 unneeded tabs and tab buttons
            $('#tabset_2').find('#tab-overview').hide();
            $('#tabset_2').find('#tab-title-overview').hide();
            $('#tabset_2').find('#tab-skills-covered').hide();
            $('#tabset_2').find('#tab-title-skills-covered').hide();
            $('#tabset_2').find('#tab-example-recipes').hide();
            $('#tabset_2').find('#tab-title-example-recipes').hide();
            $('#tabset_2').find('#tab-typical-day').hide();
            $('#tabset_2').find('#tab-title-typical-day').hide();
            $('#tabset_2').find('#tab-career-opportunities').hide();
            $('#tabset_2').find('#tab-title-career-opportunities').hide();
            $('#tabset_2').find('#tab-work-experience').hide();
            $('#tabset_2').find('#tab-title-work-experience').hide();

            // Hide tabset_3 unneeded tabs and tab buttons
            $('#tabset_3').find('#tab-overview').hide();
            $('#tabset_3').find('#tab-title-overview').hide();
            $('#tabset_3').find('#tab-skills-covered').hide();
            $('#tabset_3').find('#tab-title-skills-covered').hide();
            $('#tabset_3').find('#tab-example-recipes').hide();
            $('#tabset_3').find('#tab-title-example-recipes').hide();
            $('#tabset_3').find('#tab-typical-day').hide();
            $('#tabset_3').find('#tab-title-typical-day').hide();
            $('#tabset_3').find('#tab-suitability').hide();
            $('#tabset_3').find('#tab-title-suitability').hide();
            $('#tabset_3').find('#tab-qualifications').hide();
            $('#tabset_3').find('#tab-title-qualifications').hide();
            $('#tabset_3').find('#tab-booking-and-interview').hide();
            $('#tabset_3').find('#tab-title-booking-and-interview').hide();
            $('#tabset_3').find('#tab-school-facilities').hide();
            $('#tabset_3').find('#tab-title-school-facilities').hide();
            $('#tabset_3').find('#tab-uniform-and-equipment').hide();
            $('#tabset_3').find('#tab-title-uniform-and-equipment').hide();
        }, 500);
    });
})(jQuery);
