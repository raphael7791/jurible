(function($) {
    'use strict';

    let posts = [];
    let migratedStatus = {};

    function init() {
        loadPosts();
        bindEvents();
    }

    function bindEvents() {
        $('#refresh-list').on('click', loadPosts);
        $('#filter-pending').on('change', renderTable);
        $(document).on('click', '.btn-migrate', handleMigrate);
        $(document).on('click', '.btn-undo', handleUndo);
        $(document).on('click', '.btn-comments', handleImportComments);
    }

    function loadPosts() {
        $('#posts-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px;">Chargement des articles...</td></tr>');

        $.ajax({
            url: juribleMigration.ajaxUrl,
            method: 'POST',
            data: {
                action: 'jurible_get_source_posts',
                nonce: juribleMigration.nonce
            },
            success: function(response) {
                if (response.success) {
                    posts = response.data.posts || [];
                    migratedStatus = response.data.migrated || {};
                    updateStats();
                    renderTable();
                } else {
                    showError('Erreur: ' + response.data);
                }
            },
            error: function() {
                showError('Erreur de connexion au serveur');
            }
        });
    }

    function updateStats() {
        const total = posts.length;
        const migrated = Object.keys(migratedStatus).length;
        const pending = total - migrated;

        $('#total-count').text(total);
        $('#migrated-count').text(migrated);
        $('#pending-count').text(pending);
    }

    function renderTable() {
        const filterPending = $('#filter-pending').is(':checked');
        const $tbody = $('#posts-list');
        $tbody.empty();

        let filteredPosts = posts;
        if (filterPending) {
            filteredPosts = posts.filter(function(post) {
                return !migratedStatus[post.ID];
            });
        }

        if (filteredPosts.length === 0) {
            $tbody.html('<tr><td colspan="5" style="text-align: center; padding: 20px;">Aucun article à afficher</td></tr>');
            return;
        }

        filteredPosts.forEach(function(post) {
            const isMigrated = migratedStatus[post.ID];
            const statusClass = isMigrated ? 'status-migrated' : 'status-pending';
            const statusText = isMigrated ? '✅ Migré' : '⏳ En attente';

            let actionHtml;
            if (isMigrated) {
                actionHtml = '<a href="' + migratedStatus[post.ID].edit_url + '" target="_blank" class="btn-view">Back</a> ';
                if (migratedStatus[post.ID].view_url) {
                    actionHtml += '<a href="' + migratedStatus[post.ID].view_url + '" target="_blank" class="btn-view">Front</a> ';
                }
                actionHtml += '<button type="button" class="button btn-comments" data-source="' + post.ID + '" data-dest="' + migratedStatus[post.ID].new_id + '">💬</button> ';
                actionHtml += '<button type="button" class="button btn-undo" data-id="' + post.ID + '">Annuler</button>';
            } else {
                actionHtml = '<button type="button" class="button btn-migrate" data-id="' + post.ID + '">Migrer</button>';
            }

            const row = '<tr data-id="' + post.ID + '">' +
                '<td>' + post.ID + '</td>' +
                '<td>' + escapeHtml(post.post_title) + '</td>' +
                '<td>' + formatDate(post.post_date) + '</td>' +
                '<td class="' + statusClass + '">' + statusText + '</td>' +
                '<td>' + actionHtml + '</td>' +
                '</tr>';

            $tbody.append(row);
        });
    }

    function handleMigrate() {
        const $btn = $(this);
        const postId = $btn.data('id');
        const $row = $btn.closest('tr');

        // Disable button and show loading
        $btn.prop('disabled', true).html('<span class="spinner is-active spinner-inline"></span> Migration...');
        $row.find('td:eq(3)').html('<span class="status-migrating">⏳ Migration en cours...</span>');

        // Show log
        $('#migration-log').show();
        addLog('Démarrage de la migration de l\'article #' + postId + '...', 'info');

        $.ajax({
            url: juribleMigration.ajaxUrl,
            method: 'POST',
            data: {
                action: 'jurible_migrate_post',
                nonce: juribleMigration.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    // Update status
                    migratedStatus[postId] = {
                        new_id: response.data.new_post_id,
                        edit_url: response.data.edit_url,
                        view_url: response.data.view_url
                    };

                    // Update row
                    $row.find('td:eq(3)').html('<span class="status-migrated">✅ Migré</span>');
                    var actionHtml = '<a href="' + response.data.edit_url + '" target="_blank" class="btn-view">Back</a> ';
                    actionHtml += '<a href="' + response.data.view_url + '" target="_blank" class="btn-view">Front</a> ';
                    actionHtml += '<button type="button" class="button btn-undo" data-id="' + postId + '">Annuler</button>';
                    $row.find('td:eq(4)').html(actionHtml);

                    // Update stats
                    updateStats();

                    addLog('✅ Article #' + postId + ' migré avec succès → Nouvel ID: #' + response.data.new_post_id, 'success');
                } else {
                    $btn.prop('disabled', false).text('Migrer');
                    $row.find('td:eq(3)').html('<span class="status-error">❌ Erreur</span>');
                    addLog('❌ Erreur pour l\'article #' + postId + ': ' + response.data, 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Migrer');
                $row.find('td:eq(3)').html('<span class="status-error">❌ Erreur</span>');
                addLog('❌ Erreur de connexion pour l\'article #' + postId, 'error');
            }
        });
    }

    function handleUndo() {
        const $btn = $(this);
        const postId = $btn.data('id');
        const $row = $btn.closest('tr');

        if (!confirm('Supprimer cet article migré et ses images ? Cette action est irréversible.')) {
            return;
        }

        $btn.prop('disabled', true).text('Suppression...');

        $.ajax({
            url: juribleMigration.ajaxUrl,
            method: 'POST',
            data: {
                action: 'jurible_undo_migration',
                nonce: juribleMigration.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    delete migratedStatus[postId];
                    updateStats();

                    // Update row
                    $row.find('td:eq(3)').html('<span class="status-pending">⏳ En attente</span>');
                    $row.find('td:eq(4)').html('<button type="button" class="button btn-migrate" data-id="' + postId + '">Migrer</button>');

                    addLog('🗑️ Migration annulée pour l\'article #' + postId, 'info');
                } else {
                    $btn.prop('disabled', false).text('Annuler');
                    addLog('❌ Erreur: ' + response.data, 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Annuler');
                addLog('❌ Erreur de connexion', 'error');
            }
        });
    }

    function handleImportComments() {
        const $btn = $(this);
        const sourceId = $btn.data('source');
        const destId = $btn.data('dest');

        $btn.prop('disabled', true).text('...');
        $('#migration-log').show();
        addLog('💬 Import des commentaires pour #' + sourceId + '...', 'info');

        $.ajax({
            url: juribleMigration.ajaxUrl,
            method: 'POST',
            data: {
                action: 'jurible_import_comments',
                nonce: juribleMigration.nonce,
                source_id: sourceId,
                dest_id: destId
            },
            success: function(response) {
                $btn.prop('disabled', false).text('💬');
                if (response.success) {
                    var msg = response.data.count > 0
                        ? '✅ ' + response.data.count + ' commentaire(s) importé(s) pour #' + sourceId
                        : '⚪ Aucun commentaire à importer pour #' + sourceId;
                    addLog(msg, response.data.count > 0 ? 'success' : 'info');
                    // Afficher les erreurs de debug si présentes
                    if (response.data.debug) {
                        addLog('🔧 ' + response.data.debug, 'error');
                    }
                } else {
                    addLog('❌ Erreur: ' + response.data, 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('💬');
                addLog('❌ Erreur de connexion', 'error');
            }
        });
    }

    function addLog(message, type) {
        const $log = $('#log-content');
        const time = new Date().toLocaleTimeString('fr-FR');
        $log.prepend('<div class="log-entry log-' + type + '">[' + time + '] ' + message + '</div>');
    }

    function showError(message) {
        $('#posts-list').html('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #d63638;">' + message + '</td></tr>');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR');
    }

    $(document).ready(init);

})(jQuery);
