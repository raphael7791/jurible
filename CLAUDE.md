# Jurible - Contexte Claude Code

## Contexte business

**Jurible** = plateforme d'aide juridique pour étudiants en droit
- **jurible.com** : site public (cours, méthodologie, blog)
- **ecole.jurible.com** : espace membre payant (Fluent Community)

## Architecture

- **Repo local** : `~/Code/jurible/` (source de vérité)
- **Thème parent** : `themes/jurible` → utilisé par jurible.com ET ecole.jurible.com
- **Thème enfant** : `themes/ecole.jurible` → espace membre uniquement
- **Blocs React** : `plugins/jurible-blocks-react` (28 blocs custom)

Les sites Local by Flywheel (`~/Local Sites/`) utilisent des **symlinks** vers ce repo.

## Règles critiques

- Toujours travailler dans `~/Code/jurible/`, jamais dans `~/Local Sites/`
- Le thème enfant hérite automatiquement du parent (functions.php, templates, etc.)
- Pour override header/footer sur ecole : créer `themes/ecole.jurible/parts/header.html`
- Ne jamais modifier les templates via l'éditeur WordPress (ça écrase la version fichier)
- Toujours lancer `npm run build` après modification des blocs React

## Conventions de code

- **PHP** : préfixer les fonctions `jurible_` (ex: `jurible_register_blocks`)
- **Blocs React** : namespace `jurible/` (ex: `jurible/card-cours`)
- **CSS** : classes BEM style `.jurible-block__element--modifier`
- **Commits** : en français, format `type: description` (ex: `fix: correction header mobile`)

## Commandes fréquentes

```bash
# Compiler les blocs React
cd ~/Code/jurible/plugins/jurible-blocks-react && npm run build

# Mode watch (développement)
cd ~/Code/jurible/plugins/jurible-blocks-react && npm run start

# SSH serveur O2switch
ssh aideauxtd@dogfish.o2switch.net

# Déployer (sur le serveur)
cd ~/jurible-repo && git pull
rm -rf ~/jurible.com/wp-content/themes/jurible
cp -r themes/jurible ~/jurible.com/wp-content/themes/
# (voir README.md pour le script complet)
```

## Stack technique

- WordPress FSE (Full Site Editing)
- PHP 8, theme.json pour design tokens
- React + @wordpress/scripts v31 pour blocs Gutenberg
- Fluent Community pour l'espace membre
- ACF Pro pour champs personnalisés

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `themes/jurible/theme.json` | Design tokens (couleurs, typos, espacements) |
| `themes/jurible/functions.php` | Code PHP commun + block styles |
| `themes/jurible/parts/` | Header, footer |
| `themes/jurible/patterns/` | Assemblages de blocs réutilisables |
| `plugins/jurible-blocks-react/src/` | Code source des blocs custom |

## Pièges à éviter

- Oublier `npm run build` après modif des blocs → les changements n'apparaissent pas
- Modifier dans `~/Local Sites/` → les changements seront perdus
- Éditer via WordPress (éditeur de site) → écrase les fichiers versionnés
- Déployer sans `git pull` sur le serveur → version obsolète

## Ce qui n'est PAS versionné

- Contenu des pages (base de données)
- Médias uploadés (`wp-content/uploads/`)
- Traductions Loco (`wp-content/languages/loco/`)
- Modifications faites via l'éditeur de site WordPress
