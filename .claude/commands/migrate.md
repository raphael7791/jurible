# Migration d'article Thrive → Gutenberg

> ⚠️ **EN COURS DE CONSTRUCTION** - Améliorer ce skill au fur et à mesure des modifications

## Contexte

Voir `scripts/CLAUDE.md` pour la documentation complète et le mapping.

## Utilisation

```
/migrate <post_id>        # Migrer un article par son ID
/migrate --list           # Lister les articles disponibles
/migrate --test <post_id> # Tester la conversion sans importer
```

## Étapes de migration

1. Récupérer le contenu de l'article source (aideauxtd.com via SSH)
2. Convertir le HTML Thrive en blocs Gutenberg via `scripts/thrive-to-gutenberg.php`
3. Nettoyer l'encodage UTF-8
4. Créer le post dans WordPress local
5. (TODO) Transférer les images

## Commandes

```bash
# Variables
LOCAL_PHP="/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php"
SOCKET="$HOME/Library/Application Support/Local/run/njWhKMb7k/mysql/mysqld.sock"

# 1. Récupérer l'article
ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post get <POST_ID> --field=post_content --allow-root" > /tmp/article-source.html

# 2. Convertir
$LOCAL_PHP ~/Code/jurible/scripts/thrive-to-gutenberg.php /tmp/article-source.html > /tmp/article-gutenberg.html

# 3. Nettoyer encodage
iconv -f UTF-8 -t UTF-8//IGNORE /tmp/article-gutenberg.html > /tmp/article-clean.html

# 4. Insérer (voir script PHP dans scripts/CLAUDE.md)
```

## Problèmes connus

- Images non transférées
- Bloc Exemple invalide en éditeur (HTML ≠ save.js)
- Références en jaune au lieu de vert

## Amélioration continue

À chaque modification du script ou du mapping :
1. Mettre à jour `scripts/thrive-to-gutenberg.php`
2. Mettre à jour `scripts/CLAUDE.md` (mapping + problèmes)
3. Mettre à jour ce skill si les commandes changent
