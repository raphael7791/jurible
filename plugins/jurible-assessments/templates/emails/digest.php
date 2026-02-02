<?php
/**
 * Template email : Digest hebdomadaire (envoyé au prof)
 * 
 * Variables disponibles :
 * - $pending_count : Nombre de soumissions en attente
 * - $in_review_count : Nombre en cours de correction
 * - $graded_this_week : Nombre corrigées cette semaine
 * - $oldest_submissions : Array des soumissions les plus anciennes
 * - $inbox_url : URL vers l'inbox
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F3F4F6;">
    
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F3F4F6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(94.73deg, #B0001D 0%, #7C3AED 100%); padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 24px; font-weight: 600;">
                                📊 Résumé hebdomadaire
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            
                            <p style="margin: 0 0 25px 0; font-size: 16px; color: #1A1A1A;">
                                Bonjour,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; font-size: 16px; color: #1A1A1A; line-height: 1.6;">
                                Voici votre résumé des corrections de la semaine.
                            </p>
                            
                            <!-- Stats Grid -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td width="33%" style="padding: 10px;">
                                        <div style="background-color: #FEF3C7; border-radius: 12px; padding: 20px; text-align: center;">
                                            <p style="margin: 0; font-size: 36px; font-weight: 700; color: #B45309;">
                                                <?php echo intval($pending_count); ?>
                                            </p>
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #B45309; text-transform: uppercase; letter-spacing: 0.5px;">
                                                En attente
                                            </p>
                                        </div>
                                    </td>
                                    <td width="33%" style="padding: 10px;">
                                        <div style="background-color: #DBEAFE; border-radius: 12px; padding: 20px; text-align: center;">
                                            <p style="margin: 0; font-size: 36px; font-weight: 700; color: #1D4ED8;">
                                                <?php echo intval($in_review_count); ?>
                                            </p>
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #1D4ED8; text-transform: uppercase; letter-spacing: 0.5px;">
                                                En cours
                                            </p>
                                        </div>
                                    </td>
                                    <td width="33%" style="padding: 10px;">
                                        <div style="background-color: #D1FAE5; border-radius: 12px; padding: 20px; text-align: center;">
                                            <p style="margin: 0; font-size: 36px; font-weight: 700; color: #047857;">
                                                <?php echo intval($graded_this_week); ?>
                                            </p>
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Cette semaine
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Oldest submissions -->
                            <?php if (!empty($oldest_submissions) && $pending_count > 0): ?>
                            <div style="background-color: #F9FAFB; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                                <p style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #4A4A4A;">
                                    ⏳ Soumissions les plus anciennes :
                                </p>
                                
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <?php foreach ($oldest_submissions as $sub): ?>
                                    <tr>
                                        <td style="padding: 10px 0; border-bottom: 1px solid #E5E7EB;">
                                            <p style="margin: 0; font-size: 14px; color: #1A1A1A;">
                                                <strong><?php echo esc_html($sub->student_name); ?></strong>
                                                <span style="color: #9CA3AF;">•</span>
                                                <?php echo esc_html($sub->assessment_title); ?>
                                            </p>
                                            <p style="margin: 3px 0 0 0; font-size: 12px; color: #EF4444;">
                                                Soumis il y a <?php echo esc_html($sub->days_ago); ?> jour<?php echo $sub->days_ago > 1 ? 's' : ''; ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <?php endif; ?>
                            
                            <!-- CTA Button -->
                            <?php if ($pending_count > 0): ?>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?php echo esc_url($inbox_url); ?>" 
                                           style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                            👉 Accéder à l'inbox
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <?php else: ?>
                            <div style="text-align: center; padding: 20px;">
                                <p style="margin: 0; font-size: 24px;">🎉</p>
                                <p style="margin: 10px 0 0 0; font-size: 16px; color: #10B981; font-weight: 600;">
                                    Toutes les copies sont corrigées !
                                </p>
                            </div>
                            <?php endif; ?>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; font-size: 12px; color: #9CA3AF; text-align: center;">
                                Cet email est envoyé automatiquement chaque semaine.<br>
                                Vous pouvez modifier cette préférence dans les paramètres.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>