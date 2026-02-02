<?php
/**
 * Dashboard de statistiques
 * Page d'accueil de l'admin avec les stats d'utilisation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Afficher le dashboard de statistiques
 */
function aga_afficher_dashboard_stats() {
    // Récupérer les statistiques
    $stats = aga_calculer_statistiques();
    
    ?>
    <div class="wrap aga-dashboard">
        <h1>📊 Statistiques d'Utilisation</h1>
        
        <!-- Statistiques globales -->
        <div class="aga-stats-grid">
            <div class="aga-stat-card">
                <div class="aga-stat-icon">📄</div>
                <div class="aga-stat-content">
                    <div class="aga-stat-label">Fiches d'Arrêt</div>
                    <div class="aga-stat-value"><?php echo number_format($stats['fiches_total'], 0, ',', ' '); ?></div>
                </div>
            </div>
            
            <div class="aga-stat-card">
                <div class="aga-stat-icon">✍️</div>
                <div class="aga-stat-content">
                    <div class="aga-stat-label">Dissertations</div>
                    <div class="aga-stat-value"><?php echo number_format($stats['dissertations_total'], 0, ',', ' '); ?></div>
                </div>
            </div>
            
            <div class="aga-stat-card">
                <div class="aga-stat-icon">💬</div>
                <div class="aga-stat-content">
                    <div class="aga-stat-label">Commentaires</div>
                    <div class="aga-stat-value"><?php echo number_format($stats['commentaires_total'], 0, ',', ' '); ?></div>
                </div>
            </div>
            
            <div class="aga-stat-card">
                <div class="aga-stat-icon">📋</div>
                <div class="aga-stat-content">
                    <div class="aga-stat-label">Cas Pratiques</div>
                    <div class="aga-stat-value"><?php echo number_format($stats['cas_pratiques_total'], 0, ',', ' '); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Total général -->
        <div class="aga-stat-card-large">
            <h2>🎓 Total Générations</h2>
            <div class="aga-stat-value-large"><?php echo number_format($stats['total_general'], 0, ',', ' '); ?></div>
        </div>
        
        <!-- Évolution temporelle -->
        <div class="aga-section">
            <h2>📈 Évolution</h2>
            <div class="aga-stats-grid">
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Aujourd'hui</div>
                    <div class="aga-stat-value"><?php echo $stats['aujourd_hui']; ?></div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Cette semaine</div>
                    <div class="aga-stat-value"><?php echo $stats['cette_semaine']; ?></div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Ce mois</div>
                    <div class="aga-stat-value"><?php echo $stats['ce_mois']; ?></div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Mois dernier</div>
                    <div class="aga-stat-value"><?php echo $stats['mois_dernier']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Top matières -->
        <?php if (!empty($stats['top_matieres'])): ?>
        <div class="aga-section">
            <h2>📚 Top 5 Matières</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th style="width: 100px; text-align: center;">Générations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_matieres'] as $matiere => $count): ?>
                    <tr>
                        <td><strong><?php echo esc_html($matiere); ?></strong></td>
                        <td style="text-align: center;"><?php echo $count; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Répartition dissertations -->
        <div class="aga-section">
            <h2>📊 Répartition Dissertations</h2>
            <div class="aga-stats-grid">
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Plans détaillés (1 crédit)</div>
                    <div class="aga-stat-value"><?php echo $stats['plans_detailles']; ?></div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Dissertations complètes (3 crédits)</div>
                    <div class="aga-stat-value"><?php echo $stats['dissertations_completes']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Répartition Utilisateurs -->
        <div class="aga-section">
            <h2>👥 Répartition Utilisateurs</h2>
            <div class="aga-stats-grid">
                <div class="aga-stat-card">
                    <div class="aga-stat-icon">👤</div>
                    <div class="aga-stat-content">
                        <div class="aga-stat-label">Utilisateurs Gratuits</div>
                        <div class="aga-stat-value"><?php echo number_format($stats['users_gratuits'], 0, ',', ' '); ?></div>
                    </div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-icon">⭐</div>
                    <div class="aga-stat-content">
                        <div class="aga-stat-label">Utilisateurs Premium</div>
                        <div class="aga-stat-value"><?php echo number_format($stats['users_premium'], 0, ',', ' '); ?></div>
                    </div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-icon">📊</div>
                    <div class="aga-stat-content">
                        <div class="aga-stat-label">Générations (Gratuits)</div>
                        <div class="aga-stat-value"><?php echo number_format($stats['generations_gratuits'], 0, ',', ' '); ?></div>
                    </div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-icon">🚀</div>
                    <div class="aga-stat-content">
                        <div class="aga-stat-label">Générations (Premium)</div>
                        <div class="aga-stat-value"><?php echo number_format($stats['generations_premium'], 0, ',', ' '); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statut catalogue -->
        <div class="aga-section">
            <h2>✅ Statut Catalogue (Fiches d'Arrêt)</h2>
            <div class="aga-stats-grid">
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Brouillon</div>
                    <div class="aga-stat-value"><?php echo $stats['catalogue_brouillon']; ?></div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Publiable</div>
                    <div class="aga-stat-value"><?php echo $stats['catalogue_publiable']; ?></div>
                </div>
                
                <div class="aga-stat-card">
                    <div class="aga-stat-label">Rejeté</div>
                    <div class="aga-stat-value"><?php echo $stats['catalogue_rejete']; ?></div>
                </div>
            </div>
        </div>
        
    </div>
    
    <style>
        .aga-dashboard {
            max-width: 1400px;
        }
        
        .aga-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .aga-stat-card {
            background: white;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .aga-stat-card-large {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .aga-stat-card-large h2 {
            margin: 0 0 10px 0;
            color: white;
            font-size: 24px;
        }
        
        .aga-stat-icon {
            font-size: 36px;
            line-height: 1;
        }
        
        .aga-stat-content {
            flex: 1;
        }
        
        .aga-stat-label {
            font-size: 13px;
            color: #646970;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .aga-stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1d2327;
        }
        
        .aga-stat-value-large {
            font-size: 48px;
            font-weight: 700;
        }
        
        .aga-section {
            margin: 30px 0;
        }
        
        .aga-section h2 {
            margin-bottom: 15px;
            font-size: 18px;
        }
    </style>
    <?php
}

/**
 * Calculer toutes les statistiques
 */
function aga_calculer_statistiques() {
    global $wpdb;
    
    // Compteurs totaux
    $fiches = wp_count_posts('fiche_arret');
    $dissertations = wp_count_posts('dissertation');
    $commentaires = wp_count_posts('commentaire_arret');
    $cas_pratiques = wp_count_posts('cas_pratique');
    
    $stats = array(
        'fiches_total' => $fiches->publish,
        'dissertations_total' => $dissertations->publish,
        'commentaires_total' => $commentaires->publish,
        'cas_pratiques_total' => $cas_pratiques->publish,
        'total_general' => $fiches->publish + $dissertations->publish + $commentaires->publish + $cas_pratiques->publish,
    );
    
    // Évolution temporelle
    $aujourd_hui = date('Y-m-d');
    $debut_semaine = date('Y-m-d', strtotime('monday this week'));
    $debut_mois = date('Y-m-01');
    $debut_mois_dernier = date('Y-m-01', strtotime('first day of last month'));
    $fin_mois_dernier = date('Y-m-t', strtotime('last day of last month'));
    
    $post_types = array('fiche_arret', 'dissertation', 'commentaire_arret', 'cas_pratique');
    $post_types_str = "'" . implode("','", $post_types) . "'";
    
    // Aujourd'hui
    $stats['aujourd_hui'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_type IN ($post_types_str) 
        AND post_status = 'publish' 
        AND DATE(post_date) = %s",
        $aujourd_hui
    ));
    
    // Cette semaine
    $stats['cette_semaine'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_type IN ($post_types_str) 
        AND post_status = 'publish' 
        AND DATE(post_date) >= %s",
        $debut_semaine
    ));
    
    // Ce mois
    $stats['ce_mois'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_type IN ($post_types_str) 
        AND post_status = 'publish' 
        AND DATE(post_date) >= %s",
        $debut_mois
    ));
    
    // Mois dernier
    $stats['mois_dernier'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_type IN ($post_types_str) 
        AND post_status = 'publish' 
        AND DATE(post_date) BETWEEN %s AND %s",
        $debut_mois_dernier,
        $fin_mois_dernier
    ));
    
    // Top matières (tous CPT confondus)
    $matieres = $wpdb->get_results(
        "SELECT pm.meta_value as matiere, COUNT(*) as count 
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_aga_matiere' 
        AND p.post_status = 'publish'
        AND p.post_type IN ($post_types_str)
        GROUP BY pm.meta_value 
        ORDER BY count DESC 
        LIMIT 5"
    );
    
    $stats['top_matieres'] = array();
    foreach ($matieres as $matiere) {
        $stats['top_matieres'][$matiere->matiere] = $matiere->count;
    }
    
    // Répartition dissertations
    $stats['plans_detailles'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_aga_type_generation' 
        AND pm.meta_value = 'plan_detaille'
        AND p.post_status = 'publish'"
    );
    
    $stats['dissertations_completes'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_aga_type_generation' 
        AND pm.meta_value = 'dissertation_complete'
        AND p.post_status = 'publish'"
    );
    
    // Statut catalogue
    $stats['catalogue_brouillon'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_aga_statut_catalogue' 
        AND pm.meta_value = 'brouillon'
        AND p.post_status = 'publish'"
    );
    
    $stats['catalogue_publiable'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_aga_statut_catalogue' 
        AND pm.meta_value = 'publiable'
        AND p.post_status = 'publish'"
    );
    
    $stats['catalogue_rejete'] = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_aga_statut_catalogue' 
        AND pm.meta_value = 'rejete'
        AND p.post_status = 'publish'"
    );
    
    // Répartition utilisateurs Gratuits / Premium
    $premium_users = get_users(array(
            'role' => 'gfa_premium',
            'fields' => 'ID'
    ));
    
   $stats['users_premium'] = count($premium_users);
    
    // Compter uniquement les utilisateurs qui ont réellement généré quelque chose
    $users_actifs = $wpdb->get_var(
        "SELECT COUNT(DISTINCT post_author) 
        FROM {$wpdb->posts}
        WHERE post_type IN ($post_types_str)
        AND post_status = 'publish'"
    );
    
    $stats['users_gratuits'] = $users_actifs - $stats['users_premium'];
    
    // Générations premium
    if (!empty($premium_users)) {
        $premium_ids = implode(',', array_map('intval', $premium_users));
        
        $stats['generations_premium'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type IN ($post_types_str)
            AND post_status = 'publish'
            AND post_author IN ($premium_ids)"
        );
    } else {
        $stats['generations_premium'] = 0;
    }
    
    // Générations gratuits
    $stats['generations_gratuits'] = $stats['total_general'] - $stats['generations_premium'];
    
    return $stats;
}