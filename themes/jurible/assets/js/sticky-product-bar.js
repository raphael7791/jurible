/**
 * Sticky Product Bar
 * Affiche la barre quand l'utilisateur scrolle au-delà du hero produit
 */

document.addEventListener('DOMContentLoaded', function() {
    const stickyBar = document.getElementById('sticky-product-bar');

    if (!stickyBar) return;

    // Trouve le hero produit pour savoir quand afficher la barre
    const heroSection = document.querySelector('.hero-produit');
    const showThreshold = heroSection ? heroSection.offsetTop + heroSection.offsetHeight : 600;

    function handleScroll() {
        const scrollY = window.scrollY;

        if (scrollY > showThreshold) {
            stickyBar.classList.add('sticky-product-bar--visible');
        } else {
            stickyBar.classList.remove('sticky-product-bar--visible');
        }
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
});
