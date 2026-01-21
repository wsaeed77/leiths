(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $input = $('#school-lookup-input');
        const $results = $('#school-lookup-results');
        const $selectedId = $('#school-lookup-selected-id');
        const $selectedName = $('#school-lookup-selected-name');
        
        if (!$input.length) {
            return;
        }
        
        const minChars = parseInt($input.data('min-chars')) || 2;
        const maxResults = parseInt($input.data('max-results')) || 10;
        let searchTimeout;
        let isSearching = false;
        
        // Hide results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.school-lookup-search-wrapper').length) {
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
            const $items = $results.find('.school-lookup-item');
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
            const $active = $results.find('.school-lookup-item.active');
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
            $results.html('<div class="school-lookup-loading">' + schoolLookup.i18n.searching + '</div>').show();
            
            $.ajax({
                url: schoolLookup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'school_lookup_search',
                    search: searchTerm,
                    limit: maxResults,
                    nonce: schoolLookup.nonce
                },
                success: function(response) {
                    isSearching = false;
                    
                    if (response.success && response.data.schools.length > 0) {
                        displayResults(response.data.schools);
                    } else {
                        $results.html('<div class="school-lookup-no-results">' + schoolLookup.i18n.noResults + '</div>').show();
                    }
                },
                error: function() {
                    isSearching = false;
                    $results.html('<div class="school-lookup-error">' + schoolLookup.i18n.noResults + '</div>').show();
                }
            });
        }
        
        // Display search results
        function displayResults(schools) {
            let html = '';
            
            schools.forEach(function(school) {
                html += '<div class="school-lookup-item" data-id="' + school.id + '" data-name="' + escapeHtml(school.name) + '">';
                html += '<div class="school-lookup-item-name">' + escapeHtml(school.name) + '</div>';
                html += '<div class="school-lookup-item-address">' + escapeHtml(school.address) + '</div>';
                if (school.postcode) {
                    html += '<div class="school-lookup-item-postcode">' + escapeHtml(school.postcode) + '</div>';
                }
                html += '<div class="school-lookup-item-town">' + escapeHtml(school.town) + '</div>';
                html += '</div>';
            });
            
            $results.html(html).show();
            
            // Handle item click
            $results.find('.school-lookup-item').on('click', function() {
                const $item = $(this);
                const schoolId = $item.data('id');
                const schoolName = $item.data('name');
                
                $selectedId.val(schoolId);
                $selectedName.val(schoolName);
                $input.val(schoolName);
                $results.hide();
                
                // Trigger custom event
                $(document).trigger('school_lookup_selected', {
                    id: schoolId,
                    name: schoolName,
                    element: $item
                });
            });
            
            // Handle item hover
            $results.find('.school-lookup-item').on('mouseenter', function() {
                $results.find('.school-lookup-item').removeClass('active');
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
