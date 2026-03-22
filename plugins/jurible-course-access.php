<?php
/**
 * Plugin Name: Jurible — Contrôle accès cours
 * Description: Remplace le bouton "Rejoindre le cours" par un lien vers la page Académie + bloque l'auto-inscription.
 * Version: 6.2
 * Author: Jurible
 */

if (!defined('ABSPATH')) exit;

/**
 * JS pour remplacer le bouton Enroll + ajouter CTA dans les notifications
 */
function jurible_replace_enroll_button() {
    ?>
    <script id="jurible-course-access">
    (function(){
        var enrollTexts = ['Rejoignez ce cours', 'Enroll in this course'];
        var lockedTexts = [
            'Vous devez être inscrit à ce cours pour accéder à cette leçon',
            'You need to enroll in this course to access this lesson'
        ];
        var defaultUrl = 'https://www.aideauxtd.com/academie-droit/';
        var defaultBtnText = 'Acc\u00e9der \u00e0 ce cours';
        var defaultCtaText = 'Rejoindre l\u2019Acad\u00e9mie \u2192';

        // Exceptions par cours (slug dans l'URL)
        var courseOverrides = {
            'prepa-passdroit': {
                url: 'https://www.aideauxtd.com/prepa-pass-droit/',
                btnText: 'Acc\u00e9der \u00e0 Pr\u00e9pa PASS DROIT',
                ctaText: 'Rejoindre Pr\u00e9pa PASS DROIT \u2192'
            }
        };

        function getCourseSlug() {
            var path = window.location.pathname;
            var match = path.match(/\/course\/([^\/]+)/);
            return match ? match[1] : '';
        }

        function getConfig() {
            var slug = getCourseSlug();
            var override = courseOverrides[slug];
            return {
                url: override ? override.url : defaultUrl,
                btnText: override ? override.btnText : defaultBtnText,
                ctaText: override ? override.ctaText : defaultCtaText,
                isExternal: !override
            };
        }

        function replaceButtons() {
            var cfg = getConfig();
            document.querySelectorAll('.fcom_course_actions button, .fcom_course_actions .el-button').forEach(function(btn) {
                var text = (btn.textContent || '').trim();
                for (var i = 0; i < enrollTexts.length; i++) {
                    if (text === enrollTexts[i]) {
                        var link = document.createElement('a');
                        link.href = cfg.url;
                        if (cfg.isExternal) link.target = '_blank';
                        link.className = btn.className;
                        link.textContent = cfg.btnText;
                        link.style.cssText = 'text-decoration:none;background:linear-gradient(135deg,#B0001D 0%,#DC2626 50%,#7C3AED 100%);color:#fff;border:none;border-radius:8px;padding:6px 12px;font-size:15px;font-weight:500;cursor:pointer;';
                        btn.parentNode.replaceChild(link, btn);
                        return;
                    }
                }
            });
        }

        function addCtaToNotifications() {
            var cfg = getConfig();
            document.querySelectorAll('.el-notification').forEach(function(notif) {
                if (notif.dataset.juriblCta) return;
                var msg = notif.querySelector('.el-notification__content');
                if (!msg) return;
                var text = (msg.textContent || '').trim();
                for (var i = 0; i < lockedTexts.length; i++) {
                    if (text === lockedTexts[i]) {
                        notif.dataset.juriblCta = '1';
                        var cta = document.createElement('a');
                        cta.href = cfg.url;
                        if (cfg.isExternal) cta.target = '_blank';
                        cta.textContent = cfg.ctaText;
                        cta.style.cssText = 'display:inline-block;margin-top:4px;padding:4px 12px;background:linear-gradient(135deg,#B0001D 0%,#DC2626 50%,#7C3AED 100%);color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;';
                        msg.appendChild(cta);
                        return;
                    }
                }
            });
        }

        function run() { replaceButtons(); addCtaToNotifications(); }
        new MutationObserver(run).observe(document.documentElement, {childList: true, subtree: true});
        run();
    })();
    </script>
    <?php
}
add_action('wp_head', 'jurible_replace_enroll_button', 999);
add_action('fluent_community/portal_head', 'jurible_replace_enroll_button');

/**
 * Bloquer l'auto-inscription "self" côté serveur (sécurité)
 */
add_action('fluent_community/course/enrolled', function($course, $userId, $by) {
    if ($by === 'self' && class_exists('\FluentCommunity\Modules\Course\Services\CourseHelper')) {
        \FluentCommunity\Modules\Course\Services\CourseHelper::leaveCourse($course, $userId, 'system');
    }
}, 5, 3);
