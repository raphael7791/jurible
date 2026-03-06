<?php
/**
 * Page d'administration pour la migration
 */

defined('ABSPATH') || exit;

class Jurible_Migration_Admin_Page {

    public static function render() {
        ?>
        <div class="wrap jurible-migration-wrap">
            <h1>Migration Aideauxtd → Jurible</h1>

            <div class="jurible-migration-stats">
                <div class="stat-box">
                    <span class="stat-number" id="total-count">-</span>
                    <span class="stat-label">Articles source</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number" id="migrated-count">-</span>
                    <span class="stat-label">Migrés</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number" id="pending-count">-</span>
                    <span class="stat-label">En attente</span>
                </div>
            </div>

            <div class="jurible-migration-filters">
                <label>
                    <input type="checkbox" id="filter-pending" checked>
                    Afficher uniquement les articles non migrés
                </label>
                <button type="button" class="button" id="refresh-list">
                    Actualiser la liste
                </button>
            </div>

            <div class="jurible-migration-table-wrap">
                <table class="wp-list-table widefat fixed striped" id="migration-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Titre</th>
                            <th style="width: 120px;">Date</th>
                            <th style="width: 100px;">Statut</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="posts-list">
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">
                                Chargement des articles...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="jurible-migration-log" id="migration-log" style="display: none;">
                <h3>Journal de migration</h3>
                <div id="log-content"></div>
            </div>
        </div>
        <?php
    }
}
