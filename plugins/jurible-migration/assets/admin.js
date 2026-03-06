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
                actionHtml = '<a href="' + migratedStatus[post.ID].edit_url + '" target="_blank" class="btn-view">Voir l\'article</a>';
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
                        edit_url: response.data.edit_url
                    };

                    // Update row
                    $row.find('td:eq(3)').html('<span class="status-migrated">✅ Migré</span>');
                    $row.find('td:eq(4)').html('<a href="' + response.data.edit_url + '" target="_blank" class="btn-view">Voir l\'article</a>');

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
