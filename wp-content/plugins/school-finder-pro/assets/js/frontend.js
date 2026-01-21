(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $input = $('#school-finder-pro-input');
        const $results = $('#school-finder-pro-results');
        const $selectedId = $('#school-finder-pro-selected-id');
        const $selectedName = $('#school-finder-pro-selected-name');
        
        if (!$input.length) {
            return;
        }
        
        const minChars = parseInt($input.data('min-chars')) || 2;
        const maxResults = parseInt($input.data('max-results')) || 10;
        let searchTimeout;
        let isSearching = false;
        
        // Hide results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.school-finder-pro-search-wrapper').length) {
                $results.hide();
            }
        });
        
        // Handle input
        $input.on('input', function() {
            const searchTerm = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (searchTerm.length < minChars) {
                $results.hide().empty();
                $selectedId.val('');
                $selectedName.val('');
                return;
            }
            
            searchTimeout = setTimeout(function() {
                searchSchools(searchTerm);
            }, 300);
        });
        
        // Handle keyboard navigation
        $input.on('keydown', function(e) {
            const $items = $results.find('.school-finder-pro-item');
            const $active = $items.filter('.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if ($active.length) {
                    $active.removeClass('active').next().addClass('active');
                } else {
                    $items.first().addClass('active');
                }
                scrollToActive();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if ($active.length) {
                    $active.removeClass('active').prev().addClass('active');
                } else {
                    $items.last().addClass('active');
                }
                scrollToActive();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if ($active.length) {
                    $active.click();
                }
            } else if (e.key === 'Escape') {
                $results.hide();
            }
        });
        
        function scrollToActive() {
            const $active = $results.find('.school-finder-pro-item.active');
            if ($active.length) {
                const scrollTop = $results.scrollTop();
                const itemTop = $active.position().top + scrollTop;
                const itemBottom = itemTop + $active.outerHeight();
                const containerTop = scrollTop;
                const containerBottom = scrollTop + $results.outerHeight();
                
                if (itemTop < containerTop) {
                    $results.scrollTop(itemTop);
                } else if (itemBottom > containerBottom) {
                    $results.scrollTop(itemBottom - $results.outerHeight());
                }
            }
        }
        
        // Search schools via AJAX
        function searchSchools(searchTerm) {
            if (isSearching) {
                return;
            }
            
            isSearching = true;
            const $wrapper = $input.closest('.school-finder-pro-search-wrapper, .school-finder-pro-gf-wrapper');
            $wrapper.addClass('loading');
            $results.html('<div class="school-finder-pro-loading">' + schoolFinderPro.i18n.searching + '</div>').show();
            
            $.ajax({
                url: schoolFinderPro.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'school_finder_pro_search',
                    search: searchTerm,
                    limit: maxResults,
                    nonce: schoolFinderPro.nonce
                },
                success: function(response) {
                    isSearching = false;
                    $wrapper.removeClass('loading');
                    
                    if (response.success && response.data.schools.length > 0) {
                        displayResults(response.data.schools);
                    } else {
                        $results.html('<div class="school-finder-pro-no-results">' + schoolFinderPro.i18n.noResults + '</div>').show();
                    }
                },
                error: function() {
                    isSearching = false;
                    $wrapper.removeClass('loading');
                    $results.html('<div class="school-finder-pro-error">' + schoolFinderPro.i18n.noResults + '</div>').show();
                }
            });
        }
        
        // Display search results
        function displayResults(schools) {
            let html = '';
            
            schools.forEach(function(school) {
                html += '<div class="school-finder-pro-item" data-id="' + school.id + '" data-name="' + escapeHtml(school.name) + '">';
                html += '<div class="school-finder-pro-item-name">' + escapeHtml(school.name) + '</div>';
                html += '<div class="school-finder-pro-item-address">' + escapeHtml(school.address) + '</div>';
                if (school.postcode) {
                    html += '<div class="school-finder-pro-item-postcode">' + escapeHtml(school.postcode) + '</div>';
                }
                html += '<div class="school-finder-pro-item-town">' + escapeHtml(school.town) + '</div>';
                html += '</div>';
            });
            
            $results.html(html).show();
            
            // Handle item click
            $results.find('.school-finder-pro-item').on('click', function() {
                const $item = $(this);
                const schoolId = $item.data('id');
                const schoolName = $item.data('name');
                
                $selectedId.val(schoolId);
                $selectedName.val(schoolName);
                $input.val(schoolName);
                $results.hide();
                
                // Trigger custom event
                $(document).trigger('school_finder_pro_selected', {
                    id: schoolId,
                    name: schoolName,
                    element: $item
                });
            });
            
            // Handle item hover
            $results.find('.school-finder-pro-item').on('mouseenter', function() {
                $results.find('.school-finder-pro-item').removeClass('active');
                $(this).addClass('active');
            });
        }
        
        // Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    });
})(jQuery);
