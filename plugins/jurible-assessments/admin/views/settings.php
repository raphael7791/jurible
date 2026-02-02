<?php
if (!defined('ABSPATH')) exit;

// Récupérer les options
$options = get_option('jurible_assess_settings', [
    'email_new_submission' => 1,
    'email_graded' => 1,
    'email_digest' => 1,
    'email_reminder' => 1,
    'email_overdue' => 1,
    'digest_day' => 'monday',
    'reminder_days' => 2,
    'overdue_days' => 1,
    'sender_name' => get_bloginfo('name'),
    'sender_email' => get_option('admin_email'),
]);

// Sauvegarder si formulaire soumis
if (isset($_POST['jurible_assess_save_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'jurible_assess_settings')) {
    $options = [
        'email_new_submission' => isset($_POST['email_new_submission']) ? 1 : 0,
        'email_graded' => isset($_POST['email_graded']) ? 1 : 0,
        'email_digest' => isset($_POST['email_digest']) ? 1 : 0,
        'email_reminder' => isset($_POST['email_reminder']) ? 1 : 0,
        'email_overdue' => isset($_POST['email_overdue']) ? 1 : 0,
        'digest_day' => sanitize_text_field($_POST['digest_day']),
        'reminder_days' => intval($_POST['reminder_days']),
        'overdue_days' => intval($_POST['overdue_days']),
        'sender_name' => sanitize_text_field($_POST['sender_name']),
        'sender_email' => sanitize_email($_POST['sender_email']),
    ];
    
    update_option('jurible_assess_settings', $options);
    add_settings_error('jurible_assess', 'settings_saved', 'Paramètres enregistrés !', 'success');
}

$days_of_week = [
    'monday' => 'Lundi',
    'tuesday' => 'Mardi',
    'wednesday' => 'Mercredi',
    'thursday' => 'Jeudi',
    'friday' => 'Vendredi',
    'saturday' => 'Samedi',
    'sunday' => 'Dimanche',
];
?>

<div class="wrap jurible-assess-wrap">
    
    <h1>⚙️ Paramètres des notifications</h1>
    
    <?php settings_errors('jurible_assess'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('jurible_assess_settings'); ?>
        
        <div class="assess-card">
            
            <!-- Emails au professeur -->
            <div class="assess-settings-section">
                <h3>📧 Emails au professeur</h3>
                
                <div class="assess-toggle-row">
                    <div class="assess-toggle-label">
                        <strong>Nouvelle soumission</strong>
                        <span>Recevoir un email quand un étudiant soumet un devoir</span>
                    </div>
                    <label class="assess-toggle">
                        <input type="checkbox" name="email_new_submission" value="1" <?php checked($options['email_new_submission'], 1); ?>>
                        <span class="assess-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="assess-toggle-row">
                    <div class="assess-toggle-label">
                        <strong>Digest hebdomadaire</strong>
                        <span>Recevoir un résumé des soumissions en attente</span>
                    </div>
                    <label class="assess-toggle">
                        <input type="checkbox" name="email_digest" value="1" <?php checked($options['email_digest'], 1); ?>>
                        <span class="assess-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="assess-form-group" style="margin-top: 15px; padding-left: 20px;">
                    <label for="digest_day">Jour du digest</label>
                    <select name="digest_day" id="digest_day" style="width: 200px;">
                        <?php foreach ($days_of_week as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php selected($options['digest_day'], $value); ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Emails aux étudiants -->
            <div class="assess-settings-section">
                <h3>📧 Emails aux étudiants</h3>
                
                <div class="assess-toggle-row">
                    <div class="assess-toggle-label">
                        <strong>Devoir corrigé</strong>
                        <span>Notifier l'étudiant quand sa copie est corrigée</span>
                    </div>
                    <label class="assess-toggle">
                        <input type="checkbox" name="email_graded" value="1" <?php checked($options['email_graded'], 1); ?>>
                        <span class="assess-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="assess-toggle-row">
                    <div class="assess-toggle-label">
                        <strong>Rappel avant deadline</strong>
                        <span>Rappeler aux étudiants de rendre leur devoir</span>
                    </div>
                    <label class="assess-toggle">
                        <input type="checkbox" name="email_reminder" value="1" <?php checked($options['email_reminder'], 1); ?>>
                        <span class="assess-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="assess-form-group" style="margin-top: 15px; padding-left: 20px;">
                    <label for="reminder_days">Jours avant la deadline</label>
                    <select name="reminder_days" id="reminder_days" style="width: 200px;">
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($options['reminder_days'], $i); ?>>
                                <?php echo $i; ?> jour<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="assess-toggle-row">
                    <div class="assess-toggle-label">
                        <strong>Deadline dépassée</strong>
                        <span>Alerter les étudiants qui n'ont pas rendu après la deadline</span>
                    </div>
                    <label class="assess-toggle">
                        <input type="checkbox" name="email_overdue" value="1" <?php checked($options['email_overdue'], 1); ?>>
                        <span class="assess-toggle-slider"></span>
                    </label>
                </div>
                
                <div class="assess-form-group" style="margin-top: 15px; padding-left: 20px;">
                    <label for="overdue_days">Jours après la deadline</label>
                    <select name="overdue_days" id="overdue_days" style="width: 200px;">
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($options['overdue_days'], $i); ?>>
                                <?php echo $i; ?> jour<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <!-- Expéditeur -->
            <div class="assess-settings-section">
                <h3>📨 Expéditeur des emails</h3>
                
                <div class="assess-form-row">
                    <div class="assess-form-group">
                        <label for="sender_name">Nom de l'expéditeur</label>
                        <input type="text" name="sender_name" id="sender_name" 
                               value="<?php echo esc_attr($options['sender_name']); ?>">
                    </div>
                    
                    <div class="assess-form-group">
                        <label for="sender_email">Email de l'expéditeur</label>
                        <input type="email" name="sender_email" id="sender_email" 
                               value="<?php echo esc_attr($options['sender_email']); ?>">
                        <p class="description">Doit être configuré dans FluentSMTP pour fonctionner correctement.</p>
                    </div>
                </div>
            </div>
            
            <div class="assess-form-actions">
                <button type="submit" name="jurible_assess_save_settings" class="assess-btn assess-btn-primary">
                    💾 Enregistrer les paramètres
                </button>
            </div>
            
        </div>
        
    </form>
    
</div>