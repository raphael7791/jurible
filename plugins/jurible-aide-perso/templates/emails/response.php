<?php
/**
 * Email template : réponse à l'étudiant.
 *
 * Variables disponibles : $row, $type_label
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<h2 style="margin:0 0 8px;font-size:18px;color:#111827;">Bonjour <?php echo esc_html( $row->nom ); ?>,</h2>
<p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
    Un enseignant a répondu à votre <?php echo esc_html( $type_label ); ?> en <strong><?php echo esc_html( $row->matiere ); ?></strong>.
</p>

<!-- Réponse -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F0FDF4;border-radius:8px;border:1px solid #BBF7D0;margin-bottom:24px;">
<tr>
    <td style="padding:20px;">
        <p style="margin:0 0 8px;font-size:13px;color:#16A34A;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Réponse</p>
        <div style="font-size:15px;color:#111827;line-height:1.7;white-space:pre-line;"><?php echo nl2br( esc_html( $row->response ) ); ?></div>
    </td>
</tr>
</table>

<?php if ( ! empty( $row->response_file_url ) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
<tr>
    <td style="padding:12px 16px;background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB;">
        <?php
        $is_video = preg_match( '/\.(mp4|webm|mov)$/i', $row->response_file_url )
                    || strpos( $row->response_file_url, 'youtube' ) !== false
                    || strpos( $row->response_file_url, 'vimeo' ) !== false
                    || strpos( $row->response_file_url, 'loom' ) !== false;
        ?>
        <p style="margin:0;font-size:14px;color:#374151;">
            <?php echo $is_video ? '🎬' : '📎'; ?>
            <a href="<?php echo esc_url( $row->response_file_url ); ?>" style="color:#7C3AED;text-decoration:none;font-weight:600;" target="_blank" rel="noopener">
                <?php echo $is_video ? 'Voir la vidéo de correction' : 'Télécharger le fichier de correction'; ?>
            </a>
        </p>
    </td>
</tr>
</table>
<?php endif; ?>

<p style="margin:0;font-size:14px;color:#6B7280;line-height:1.6;">
    Si vous avez des questions sur cette réponse, n'hésitez pas à soumettre une nouvelle demande.
</p>
