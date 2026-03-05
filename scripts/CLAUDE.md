# Migration Thrive → Gutenberg

> ⚠️ **EN COURS DE CONSTRUCTION** - Mettre à jour ce fichier à chaque amélioration

## Objectif

Migrer les articles de aideauxtd.com (Thrive Architect) vers jurible.com (WordPress FSE + blocs custom).

## Connexions

**Source (aideauxtd.com) :**
- SSH : `aideauxtd@dogfish.o2switch.net`
- WordPress : `/home/aideauxtd/public_html`
- BDD : `aideauxtd_wp` (credentials dans wp-config.php)

**Destination (jurible-local) :**
- Path : `~/Local Sites/jurible-local/app/public`
- MySQL socket : `~/Library/Application Support/Local/run/njWhKMb7k/mysql/mysqld.sock`

## Mapping Thrive → Gutenberg (À COMPLÉTER)

| Pattern Thrive | Bloc Gutenberg | Status |
|----------------|----------------|--------|
| 📌 Exemple + `<p style="font-size: var(--tve-font-size...)">` | `jurible/infobox` type=`exemple` | ⚠️ HTML invalide en éditeur |
| 💬 Aparté + `<span>Titre</span>` + `<p>` | `jurible/infobox` type=`astuce` | À tester |
| 🔎 Article + `<p><em>citation</em></p>` | `jurible/citation` | À faire |
| `<span style="--tcb-text-highlight-color...">` | `has-success-color` (vert theme.json) | À faire |
| CTA Thrive (bannières, boutons promo) | **SUPPRIMER** | À faire |
| `<h2 id="t-...">` | `core/heading` level=2 | ✅ OK |
| `<h3>` | `core/heading` level=3 | ✅ OK |
| `<p>` | `core/paragraph` | ✅ OK |
| `<ul>/<ol>` | `core/list` | ✅ OK |
| `<table>` | `core/table` | À tester |
| `<span><img src="...">` | `core/image` + import média | ❌ À faire |

## Problèmes connus

1. **Images** : Non transférées, URLs non remplacées
2. **Bloc Exemple** : HTML généré ≠ save.js → erreur "contenu invalide"
3. **Références légales** : En jaune (`<mark>`) au lieu de vert (variable theme.json)
4. **CTA** : Pas encore détectés/supprimés
5. **Méthode BDD** : INSERT direct → préférer WP-CLI ou API REST

## Scripts

- `scripts/thrive-to-gutenberg.php` : Convertisseur principal

## Commandes utiles

```bash
# PHP de Local
LOCAL_PHP="/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php"

# Récupérer un article source
ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post get <ID> --field=post_content --allow-root"

# Convertir
$LOCAL_PHP ~/Code/jurible/scripts/thrive-to-gutenberg.php article.html

# Lister les articles source
ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post list --post_type=post --post_status=publish --fields=ID,post_title --format=csv --allow-root"
```

## Prochaines étapes

1. [ ] Définir mapping précis Thrive → Gutenberg
2. [ ] Corriger génération bloc Exemple (matcher save.js)
3. [ ] Références légales en vert
4. [ ] Détecter et supprimer CTA
5. [ ] Transférer images + import médiathèque
6. [ ] Méthode de transfert BDD propre (WP-CLI)
