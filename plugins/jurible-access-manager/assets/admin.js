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

    // ─── Credit price map rows ───
    $('#jam-add-price-row').on('click', function() {
        var row = '<div class="jam-price-map-row" style="display:flex;gap:8px;margin-bottom:6px;align-items:center;">' +
            '<input type="text" name="credit_price_ids[]" value="" placeholder="Price ID SureCart" style="flex:1;">' +
            '<input type="number" name="credit_price_amounts[]" value="" placeholder="Crédits" style="width:100px;" min="0">' +
            '<button type="button" class="button jam-remove-price-row" title="Supprimer">&times;</button>' +
            '</div>';
        $('#jam-price-map-rows').append(row);
    });

    $(document).on('click', '.jam-remove-price-row', function() {
        $(this).closest('.jam-price-map-row').remove();
    });

    // ─── Accordion toggle ───
    $(document).on('click', '.jam-accordion__toggle', function() {
        var $accordion = $(this).closest('.jam-accordion');
        var $content = $accordion.find('.jam-accordion__content');

        $accordion.toggleClass('is-open');
        $content.slideToggle(200);
    });

    // ─── Rule courses toggle ───
    $(document).on('click', '.jam-rule-toggle', function() {
        var ruleId = $(this).data('rule-id');
        var $courses = $('#jam-rule-courses-' + ruleId);
        var $arrow = $(this).find('.jam-rule-toggle__arrow');

        $courses.slideToggle(150);
        $arrow.toggleClass('is-open');
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
        var dryRun = $('#jam-sync-dry-run').is(':checked');

        var label = dryRun ? 'Simulation en cours...' : 'Synchronisation en cours...';
        $btn.prop('disabled', true).text(label);
        $report.html('<div class="jam-loading"><span class="spinner"></span> Veuillez patienter (peut prendre 1-2 minutes)...</div>');

        $.ajax({
            url: jamAdmin.ajaxUrl,
            type: 'POST',
            timeout: 120000,
            data: {
                action: 'jam_run_sync',
                nonce: jamAdmin.nonce,
                dry_run: dryRun ? 1 : 0
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Lancer la synchronisation');

                if (response.success) {
                    $report.html(buildSyncReport(response.data));
                } else {
                    $report.html('<div class="jam-notice jam-notice--error">' + escHtml(response.data || 'Erreur inconnue.') + '</div>');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Lancer la synchronisation');
                $report.html('<div class="jam-notice jam-notice--error">Erreur reseau ou timeout.</div>');
            }
        });
    });

    function buildSyncReport(r) {
        var isDry = r.dry_run;
        var title = isDry ? 'Rapport de simulation' : 'Rapport de synchronisation';
        var usersEnrolled = r.users_enrolled || 0;
        var totalEnrolled = r.enrolled || 0;
        var totalAlready  = r.already_enrolled || 0;
        var totalErrors   = r.errors || 0;

        var html = '<div class="jam-sync-report" style="margin-top:16px;">';

        if (isDry) {
            html += '<div class="jam-notice jam-notice--info" style="margin-bottom:12px;">Mode simulation — aucune inscription effectuee.</div>';
        }

        html += '<h3>' + escHtml(title) + '</h3>';
        html += '<ul>';
        html += '<li><strong>' + usersEnrolled + '</strong> utilisateur' + (usersEnrolled > 1 ? 's' : '') + ' concerne' + (usersEnrolled > 1 ? 's' : '') + '</li>';
        html += '<li><strong>' + totalEnrolled + '</strong> inscription' + (totalEnrolled > 1 ? 's' : '') + ' aux cours ' + (isDry ? 'a effectuer' : 'effectuees') + '</li>';
        html += '<li><strong>' + totalAlready + '</strong> deja inscrit' + (totalAlready > 1 ? 's' : '') + ' (aucun changement)</li>';
        html += '<li><strong>' + totalErrors + '</strong> client' + (totalErrors > 1 ? 's' : '') + ' SureCart sans compte WP</li>';
        html += '<li>Duree : <strong>' + (r.duration || 0) + 's</strong></li>';
        html += '</ul>';

        // Per-product table
        if (r.products && r.products.length > 0) {
            html += '<h4>Detail par produit</h4>';
            html += '<table class="jam-table jam-table--compact">';
            html += '<thead><tr><th>Produit</th><th>Users</th><th>' + (isDry ? 'Inscriptions a faire' : 'Inscriptions') + '</th><th>Deja OK</th><th>Sans compte WP</th></tr></thead>';
            html += '<tbody>';
            for (var i = 0; i < r.products.length; i++) {
                var p = r.products[i];
                html += '<tr>';
                html += '<td>' + escHtml(p.name) + '</td>';
                html += '<td><strong>' + (p.users || 0) + '</strong></td>';
                html += '<td>' + (p.enrolled || 0) + '</td>';
                html += '<td>' + (p.already || 0) + '</td>';
                html += '<td>' + (p.errors || 0) + '</td>';
                html += '</tr>';
            }
            html += '</tbody></table>';
        }

        // Success emails
        if (r.success_emails && r.success_emails.length > 0) {
            html += '<h4>Utilisateurs ' + (isDry ? 'a synchroniser' : 'synchronises') + ' (' + r.success_emails.length + ')</h4>';
            html += '<div style="max-height:200px;overflow-y:auto;background:#f0fdf4;padding:8px;border-radius:4px;font-size:12px;border:1px solid #bbf7d0;">';
            for (var j = 0; j < r.success_emails.length; j++) {
                html += escHtml(r.success_emails[j]) + '<br>';
            }
            html += '</div>';
        }

        // Error emails
        if (r.error_emails && r.error_emails.length > 0) {
            html += '<h4>Clients SureCart sans compte WP (' + r.error_emails.length + ')</h4>';
            html += '<div style="max-height:200px;overflow-y:auto;background:#f9f9f9;padding:8px;border-radius:4px;font-size:12px;">';
            for (var k = 0; k < r.error_emails.length; k++) {
                html += escHtml(r.error_emails[k]) + '<br>';
            }
            html += '</div>';
        }

        if (r.message) {
            html += '<p style="color:#646970;">' + escHtml(r.message) + '</p>';
        }

        html += '</div>';
        return html;
    }

})(jQuery);
