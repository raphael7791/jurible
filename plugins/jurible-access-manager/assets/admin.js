(function($) {
    'use strict';

    // ─── Select All / Deselect All courses ───
    $('#jam-select-all-courses').on('click', function(e) {
        e.preventDefault();
        $('input[name="fcom_course_ids[]"]').prop('checked', true);
    });

    $('#jam-deselect-all-courses').on('click', function(e) {
        e.preventDefault();
        $('input[name="fcom_course_ids[]"]').prop('checked', false);
    });

    // ─── Product dual dropdown sync ───
    $('#jam-select-new').on('change', function() {
        if ($(this).val()) {
            $('#jam-select-old').val('');
            $('#sc_product_id').val($(this).val());
        } else if (!$('#jam-select-old').val()) {
            $('#sc_product_id').val('');
        }
    });

    $('#jam-select-old').on('change', function() {
        if ($(this).val()) {
            $('#jam-select-new').val('');
            $('#sc_product_id').val($(this).val());
        } else if (!$('#jam-select-new').val()) {
            $('#sc_product_id').val('');
        }
    });

    // ─── Accordion toggle ───
    $(document).on('click', '.jam-accordion__toggle', function() {
        var $accordion = $(this).closest('.jam-accordion');
        var $content = $accordion.find('.jam-accordion__content');

        $accordion.toggleClass('is-open');
        $content.slideToggle(200);
    });

    // ─── Toggle product new/old ───
    $(document).on('click', '.jam-toggle-new', function() {
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var isNew = $btn.data('is-new');

        $btn.prop('disabled', true).text('...');

        $.post(jamAdmin.ajaxUrl, {
            action: 'jam_toggle_product_new',
            nonce: jamAdmin.nonce,
            product_id: productId,
            is_new: isNew
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                $btn.prop('disabled', false).text('Erreur');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Erreur');
        });
    });

    // ─── User details expand/collapse ───
    $(document).on('click', '.jam-user-toggle', function() {
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var $detailRow = $('tr.jam-user-detail[data-user-id="' + userId + '"]');
        var $inner = $detailRow.find('.jam-user-detail__inner');

        if ($detailRow.is(':visible')) {
            $detailRow.hide();
            $btn.html('Details &#9662;');
            return;
        }

        $detailRow.show();
        $btn.html('Fermer &#9652;');

        // Only load if not already loaded
        if ($inner.data('loaded')) {
            return;
        }

        $inner.html('<div class="jam-loading"><span class="spinner"></span> Chargement des donnees...</div>');

        $.post(jamAdmin.ajaxUrl, {
            action: 'jam_get_user_details',
            nonce: jamAdmin.nonce,
            user_id: userId
        }, function(response) {
            if (!response.success) {
                $inner.html('<div class="jam-notice jam-notice--error">' + (response.data || 'Erreur.') + '</div>');
                return;
            }

            $inner.data('loaded', true);
            var d = response.data;
            var html = '<div class="jam-user-detail__cols">';

            // ─── Left column: SureCart subscriptions ───
            html += '<div class="jam-user-detail__col">';
            html += '<h4>Abonnements SureCart</h4>';

            if (d.subscriptions.length === 0 && d.purchases.length === 0) {
                html += '<p style="color:#646970;">Aucun abonnement ou achat trouve.</p>';
            } else {
                if (d.subscriptions.length > 0) {
                    html += '<table class="jam-table jam-table--compact">';
                    html += '<thead><tr><th>Produit</th><th>Prix</th><th>Statut</th><th>Depuis</th></tr></thead><tbody>';
                    for (var i = 0; i < d.subscriptions.length; i++) {
                        var sub = d.subscriptions[i];
                        html += '<tr>';
                        html += '<td>' + escHtml(sub.product_name) + '</td>';
                        html += '<td>' + escHtml(sub.price) + '</td>';
                        html += '<td>' + statusBadge(sub.status) + '</td>';
                        html += '<td>' + escHtml(sub.created_at) + '</td>';
                        html += '</tr>';
                    }
                    html += '</tbody></table>';
                }

                if (d.purchases.length > 0) {
                    html += '<h4 style="margin-top:12px;">Achats (one-shot)</h4>';
                    html += '<table class="jam-table jam-table--compact">';
                    html += '<thead><tr><th>Produit</th><th>Statut</th><th>Depuis</th></tr></thead><tbody>';
                    for (var j = 0; j < d.purchases.length; j++) {
                        var pur = d.purchases[j];
                        html += '<tr>';
                        html += '<td>' + escHtml(pur.product_name) + '</td>';
                        html += '<td>' + statusBadge(pur.status) + '</td>';
                        html += '<td>' + escHtml(pur.created_at) + '</td>';
                        html += '</tr>';
                    }
                    html += '</tbody></table>';
                }
            }

            html += '</div>';

            // ─── Right column: Courses + coherence ───
            html += '<div class="jam-user-detail__col">';
            html += '<h4>Acces aux cours</h4>';

            if (d.courses.length === 0) {
                html += '<p style="color:#646970;">Aucun cours Fluent Community.</p>';
            } else {
                for (var k = 0; k < d.courses.length; k++) {
                    var course = d.courses[k];
                    var itemClass = 'jam-course-item';
                    if (course.coherence === 'missing') {
                        itemClass += ' jam-course-item--missing';
                    } else if (course.coherence === 'extra') {
                        itemClass += ' jam-course-item--extra';
                    }

                    html += '<div class="' + itemClass + '" data-course-id="' + course.id + '">';
                    html += '<div class="jam-course-item__info">';
                    html += '<span class="jam-course-item__name">' + escHtml(course.title) + '</span>';

                    if (course.enrolled) {
                        html += ' <span class="jam-badge jam-badge--green">Inscrit</span>';
                    } else {
                        html += ' <span class="jam-badge jam-badge--gray">Non inscrit</span>';
                    }

                    if (course.coherence === 'missing') {
                        html += ' <span class="jam-badge jam-badge--orange">Manquant</span>';
                    } else if (course.coherence === 'extra') {
                        html += ' <span class="jam-badge jam-badge--blue">Sans regle</span>';
                    }

                    html += '</div>';
                    html += '<div class="jam-course-item__action">';
                    if (course.enrolled) {
                        html += '<button type="button" class="button button-small jam-enroll-btn jam-enroll-btn--unenroll" data-user-id="' + userId + '" data-course-id="' + course.id + '" data-action="unenroll">Desinscrire</button>';
                    } else {
                        html += '<button type="button" class="button button-small button-primary jam-enroll-btn jam-enroll-btn--enroll" data-user-id="' + userId + '" data-course-id="' + course.id + '" data-action="enroll">Inscrire</button>';
                    }
                    html += '</div>';
                    html += '</div>';
                }
            }

            html += '</div>';
            html += '</div>';

            $inner.html(html);
        }).fail(function() {
            $inner.html('<div class="jam-notice jam-notice--error">Erreur reseau.</div>');
        });
    });

    // ─── Enroll / Unenroll button ───
    $(document).on('click', '.jam-enroll-btn', function() {
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var courseId = $btn.data('course-id');
        var action = $btn.data('action');

        $btn.prop('disabled', true).text('...');

        $.post(jamAdmin.ajaxUrl, {
            action: 'jam_toggle_enrollment',
            nonce: jamAdmin.nonce,
            user_id: userId,
            course_id: courseId,
            enrollment_action: action
        }, function(response) {
            if (response.success) {
                // Reload the user details
                var $detailRow = $('tr.jam-user-detail[data-user-id="' + userId + '"]');
                $detailRow.find('.jam-user-detail__inner').data('loaded', false);
                // Trigger reload
                $detailRow.hide();
                $('tr.jam-user-row[data-user-id="' + userId + '"] .jam-user-toggle').html('Details &#9662;').click();

                // Also update the course badges in the main row
                updateUserRowBadges(userId);
            } else {
                $btn.prop('disabled', false).text(action === 'enroll' ? 'Inscrire' : 'Desinscrire');
                alert(response.data || 'Erreur');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text(action === 'enroll' ? 'Inscrire' : 'Desinscrire');
            alert('Erreur reseau.');
        });
    });

    function updateUserRowBadges(userId) {
        // After enroll/unenroll, reload the page row via a simple approach:
        // We'll let the detail panel show the updated state; a full refresh would
        // update the badges in the main table row too. For now, this is fine.
    }

    // ─── Helpers ───
    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function statusBadge(status) {
        var map = {
            'active':    ['green', 'Actif'],
            'trialing':  ['blue', 'Essai'],
            'past_due':  ['orange', 'En retard'],
            'canceled':  ['red', 'Annule'],
            'completed': ['gray', 'Termine'],
            'unpaid':    ['red', 'Impaye'],
            'unknown':   ['gray', 'Inconnu']
        };
        var info = map[status] || ['gray', status];
        return '<span class="jam-badge jam-badge--' + info[0] + '">' + escHtml(info[1]) + '</span>';
    }

    // ─── Sync button ───
    $('#jam-sync-btn').on('click', function() {
        var $btn = $(this);
        var $report = $('#jam-sync-report');

        $btn.prop('disabled', true).text('Synchronisation en cours...');
        $report.html('<div class="jam-loading"><span class="spinner"></span> Veuillez patienter...</div>');

        $.post(jamAdmin.ajaxUrl, {
            action: 'jam_run_sync',
            nonce: jamAdmin.nonce
        }, function(response) {
            $btn.prop('disabled', false).text('Lancer la synchronisation');

            if (response.success) {
                var r = response.data;
                $report.html(
                    '<div class="jam-sync-report">' +
                    '<h3>Rapport de synchronisation</h3>' +
                    '<ul>' +
                    '<li><strong>' + (r.enrolled || 0) + '</strong> utilisateurs inscrits</li>' +
                    '<li><strong>' + (r.already_enrolled || 0) + '</strong> déjà à jour</li>' +
                    '<li><strong>' + (r.errors || 0) + '</strong> erreurs</li>' +
                    '</ul>' +
                    '</div>'
                );
            } else {
                $report.html('<div class="jam-notice jam-notice--error">' + (response.data || 'Erreur inconnue.') + '</div>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Lancer la synchronisation');
            $report.html('<div class="jam-notice jam-notice--error">Erreur réseau.</div>');
        });
    });

})(jQuery);
