<?php
/**
 * Template email : Devoir corrigé (envoyé à l'étudiant)
 * 
 * Variables disponibles :
 * - $student_name : Nom de l'étudiant
 * - $assessment_title : Titre de l'assessment
 * - $score : Note obtenue
 * - $max_score : Note maximale
 * - $feedback : Feedback du professeur
 * - $video_url : URL de la vidéo de correction (optionnel)
 * - $lesson_url : URL vers la leçon
 */

if (!defined('ABSPATH')) exit;

$percentage = ($score / $max_score) * 100;
$score_color = $percentage >= 70 ? '#10B981' : ($percentage >= 50 ? '#F59E0B' : '#EF4444');
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
                                ✅ Votre devoir a été corrigé !
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
                                Votre travail pour <strong><?php echo esc_html($assessment_title); ?></strong> a été corrigé.
                            </p>
                            
                            <!-- Score Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; background-color: #F9FAFB; border-radius: 12px; padding: 25px 50px; text-align: center;">
                                            <p style="margin: 0 0 5px 0; font-size: 14px; color: #6B7280; text-transform: uppercase; letter-spacing: 1px;">
                                                Votre note
                                            </p>
                                            <p style="margin: 0; font-size: 48px; font-weight: 700; color: <?php echo $score_color; ?>;">
                                                <?php echo esc_html($score); ?><span style="font-size: 24px; color: #9CA3AF;">/ <?php echo esc_html($max_score); ?></span>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Feedback -->
                            <div style="background-color: #F9FAFB; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #7C3AED;">
                                <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #4A4A4A;">
                                    💬 Feedback de votre professeur :
                                </p>
                                <p style="margin: 0; font-size: 15px; color: #1A1A1A; line-height: 1.7; white-space: pre-wrap;"><?php echo esc_html($feedback); ?></p>
                            </div>
                            
                            <!-- Video -->
                            <?php if (!empty($video_url)): ?>
                            <div style="background-color: #EEF2FF; border-radius: 8px; padding: 20px; margin-bottom: 25px; text-align: center;">
                                <p style="margin: 0 0 15px 0; font-size: 16px; color: #1A1A1A;">
                                    🎥 <strong>Une vidéo de correction est disponible !</strong>
                                </p>
                                <a href="<?php echo esc_url($video_url); ?>" 
                                   style="display: inline-block; background-color: #7C3AED; color: #FFFFFF; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;">
                                    ▶️ Voir la vidéo de correction
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0;">
                                        <a href="<?php echo esc_url($lesson_url); ?>" 
                                           style="display: inline-block; background: linear-gradient(94.73deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%); color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                            👉 Voir ma correction complète
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
                                Continuez à travailler, vous êtes sur la bonne voie ! 💪
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>