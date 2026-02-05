# Règles de construction des Patterns WordPress

## Principes généraux

### 1. Utiliser les blocs natifs WordPress en priorité

Toujours privilégier les blocs natifs plutôt que du CSS custom :

| Besoin | Bloc natif | Éviter |
|--------|-----------|--------|
| Colonnes variables | `wp:columns` + `wp:column` | CSS Grid custom |
| Mise en page flex | `layout: {"type":"flex"}` | CSS flexbox custom |
| Espacement | `spacing` dans les attributs | margin/padding inline |
| Couleurs | `backgroundColor`, `textColor` | styles inline |

### 2. Structure d'une section

```
<!-- wp:group {"align":"full","className":"ma-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
```

**Règles :**
- `align: "full"` pour les sections pleine largeur
- Padding sur les 4 côtés (xl top/bottom, md left/right)
- `layout: {"type":"constrained"}` pour centrer le contenu
- Classe CSS pour identifier la section (ex: `equipe-grille-section`)

### 3. Colonnes natives vs CSS Grid

**Utiliser `wp:columns` quand :**
- Le nombre d'éléments est variable (l'utilisateur peut ajouter/supprimer)
- Besoin d'édition intuitive dans Gutenberg
- Responsive géré automatiquement

**Utiliser CSS Grid quand :**
- Grille fixe qui ne change jamais
- Layouts complexes impossibles avec Columns

### 4. Images

Format correct pour les blocs image :
```
<!-- wp:image {"sizeSlug":"full","className":"ma-classe"} -->
<figure class="wp-block-image size-full ma-classe"><img src="..." alt=""/></figure>
<!-- /wp:image -->
```

**Éviter :**
- `width` et `height` en pixels dans les attributs (cause erreur de récupération)
- `scale` dans les attributs

**Préférer :**
- Dimensionner via CSS avec les classes

### 5. Attributs JSON vs HTML

Le JSON dans le commentaire de bloc DOIT correspondre au HTML généré.

**Exemple problématique :**
```
<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}}} -->
<div class="wp-block-group">  <!-- Pas de style blockGap dans le HTML = erreur -->
```

**Solution :**
- Soit ajouter le style dans le HTML
- Soit retirer l'attribut du JSON

### 6. Espacements

Utiliser les variables de spacing WordPress :
- `var:preset|spacing|xs` = 8px
- `var:preset|spacing|sm` = 16px
- `var:preset|spacing|md` = 24px
- `var:preset|spacing|lg` = 32px
- `var:preset|spacing|xl` = 48px

### 7. Couleurs

Utiliser les presets de couleur du thème :
- `textColor: "primary"` → bordeaux
- `textColor: "secondary"` → violet
- `textColor: "text-dark"` → noir
- `textColor: "text-gray"` → gris foncé
- `textColor: "text-muted"` → gris clair
- `backgroundColor: "muted"` → gris très clair
- `backgroundColor: "white"` → blanc

### 8. Styles de tags

Utiliser les styles prédéfinis pour les tags :
- `is-style-tag-primary` → rouge
- `is-style-tag-secondary` → violet
- `is-style-tag-gray` → gris
- `is-style-tag-success` → vert

### 9. CSS custom

Le CSS custom ne doit servir qu'aux effets impossibles avec les blocs natifs :
- Avatars empilés (margin négatif)
- Hover effects (shadow, transform)
- Pseudo-éléments (icônes SVG)
- Responsive spécifique

### 10. Responsive

Le responsive des Colonnes est automatique. Pour le CSS custom :
```css
@media (max-width: 900px) { /* Tablette */ }
@media (max-width: 600px) { /* Mobile */ }
```

## Checklist avant commit

- [ ] Pas d'erreur "tentative de récupération" dans l'éditeur
- [ ] Colonnes natives pour les layouts variables
- [ ] Padding sur les sections (xl vertical, md horizontal)
- [ ] Variables de couleur/spacing du thème utilisées
- [ ] Attributs JSON cohérents avec le HTML
- [ ] Images sans width/height en pixels
