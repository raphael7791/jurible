(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        initDropzones();
        initForms();
        loadHistory();
    });

    /* ── Tabs ─────────────────────────────────────────────────────────── */

    function initTabs() {
        var tabs = document.querySelectorAll('.aide-perso__tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var type = tab.dataset.tab;
                switchTab(type);
            });
        });
    }

    function switchTab(type) {
        // Tabs
        document.querySelectorAll('.aide-perso__tab').forEach(function (t) {
            t.classList.toggle('aide-perso__tab--active', t.dataset.tab === type);
        });
        // Panels
        document.querySelectorAll('.aide-perso__panel').forEach(function (p) {
            p.classList.remove('aide-perso__panel--active');
        });
        var panel = document.getElementById('panel-' + type);
        if (panel) {
            panel.classList.add('aide-perso__panel--active');
        }
    }

    /* ── Accordion ────────────────────────────────────────────────────── */

    window.jaideToggleAccordion = function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.toggle('aide-perso__accordion--open');
        }
    };

    /* ── Dropzones ────────────────────────────────────────────────────── */

    function initDropzones() {
        document.querySelectorAll('.aide-perso__dropzone').forEach(function (zone) {
            var input = zone.querySelector('.aide-perso__dropzone-input');
            var content = zone.querySelector('.aide-perso__dropzone-content');
            var chip = zone.querySelector('.aide-perso__file-chip');
            var chipName = zone.querySelector('.aide-perso__file-chip-name');
            var chipRemove = zone.querySelector('.aide-perso__file-chip-remove');

            // File selected
            input.addEventListener('change', function () {
                if (input.files.length > 0) {
                    showFileChip(zone, content, chip, chipName, input.files[0]);
                }
            });

            // Drag events
            zone.addEventListener('dragover', function (e) {
                e.preventDefault();
                zone.classList.add('aide-perso__dropzone--dragover');
            });

            zone.addEventListener('dragleave', function () {
                zone.classList.remove('aide-perso__dropzone--dragover');
            });

            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                zone.classList.remove('aide-perso__dropzone--dragover');
                if (e.dataTransfer.files.length > 0) {
                    var file = e.dataTransfer.files[0];
                    if (validateFile(file)) {
                        // Create a new DataTransfer to set the input files
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                        showFileChip(zone, content, chip, chipName, file);
                    }
                }
            });

            // Remove file
            chipRemove.addEventListener('click', function (e) {
                e.stopPropagation();
                input.value = '';
                chip.style.display = 'none';
                content.style.display = '';
                zone.classList.remove('aide-perso__dropzone--success');
            });
        });
    }

    function showFileChip(zone, content, chip, chipName, file) {
        chipName.textContent = file.name;
        chip.style.display = 'inline-flex';
        content.style.display = 'none';
        zone.classList.add('aide-perso__dropzone--success');
    }

    function validateFile(file) {
        var allowed = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'];
        var ext = file.name.split('.').pop().toLowerCase();
        var allowedExt = ['pdf', 'docx', 'odt'];

        if (!allowed.includes(file.type) && !allowedExt.includes(ext)) {
            alert('Format non accepté. Veuillez utiliser PDF, DOCX ou ODT.');
            return false;
        }
        if (file.size > 20 * 1024 * 1024) {
            alert('Fichier trop volumineux (max 20 Mo).');
            return false;
        }
        return true;
    }

    /* ── Forms ────────────────────────────────────────────────────────── */

    function initForms() {
        document.querySelectorAll('.aide-perso__form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                handleSubmit(form);
            });
        });
    }

    function handleSubmit(form) {
        var type = form.dataset.type;
        var btn = form.querySelector('.aide-perso__submit');
        var textEl = btn.querySelector('.aide-perso__submit-text');
        var loadingEl = btn.querySelector('.aide-perso__submit-loading');

        // Collect data
        var formData = new FormData();
        formData.append('type', type);
        formData.append('nom', form.querySelector('[name="nom"]').value.trim());
        formData.append('email', form.querySelector('[name="email"]').value.trim());
        formData.append('annee', form.querySelector('[name="annee"]').value);
        formData.append('matiere', form.querySelector('[name="matiere"]').value.trim());
        formData.append('message', form.querySelector('[name="message"]').value.trim());

        // File
        var fileInput = form.querySelector('.aide-perso__dropzone-input');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }

        // Validation
        if (!formData.get('nom') || !formData.get('email') || !formData.get('annee') || !formData.get('matiere')) {
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        if (type === 'question' && !formData.get('message')) {
            alert('Veuillez écrire votre question.');
            return;
        }
        if (type === 'copie' && (!fileInput || fileInput.files.length === 0)) {
            alert('Veuillez joindre votre copie.');
            return;
        }

        // Loading state
        btn.disabled = true;
        textEl.style.display = 'none';
        loadingEl.style.display = 'flex';

        fetch(jaideData.restUrl + '/submit', {
            method: 'POST',
            headers: {
                'X-WP-Nonce': jaideData.nonce,
            },
            body: formData,
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.disabled = false;
                textEl.style.display = '';
                loadingEl.style.display = 'none';

                if (data.success) {
                    form.reset();
                    // Reset dropzone
                    var zone = form.querySelector('.aide-perso__dropzone');
                    if (zone) {
                        zone.classList.remove('aide-perso__dropzone--success');
                        zone.querySelector('.aide-perso__file-chip').style.display = 'none';
                        zone.querySelector('.aide-perso__dropzone-content').style.display = '';
                    }
                    showModal(type);
                    loadHistory();
                } else {
                    alert(data.message || 'Une erreur est survenue.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                textEl.style.display = '';
                loadingEl.style.display = 'none';
                alert('Erreur réseau. Veuillez réessayer.');
            });
    }

    /* ── Modal ────────────────────────────────────────────────────────── */

    function showModal(type) {
        var modal = document.getElementById('jaide-modal');
        var title = document.getElementById('jaide-modal-title');
        var text = document.getElementById('jaide-modal-text');

        if (type === 'question') {
            title.textContent = 'Question envoyée !';
            text.textContent = 'Votre question a bien été transmise à notre équipe pédagogique. Vous recevrez une réponse par email dans les meilleurs délais.';
        } else {
            title.textContent = 'Copie déposée !';
            text.textContent = 'Votre copie a bien été reçue. La correction sera effectuée sous 4 jours ouvrés maximum et vous recevrez un email avec le retour détaillé.';
        }

        modal.style.display = 'flex';
    }

    window.jaideCloseModal = function () {
        var modal = document.getElementById('jaide-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    /* ── View detail (réponse) ───────────────────────────────────────── */

    window.jaideViewDetail = function (id) {
        var modal = document.getElementById('jaide-modal');
        var title = document.getElementById('jaide-modal-title');
        var text = document.getElementById('jaide-modal-text');
        var icon = modal.querySelector('.aide-perso__modal-icon');

        title.textContent = 'Chargement...';
        text.innerHTML = '';
        icon.innerHTML = '<svg class="aide-perso__spinner" width="32" height="32" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="#7C3AED" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/></svg>';
        modal.style.display = 'flex';

        fetch(jaideData.restUrl + '/my-requests/' + id, {
            headers: { 'X-WP-Nonce': jaideData.nonce },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success || !data.data) {
                    title.textContent = 'Erreur';
                    text.textContent = 'Impossible de charger la demande.';
                    icon.innerHTML = '';
                    return;
                }

                var r = data.data;
                var typeLabel = r.type === 'question' ? 'Question' : 'Copie';

                icon.innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                title.textContent = typeLabel + ' — ' + r.matiere;

                var html = '';

                // Ma demande
                if (r.message) {
                    html += '<div class="aide-perso__detail-section">';
                    html += '<p class="aide-perso__detail-label">' + (r.type === 'question' ? 'Ma question' : 'Mes commentaires') + '</p>';
                    html += '<p class="aide-perso__detail-text">' + escapeHtml(r.message).replace(/\n/g, '<br>') + '</p>';
                    html += '</div>';
                }

                // Réponse
                if (r.response) {
                    html += '<div class="aide-perso__detail-section aide-perso__detail-section--response">';
                    html += '<p class="aide-perso__detail-label">Réponse de l\'enseignant</p>';
                    html += '<p class="aide-perso__detail-text">' + escapeHtml(r.response).replace(/\n/g, '<br>') + '</p>';
                    html += '</div>';
                }

                // Fichier/vidéo de correction
                if (r.response_file_url) {
                    html += '<p class="aide-perso__detail-file"><a href="' + escapeHtml(r.response_file_url) + '" target="_blank" rel="noopener">Voir le fichier / la vidéo de correction &rarr;</a></p>';
                }

                if (r.responded_at) {
                    html += '<p class="aide-perso__detail-date">Répondu le ' + formatDate(r.responded_at) + '</p>';
                }

                text.innerHTML = html;
            })
            .catch(function () {
                title.textContent = 'Erreur';
                text.textContent = 'Impossible de charger la demande.';
                icon.innerHTML = '';
            });
    };

    /* ── History ──────────────────────────────────────────────────────── */

    function loadHistory() {
        var wrapper = document.getElementById('jaide-history');
        var container = document.getElementById('jaide-history-list');
        if (!container || !wrapper || typeof jaideData === 'undefined') return;

        fetch(jaideData.restUrl + '/my-requests', {
            headers: {
                'X-WP-Nonce': jaideData.nonce,
            },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success || !data.data || data.data.length === 0) {
                    wrapper.style.display = 'none';
                    return;
                }

                var statusLabels = {
                    pending: 'En attente',
                    in_progress: 'En cours',
                    completed: 'Traité',
                };

                var html = '';
                data.data.forEach(function (item) {
                    var clickable = item.status === 'completed' ? ' aide-perso__history-item--clickable' : '';
                    html += '<div class="aide-perso__history-item' + clickable + '"' + (item.status === 'completed' ? ' data-id="' + escapeHtml(item.id) + '" onclick="jaideViewDetail(' + escapeHtml(item.id) + ')"' : '') + '>';
                    html += '<span class="aide-perso__history-type aide-perso__history-type--' + escapeHtml(item.type) + '">' + escapeHtml(item.type === 'question' ? 'Question' : 'Copie') + '</span>';
                    html += '<span class="aide-perso__history-info">' + escapeHtml(item.matiere) + ' (' + escapeHtml(item.annee) + ')</span>';
                    html += '<span class="aide-perso__history-date">' + formatDate(item.created_at) + '</span>';
                    html += '<span class="aide-perso__history-status aide-perso__history-status--' + escapeHtml(item.status) + '">' + escapeHtml(statusLabels[item.status] || item.status) + '</span>';
                    if (item.status === 'completed') {
                        html += '<span class="aide-perso__history-view">Voir la réponse &rarr;</span>';
                    }
                    html += '</div>';
                });

                container.innerHTML = html;
                wrapper.style.display = '';
            })
            .catch(function () {
                wrapper.style.display = 'none';
            });
    }

    /* ── Helpers ──────────────────────────────────────────────────────── */

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
    }
})();
