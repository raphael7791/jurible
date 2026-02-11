# Statut des Patterns Jurible

> Derniere mise a jour : Fevrier 2026

## Resume

| Categorie | Prevu | Realise | Statut |
|-----------|-------|---------|--------|
| Hero | 15 | 15 | ✅ Complet |
| Contenu | 14 | 14 | ✅ Complet |
| Commerce | 12 | 10 | 🟡 2 manquants |
| Confiance | 5 | 5 | ✅ Complet |
| Marketing | 12 | 12 | ✅ Complet |
| Equipe | 6 | 6 | ✅ Complet |
| Structure | 6 | 4 | 🟡 2 manquants |
| **Total** | **70** | **66** | **4 a faire** |

---

## Patterns a creer (4)

### Commerce
- [ ] `commerce/11-pricing-prepa.php` — Maquette: `commerce-11-pricing-prepa.html`
- [ ] `commerce/12-pricing-fiches.php` — Maquette: `commerce-12-pricing-fiches.html`

### Structure
- [ ] `structure/01-formulaire-contact.php` — Maquette: `structure-01-formulaire-contact.html` (necessite plugin formulaire)
- [ ] `structure/05-catalogue-matieres.php` — Maquette: `structure-07-catalogue-matieres.html` (complexe: tabs JS + filtrage taxonomie)

---

## Variantes bonus (non prevues dans le brief)

Ces variantes ont ete creees en plus du brief initial:

| Fichier | Description |
|---------|-------------|
| `commerce/09-pricing-comparatif-homepage.php` | Variante homepage du comparatif |
| `confiance/05-temoignages-gris.php` | Temoignages sur fond gris |
| `marketing/03-solution-4.php` | Grille 4 cartes solution |
| `marketing/03-solution-4-gris.php` | Grille 4 cartes solution fond gris |
| `marketing/03-solution-6.php` | Grille 6 cartes solution |
| `equipe/06-citation-hero.php` | Citation hero 2 colonnes (photo + quote) |

---

## Inventaire complet par categorie

### Hero (15 patterns)
```
hero/01-conversion-homepage.php
hero/02-conversion-academie.php
hero/03-conversion-prepa.php
hero/04-conversion-suite-ia.php
hero/05-archive-blog.php
hero/06-archive-cours.php
hero/07-archive-fiches.php
hero/08-archive-search.php
hero/09-produit-cours.php
hero/10-produit-support.php
hero/11-simple-about.php
hero/12-simple-contact.php
hero/13-simple-faq.php
hero/14-simple-legal.php
hero/15-article.php
```

### Contenu (14 patterns)
```
contenu/01-paragraphe-standard.php
contenu/02-paragraphe-card.php
contenu/03-paragraphe-gris.php
contenu/04-paragraphe-minimal.php
contenu/05-texte-image.php
contenu/06-image-texte.php
contenu/07-texte-video.php
contenu/08-chiffres-cles.php
contenu/09-grille-matieres.php
contenu/10-programme.php
contenu/11-sommaire.php
contenu/12-stats-sommaire.php
contenu/13-stats.php
contenu/14-methode-onglets.php
```

### Commerce (10 patterns + 2 a faire)
```
commerce/01-pricing-academie.php
commerce/02-offre-suite-ia.php
commerce/03-cta-cross-sell.php
commerce/04-quelle-offre.php
commerce/05-produits-complementaires.php
commerce/06-cta-final-basique.php
commerce/07-cta-final-promo.php
commerce/08-cta-final-urgence.php
commerce/09-pricing-comparatif.php
commerce/09-pricing-comparatif-homepage.php (bonus)
commerce/10-pricing-suite-ia.php
# A FAIRE:
# commerce/11-pricing-prepa.php
# commerce/12-pricing-fiches.php
```

### Confiance (5 patterns + 1 bonus)
```
confiance/01-reassurance-full.php
confiance/02-reassurance-minimal.php
confiance/03-logos-partenaires.php
confiance/04-faq.php
confiance/05-temoignages.php
confiance/05-temoignages-gris.php (bonus)
```

### Marketing (12 patterns + 3 bonus)
```
marketing/01-pain-points-4.php
marketing/02-pain-points-6.php
marketing/03-solution-4.php (bonus)
marketing/03-solution-4-gris.php (bonus)
marketing/03-solution-6.php (bonus)
marketing/04-features-6.php
marketing/05-features-4.php
marketing/06-features-detailed.php
marketing/07-features-incluses.php
marketing/08-steps.php
marketing/09-comparaison-cards.php
marketing/10-comparaison-avant-apres.php
marketing/11-comparaison-multi.php
marketing/12-comparaison-dark.php
```

### Equipe (7 patterns)
```
equipe/01-enseignants-grille.php
equipe/02-enseignant-matiere.php
equipe/03-enseignants-teaser.php
equipe/04-enseignants-video.php
equipe/05-bio-auteur.php
equipe/06-citation.php
equipe/06-citation-hero.php (bonus)
```

### Structure (4 patterns + 2 a faire)
```
structure/01-article-featured.php
structure/02-articles-grid.php
structure/04-articles-lies.php
structure/06-page-404.php
# A FAIRE:
# structure/01-formulaire-contact.php (renumeroter apres creation)
# structure/05-catalogue-matieres.php
```

---

## Notes techniques

### Patterns dynamiques (Query Loop)
- `structure/01-article-featured.php` — queryId:1, sticky:only
- `structure/02-articles-grid.php` — queryId:2, sticky:exclude
- `structure/04-articles-lies.php` — queryId:3, filtre par categorie

### Blocs custom utilises
- `jurible/card-testimonial` — Temoignages
- `jurible/solution-card` — Cartes solution
- `jurible/card-cours` — Cartes catalogue cours
- `jurible/citation` — Citations

### CSS dedies
Les patterns suivants ont leur propre fichier CSS:
- `structure-article-featured.css`
- `structure-articles-grid.css`
- `structure-articles-lies.css`
- `structure-page-404.css`
