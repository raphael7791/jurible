<?php
/**
 * Template email : Nouvelle soumission (envoyé au prof)
 * 
 * Variables disponibles :
 * - $student_name : Nom de l'étudiant
 * - $student_email : Email de l'étudiant
 * - $assessment_title : Titre de l'assessment
 * - $submission_date : Date de soumission
 * - $file_name : Nom du fichier soumis
 * - $correction_url : URL vers la page de correction
 * - $pending_count : Nombre de soumissions en attente
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
                                📝 Nouvelle soumission
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #1A1A1A;">
                                Bonjour,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; font-size: 16px; color: #1A1A1A; line-height: 1.6;">
                                <strong><?php echo esc_html($student_name); ?></strong> vient de soumettre son travail.
                            </p>
                            
                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F9FAFB; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px;">📚 Assessment</td>
                                                <td style="padding: 8px 0; color: #1A1A1A; font-size: 14px; font-weight: 600; text-align: right;">
                                                    <?php echo esc_html($assessment_title); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; border-top: 1px solid #E5E7EB;">📅 Soumis le</td>
                                                <td style="padding: 8px 0; color: #1A1A1A; font-size: 14px; text-align: right; border-top: 1px solid #E5E7EB;">
                                                    <?php echo esc_html($submission_date); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; border-top: 1px solid #E5E7EB;">📄 Fichier</td>
                                                <td style="padding: 8px 0; color: #1A1A1A; font-size: 14px; text-align: right; border-top: 1px solid #E5E7EB;">
                                                    <?php echo esc_html($file_name); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; border-top: 1px solid #E5E7EB;">✉️ Email étudiant</td>
                                                <td style="padding: 8px 0; color: #1A1A1A; font-size: 14px; text-align: right; border-top: 1px solid #E5E7EB;">
                                                    <?php echo esc_html($student_email); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 30px 0;">
                                        <a href="<?php echo esc_url($correction_url); ?>" 
                                           style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                            👉 Corriger maintenant
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Pending count -->
                            <?php if ($pending_count > 1): ?>
                            <p style="margin: 0; padding: 15px; background-color: #FEF3C7; border-radius: 8px; font-size: 14px; color: #B45309; text-align: center;">
                                📊 Vous avez actuellement <strong><?php echo intval($pending_count); ?> soumissions</strong> en attente de correction.
                            </p>
                            <?php endif; ?>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; font-size: 12px; color: #9CA3AF; text-align: center;">
                                Cet email a été envoyé automatiquement par Jurible Assessments.<br>
                                Vous pouvez gérer vos préférences de notification dans les paramètres.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>