// Assessment View - Front-end interactivity

function initAssessment(container) {
    if (container.dataset.initialized === 'true') return;
    container.dataset.initialized = 'true';

    const assessmentId = container.dataset.assessmentId;
    const assessmentTitle = container.dataset.assessmentTitle;

    if (!assessmentId) {
        container.innerHTML = '<p style="color: #EF4444;">Assessment non configuré.</p>';
        return;
    }

    // Afficher le loading
    container.innerHTML = '<div class="assess-loading"><span class="assess-spinner"></span> Chargement...</div>';

    // Récupérer les données de l'assessment et la soumission de l'utilisateur
    fetch(`/wp-json/jurible/v1/assessments/${assessmentId}/my-submission`, {
        credentials: 'same-origin',
        headers: {
            'X-WP-Nonce': getWPNonce()
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderAssessment(container, data.data, assessmentTitle);
        } else {
            container.innerHTML = `<p style="color: #EF4444;">${data.message || 'Erreur de chargement'}</p>`;
        }
    })
    .catch(err => {
        console.error('Erreur:', err);
        container.innerHTML = '<p style="color: #EF4444;">Erreur de connexion. Veuillez rafraîchir la page.</p>';
    });
}

function renderAssessment(container, data, title) {
    const { assessment, submission } = data;

    let html = `<div class="assess-block">`;
    html += `<div class="assess-block-header"><span class="assess-block-icon">📤</span> RENDRE VOTRE TRAVAIL</div>`;
    html += `<div class="assess-block-content">`;

    if (!submission) {
        // État 1 : Pas encore soumis
        html += renderNotSubmitted(assessment);
    } else if (submission.status === 'submitted') {
        // État 2 : Soumis, en attente
        html += renderSubmitted(submission, assessment);
    } else if (submission.status === 'in_review') {
        // État 3 : En cours de correction
        html += renderInReview(submission, assessment);
    } else if (submission.status === 'graded') {
        // État 4 : Corrigé
        html += renderGraded(submission, assessment);
    }

    html += `</div></div>`;
    container.innerHTML = html;

    // Attacher les événements
    attachEvents(container, assessment);
}

function renderSubjectPdf(assessment) {
    if (!assessment.subject_pdf_url) return '';
    
    return `
        <div class="assess-subject-pdf">
            <a href="${escapeHtml(assessment.subject_pdf_url)}" target="_blank" class="assess-subject-btn">
                📋 Télécharger le sujet (PDF)
            </a>
        </div>
    `;
}

function renderNotSubmitted(assessment) {
    let html = `
        <div class="assess-status assess-status-pending">
            <span class="assess-status-icon">⏳</span>
            <span>Non rendu</span>
        </div>
    `;

    if (assessment.due_date) {
        const dueDate = new Date(assessment.due_date);
        const now = new Date();
        const isOverdue = dueDate < now;
        html += `
            <div class="assess-due-date ${isOverdue ? 'overdue' : ''}">
                📅 Date limite : ${formatDate(dueDate)}
                ${isOverdue ? '<span class="overdue-badge">En retard</span>' : ''}
            </div>
        `;
    }

    // Afficher le PDF du sujet
    html += renderSubjectPdf(assessment);

    html += `
        <div class="assess-upload-zone" id="assess-upload-zone">
            <div class="assess-upload-icon">📄</div>
            <p>Glissez votre fichier ici ou</p>
            <label class="assess-upload-btn">
                <input type="file" id="assess-file-input" accept=".pdf,.docx,.doc,.odt" style="display:none;">
                Choisir un fichier
            </label>
            <p class="assess-upload-hint">Formats acceptés : PDF, DOCX, ODT • Max 20 MB</p>
        </div>
        <div class="assess-file-preview" id="assess-file-preview" style="display:none;">
            <span class="assess-file-icon">📄</span>
            <span class="assess-file-name" id="assess-file-name"></span>
            <button type="button" class="assess-file-remove" id="assess-file-remove">✕</button>
        </div>
        <button type="button" class="assess-submit-btn" id="assess-submit-btn" disabled>
            Soumettre mon travail
        </button>
    `;

    return html;
}

function renderSubmitted(submission, assessment) {
    let html = `
        <div class="assess-status assess-status-submitted">
            <span class="assess-status-icon">✅</span>
            <span>Soumis</span>
        </div>
        <div class="assess-submission-info">
            <p><strong>📄 ${getFileName(submission.file_url)}</strong></p>
            <p>Soumis le ${formatDate(new Date(submission.submitted_at))}</p>
        </div>
    `;
    
    // Afficher le PDF du sujet
    html += renderSubjectPdf(assessment);
    
    html += `
        <div class="assess-waiting">
            <p>⏳ En attente de correction</p>
            <p class="assess-waiting-hint">Vous serez notifié par email dès que votre travail sera corrigé.</p>
        </div>
    `;
    
    return html;
}

function renderInReview(submission, assessment) {
    let html = `
        <div class="assess-status assess-status-review">
            <span class="assess-status-icon">🔵</span>
            <span>En cours de correction</span>
        </div>
        <div class="assess-submission-info">
            <p><strong>📄 ${getFileName(submission.file_url)}</strong></p>
            <p>Soumis le ${formatDate(new Date(submission.submitted_at))}</p>
        </div>
    `;
    
    // Afficher le PDF du sujet
    html += renderSubjectPdf(assessment);
    
    html += `
        <div class="assess-waiting">
            <p>🔍 Votre professeur corrige actuellement votre travail...</p>
        </div>
    `;
    
    return html;
}

function renderGraded(submission, assessment) {
    const percentage = (submission.score / assessment.max_score) * 100;
    let scoreClass = 'score-low';
    if (percentage >= 70) scoreClass = 'score-high';
    else if (percentage >= 50) scoreClass = 'score-medium';

    let html = `
        <div class="assess-status assess-status-graded">
            <span class="assess-status-icon">✅</span>
            <span>Corrigé</span>
        </div>
        <div class="assess-score ${scoreClass}">
            <span class="assess-score-value">${submission.score}</span>
            <span class="assess-score-max">/ ${assessment.max_score}</span>
        </div>
        <div class="assess-feedback">
            <div class="assess-feedback-header">💬 Feedback de votre professeur</div>
            <div class="assess-feedback-content">${escapeHtml(submission.feedback)}</div>
        </div>
    `;

    // Vidéo de correction
    if (submission.video_url) {
        // Vérifier si c'est un lien Tella
        const tellaMatch = submission.video_url.match(/tella\.tv\/video\/([a-zA-Z0-9-]+)/);
        
        if (tellaMatch) {
            // Embed Tella
            const videoId = tellaMatch[1];
            html += `
                <div class="assess-video-embed">
                    <div class="assess-video-header">🎥 Vidéo de correction</div>
                    <div class="assess-video-container">
                        <iframe 
                            src="https://www.tella.tv/video/${videoId}/embed?b=1&title=1&a=1&loop=0&t=0&muted=0&wt=1" 
                            allowfullscreen 
                            allowtransparency>
                        </iframe>
                    </div>
                </div>
            `;
        } else {
            // Bouton classique pour autres liens
            html += `
                <div class="assess-video">
                    <a href="${escapeHtml(submission.video_url)}" target="_blank" class="assess-video-btn">
                        🎥 Voir la vidéo de correction
                    </a>
                </div>
            `;
        }
    }

    // PDF de correction type (débloqué après la note)
    if (assessment.correction_pdf_url) {
        html += `
            <div class="assess-correction-pdf">
                <a href="${escapeHtml(assessment.correction_pdf_url)}" target="_blank" class="assess-correction-btn">
                    📝 Télécharger la correction type (PDF)
                </a>
            </div>
        `;
    }

    // PDF du sujet
    html += renderSubjectPdf(assessment);

    // Copie de l'étudiant
    html += `
        <div class="assess-submission-file">
            <p>📄 Votre copie : <a href="${escapeHtml(submission.file_url)}" target="_blank">${getFileName(submission.file_url)}</a></p>
        </div>
    `;

    return html;
}

function attachEvents(container, assessment) {
    const fileInput = container.querySelector('#assess-file-input');
    const uploadZone = container.querySelector('#assess-upload-zone');
    const filePreview = container.querySelector('#assess-file-preview');
    const fileName = container.querySelector('#assess-file-name');
    const fileRemove = container.querySelector('#assess-file-remove');
    const submitBtn = container.querySelector('#assess-submit-btn');

    if (!fileInput) return;

    let selectedFile = null;

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            selectedFile = this.files[0];
            showFilePreview(selectedFile);
        }
    });

    // Drag and drop
    if (uploadZone) {
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                selectedFile = e.dataTransfer.files[0];
                showFilePreview(selectedFile);
            }
        });
    }

    // Remove file
    if (fileRemove) {
        fileRemove.addEventListener('click', function() {
            selectedFile = null;
            fileInput.value = '';
            filePreview.style.display = 'none';
            uploadZone.style.display = 'block';
            submitBtn.disabled = true;
        });
    }

    // Submit
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (!selectedFile) return;
            submitAssessment(container, assessment.id, selectedFile, submitBtn);
        });
    }

    function showFilePreview(file) {
        // Validate file
        const maxSize = 20 * 1024 * 1024; // 20MB
        const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/vnd.oasis.opendocument.text'];

        if (file.size > maxSize) {
            alert('Le fichier est trop volumineux. Taille maximum : 20 MB');
            return;
        }

        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|docx?|odt)$/i)) {
            alert('Format de fichier non accepté. Utilisez PDF, DOCX ou ODT.');
            return;
        }

        fileName.textContent = file.name;
        filePreview.style.display = 'flex';
        uploadZone.style.display = 'none';
        submitBtn.disabled = false;
    }
}

function submitAssessment(container, assessmentId, file, submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="assess-spinner"></span> Envoi en cours...';

    const formData = new FormData();
    formData.append('file', file);

    fetch(`/wp-json/jurible/v1/assessments/${assessmentId}/submit`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-WP-Nonce': getWPNonce()
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Recharger l'affichage
            container.dataset.initialized = 'false';
            initAssessment(container);
        } else {
            alert(data.message || 'Erreur lors de la soumission');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Soumettre mon travail';
        }
    })
    .catch(err => {
        console.error('Erreur:', err);
        alert('Erreur de connexion. Veuillez réessayer.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Soumettre mon travail';
    });
}

// Helpers
function getWPNonce() {
    if (typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) {
        return wpApiSettings.nonce;
    }
    if (typeof juribleAssess !== 'undefined' && juribleAssess.nonce) {
        return juribleAssess.nonce;
    }
    const nonceMeta = document.querySelector('meta[name="wp-nonce"]');
    if (nonceMeta) {
        return nonceMeta.content;
    }
    return '';
}

function formatDate(date) {
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getFileName(url) {
    if (!url) return 'Fichier';
    return url.split('/').pop().split('?')[0];
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize on DOM ready and handle SPA navigation
function initAllAssessments() {
    document.querySelectorAll('.jurible-assessment-container').forEach(initAssessment);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllAssessments);
} else {
    initAllAssessments();
}

// MutationObserver for SPA navigation (Fluent Community)
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        mutation.addedNodes.forEach(function(node) {
            if (node.nodeType === 1) {
                if (node.classList && node.classList.contains('jurible-assessment-container')) {
                    initAssessment(node);
                }
                node.querySelectorAll && node.querySelectorAll('.jurible-assessment-container').forEach(initAssessment);
            }
        });
    });
});

observer.observe(document.body, { childList: true, subtree: true });