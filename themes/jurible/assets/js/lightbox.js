/**
 * Simple Lightbox pour images zoomables
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Crée l'overlay lightbox
        const overlay = document.createElement('div');
        overlay.className = 'jurible-lightbox-overlay';
        overlay.innerHTML = '<img class="jurible-lightbox-image" src="" alt=""/><span class="jurible-lightbox-close">&times;</span>';
        document.body.appendChild(overlay);

        const lightboxImg = overlay.querySelector('.jurible-lightbox-image');

        // Gère le clic sur les images avec zoom
        document.querySelectorAll('.contenu-fiche__sommaire-image a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                lightboxImg.src = this.href;
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        // Ferme au clic sur l'overlay ou le bouton close
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay || e.target.classList.contains('jurible-lightbox-close')) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Ferme avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
})();
