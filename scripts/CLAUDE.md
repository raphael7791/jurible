# Migration Thrive → Gutenberg

> ⚠️ **EN COURS DE CONSTRUCTION** - Mettre à jour ce fichier à chaque amélioration

## Objectif

Migrer les articles de aideauxtd.com (Thrive Architect) vers jurible.com (WordPress FSE + blocs custom).

## IMPORTANT : Blocs custom jurible

**Toujours vérifier les blocs existants avant de convertir !**

Les blocs custom sont dans `plugins/jurible-blocks-react/src/`. Consulter `block.json` pour les attributs et titres par défaut.

### jurible/infobox

Types disponibles : `retenir`, `attention`, `exemple`, `important`, `astuce`, `definition`, `conditions`

| Type | Icône | Titre par défaut |
|------|-------|------------------|
| retenir | 🌟 | "À retenir" |
| attention | ⚠️ | - |
| exemple | 💡 | "Exemple" |
| important | 📌 | - |
| astuce | 🎯 | - |
| definition | 📖 | - |
| conditions | 📌 | "Conditions" |

**Ne jamais inventer de nouveaux types ou titres !** Toujours utiliser les valeurs définies dans le bloc.

## Connexions

**Source (aideauxtd.com) :**
- SSH : `aideauxtd@dogfish.o2switch.net`
- WordPress : `/home/aideauxtd/public_html`
- Contenu Thrive : `wp post meta get <ID> tve_updated_post` (pas post_content !)

**Destination (jurible-local) :**
- Path : `~/Local Sites/jurible-local/app/public`
- MySQL socket : `~/Library/Application Support/Local/run/njWhKMb7k/mysql/mysqld.sock`
- URL : `http://jurible-local.local`

## Mapping Thrive → Gutenberg (VALIDÉ)

| Pattern Thrive | Bloc Gutenberg | Status |
|----------------|----------------|--------|
| 📌 Exemple + contenu | `jurible/infobox` type=`exemple` titre="Exemple" | ✅ OK |
| 💬 + titre custom | `jurible/infobox` type=`retenir` titre="À retenir" | ✅ OK |
| 🔎 Article + citation | `jurible/citation` | À faire |
| CTA Thrive (Académie, cours complet...) | **SUPPRIMER** | ✅ OK |
| `<h2>`, `<h3>`, `<h4>` | `core/heading` | ✅ OK |
| `<p>` | `core/paragraph` | ✅ OK |
| `<ul>/<ol>` | `core/list` | ✅ OK |
| `<table>` avec `<tbody>` | `core/table` | ✅ OK |
| Images aideauxtd.com | `core/image` + téléchargement SCP | ✅ OK |
| YouTube (data-url) | `core/embed` youtube | ✅ OK |

## Scripts

- `scripts/thrive-to-gutenberg.php` : Convertisseur principal (tests locaux)
- `scripts/import-media.php` : Import dans la médiathèque WordPress via SQL (tests locaux)

## Plugin de migration (PRODUCTION)

**`plugins/jurible-migration/`** - Plugin WordPress pour la migration en production

### Installation
1. Déployer le plugin sur jurible.com
2. Activer dans WordPress
3. Menu "Migration" dans l'admin

### Utilisation
- Liste tous les articles de aideauxtd.com
- Bouton "Migrer" pour chaque article
- Statut migré/en attente avec checkbox
- Journal de migration en temps réel

### Suppression propre
Le plugin supprime toutes ses données à la désinstallation (uninstall.php)

## Commandes utiles

```bash
# PHP de Local
LOCAL_PHP="/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php"

# Récupérer le contenu Thrive d'un article (PAS post_content !)
ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post meta get <ID> tve_updated_post --allow-root" > article.html

# Convertir
$LOCAL_PHP ~/Code/jurible/scripts/thrive-to-gutenberg.php article.html > article-gutenberg.html

# Lister les articles source
ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post list --post_type=post --post_status=publish --fields=ID,post_title --format=csv --allow-root"
```

## Prochaines étapes

1. [x] Définir mapping précis Thrive → Gutenberg
2. [x] Blocs infobox avec bons titres
3. [x] Images téléchargées via SCP
4. [x] YouTube embeds
5. [x] Import médiathèque WordPress + image à la une
6. [x] Détecter et supprimer CTA
7. [x] Méthode de transfert BDD propre (WP-CLI)
8. [x] Plugin migration avec interface web (`plugins/jurible-migration/`)
