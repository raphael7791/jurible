# Bloc Méthode - Spécifications

> Document temporaire pour le développement du bloc "Découvrez notre méthode"

## Objectif

Créer un bloc interactif avec 7 onglets permettant aux visiteurs de prévisualiser les différents types de contenus disponibles sur la plateforme AideAuxTD.

---

## Structure des onglets

| # | Onglet | Contenu | Dynamique ? | Bandeau exemple |
|---|--------|---------|-------------|-----------------|
| 1 | Vidéo | Lecteur YouTube/Vimeo | ✅ Oui (URL ACF) | ❌ Non |
| 2 | Cours écrits | Exemple de leçon fixe | ❌ Non | ✅ "Voici un exemple en droit constitutionnel" |
| 3 | Mindmap | Image fixe de carte mentale | ❌ Non | ✅ "Voici un exemple en droit constitutionnel" |
| 4 | QCM | Bloc QCM existant (1-2 questions) | ❌ Non | ✅ "Voici un exemple en droit constitutionnel" |
| 5 | Flashcards | Bloc Flashcard existant (1 carte) | ❌ Non | ✅ "Voici un exemple en droit constitutionnel" |
| 6 | Annales | Exemple d'annale corrigée | ❌ Non | ✅ "Voici un exemple en droit constitutionnel" |
| 7 | Fiche vidéo | Plugin playlist reader (1ère vidéo) | ❌ Non | ✅ "Voici un exemple en droit constitutionnel" |

---

## Détail par onglet

### 1. Vidéo (dynamique)
- **Champ ACF** : `methode_video_url` (URL YouTube ou Vimeo)
- **Layout** : Split (vidéo à gauche, description à droite)
- **Fonctionnalité** : Embed iframe responsive

### 2. Cours écrits (fixe)
- **Contenu** : Leçon "Les composantes de l'État" en droit constitutionnel
- **Éléments** :
  - Titre de la leçon
  - Métadonnées (matière, numéro de leçon)
  - Blocs définition (bordure violette)
  - Blocs attention (bordure rouge)
  - Texte avec paragraphes, H4, H5
- **Effet** : Fade en bas avec "Scrollez pour voir la suite"
- **Bandeau** : "Voici un exemple en droit constitutionnel"

### 3. Mindmap (fixe)
- **Contenu** : Image statique d'une carte mentale
- **Layout** : Split (image à gauche, description à droite)
- **Image** : SVG ou PNG de la mindmap "Les composantes de l'État"
- **Bandeau** : "Voici un exemple en droit constitutionnel"

### 4. QCM (fixe)
- **Intégration** : Réutiliser le bloc QCM existant du plugin
- **Contenu** : 1-2 questions d'exemple sur le droit constitutionnel
- **Interactivité** : Sélection de réponse + feedback correct/incorrect
- **Bandeau** : "Voici un exemple en droit constitutionnel"

### 5. Flashcards (fixe)
- **Intégration** : Réutiliser le bloc Flashcard existant du plugin
- **Contenu** : 1 carte d'exemple (question/réponse)
- **Interactivité** : Flip de la carte au clic
- **Bandeau** : "Voici un exemple en droit constitutionnel"

### 6. Annales (fixe)
- **Contenu** : Dissertation "Le régime britannique"
- **Éléments** :
  - Titre + tag matière
  - Métadonnées (durée)
  - Énoncé du sujet
  - Mini-player vidéo correction
  - Plan/sommaire de la correction
  - Début de la correction rédigée
- **Effet** : Fade en bas
- **Bandeau** : "Voici un exemple en droit constitutionnel"

### 7. Fiche vidéo (fixe)
- **Intégration** : Plugin playlist reader existant
- **Layout** : Split (vidéo à gauche, playlist à droite)
- **Contenu** : Afficher uniquement la 1ère vidéo disponible
- **Playlist** : Afficher les autres vidéos verrouillées (icône cadenas)
- **Bandeau** : "Voici un exemple en droit constitutionnel"

---

## Design

### Couleurs (variables CSS existantes)
- `--bordeaux: #B0001D`
- `--rouge: #DC2626`
- `--violet: #7C3AED`
- `--bg-section: #F6F5FF`
- `--green: #10B981`

### Composants UI
- **Onglets** : Pills avec icônes, état actif en gradient
- **Card preview** : Fond blanc, border-radius 20px, shadow légère
- **Bandeau exemple** : Badge vert clair "Voici un exemple en droit constitutionnel"
- **Boutons** : Style gradient Jurible

### Responsive
- Desktop : Layout split (visuel + texte)
- Tablet : Stack vertical, tabs scrollables
- Mobile : Tabs en icônes uniquement

---

## Fichiers de référence

- **Mockup HTML** : `~/Downloads/P08-bloc-methode-desktop (1).html`
- **Blocs existants** : `~/Code/jurible/plugins/jurible-blocks-react/src/`
- **CSS existant** : `~/Code/jurible/themes/jurible/assets/css/`

---

## TODO

- [ ] Décider : Bloc React ou Pattern PHP ?
- [ ] Créer la structure du bloc
- [ ] Intégrer les blocs existants (QCM, Flashcard, Playlist)
- [ ] Ajouter le champ ACF pour l'URL vidéo
- [ ] Implémenter le système d'onglets
- [ ] Ajouter le bandeau "exemple" sur les onglets fixes
- [ ] Responsive design
- [ ] Tests sur le template single-course.html
