<?php
/**
 * Email template : nouvelle demande (envoyé au prof).
 *
 * Variables disponibles : $row, $type_label, $admin_url, $excerpt
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<h2 style="margin:0 0 16px;font-size:18px;color:#111827;">Nouvelle <?php echo esc_html( $type_label ); ?></h2>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB;margin-bottom:24px;">
<tr>
    <td style="padding:16px 20px;">
        <p style="margin:0 0 8px;font-size:14px;color:#6B7280;">Étudiant</p>
        <p style="margin:0 0 16px;font-size:16px;color:#111827;font-weight:600;"><?php echo esc_html( $row->nom ); ?> (<?php echo esc_html( $row->annee ); ?>)</p>

        <p style="margin:0 0 8px;font-size:14px;color:#6B7280;">Matière</p>
        <p style="margin:0 0 16px;font-size:16px;color:#111827;font-weight:600;"><?php echo esc_html( $row->matiere ); ?></p>

        <p style="margin:0 0 8px;font-size:14px;color:#6B7280;">Type</p>
        <p style="margin:0 0 16px;">
            <span style="display:inline-block;padding:4px 12px;border-radius:99px;font-size:13px;font-weight:600;
            <?php echo $row->type === 'question'
                ? 'background:#EDE9FE;color:#7C3AED;'
                : 'background:#FEF3C7;color:#D97706;'; ?>
            "><?php echo esc_html( ucfirst( $row->type ) ); ?></span>
        </p>

        <?php if ( ! empty( $row->message ) ) : ?>
        <p style="margin:0 0 8px;font-size:14px;color:#6B7280;">Message</p>
        <p style="margin:0;font-size:14px;color:#374151;line-height:1.6;"><?php echo esc_html( $excerpt ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $row->file_name ) ) : ?>
        <p style="margin:16px 0 0;font-size:13px;color:#6B7280;">📎 Fichier joint : <?php echo esc_html( $row->file_name ); ?></p>
        <?php endif; ?>
    </td>
</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0">
<tr><td align="center">
    <a href="<?php echo esc_url( $admin_url ); ?>" style="display:inline-block;padding:12px 32px;background:linear-gradient(135deg,#B0001D 0%,#DC2626 50%,#7C3AED 100%);color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;">
        Voir la demande
    </a>
</td></tr>
</table>
