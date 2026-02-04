<?php
/**
 * Header Navigation Template
 * Compatible avec Max Mega Menu
 */

// Logo personnalise (definir dans Personnaliser > Identite du site > Logo)
$custom_logo_id = get_theme_mod('custom_logo');
$logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';
?>
<header class="site-header" id="site-header">
    <div class="site-header__inner">
        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo">
            <?php if ($logo_url) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="site-header__logo-img">
            <?php else : ?>
                <?php echo esc_html(get_bloginfo('name')); ?>
            <?php endif; ?>
        </a>

        <!-- Navigation Desktop -->
        <nav class="site-header__nav" aria-label="<?php esc_attr_e('Navigation principale', 'jurible'); ?>">
            <?php
            if (has_nav_menu('header')) {
                wp_nav_menu([
                    'theme_location' => 'header',
                    'container'      => false,
                    'menu_class'     => 'site-header__menu',
                    'fallback_cb'    => false,
                ]);
            } else {
                echo '<p class="site-header__no-menu">Configurer le menu dans Apparence → Menus</p>';
            }
            ?>
        </nav>

        <!-- Actions Desktop -->
        <div class="site-header__actions">
            <a href="/tarifs" class="btn btn--primary btn--sm">S'ABONNER</a>
            <a href="/connexion" class="btn btn--outline btn--sm">SE CONNECTER</a>
            <a href="/panier" class="site-header__cart" aria-label="Panier">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </a>
        </div>

        <!-- Burger Menu Mobile -->
        <button class="site-header__burger" id="header-burger" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
