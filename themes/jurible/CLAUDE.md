# Thème Jurible (Parent) - Contexte

## Rôle

Thème WordPress FSE (Full Site Editing) utilisé par :
- **jurible.com** : site public
- **ecole.jurible.com** : via le thème enfant `ecole.jurible`

## Structure

```
themes/jurible/
├── theme.json          # Design tokens (couleurs, typos, espacements)
├── functions.php       # Code PHP, hooks, enqueue scripts
├── style.css           # Métadonnées thème + styles globaux
├── style-editor.css    # Styles pour l'éditeur Gutenberg
├── templates/          # Templates de pages complètes
├── parts/              # Morceaux réutilisables (header, footer)
├── patterns/           # Assemblages de blocs
├── assets/             # CSS, JS, fonts, images
├── acf-json/           # Sync ACF (champs personnalisés)
└── template-parts/     # Parties PHP legacy
```

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `theme.json` | Design system : couleurs, typos, espacements, presets |
| `functions.php` | Logique PHP, hooks, shortcodes, block styles |
| `style.css` | Header obligatoire + styles CSS globaux |

## Templates (10)

- `index.html` : fallback par défaut
- `page.html` / `page-minimal.html` : pages standards
- `single.html` : articles de blog
- `single-course.html` : cours LearnDash
- `single-sc_product.html` : produits SureCart
- `archive.html` : listes d'articles
- `search.html` : résultats de recherche
- `404.html` : page d'erreur
- `taxonomy-sc_collection.html` : collections SureCart

## Parts (5)

- `header.html` / `header-minimal.html` / `header-minimal-checkout.html`
- `footer.html` / `footer-minimal.html`

## Patterns (organisés par catégorie)

- `hero/` : sections d'en-tête de page
- `commerce/` : blocs e-commerce
- `confiance/` : témoignages, preuves sociales
- `contenu/` : blocs de contenu éditorial
- `marketing/` : CTA, newsletters
- `structure/` : layouts, grilles
- `equipe/` : présentation équipe

## Conventions

- Noms de fichiers patterns : `categorie-nom.php` (ex: `hero-homepage.php`)
- Classes CSS : `.jurible-` préfixe pour styles custom
- Design tokens : toujours utiliser les variables de `theme.json`

## Modifier le design system

1. Éditer `theme.json` (couleurs, fonts, spacings)
2. Les changements s'appliquent automatiquement à l'éditeur et au front

## Pièges courants

- Modifier via l'éditeur WordPress → écrase les fichiers versionnés
- Oublier de déployer `theme.json` → design incohérent
- Styles inline dans les templates → préférer `style.css` ou `theme.json`
