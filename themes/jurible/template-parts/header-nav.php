<?php
/**
 * Header Navigation Template
 * Mega Menu hardcodé - Design System Jurible
 *
 * Note: Le HTML est compressé via ob_start/ob_get_clean pour éviter
 * que wpautop n'ajoute des <p> et <br> indésirables entre les éléments.
 * Le regex ne supprime que les espaces contenant des retours à la ligne.
 */

ob_start();

// Sticky Bar (si activée dans Customizer)
get_template_part('template-parts/sticky-bar');
?>
<header class="site-header" id="site-header">
    <div class="site-header__inner">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-header__logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logos/logo-color.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="site-header__logo-img">
        </a>
        <nav class="site-header__nav" aria-label="<?php esc_attr_e('Navigation principale', 'jurible'); ?>">
            <ul class="site-header__menu">
                <!-- COURS -->
                <li class="menu-item menu-item-has-megamenu">
                    <a href="/academie" class="site-header__nav-item">COURS <svg class="site-header__nav-chevron" width="10" height="10" viewBox="0 0 10 10"><path d="M2 4L5 7L8 4" stroke="currentColor" stroke-width="1.5" fill="none"/></svg></a>
                    <div class="megamenu megamenu--cours">
                        <div class="megamenu__grid megamenu__grid--3cols">
                            <div class="megamenu__col">
                                <p class="megamenu__title">Par matière</p>
                                <div class="megamenu__links megamenu__links--2cols">
                                    <a href="/matiere/institutions-juridictionnelles" class="megamenu__link">Institutions juridictionnelles</a>
                                    <a href="/matiere/droit-international-public" class="megamenu__link">Droit international public</a>
                                    <a href="/matiere/introduction-au-droit" class="megamenu__link">Introduction au droit</a>
                                    <a href="/matiere/droit-administratif-des-biens" class="megamenu__link">Droit administratif des biens</a>
                                    <a href="/matiere/histoire-du-droit" class="megamenu__link">Histoire du droit</a>
                                    <a href="/matiere/droit-commercial" class="megamenu__link">Droit commercial</a>
                                    <a href="/matiere/droit-de-la-famille" class="megamenu__link">Droit de la famille</a>
                                    <a href="/matiere/procedure-penale" class="megamenu__link">Procédure pénale</a>
                                    <a href="/matiere/droit-des-personnes" class="megamenu__link">Droit des personnes</a>
                                    <a href="/matiere/droit-fiscal" class="megamenu__link">Droit fiscal</a>
                                    <a href="/matiere/droit-constitutionnel-s1" class="megamenu__link">Constitutionnel S1</a>
                                    <a href="/matiere/droit-des-societes" class="megamenu__link">Droit des sociétés</a>
                                    <a href="/matiere/droit-constitutionnel-s2" class="megamenu__link">Constitutionnel S2</a>
                                    <a href="/matiere/droit-du-travail" class="megamenu__link">Droit du travail</a>
                                    <a href="/matiere/droit-des-biens" class="megamenu__link">Droit des biens</a>
                                    <a href="/matiere/relations-collectives" class="megamenu__link">Relations collectives</a>
                                    <a href="/matiere/droit-administratif" class="megamenu__link">Droit administratif</a>
                                    <a href="/matiere/droit-penal" class="megamenu__link">Droit pénal</a>
                                    <a href="/matiere/droit-des-obligations" class="megamenu__link">Droit des obligations</a>
                                    <a href="/matiere/procedure-civile" class="megamenu__link">Procédure civile</a>
                                </div>
                            </div>
                            <div class="megamenu__col">
                                <p class="megamenu__title">Par niveau</p>
                                <div class="megamenu__niveaux">
                                    <a href="/niveau/l1" class="megamenu__niveau"><span class="megamenu__niveau-dot megamenu__niveau-dot--l1"></span>Licence 1 (L1)</a>
                                    <a href="/niveau/l2" class="megamenu__niveau"><span class="megamenu__niveau-dot megamenu__niveau-dot--l2"></span>Licence 2 (L2)</a>
                                    <a href="/niveau/l3" class="megamenu__niveau"><span class="megamenu__niveau-dot megamenu__niveau-dot--l3"></span>Licence 3 (L3)</a>
                                    <a href="/niveau/capacite" class="megamenu__niveau"><span class="megamenu__niveau-dot megamenu__niveau-dot--capa"></span>Capacité en Droit</a>
                                </div>
                            </div>
                            <div class="megamenu__col">
                                <p class="megamenu__title">Par outil</p>
                                <div class="megamenu__outils">
                                    <a href="/outils/videos" class="megamenu__outil"><span class="megamenu__outil-icon">▶️</span> Vidéos</a>
                                    <a href="/outils/fiches" class="megamenu__outil"><span class="megamenu__outil-icon">📄</span> Fiches de révision</a>
                                    <a href="/outils/flashcards" class="megamenu__outil"><span class="megamenu__outil-icon">🎴</span> Flashcards</a>
                                    <a href="/outils/qcm" class="megamenu__outil"><span class="megamenu__outil-icon">✅</span> QCM</a>
                                    <a href="/outils/annales" class="megamenu__outil"><span class="megamenu__outil-icon">📝</span> Annales corrigées</a>
                                    <a href="/outils/fiches-videos" class="megamenu__outil"><span class="megamenu__outil-icon">🎬</span> Fiches vidéos</a>
                                    <a href="/outils/mindmaps" class="megamenu__outil"><span class="megamenu__outil-icon">🗺️</span> Mindmaps</a>
                                </div>
                            </div>
                        </div>
                        <div class="megamenu__footer">
                            <p class="megamenu__footer-text">Accédez à <span>+10 000 ressources</span> pédagogiques</p>
                            <a href="/tarifs" class="btn btn--primary btn--sm">Découvrir les formules</a>
                        </div>
                    </div>
                </li>
                <!-- FORMULES -->
                <li class="menu-item">
                    <a href="/tarifs" class="site-header__nav-item">FORMULES</a>
                </li>
                <!-- RESSOURCES -->
                <li class="menu-item menu-item-has-megamenu">
                    <a href="/ressources" class="site-header__nav-item">RESSOURCES <svg class="site-header__nav-chevron" width="10" height="10" viewBox="0 0 10 10"><path d="M2 4L5 7L8 4" stroke="currentColor" stroke-width="1.5" fill="none"/></svg></a>
                    <div class="megamenu megamenu--ressources">
                        <div class="megamenu__col">
                            <p class="megamenu__title">Blog</p>
                            <a href="/blog/reussir-premiere-annee-droit" class="megamenu__article">
                                <span class="megamenu__article-img megamenu__article-img--gradient1"></span>
                                <span class="megamenu__article-content">
                                    <span class="megamenu__article-title">Comment réussir sa première année de droit</span>
                                    <span class="megamenu__article-desc">Découvrez les stratégies éprouvées pour exceller en L1 et valider votre année avec succès.</span>
                                    <span class="megamenu__article-date">15 janvier 2025</span>
                                </span>
                            </a>
                            <a href="/blog/methodologie-juridique" class="megamenu__article">
                                <span class="megamenu__article-img megamenu__article-img--gradient2"></span>
                                <span class="megamenu__article-content">
                                    <span class="megamenu__article-title">Maîtriser la méthodologie juridique</span>
                                    <span class="megamenu__article-desc">Les techniques essentielles pour réussir vos cas pratiques et commentaires d'arrêt.</span>
                                    <span class="megamenu__article-date">12 janvier 2025</span>
                                </span>
                            </a>
                            <a href="/blog" class="megamenu__see-all">Voir tous les articles →</a>
                            <div class="megamenu__separator"></div>
                            <p class="megamenu__title">Guides</p>
                            <div class="megamenu__outils">
                                <a href="/guide/augmenter-notes" class="megamenu__outil"><span class="megamenu__outil-icon">📖</span> Comment augmenter ses notes en droit</a>
                                <a href="/guide/reprendre-etudes" class="megamenu__outil"><span class="megamenu__outil-icon">📖</span> Guide : Reprendre des études de droit</a>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- À PROPOS -->
                <li class="menu-item menu-item-has-megamenu">
                    <a href="/a-propos" class="site-header__nav-item">À PROPOS <svg class="site-header__nav-chevron" width="10" height="10" viewBox="0 0 10 10"><path d="M2 4L5 7L8 4" stroke="currentColor" stroke-width="1.5" fill="none"/></svg></a>
                    <div class="megamenu megamenu--apropos">
                        <div class="megamenu__col">
                            <div class="megamenu__outils">
                                <a href="/projet" class="megamenu__outil"><span class="megamenu__outil-icon">🎯</span> Notre projet</a>
                                <a href="/enseignants" class="megamenu__outil"><span class="megamenu__outil-icon">👥</span> Nos enseignants</a>
                                <a href="/temoignages" class="megamenu__outil"><span class="megamenu__outil-icon">💬</span> Témoignages</a>
                                <a href="/aide" class="megamenu__outil"><span class="megamenu__outil-icon">❓</span> Aide et support</a>
                            </div>
                            <div class="megamenu__contact-cta">
                                <p>Une question ?</p>
                                <a href="/contact" class="btn btn--primary btn--sm" style="width:100%">Nous contacter</a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="site-header__actions">
            <a href="/tarifs" class="btn btn--primary btn--sm">S'ABONNER</a>
            <a href="https://ecole.jurible.com/login" class="btn btn--outline btn--sm">SE CONNECTER</a>
            <?php echo do_shortcode('[sc_cart_icon]'); ?>
        </div>
        <button class="site-header__burger" id="header-burger" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- Menu Mobile -->
<div class="mobile-menu" id="mobile-menu">
    <div class="mobile-menu__header">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-menu__logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logos/logo-color.svg'); ?>" alt="Jurible" class="site-header__logo-img">
        </a>
        <button class="mobile-menu__close" id="mobile-menu-close" aria-label="Fermer le menu">✕</button>
    </div>
    <div class="mobile-menu__body">
        <!-- Cours -->
        <div class="mobile-menu__item" data-accordion>
            <div class="mobile-menu__item-header">
                <span>Cours</span>
                <span class="mobile-menu__chevron">▼</span>
            </div>
            <div class="mobile-menu__submenu">
                <p class="mobile-menu__subtitle">Par niveau</p>
                <div class="mobile-menu__niveaux">
                    <a href="/niveau/l1" class="mobile-menu__niveau"><span class="mobile-menu__niveau-dot mobile-menu__niveau-dot--l1"></span>Licence 1 (L1)</a>
                    <a href="/niveau/l2" class="mobile-menu__niveau"><span class="mobile-menu__niveau-dot mobile-menu__niveau-dot--l2"></span>Licence 2 (L2)</a>
                    <a href="/niveau/l3" class="mobile-menu__niveau"><span class="mobile-menu__niveau-dot mobile-menu__niveau-dot--l3"></span>Licence 3 (L3)</a>
                    <a href="/niveau/capacite" class="mobile-menu__niveau"><span class="mobile-menu__niveau-dot mobile-menu__niveau-dot--capa"></span>Capacité en Droit</a>
                </div>
                <p class="mobile-menu__subtitle">Par outil</p>
                <div class="mobile-menu__outils">
                    <a href="/outils/videos" class="mobile-menu__outil"><span>▶️</span> Vidéos</a>
                    <a href="/outils/fiches" class="mobile-menu__outil"><span>📄</span> Fiches de révision</a>
                    <a href="/outils/flashcards" class="mobile-menu__outil"><span>🎴</span> Flashcards</a>
                    <a href="/outils/qcm" class="mobile-menu__outil"><span>✅</span> QCM</a>
                </div>
            </div>
        </div>
        <!-- Formules -->
        <a href="/tarifs" class="mobile-menu__item mobile-menu__item--link">
            <span>Formules</span>
        </a>
        <!-- Ressources -->
        <div class="mobile-menu__item" data-accordion>
            <div class="mobile-menu__item-header">
                <span>Ressources</span>
                <span class="mobile-menu__chevron">▼</span>
            </div>
            <div class="mobile-menu__submenu">
                <div class="mobile-menu__outils">
                    <a href="/blog" class="mobile-menu__outil"><span>📝</span> Blog</a>
                    <a href="/guides" class="mobile-menu__outil"><span>📖</span> Guides gratuits</a>
                    <a href="/qcm-gratuits" class="mobile-menu__outil"><span>✅</span> QCM gratuits</a>
                    <a href="/aide" class="mobile-menu__outil"><span>❓</span> Aide et support</a>
                </div>
            </div>
        </div>
        <!-- À propos -->
        <div class="mobile-menu__item" data-accordion>
            <div class="mobile-menu__item-header">
                <span>À propos</span>
                <span class="mobile-menu__chevron">▼</span>
            </div>
            <div class="mobile-menu__submenu">
                <div class="mobile-menu__outils">
                    <a href="/projet" class="mobile-menu__outil"><span>🎯</span> Notre projet</a>
                    <a href="/enseignants" class="mobile-menu__outil"><span>👥</span> Nos enseignants</a>
                    <a href="/temoignages" class="mobile-menu__outil"><span>💬</span> Témoignages</a>
                    <a href="/contact" class="mobile-menu__outil"><span>✉️</span> Nous contacter</a>
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-menu__footer">
        <p class="mobile-menu__user">👤 Déjà membre ? <a href="https://ecole.jurible.com/login">Se connecter</a></p>
        <a href="/tarifs" class="btn btn--primary" style="width:100%">S'abonner à l'Académie</a>
    </div>
</div>
<div class="mobile-menu__overlay" id="mobile-menu-overlay"></div>
<?php
// Compresse le HTML
$html = ob_get_clean();
$html = preg_replace('/<!--.*?-->/s', '', $html); // Supprime les commentaires HTML
$html = preg_replace('/>\s+</', '><', $html); // Supprime tous les espaces entre balises
echo $html;
