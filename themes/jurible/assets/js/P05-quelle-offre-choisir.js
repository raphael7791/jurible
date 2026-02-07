/**
 * P05 - Quelle Offre Choisir
 * Flip cards interaction
 */

document.addEventListener('DOMContentLoaded', function() {
    const flipCards = document.querySelectorAll('.flip-card');

    flipCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Don't flip if clicking on the CTA button
            if (e.target.closest('.wp-block-button__link')) {
                return;
            }

            // Toggle flipped state
            this.classList.toggle('flipped');
        });
    });
});
