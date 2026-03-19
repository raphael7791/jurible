(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initClaimButtons();
        initRespondForm();
    });

    /* ── Claim buttons (inbox) ───────────────────────────────────────── */

    function initClaimButtons() {
        document.querySelectorAll('.jaide-btn-claim').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.dataset.id;
                btn.disabled = true;
                btn.textContent = 'En cours...';

                fetch(jaideAdmin.restUrl + '/requests/' + id + '/claim', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': jaideAdmin.nonce,
                    },
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            window.location.href = jaideAdmin.adminUrl + '?page=jaide-detail&id=' + id;
                        } else {
                            btn.disabled = false;
                            btn.textContent = 'Prendre en charge';
                            alert('Erreur : ' + (data.message || 'Impossible de prendre en charge'));
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        btn.textContent = 'Prendre en charge';
                        alert('Erreur réseau');
                    });
            });
        });
    }

    /* ── Respond form (detail page) ──────────────────────────────────── */

    function initRespondForm() {
        var form = document.getElementById('jaide-respond-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var id = form.dataset.id;
            var response = document.getElementById('jaide-response').value.trim();
            var videoUrl = document.getElementById('jaide-video-url').value.trim();
            var fileInput = document.getElementById('jaide-response-file');
            var btn = form.querySelector('.jaide-btn-respond');

            if (!response) {
                alert('Veuillez écrire une réponse.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Envoi en cours...';

            var formData = new FormData();
            formData.append('response', response);
            if (videoUrl) {
                formData.append('video_url', videoUrl);
            }
            if (fileInput.files.length > 0) {
                formData.append('file', fileInput.files[0]);
            }

            fetch(jaideAdmin.restUrl + '/requests/' + id + '/respond', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': jaideAdmin.nonce,
                },
                body: formData,
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        window.location.href = jaideAdmin.adminUrl + '?page=jaide-inbox';
                    } else {
                        btn.disabled = false;
                        btn.textContent = 'Envoyer la réponse';
                        alert('Erreur : ' + (data.message || 'Impossible d\'envoyer'));
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.textContent = 'Envoyer la réponse';
                    alert('Erreur réseau');
                });
        });
    }
})();
