<?php
/**
 * Plugin Name:       Jurible Assessments
 * Description:       Système de soumission et correction de devoirs pour Fluent Community
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Jurible
 * License:           GPL-2.0-or-later
 * Text Domain:       jurible-assessments
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constantes
define('JURIBLE_ASSESS_VERSION', '1.0.0');
define('JURIBLE_ASSESS_PATH', plugin_dir_path(__FILE__));
define('JURIBLE_ASSESS_URL', plugin_dir_url(__FILE__));

// Activation du plugin : créer les tables
register_activation_hook(__FILE__, 'jurible_assess_activate');

function jurible_assess_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table des assessments (les devoirs)
    $table_assessments = $wpdb->prefix . 'jurible_assessments';
    $sql_assessments = "CREATE TABLE $table_assessments (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        course_id BIGINT(20) UNSIGNED DEFAULT NULL,
        lesson_id BIGINT(20) UNSIGNED DEFAULT NULL,
        max_score DECIMAL(5,2) DEFAULT 20.00,
        due_date DATETIME DEFAULT NULL,
        subject_pdf_url VARCHAR(500) DEFAULT NULL,
        correction_pdf_url VARCHAR(500) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY course_id (course_id),
        KEY lesson_id (lesson_id)
    ) $charset_collate;";

    // Table des soumissions (les copies des étudiants)
    $table_submissions = $wpdb->prefix . 'jurible_assessment_submissions';
    $sql_submissions = "CREATE TABLE $table_submissions (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        assessment_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        file_url VARCHAR(500) DEFAULT NULL,
        file_name VARCHAR(255) DEFAULT NULL,
        status ENUM('submitted', 'in_review', 'graded') DEFAULT 'submitted',
        assigned_to BIGINT(20) UNSIGNED DEFAULT NULL,
        score DECIMAL(5,2) DEFAULT NULL,
        feedback TEXT,
        video_url VARCHAR(500) DEFAULT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        graded_at DATETIME DEFAULT NULL,
        graded_by BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_assessment (user_id, assessment_id),
        KEY assessment_id (assessment_id),
        KEY user_id (user_id),
        KEY status (status),
        KEY assigned_to (assigned_to)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_assessments);
    dbDelta($sql_submissions);

    // Sauvegarder la version
    update_option('jurible_assess_version', JURIBLE_ASSESS_VERSION);

    // Options par défaut pour les notifications
    $default_options = [
        'notify_prof_new_submission' => true,
        'notify_student_graded' => true,
        'notify_student_reminder' => true,
        'notify_student_overdue' => true,
        'notify_prof_digest' => true,
        'digest_day' => 'monday',
        'reminder_days_before' => 2,
        'overdue_days_after' => 1,
        'from_name' => get_bloginfo('name'),
        'from_email' => get_option('admin_email'),
    ];
    
    if (!get_option('jurible_assess_options')) {
        update_option('jurible_assess_options', $default_options);
    }

    // Créer le dossier d'uploads pour les soumissions
    $upload_dir = wp_upload_dir();
    $assess_dir = $upload_dir['basedir'] . '/jurible-assessments';
    if (!file_exists($assess_dir)) {
        wp_mkdir_p($assess_dir);
        // Fichier .htaccess pour protéger les fichiers
        file_put_contents($assess_dir . '/.htaccess', 'Options -Indexes');
    }
}

// Désactivation : ne pas supprimer les tables
register_deactivation_hook(__FILE__, 'jurible_assess_deactivate');

function jurible_assess_deactivate() {
    // Nettoyer les crons
    wp_clear_scheduled_hook('jurible_assess_daily_cron');
    wp_clear_scheduled_hook('jurible_assess_weekly_digest');
}

// Charger les fichiers
require_once JURIBLE_ASSESS_PATH . 'includes/class-admin.php';
require_once JURIBLE_ASSESS_PATH . 'includes/class-api.php';
require_once JURIBLE_ASSESS_PATH . 'includes/class-notifications.php';

// Initialiser l'admin
add_action('admin_menu', ['Jurible_Assess_Admin', 'add_menu']);
add_action('admin_enqueue_scripts', ['Jurible_Assess_Admin', 'enqueue_scripts']);

// Initialiser l'API REST
add_action('rest_api_init', ['Jurible_Assess_API', 'register_routes']);

// Initialiser les notifications
add_action('init', ['Jurible_Assess_Notifications', 'init']);

// Ajouter le compteur de soumissions en attente dans le menu admin
add_action('admin_menu', 'jurible_assess_add_pending_count');

function jurible_assess_add_pending_count() {
    global $wpdb, $menu;
    
    $table = $wpdb->prefix . 'jurible_assessment_submissions';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'submitted'");
    
    if ($count > 0) {
        foreach ($menu as $key => $item) {
            if (isset($item[2]) && $item[2] === 'jurible-assessments') {
                $menu[$key][0] .= ' <span class="awaiting-mod">' . $count . '</span>';
                break;
            }
        }
    }
}

// Cron quotidien pour les rappels
add_action('wp_loaded', 'jurible_assess_schedule_crons');

function jurible_assess_schedule_crons() {
    if (!wp_next_scheduled('jurible_assess_daily_cron')) {
        wp_schedule_event(time(), 'daily', 'jurible_assess_daily_cron');
    }
    if (!wp_next_scheduled('jurible_assess_weekly_digest')) {
        wp_schedule_event(time(), 'weekly', 'jurible_assess_weekly_digest');
    }
}

add_action('jurible_assess_daily_cron', ['Jurible_Assess_Notifications', 'send_reminders']);
add_action('jurible_assess_weekly_digest', ['Jurible_Assess_Notifications', 'send_digest']);