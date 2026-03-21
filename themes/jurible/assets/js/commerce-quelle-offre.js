/**
 * P05 - Quelle Offre Choisir
 * Flip cards interaction
 */

document.addEventListener('DOMContentLoaded', function() {
    var flipCards = document.querySelectorAll('.flip-card');

    flipCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            var link = e.target.closest('.wp-block-button__link');
            if (link) {
                var href = link.getAttribute('href');
                if (href && href !== '#') {
                    return;
                }
                e.preventDefault();
            }
            this.classList.toggle('flipped');
        });
    });
});
