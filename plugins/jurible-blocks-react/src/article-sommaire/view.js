/**
 * Sommaire Article — fold / unfold.
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.jurible-sommaire-toggle');

    toggles.forEach((btn) => {
        const sommaire = btn.closest('.jurible-sommaire');
        if (!sommaire) return;

        btn.addEventListener('click', () => {
            const isCollapsed = sommaire.classList.contains('is-collapsed');

            const arrowDown = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            const arrowUp = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 7.5L6 4L9.5 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            const count = btn.getAttribute('data-count');

            if (isCollapsed) {
                sommaire.classList.remove('is-collapsed');
                sommaire.classList.add('is-expanded');
                btn.innerHTML = `Réduire le sommaire ${arrowUp}`;
            } else {
                sommaire.classList.remove('is-expanded');
                sommaire.classList.add('is-collapsed');
                btn.innerHTML = `Voir tout le sommaire ${arrowDown}`;
            }
        });
    });
});
