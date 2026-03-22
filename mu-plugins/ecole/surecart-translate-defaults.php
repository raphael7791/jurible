<?php
/**
 * Plugin Name: Jurible École — Correctifs divers
 * Description: Traduction defaults SureCart + redirection homepage vers portail FC + logout redirect + dashboard
 */
if (!defined('ABSPATH')) exit;

// Traduit les attributs par défaut anglais des blocs SureCart
add_filter('render_block', function($content, $block) {
    if (strpos($block['blockName'] ?? '', 'surecart/') !== 0) return $content;

    $replacements = [
        'This is a secure, encrypted payment.' => __('This is a secure, encrypted payment.', 'surecart'),
    ];

    foreach ($replacements as $en => $fr) {
        if ($en !== $fr) {
            $content = str_replace(esc_attr($en), esc_attr($fr), $content);
        }
    }

    return $content;
}, 10, 2);

// Redirige la homepage vers le portail Fluent Community
add_action('template_redirect', function() {
    if (is_front_page()
        && !isset($_GET['sc_checkout_product_id'])
        && !isset($_GET['customer_link_id'])
        && !isset($_GET['sc_login_code'])
        && !isset($_GET['sc_nonce'])
        && strpos($_SERVER['REQUEST_URI'], '/buy/') === false
        && strpos($_SERVER['REQUEST_URI'], '/surecart/') === false
    ) {
        wp_redirect('/accueil/', 302);
        exit;
    }
});

// Redirige vers /accueil/ après déconnexion
add_filter('logout_redirect', function() {
    return '/accueil/';
}, 10);

// Dashboard SureCart : texte + URL du bouton retour
add_filter('sc_customer_dashboard_back_home_text', function() {
    return 'Retour à la plateforme';
});
add_filter('sc_customer_dashboard_back_home_url', function() {
    return 'https://ecole.aideauxtd.com/accueil/';
});

// Dashboard SureCart : bouton retour en haut avec fond
add_action('wp_head', function() {
    if ( ! is_page('mon-compte') ) return;
    ?>
    <style>
    .sc-dashboard__sidebar {
        display: flex !important;
        flex-direction: column !important;
    }
    .sc-dashboard__back.sc-pin-bottom {
        order: -1 !important;
        margin-top: 0 !important;
        margin-bottom: 8px !important;
        padding: 12px 16px !important;
        position: static !important;
        bottom: auto !important;
        flex-shrink: 0 !important;
        text-align: left !important;
    }
    .sc-dashboard__back .sc-link-home {
        display: inline-flex !important;
        justify-content: flex-start !important;
    }
    .sc-dashboard__back .sc-link-home::part(base) {
        background: linear-gradient(135deg, #B0001D 0%, #DC2626 50%, #7C3AED 100%);
        border-radius: 8px;
        padding: 10px 18px;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        justify-content: flex-start;
    }
    .sc-dashboard__back .sc-link-home::part(base):hover {
        opacity: 0.9;
    }
    </style>
    <?php
});

// Toggle œil pour voir/masquer le mot de passe dans le dashboard SureCart
add_action('wp_footer', function() {
    if ( ! is_page('mon-compte') ) return;
    ?>
    <script>
    (function() {
        function addEyeToggle() {
            document.querySelectorAll('sc-input[type="password"]').forEach(function(scInput) {
                if (scInput.dataset.eyeAdded) return;
                scInput.dataset.eyeAdded = '1';
                var shadow = scInput.shadowRoot;
                if (!shadow) return;
                var input = shadow.querySelector('input[type="password"]');
                if (!input) return;
                var wrapper = input.parentElement;
                if (!wrapper) return;
                wrapper.style.position = 'relative';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                btn.style.cssText = 'position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#6b7280;display:flex;align-items:center;z-index:10;';
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
                    } else {
                        input.type = 'password';
                        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                    }
                });
                wrapper.appendChild(btn);
                input.style.paddingRight = '40px';
            });
        }
        var observer = new MutationObserver(function() { addEyeToggle(); });
        observer.observe(document.body, { childList: true, subtree: true });
        addEyeToggle();
    })();
    </script>
    <?php
});
