/**
 * Footer Accordion - Mobile only
 */
(function() {
    'use strict';

    function initFooterAccordion() {
        // Only on mobile
        if (window.innerWidth > 768) return;

        const footerColumns = document.querySelectorAll('.site-footer .footer-column:not(.footer-column--contact)');

        footerColumns.forEach(column => {
            const heading = column.querySelector('.wp-block-heading');

            if (heading) {
                heading.addEventListener('click', function() {
                    // Close other open columns
                    footerColumns.forEach(col => {
                        if (col !== column) {
                            col.classList.remove('is-open');
                        }
                    });

                    // Toggle current column
                    column.classList.toggle('is-open');
                });
            }
        });
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFooterAccordion);
    } else {
        initFooterAccordion();
    }

    // Re-init on resize (in case switching between mobile/desktop)
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initFooterAccordion, 250);
    });
})();
