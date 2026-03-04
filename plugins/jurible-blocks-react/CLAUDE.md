# Jurible Blocks React - Contexte

## Rôle

Plugin WordPress contenant tous les blocs Gutenberg custom de Jurible, développés en React avec `@wordpress/scripts`.

## Structure d'un bloc

Chaque bloc dans `src/` suit cette structure :
```
src/nom-du-bloc/
├── block.json      # Métadonnées (nom, attributs, supports)
├── index.js        # Point d'entrée, registerBlockType()
├── edit.js         # Composant React pour l'éditeur
├── save.js         # Rendu HTML côté front (ou null si dynamique)
├── style.scss      # Styles front-end
└── editor.scss     # Styles éditeur uniquement
```

## Blocs disponibles (28)

**UI Components** : alert, badge-trust, bouton, citation, infobox, breadcrumb
**Cards** : card-cours, card-formule-reussite, card-pricing-suite-ia, card-produits-comparatif, card-testimonial, solution-card
**Checkout** : checkout-included, checkout-reassurance, checkout-social-proof, checkout-testimonial
**Navigation** : lien-lecon, sommaire, step-indicator
**Marketing** : cta-banner, hero-dashboard, newsletter, pricing-duration-selector
**Pédagogie** : assessment, flashcards, method-tabs, playlist
**Layout** : footer-accordion

## Commandes

```bash
# Développement (watch mode)
npm run start

# Build production
npm run build

# Lint
npm run lint:js
npm run lint:css
```

## Conventions

- Nom du bloc : `jurible/nom-en-kebab-case`
- Préfixe CSS : `.wp-block-jurible-nom-du-bloc`
- Attributs : camelCase dans block.json
- Toujours définir `supports` dans block.json (align, color, spacing...)

## Créer un nouveau bloc

1. Créer le dossier `src/nouveau-bloc/`
2. Créer les fichiers (block.json, index.js, edit.js, save.js, style.scss)
3. Le bloc est auto-détecté grâce au flag `--blocks-manifest`
4. Lancer `npm run build`

## Pièges courants

- Oublier de rebuild après modif → `npm run build`
- Attributs non déclarés dans block.json → erreur silencieuse
- Modifier `build/` directement → écrasé au prochain build
