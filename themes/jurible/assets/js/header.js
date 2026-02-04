/**
 * Header Scripts
 * - Glassmorphism effect on scroll
 * - Mobile menu toggle
 */

document.addEventListener('DOMContentLoaded', function() {
    const header = document.getElementById('site-header');
    const burger = document.getElementById('header-burger');

    if (!header) return;

    // Glassmorphism on scroll
    let lastScrollY = 0;
    const scrollThreshold = 50;

    function handleScroll() {
        const scrollY = window.scrollY;

        if (scrollY > scrollThreshold) {
            header.classList.add('site-header--scrolled');
        } else {
            header.classList.remove('site-header--scrolled');
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

    // Mobile menu toggle
    if (burger) {
        burger.addEventListener('click', function() {
            const isExpanded = burger.getAttribute('aria-expanded') === 'true';
            burger.setAttribute('aria-expanded', !isExpanded);
            header.classList.toggle('site-header--menu-open');
            document.body.classList.toggle('menu-open');
        });
    }
});
