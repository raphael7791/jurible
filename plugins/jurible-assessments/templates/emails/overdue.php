<?php
/**
 * Template email : Deadline dépassée (envoyé à l'étudiant)
 * 
 * Variables disponibles :
 * - $student_name : Nom de l'étudiant
 * - $assessment_title : Titre de l'assessment
 * - $due_date : Date limite formatée
 * - $days_overdue : Nombre de jours de retard
 * - $lesson_url : URL vers la leçon
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
                        <td style="background: linear-gradient(94.73deg, #EF4444 0%, #B91C1C 100%); padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 24px; font-weight: 600;">
                                ⚠️ Deadline dépassée
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            
                            <p style="margin: 0 0 20px 0; font-size: 16px; color: #1A1A1A;">
                                Bonjour <?php echo esc_html($student_name); ?>,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; font-size: 16px; color: #1A1A1A; line-height: 1.6;">
                                La date limite pour rendre votre travail est dépassée.
                            </p>
                            
                            <!-- Alert Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; background-color: #FEF2F2; border-radius: 12px; padding: 25px 50px; text-align: center; border: 2px solid #EF4444;">
                                            <p style="margin: 0 0 5px 0; font-size: 14px; color: #991B1B; text-transform: uppercase; letter-spacing: 1px;">
                                                Retard
                                            </p>
                                            <p style="margin: 0; font-size: 42px; font-weight: 700; color: #EF4444;">
                                                <?php echo intval($days_overdue); ?> jour<?php echo $days_overdue > 1 ? 's' : ''; ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
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
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; border-top: 1px solid #E5E7EB;">📅 Date limite était</td>
                                                <td style="padding: 8px 0; color: #EF4444; font-size: 14px; font-weight: 600; text-align: right; border-top: 1px solid #E5E7EB;">
                                                    <?php echo esc_html($due_date); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Message -->
                            <div style="background-color: #FEF3C7; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #F59E0B;">
                                <p style="margin: 0; font-size: 15px; color: #92400E; line-height: 1.6;">
                                    <strong>Il n'est pas trop tard !</strong><br>
                                    Nous vous encourageons à soumettre votre travail dès que possible. 
                                    Même en retard, il est important de compléter vos exercices pour progresser.
                                </p>
                            </div>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?php echo esc_url($lesson_url); ?>" 
                                           style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                            👉 Soumettre maintenant
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; font-size: 12px; color: #9CA3AF; text-align: center;">
                                Cet email a été envoyé automatiquement par Jurible.<br>
                                Si vous avez des difficultés, n'hésitez pas à contacter votre professeur.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>