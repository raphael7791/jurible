/**
 * Sticky Bar - Dismiss functionality
 * Utilise sessionStorage pour ne pas réafficher la barre pendant la session
 */
(function() {
    'use strict';

    const STORAGE_KEY = 'jurible_sticky_bar_dismissed';

    function init() {
        const stickyBar = document.getElementById('sticky-bar');
        const closeBtn = document.getElementById('sticky-bar-close');

        if (!stickyBar) return;

        // Vérifier si déjà fermée dans cette session
        if (sessionStorage.getItem(STORAGE_KEY) === 'true') {
            stickyBar.classList.add('is-hidden');
            return;
        }

        // Gérer le clic sur le bouton fermer
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                stickyBar.classList.add('is-hidden');
                sessionStorage.setItem(STORAGE_KEY, 'true');
            });
        }
    }

    // Initialiser au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
