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

            if (isCollapsed) {
                sommaire.classList.remove('is-collapsed');
                sommaire.classList.add('is-expanded');
                btn.textContent = 'Réduire le sommaire';
            } else {
                sommaire.classList.remove('is-expanded');
                sommaire.classList.add('is-collapsed');
                const count = btn.getAttribute('data-count');
                btn.textContent = `Voir tout le sommaire (${count})`;
            }
        });
    });
});
