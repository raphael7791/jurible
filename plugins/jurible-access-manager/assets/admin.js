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
