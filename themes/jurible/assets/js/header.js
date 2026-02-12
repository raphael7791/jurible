/**
 * Header Scripts
 * - Glassmorphism effect on scroll
 * - Mobile menu toggle
 * - Mobile menu accordion
 */

document.addEventListener('DOMContentLoaded', function() {
    const header = document.getElementById('site-header');
    const burger = document.getElementById('header-burger');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

    if (!header) return;

    // ===========================================
    // Glassmorphism on scroll + Hide on scroll down
    // ===========================================
    let lastScrollY = 0;
    const scrollThreshold = 50;
    const hideThreshold = 100; // Start hiding after 100px scroll

    function handleScroll() {
        const scrollY = window.scrollY;
        const scrollDelta = scrollY - lastScrollY;

        // Glassmorphism effect
        if (scrollY > scrollThreshold) {
            header.classList.add('site-header--scrolled');
        } else {
            header.classList.remove('site-header--scrolled');
        }

        // Hide/show on scroll direction
        if (scrollY > hideThreshold) {
            if (scrollDelta > 5) {
                // Scrolling down - hide header
                header.classList.add('site-header--hidden');
            } else if (scrollDelta < -5) {
                // Scrolling up - show header
                header.classList.remove('site-header--hidden');
            }
        } else {
            // Near top - always show
            header.classList.remove('site-header--hidden');
        }

        lastScrollY = scrollY;
    }

    // Throttle scroll event
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Initial check
    handleScroll();

    // ===========================================
    // Mobile menu toggle
    // ===========================================
    function openMobileMenu() {
        mobileMenu.classList.add('is-open');
        mobileMenuOverlay.classList.add('is-visible');
        document.body.classList.add('menu-open');
        burger.setAttribute('aria-expanded', 'true');
    }

    function closeMobileMenu() {
        mobileMenu.classList.remove('is-open');
        mobileMenuOverlay.classList.remove('is-visible');
        document.body.classList.remove('menu-open');
        burger.setAttribute('aria-expanded', 'false');
    }

    if (burger) {
        burger.addEventListener('click', function() {
            const isExpanded = burger.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('is-open')) {
            closeMobileMenu();
        }
    });

    // ===========================================
    // Mobile menu accordion
    // ===========================================
    const accordionItems = document.querySelectorAll('.mobile-menu__item[data-accordion]');

    accordionItems.forEach(function(item) {
        const header = item.querySelector('.mobile-menu__item-header');

        if (header) {
            header.addEventListener('click', function() {
                // Close other accordions (optional - remove if you want multiple open)
                accordionItems.forEach(function(otherItem) {
                    if (otherItem !== item && otherItem.classList.contains('is-open')) {
                        otherItem.classList.remove('is-open');
                    }
                });

                // Toggle current accordion
                item.classList.toggle('is-open');
            });
        }
    });
});
