<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Jaide_Notifications {

    /**
     * Email au prof — Nouvelle demande.
     */
    public static function send_new_request( $row ) {
        $options = get_option( 'jaide_options', [] );

        if ( empty( $options['notify_prof_new'] ) ) {
            return;
        }

        $type_label = $row->type === 'question' ? 'question' : 'copie';
        $subject    = "📩 Nouvelle {$type_label} — {$row->matiere}";

        $admin_url = admin_url( 'admin.php?page=jaide-detail&id=' . $row->id );
        $excerpt   = wp_trim_words( $row->message ?: '(Aucun message)', 30 );

        ob_start();
        include JAIDE_PATH . 'templates/emails/new-request.php';
        $content = ob_get_clean();

        $html = self::wrap_email( $content );
        self::send( get_option( 'admin_email' ), $subject, $html, $options );
    }

    /**
     * Email à l'étudiant — Réponse du prof.
     */
    public static function send_response( $row ) {
        $options = get_option( 'jaide_options', [] );

        if ( empty( $options['notify_student_reply'] ) ) {
            return;
        }

        $type_label = $row->type === 'question' ? 'question' : 'copie';
        $subject    = "✅ Réponse à votre {$type_label} — {$row->matiere}";

        ob_start();
        include JAIDE_PATH . 'templates/emails/response.php';
        $content = ob_get_clean();

        $html = self::wrap_email( $content );
        self::send( $row->email, $subject, $html, $options );
    }

    /**
     * Envoie un email via wp_mail.
     */
    private static function send( $to, $subject, $html, $options ) {
        $from_name  = $options['from_name'] ?? get_bloginfo( 'name' );
        $from_email = $options['from_email'] ?? get_option( 'admin_email' );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_email}>",
        ];

        wp_mail( $to, $subject, $html, $headers );
    }

    /**
     * Wrapper HTML pour les emails (même design que assessments).
     */
    private static function wrap_email( $content ) {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F3F4F6;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

<!-- Header gradient -->
<tr><td style="background:linear-gradient(135deg,#B0001D 0%,#DC2626 50%,#7C3AED 100%);padding:32px 24px;border-radius:12px 12px 0 0;text-align:center;">
<h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:700;">Jurible — Aide Personnalisée</h1>
</td></tr>

<!-- Content -->
<tr><td style="background:#ffffff;padding:32px 24px;">
' . $content . '
</td></tr>

<!-- Footer -->
<tr><td style="background:#F9FAFB;padding:20px 24px;border-radius:0 0 12px 12px;text-align:center;border-top:1px solid #E5E7EB;">
<p style="margin:0;font-size:13px;color:#9CA3AF;">Cet email a été envoyé automatiquement par Jurible.</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }
}
