# Jurible - Contexte Claude Code

## Architecture

- **Repo local** : `~/Code/jurible/` (source de vérité)
- **Thème parent** : `themes/jurible` → utilisé par jurible.com ET ecole.jurible.com
- **Thème enfant** : `themes/ecole.jurible` → espace membre uniquement
- **Blocs React** : `plugins/jurible-blocks-react`

Les sites Local by Flywheel (`~/Local Sites/`) utilisent des **symlinks** vers ce repo.

## Règles critiques

- Toujours travailler dans `~/Code/jurible/`, jamais dans `~/Local Sites/`
- Le thème enfant hérite automatiquement du parent (functions.php, templates, etc.)
- Pour override header/footer sur ecole : créer `themes/ecole.jurible/parts/header.html`
- Ne jamais modifier les templates via l'éditeur WordPress (ça écrase la version fichier)

## Commandes fréquentes

```bash
# Compiler les blocs React
cd ~/Code/jurible/plugins/jurible-blocks-react && npm run build

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
- React + @wordpress/scripts pour blocs Gutenberg
- Fluent Community pour l'espace membre

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `themes/jurible/theme.json` | Design tokens (couleurs, typos, espacements) |
| `themes/jurible/functions.php` | Code PHP commun + block styles |
| `themes/jurible/parts/` | Header, footer |
| `themes/jurible/patterns/` | Assemblages de blocs réutilisables |
| `plugins/jurible-blocks-react/src/` | Code source des blocs custom |

## Ce qui n'est PAS versionné

- Contenu des pages (base de données)
- Médias uploadés (`wp-content/uploads/`)
- Traductions Loco (`wp-content/languages/loco/`)
- Modifications faites via l'éditeur de site WordPress
