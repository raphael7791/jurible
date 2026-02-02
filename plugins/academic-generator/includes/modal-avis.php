<?php
/**
 * Modal d'avis TrustPilot - Design Jurible
 */

if (!defined('ABSPATH')) {
    exit;
}

function aga_render_modal_avis() {
    ?>
    <!-- Modal demande d'avis -->
    <div class="aga-modal-overlay">
        <div class="aga-modal">
            <button class="aga-modal-close" onclick="fermerModalAvis()">&times;</button>
            
            <h3 class="aga-modal-title">Comment trouvez-vous notre générateur ?</h3>
            <p class="aga-modal-subtitle">Votre avis nous aide à améliorer le service</p>
            
            <!-- Système d'étoiles -->
            <div class="aga-etoiles" id="avisEtoiles">
                <span class="aga-etoile" data-note="1">★</span>
                <span class="aga-etoile" data-note="2">★</span>
                <span class="aga-etoile" data-note="3">★</span>
                <span class="aga-etoile" data-note="4">★</span>
                <span class="aga-etoile" data-note="5">★</span>
            </div>
            
            <!-- Zone de contenu dynamique selon la note -->
            <div id="avisContenu" class="aga-modal-content-hidden"></div>
        </div>
    </div>

    <script>
    // Déplacer la modal dans le body
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.querySelector('.aga-modal-overlay');
        if (modal) {
            document.body.appendChild(modal);
        }
    });

    // Fermer la modal
    function fermerModalAvis() {
        const modal = document.querySelector('.aga-modal-overlay');
        const limitSection = document.querySelector('.aga-limit-card');
        
        if (modal) {
            modal.remove();
        }
        if (limitSection) {
            limitSection.style.display = 'block';
        }
    }
    
    let noteSelectionnee = 0;
let choixVerrouille = false;

// Gestion des clics sur les étoiles
document.querySelectorAll('.aga-etoile').forEach(etoile => {
    etoile.addEventListener('click', function() {
        if (choixVerrouille) return; // Bloqué après premier choix
        
        noteSelectionnee = parseInt(this.dataset.note);
        choixVerrouille = true; // Verrouiller
        
        document.querySelectorAll('.aga-etoile').forEach((e, index) => {
            if (index < noteSelectionnee) {
                e.classList.add('active');
            } else {
                e.classList.remove('active');
            }
            e.style.cursor = 'default'; // Enlever le curseur pointer
        });
        
        afficherContenuSelonNote(noteSelectionnee);
    });
    
    etoile.addEventListener('mouseenter', function() {
        if (choixVerrouille) return; // Pas de hover si verrouillé
        
        const note = parseInt(this.dataset.note);
        document.querySelectorAll('.aga-etoile').forEach((e, index) => {
            if (index < note) {
                e.classList.add('hover');
            } else {
                e.classList.remove('hover');
            }
        });
    });
});

document.getElementById('avisEtoiles')?.addEventListener('mouseleave', function() {
    if (choixVerrouille) return;
    document.querySelectorAll('.aga-etoile').forEach(e => e.classList.remove('hover'));
});
    
    function afficherContenuSelonNote(note) {
        const contenu = document.getElementById('avisContenu');
        contenu.classList.remove('aga-modal-content-hidden');
        
        if (note === 5) {
            contenu.innerHTML = `
                <p class="aga-modal-success">🎉 Super ! Partagez votre expérience sur TrustPilot</p>
                <p class="aga-modal-reward">En remerciement : <strong>3 crédits bonus</strong></p>
                <p class="aga-modal-info">Dès que votre avis sera publié sur TrustPilot (quelques heures), vos 3 crédits seront ajoutés.</p>
                <button class="aga-btn aga-btn-primary aga-btn-full" onclick="ouvrirTrustPilot()">Laisser un avis sur TrustPilot</button>
            `;
        } else {
            contenu.innerHTML = `
                <p class="aga-modal-message">Merci pour votre retour. Comment pouvons-nous nous améliorer ?</p>
                <textarea id="feedbackTexte" class="aga-textarea" placeholder="Dites-nous ce qui ne va pas..." rows="4"></textarea>
                <button class="aga-btn aga-btn-primary aga-btn-full" onclick="envoyerFeedback()">Envoyer mon feedback</button>
            `;
        }
    }
    
    function envoyerFeedback() {
        const texte = document.getElementById('feedbackTexte').value.trim();
        
        if (!texte) {
            alert('Veuillez saisir votre feedback');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'aga_enregistrer_avis');
        formData.append('note', noteSelectionnee);
        formData.append('feedback', texte);
        formData.append('type', 'feedback');
        formData.append('nonce', '<?php echo wp_create_nonce('aga_avis_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Merci pour votre feedback !');
                location.reload();
            } else {
                alert('Erreur : ' + data.data.message);
            }
        });
    }
    
    function ouvrirTrustPilot() {
        window.open('https://fr.trustpilot.com/evaluate/aideauxtd.com', '_blank');
        
        document.getElementById('avisContenu').innerHTML = `
            <p class="aga-modal-success">✅ Merci ! N'oubliez pas de publier votre avis.</p>
            <p class="aga-modal-info">Vos crédits seront ajoutés dès validation (quelques heures).</p>
            <button class="aga-btn aga-btn-outline aga-btn-full" onclick="fermerModalAvis()">Fermer</button>
        `;
        
        const formData = new FormData();
        formData.append('action', 'aga_enregistrer_avis');
        formData.append('note', noteSelectionnee);
        formData.append('type', 'trustpilot');
        formData.append('nonce', '<?php echo wp_create_nonce('aga_avis_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        }).catch(() => {});
    }
    </script>
    <?php
}