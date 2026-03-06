# Jurible - Contexte Claude Code

## Contexte business

**Jurible** = plateforme d'aide juridique pour étudiants en droit
- **jurible.com** : site public (cours, méthodologie, blog)
- **ecole.jurible.com** : espace membre payant (Fluent Community)

## Projet de refonte (Migration aideauxtd → jurible)

**Contexte :** Le site principal actuel est **aideauxtd.com** (Thrive Architect + Yoast SEO). La refonte se fait sur **jurible.com** (WordPress FSE + Gutenberg + Rank Math).

**Flux de migration :**
1. **Phase actuelle** : Migration articles aideauxtd.com → jurible.com (refonte/développement)
2. **Phase finale** : Copie complète jurible.com → aideauxtd.com (mise en production)

**Plugin de migration** : `plugins/jurible-migration/`
- Convertit Thrive Architect → blocs Gutenberg (jurible/infobox, etc.)
- Migre les données SEO Yoast → Rank Math (titre, description, image OG)
- **Conserve les dates de publication originales** (important pour SEO)
- Copie les images vers la médiathèque

**Impact SEO = AUCUN si :**
- Mêmes URLs/slugs conservés
- Mêmes dates de publication (✅ gérées par le plugin)
- Même domaine final (aideauxtd.com)
- Contenu identique (juste reformaté)

**Déploiement plugin migration :**
```bash
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/jurible.com/wp-content/plugins/jurible-migration && cp -r plugins/jurible-migration ~/jurible.com/wp-content/plugins/"
```

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
```

## Déploiement (via SSH)

Claude Code peut déployer directement sur O2switch. Utilise `/deploy` ou demande "déploie".

**Serveur O2switch :**
- Hôte : `dogfish.o2switch.net`
- User : `aideauxtd`
- Repo sur serveur : `~/jurible-repo`

**Si SSH échoue (timeout ou permission denied) :**
L'IP actuelle n'est pas autorisée sur O2switch. L'utilisateur doit :
1. Aller sur cPanel : https://dogfish.o2switch.net:2083
2. Section Sécurité → Accès SSH → Autoriser l'IP actuelle
3. Relancer le déploiement

**Commandes de déploiement (exécutables depuis Claude Code) :**

```bash
# Déployer le thème jurible
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/jurible.com/wp-content/themes/jurible && cp -r themes/jurible ~/jurible.com/wp-content/themes/"

# Déployer le thème ecole.jurible
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/ecole.jurible.com/wp-content/themes/ecole.jurible && cp -r themes/ecole.jurible ~/ecole.jurible.com/wp-content/themes/"

# Déployer les blocs React
ssh aideauxtd@dogfish.o2switch.net "cd ~/jurible-repo && git pull && rm -rf ~/jurible.com/wp-content/plugins/jurible-blocks-react && cp -r plugins/jurible-blocks-react ~/jurible.com/wp-content/plugins/"
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
