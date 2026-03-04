# Thème Ecole Jurible (Enfant) - Contexte

## Rôle

Thème enfant WordPress pour **ecole.jurible.com** (espace membre payant).
Hérite de tout le thème parent `jurible` et ajoute/surcharge uniquement ce qui est spécifique à l'espace membre.

## Principe d'héritage

Le thème enfant hérite automatiquement :
- `theme.json` (design tokens)
- `functions.php` (le parent est chargé AVANT l'enfant)
- `templates/` et `parts/` (si non surchargés)
- `patterns/`

## Structure actuelle

```
themes/ecole.jurible/
├── style.css           # Obligatoire : déclare le thème enfant
├── functions.php       # Code PHP spécifique à l'espace membre
└── assets/
    └── css/            # Styles spécifiques
```

## Surcharger un élément du parent

Pour **modifier un template** du parent :
1. Copier le fichier depuis `themes/jurible/templates/`
2. Le coller dans `themes/ecole.jurible/templates/`
3. Modifier la copie

Pour **modifier le header/footer** :
1. Créer `themes/ecole.jurible/parts/header.html`
2. WordPress utilisera cette version au lieu de celle du parent

## Quand modifier ce thème

- Personnalisation de l'interface membre (header différent, navigation...)
- Intégration spécifique Fluent Community
- Styles ou fonctionnalités uniquement pour les membres

## Quand modifier le thème PARENT

- Tout ce qui doit apparaître sur les deux sites
- Design system global (couleurs, typos)
- Blocs et patterns réutilisables

## Pièges courants

- Dupliquer du code déjà dans le parent → utiliser l'héritage
- Modifier le parent pour du code spécifique membre → mettre ici
- Oublier `Template: jurible` dans style.css → thème cassé
