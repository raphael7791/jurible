# Jurible - Monorepo WordPress FSE

Documentation complète du projet WordPress Full Site Editing pour jurible.com et ecole.jurible.com.

---

## 📁 Structure du projet

```
jurible/
├── themes/
│   ├── jurible/              ← Thème parent (utilisé par les 2 sites)
│   │   ├── theme.json        ← Design tokens (couleurs, typos, espacements)
│   │   ├── style.css         ← Métadonnées du thème
│   │   ├── functions.php     ← Code PHP commun
│   │   ├── assets/css/       ← CSS custom (hover, animations)
│   │   ├── templates/        ← Structure des pages (home, single, archive...)
│   │   ├── parts/            ← Morceaux réutilisables (header, footer)
│   │   └── patterns/         ← Assemblages de blocs
│   │
│   └── ecole.jurible/        ← Thème enfant (espace membre uniquement)
│       ├── style.css         ← Déclare le parent
│       ├── functions.php     ← Code spécifique Fluent Community
│       └── assets/css/
│           └── jurible-design-system.css
│
└── plugins/
    ├── academic-generator/
    ├── jurible-assessments/
    ├── jurible-blocks-react/  ← Blocs Gutenberg custom (React)
    ├── jurible-flashcards/
    └── jurible-playlist/
```

---

## 🌐 Architecture des sites

| Site | URL | Thème actif | Plugins custom |
|------|-----|-------------|----------------|
| Site principal | jurible.com | `jurible` | Aucun |
| Espace membre | ecole.jurible.com | `ecole.jurible` | Tous |

### Principe du thème parent/enfant

- **Thème parent `jurible`** : Design system, blocs, patterns, templates communs
- **Thème enfant `ecole.jurible`** : Hérite du parent + code spécifique Fluent Community

Le thème enfant charge automatiquement le `functions.php` du parent, puis ajoute le sien par-dessus.

---

## 💻 Environnement local (Mac)

### Dossiers

| Chemin | Contenu |
|--------|---------|
| `~/Code/jurible/` | Repo Git local (source de vérité pour le code) |
| `~/Local Sites/jurible-local/` | Site principal en local |
| `~/Local Sites/ecole-jurible-local/` | Espace membre en local |

### Liens symboliques

Les sites Local by Flywheel pointent vers `~/Code/jurible/` via des liens symboliques. Toute modification dans le repo est visible instantanément sur les sites locaux.

```bash
# Vérifier les liens
ls -la ~/Local\ Sites/jurible-local/app/public/wp-content/themes/
ls -la ~/Local\ Sites/ecole-jurible-local/app/public/wp-content/themes/
ls -la ~/Local\ Sites/ecole-jurible-local/app/public/wp-content/plugins/
```

---

## 🖥️ Serveur (O2switch)

### Connexion SSH

```bash
ssh aideauxtd@dogfish.o2switch.net
```

### Dossiers sur le serveur

| Chemin | Contenu |
|--------|---------|
| `~/jurible-repo/` | Clone du repo GitHub |
| `~/jurible.com/` | Site principal (WordPress) |
| `~/ecole.jurible.com/` | Espace membre (WordPress) |

---

## 🔄 Workflow de développement

### 1. Modifier le code

Éditer les fichiers dans `~/Code/jurible/` sur ton Mac (avec VS Code, Claude Code, etc.)

### 2. Tester en local

Rafraîchir `jurible-local.local` ou `ecole-jurible-local.local` pour voir les changements.

### 3. Sauvegarder sur GitHub

```bash
cd ~/Code/jurible
git add .
git commit -m "Description de la modification"
git push
```

### 4. Déployer sur les serveurs live

```bash
# Se connecter
ssh aideauxtd@dogfish.o2switch.net

# Récupérer les modifications
cd ~/jurible-repo
git pull

# Copier vers les sites
cp -r themes/jurible ~/jurible.com/wp-content/themes/
cp -r themes/jurible ~/ecole.jurible.com/wp-content/themes/
cp -r themes/ecole.jurible ~/ecole.jurible.com/wp-content/themes/

# Si tu as modifié des plugins aussi :
cp -r plugins/jurible-blocks-react ~/ecole.jurible.com/wp-content/plugins/
# etc.
```

---

## 🎨 Créer son Design System FSE

### Ordre de création

1. **`theme.json`** — Design tokens (couleurs, typos, espacements, ombres)
2. **`assets/css/`** — Styles globaux, hover, animations, responsive
3. **Block Styles** — Variations sur blocs natifs (`functions.php` + CSS)
4. **Custom Blocks** — Blocs impossibles avec les natifs (`plugins/jurible-blocks-react/`)
5. **Patterns** — Assemblages de blocs (`patterns/`)
6. **Template Parts** — Header, footer (`parts/`)
7. **Templates** — Pages complètes (`templates/`)

### Inventaire des composants

Avant de coder, lister tous les composants de ta maquette Figma :

| Composant | Type | Fichier |
|-----------|------|---------|
| Couleurs, typos, espacements | Design tokens | `theme.json` |
| Bouton outline, ghost | Block Style | `functions.php` + `assets/css/` |
| Header | Template Part | `parts/header.html` |
| Footer | Template Part | `parts/footer.html` |
| Hero | Pattern | `patterns/hero.php` |
| Card article | Pattern | `patterns/card-article.php` |
| Flashcard interactive | Custom Block | `plugins/jurible-blocks-react/` |

---

## 🧱 Blocs Gutenberg Custom

### Structure d'un bloc (jurible-blocks-react)

```
plugins/jurible-blocks-react/
├── src/
│   └── mon-bloc/
│       ├── block.json      ← Métadonnées
│       ├── index.js        ← Code React (éditeur)
│       ├── edit.js         ← Composant d'édition
│       ├── save.js         ← Rendu sauvegardé
│       ├── view.js         ← Interactivité front (si besoin)
│       ├── editor.scss     ← Styles éditeur
│       └── style.scss      ← Styles front
└── build/                   ← Fichiers compilés
```

### Compiler les blocs

```bash
cd ~/Code/jurible/plugins/jurible-blocks-react
npm install
npm run build
```

### Blocs dans Fluent Community

Les blocs standards sont rendus compatibles Fluent via le `functions.php` du thème enfant :

1. `fluent_community/allowed_block_types` — Autoriser le bloc
2. `fluent_community/block_editor_footer` — Charger le JS
3. `fluent_community/portal_head` — Charger le CSS et les scripts view.js

---

## 📝 Ce qui est versionné vs pas versionné

| Élément | Versionné (Git) | Stockage |
|---------|-----------------|----------|
| `theme.json` | ✅ Oui | Fichier |
| Patterns | ✅ Oui | Fichier |
| Templates | ✅ Oui | Fichier |
| Template Parts | ✅ Oui | Fichier |
| Custom Blocks | ✅ Oui | Fichier |
| **Contenu des pages/articles** | ❌ Non | Base de données |
| **Médias uploadés** | ❌ Non | `wp-content/uploads/` |
| **Modifications via l'éditeur de site** | ❌ Non | Base de données |

### ⚠️ Attention aux modifications dans l'éditeur de site

Si tu modifies un template/part via l'éditeur WordPress (Apparence → Éditeur), ça s'enregistre en base de données et écrase la version fichier. Pour garder la synchro Git :

- Soit tu ne touches jamais aux templates dans l'éditeur
- Soit tu exportes tes modifications vers les fichiers après

---

## 🛠️ Commandes utiles

### Git

```bash
# Voir les modifications en cours
git status

# Voir l'historique
git log --oneline

# Annuler les modifications non commitées
git checkout .

# Créer une branche pour une feature
git checkout -b feature/nouvelle-fonctionnalite

# Revenir sur main
git checkout main

# Merger une branche
git merge feature/nouvelle-fonctionnalite
```

### Serveur

```bash
# Voir les thèmes installés
ls -la ~/ecole.jurible.com/wp-content/themes/

# Voir les plugins installés
ls -la ~/ecole.jurible.com/wp-content/plugins/

# Voir le contenu d'un fichier
cat ~/ecole.jurible.com/wp-content/themes/jurible/theme.json
```

### Local (Mac)

```bash
# Recréer un lien symbolique
ln -s ~/Code/jurible/themes/jurible ~/Local\ Sites/jurible-local/app/public/wp-content/themes/jurible

# Supprimer un lien symbolique
rm ~/Local\ Sites/jurible-local/app/public/wp-content/themes/jurible
```

---

## 📚 Ressources

- [Documentation theme.json](https://developer.wordpress.org/themes/global-settings-and-styles/)
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Full Site Editing](https://fullsiteediting.com/)
- [Capitaine WP - Formation FSE](https://capitainewp.io/formations/wordpress-full-site-editing/)# jurible
