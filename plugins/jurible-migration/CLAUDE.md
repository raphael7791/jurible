# Plugin Jurible Migration

## Objectif

Migration des articles de **aideauxtd.com** (Thrive Architect + Yoast SEO) vers **jurible.com** (WordPress FSE + Gutenberg + Rank Math).

## Contexte projet

Ce plugin fait partie d'une **refonte complète** :
1. Les articles sont migrés de aideauxtd.com → jurible.com pour la refonte
2. Une fois la refonte terminée, tout le contenu de jurible.com sera recopié vers aideauxtd.com
3. Le domaine final reste **aideauxtd.com**

## Données conservées lors de la migration

| Donnée | Conservée | Notes |
|--------|-----------|-------|
| Titre | ✅ | |
| Contenu | ✅ | Converti Thrive → Gutenberg |
| **Date de publication** | ✅ | `post_date` original conservé |
| Slug/URL | ✅ | Via `post_name` |
| Catégories | ✅ | |
| Tags | ✅ | |
| Image à la une | ✅ | Copiée + importée médiathèque |
| Images du contenu | ✅ | Copiées + importées médiathèque |
| Meta description SEO | ✅ | Yoast → Rank Math |
| Titre SEO | ✅ | Seulement si personnalisé (pas les templates %%) |
| Focus keyword | ✅ | Yoast → Rank Math |
| Image Open Graph | ✅ | Copiée + importée |
| Auteur | ✅ | Utilisateur courant assigné |

## Conversions Thrive → Gutenberg

| Élément Thrive | Bloc Gutenberg |
|----------------|----------------|
| Content box avec 📌 Exemple | `jurible/infobox` type="exemple" |
| Content box avec 💬 Aparté | `jurible/infobox` type="retenir" |
| Blockquote | `wp:quote` |
| Responsive video YouTube | `wp:embed` YouTube |
| Images | `wp:image` (centrées) |
| Headings h2-h4 | `wp:heading` |
| Paragraphs | `wp:paragraph` |
| Tables | `wp:table` |
| Lists ul/ol | `wp:list` |

## Éléments Thrive supprimés (analyse complète)

**Blocs interactifs (non convertibles) :**
- Toggle/FAQ/Accordéons (`tve_toggle`, `tve_faqI`, `thrv_toggle_item`)
- Quiz Thrive (`thrive-quiz-builder-shortcode`)
- Thrive Leads - formulaires email (`thrive_leads_shortcode`)
- Thrive Symbols - blocs réutilisables (`thrv_symbol`)

**Éléments promotionnels :**
- Blocs CTA (Académie, cours complet, etc.)
- Boutons "Lire aussi", "Voir plus de", "Accéder au cours" (`tcb-button-link`)

**Éléments visuels Thrive :**
- SVG icons (`<svg>`, `thrv_icon`, `tcb-icon`)
- Blocs `__CONFIG__` Thrive
- Timelines avec `position: absolute/relative`
- Conteneurs Poppins (`font-family: Poppins`)
- Flex containers (`tcb-flex-col`, `tcb-flex-row`)
- Custom HTML wrappers (`thrv_custom_html_shortcode`)
- Iframe covers (`tve_iframe_cover`)

**Iframes externes :**
- Spotify, Soundcloud (orphelins)
- Leaflet maps

**Data attributes nettoyés :**
- `data-css`, `data-ct`, `data-ct-name`, `data-element-name`, `data-selector`

**Divs vides supprimés :**
- `thrv_wrapper` vides
- `tcb-clear` vides
- `tve_empty_dropzone`

**Filet de sécurité :**
- Équilibrage automatique des `<div>` non fermés

## Règles critiques du converter

**ORDRE D'EXÉCUTION** (class-converter.php) :
1. `convertExempleBlocks()` et `convertAparteBlocks()` **EN PREMIER**
2. `removeCTABlocks()` **APRÈS**
3. Puis normalizeHtml, YouTube, images, etc.

**Pourquoi cet ordre ?** Le pattern `thrv_symbol` dans removeCTABlocks supprime des parties du HTML qui cassent les patterns Aparté/Exemple. Si on convertit les blocs Aparté en placeholders (`###INFOBOX###`) d'abord, ils sont protégés de la suppression.

**Ne JAMAIS modifier l'ordre** sans tester sur l'article Carbonnier (ID source 54322) qui contient 3 blocs Aparté.

## Mapping SEO Yoast → Rank Math

| Meta Yoast | Meta Rank Math |
|------------|----------------|
| `_yoast_wpseo_title` | `rank_math_title` |
| `_yoast_wpseo_metadesc` | `rank_math_description` |
| `_yoast_wpseo_focuskw` | `rank_math_focus_keyword` |
| `_yoast_wpseo_opengraph-title` | `rank_math_facebook_title` |
| `_yoast_wpseo_opengraph-description` | `rank_math_facebook_description` |
| `_yoast_wpseo_opengraph-image` | `rank_math_facebook_image` |
| `_yoast_wpseo_twitter-title` | `rank_math_twitter_title` |
| `_yoast_wpseo_twitter-description` | `rank_math_twitter_description` |

**Note :** Les templates Yoast avec `%%placeholders%%` sont ignorés. Rank Math utilisera ses propres templates.

## Fichiers du plugin

| Fichier | Rôle |
|---------|------|
| `jurible-migration.php` | Point d'entrée, hooks AJAX, admin menu |
| `includes/class-converter.php` | Conversion HTML Thrive → Gutenberg |
| `includes/class-migrator.php` | Logique migration (WP-CLI, images, SEO) |
| `includes/class-admin-page.php` | Interface admin |
| `assets/admin.js` | Interactions frontend (AJAX) |
| `assets/admin.css` | Styles interface admin |

## Commandes WP-CLI utilisées

Le plugin utilise WP-CLI pour communiquer entre les deux sites (même serveur O2switch) :

```bash
# Lire un post source
wp post get {id} --path=/home/aideauxtd/public_html

# Lire meta Thrive
wp post meta get {id} tve_updated_post --path=/home/aideauxtd/public_html

# Créer post destination
wp post create {file} --post_title=... --post_date=... --post_author=...

# Importer média
wp media import {path} --title=... --porcelain
```

## Impact SEO

**Aucun impact négatif** car :
- Les dates de publication sont conservées (`post_date` original)
- Les URLs/slugs restent identiques
- Le contenu est le même (reformaté)
- Le domaine final est le même (aideauxtd.com)

La migration est transparente pour Google.
