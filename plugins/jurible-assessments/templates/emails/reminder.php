<?php
/**
 * Template email : Rappel avant deadline (envoyé à l'étudiant)
 * 
 * Variables disponibles :
 * - $student_name : Nom de l'étudiant
 * - $assessment_title : Titre de l'assessment
 * - $due_date : Date limite formatée
 * - $days_left : Nombre de jours restants
 * - $lesson_url : URL vers la leçon
 */

if (!defined('ABSPATH')) exit;

$urgency_color = $days_left <= 1 ? '#EF4444' : ($days_left <= 2 ? '#F59E0B' : '#3B82F6');
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
                                ⏰ Rappel : devoir à rendre
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
                                N'oubliez pas de rendre votre travail !
                            </p>
                            
                            <!-- Countdown Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; background-color: #F9FAFB; border-radius: 12px; padding: 25px 50px; text-align: center; border: 2px solid <?php echo $urgency_color; ?>;">
                                            <p style="margin: 0 0 5px 0; font-size: 14px; color: #6B7280; text-transform: uppercase; letter-spacing: 1px;">
                                                Temps restant
                                            </p>
                                            <p style="margin: 0; font-size: 42px; font-weight: 700; color: <?php echo $urgency_color; ?>;">
                                                <?php echo intval($days_left); ?> jour<?php echo $days_left > 1 ? 's' : ''; ?>
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
                                                <td style="padding: 8px 0; color: #6B7280; font-size: 14px; border-top: 1px solid #E5E7EB;">📅 Date limite</td>
                                                <td style="padding: 8px 0; color: <?php echo $urgency_color; ?>; font-size: 14px; font-weight: 600; text-align: right; border-top: 1px solid #E5E7EB;">
                                                    <?php echo esc_html($due_date); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?php echo esc_url($lesson_url); ?>" 
                                           style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                            👉 Rendre mon travail
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Tips -->
                            <div style="margin-top: 25px; padding: 15px; background-color: #EEF2FF; border-radius: 8px;">
                                <p style="margin: 0; font-size: 14px; color: #4338CA;">
                                    💡 <strong>Conseil :</strong> N'attendez pas la dernière minute ! Soumettez votre travail dès qu'il est prêt pour éviter tout problème technique.
                                </p>
                            </div>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #F9FAFB; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; font-size: 12px; color: #9CA3AF; text-align: center;">
                                Cet email a été envoyé automatiquement par Jurible.<br>
                                Bonne chance pour votre travail ! 📚
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>