/**
 * Footer Accordion - Mobile only
 * Uses event delegation for reliability
 */
(function() {
    'use strict';

    var footer = document.querySelector('.site-footer .footer-columns');
    if (!footer) return;

    footer.addEventListener('click', function(e) {
        // Only on mobile
        if (window.innerWidth > 768) return;

        // Find the heading that was clicked
        var heading = e.target.closest('.footer-column:not(.footer-column--contact) > .wp-block-heading');
        if (!heading) return;

        var column = heading.parentElement;
        var allColumns = footer.querySelectorAll('.footer-column:not(.footer-column--contact)');

        // Close others
        allColumns.forEach(function(col) {
            if (col !== column) {
                col.classList.remove('is-open');
            }
        });

        // Toggle current
        column.classList.toggle('is-open');
    });
})();
