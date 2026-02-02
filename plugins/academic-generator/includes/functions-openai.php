<?php
/**
 * Gestion de l'API OpenAI
 * Fonctions centralisées pour appeler GPT-4o-mini
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// APPEL API OPENAI PRINCIPAL
// ============================================================================

/**
 * Appeler l'API OpenAI pour générer du contenu juridique
 * 
 * @param string $prompt Le prompt complet à envoyer
 * @param string $type_generation Type de document ('fiche', 'dissertation', etc.)
 * @return array ['succes' => string] ou ['erreur' => string]
 */
function aga_appeler_openai($prompt, $type_generation = 'fiche') {
    // Vérifier que la clé API est définie
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        return array('erreur' => 'Clé API OpenAI non configurée. Veuillez ajouter OPENAI_API_KEY dans wp-config.php');
    }
    
    // Préparer les données pour l'API
    $data = array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array(
                'role' => 'user',
                'content' => $prompt
            )
        ),
        'max_completion_tokens' => 3500,
        'temperature' => 0.3,
        'frequency_penalty' => 0.2,
        'presence_penalty' => 0.1
    );
    
    // Configurer l'appel HTTP
    $args = array(
        'body' => json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . OPENAI_API_KEY
        ),
        'timeout' => 90
    );
    
    // Effectuer l'appel API
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
    
    // Vérifier les erreurs HTTP
    if (is_wp_error($response)) {
        return array('erreur' => 'Erreur de connexion : ' . $response->get_error_message());
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return array('erreur' => 'Erreur API (Code ' . $response_code . ') : ' . wp_remote_retrieve_body($response));
    }
    
    // Décoder la réponse
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        return array('erreur' => 'Réponse API invalide');
    }

    $contenu = trim($result['choices'][0]['message']['content']);

    // Vérifier si c'est une erreur de contenu non juridique
    if (strpos($contenu, 'ERREUR_NON_JURIDIQUE') === 0) {
        return array('erreur' => 'Le texte fourni ne semble pas être un document juridique. Veuillez vérifier votre saisie.');
    }
    
    if (empty($contenu)) {
        return array('erreur' => 'Contenu vide reçu de l\'API');
    }
    
    // POST-TRAITEMENT : Corriger les erreurs récurrentes
    $contenu = aga_post_traiter_contenu($contenu);
    
    return array('succes' => $contenu);
}

// ============================================================================
// POST-TRAITEMENT DU CONTENU GÉNÉRÉ
// ============================================================================

/**
 * Post-traiter le contenu généré pour corriger les erreurs récurrentes
 */
function aga_post_traiter_contenu($contenu) {
    // Corrections des formulations juridiques standard
    $corrections = array(
        // Juridictions suprêmes françaises
        '/(?<![Ll]a )\bCour de cassation\b/' => 'La Cour de cassation',
        '/\bcour de cassation\b/' => 'la Cour de cassation',
        '/(?<![Ll]e )\bConseil d\'État\b/' => 'Le Conseil d\'État',
        '/\bconseil d\'état\b/i' => 'le Conseil d\'État',
        '/(?<![Ll]e )\bConseil constitutionnel\b/' => 'Le Conseil constitutionnel',
        '/\bconseil constitutionnel\b/' => 'le Conseil constitutionnel',
        
        // Juridictions européennes
        '/(?<![Ll]a )\bCour de justice de l\'Union européenne\b/' => 'La Cour de justice de l\'Union européenne',
        '/\bcour de justice de l\'union européenne\b/i' => 'la Cour de justice de l\'Union européenne',
        '/(?<![Ll]a )\bCJUE\b/' => 'La CJUE',
        '/(?<![Ll]a )\bCour européenne des droits de l\'homme\b/' => 'La Cour européenne des droits de l\'homme',
        '/\bcour européenne des droits de l\'homme\b/i' => 'la Cour européenne des droits de l\'homme',
        '/(?<![Ll]a )\bCEDH\b/' => 'La CEDH',
        
        // Haute juridiction (terme générique)
        '/(?<![Ll]a )\bHaute juridiction\b/' => 'La Haute juridiction',
        '/\bhaute juridiction\b/' => 'la Haute juridiction',
        
        // Autres juridictions en début de phrase
        '/^Tribunal judiciaire/m' => 'Le tribunal judiciaire',
        '/^Tribunal administratif/m' => 'Le tribunal administratif',
        '/^Tribunal de commerce/m' => 'Le tribunal de commerce',
        '/^Cour d\'appel/m' => 'La cour d\'appel',
        '/^Cour administrative d\'appel/m' => 'La cour administrative d\'appel',
        
        // Corrections procédurales (seule la Cour de cassation et le Conseil d'État "cassent")
        '/(?:la cour d\'appel|La cour d\'appel)[^.]*a cassé/' => 'la cour d\'appel a infirmé',
        '/(?:le tribunal|Le tribunal)[^.]*a cassé/' => 'le tribunal a infirmé',
        
        // Nettoyage formatage résiduel
        '/\*\*([^*]+)\*\*/' => '$1', // Enlever **texte**
        '/#{1,4}\s*/' => '', // Enlever # ## ### ####
        //'/\s+/' => ' ', // Double espaces
        '/\n{3,}/' => "\n\n", // Triple retours à la ligne
    );
    
    foreach ($corrections as $pattern => $remplacement) {
        $contenu = preg_replace($pattern, $remplacement, $contenu);
    }

    // Améliorer les retours à la ligne pour la lisibilité
    $contenu = preg_replace('/\b(En première instance[^.]*\.)\s*/', '$1' . "\n\n", $contenu);
    $contenu = preg_replace('/\b(Par un arrêt[^.]*\.)\s*/', '$1' . "\n\n", $contenu);
    $contenu = preg_replace('/\b(C\'est dans ces conditions[^.]*\.)\s*/', '$1' . "\n\n", $contenu);
    $contenu = preg_replace('/\b(La [Cc]our de cassation [^.]*\.)\s*/', '$1' . "\n\n", $contenu);
    $contenu = preg_replace('/\b(Elle juge que[^.]*\.)\s*/', '$1' . "\n\n", $contenu);
    $contenu = preg_replace('/\b(La Haute juridiction[^.]*\.)\s*/', '$1' . "\n\n", $contenu);
    
    // Nettoyer les triples sauts de ligne
    $contenu = preg_replace('/\n{3,}/', "\n\n", $contenu);

    return trim($contenu);
}

// ============================================================================
// CONSTRUCTION DES PROMPTS PAR TYPE DE DOCUMENT
// ============================================================================

/**
 * Construire le prompt pour une fiche d'arrêt
 */
function aga_construire_prompt_fiche_arret($references, $matiere, $contenu) {
    $prompt = <<<PROMPT
Tu es professeur de droit français. Rédige une fiche d'arrêt académique selon la méthodologie universitaire française exacte.

IMPORTANT : Analyse UNIQUEMENT le texte entre ===DEBUT_ARRET=== et ===FIN_ARRET===. 
Si ce n'est PAS un arrêt de justice français, réponds uniquement : "ERREUR_NON_JURIDIQUE: Ce texte n'est pas un arrêt de justice"

===DEBUT_ARRET===
{$contenu}
===FIN_ARRET===

CONTRAINTES IMPÉRATIVES :
- Style RÉDACTIONNEL FLUIDE uniquement (jamais de listes, tirets, puces)
- Français juridique précis et élégant
- Paragraphes séparés par une ligne vide
- AUCUN formatage (pas de **, #, ##, tirets)
- Texte brut uniquement
- OBLIGATOIRE : Rédiger les 4 sections complètes
- Respecte la taille de 1200 mots maximum au total
- Séparer obligatoirement par des retours à la ligne les étapes de procédure et les arguments de solution
- ❌ INTERDICTION : Ne jamais répéter les références dans les sections de contenu
- ❌ INTERDICTION : Ne jamais utiliser le verbe "stipuler" pour une loi/un article/un texte/un code
- ✅ OBLIGATION : "stipuler" uniquement pour les contrats (actes bilatéraux entre deux parties)
- ✅ Pour les textes juridiques, utiliser : "prévoir", "disposer", "énoncer", "établir", "prescrire", "définir"
- Exemples CORRECTS : "L'article 1240 du Code civil dispose que", "La loi prévoit que", "Le texte énonce que"
- Exemples INTERDITS : "L'article stipule", "La loi stipule", "Le Code civil stipule"

STRUCTURE OBLIGATOIRE AVEC MARQUEURS :

===FAITS===
[Rédiger les faits en 100-150 mots. Qualifier juridiquement, pas de noms propres. 
INTERDICTION : Ne jamais commencer par "En l'espèce", "En la circonstance" ou formules similaires. 
Commencer directement par les faits.]

===PROCEDURE===
[Rédiger la procédure en 200-250 mots avec la structure : première instance → appel → pourvoi]

===PROBLEME===
[Une seule phrase interrogative permettant une réponse oui/non]

===SOLUTION===
[Rédiger la solution en 200-250 mots. Si cassation : "La Cour casse..." Si rejet : "La Cour rejette..."]. Pense à mettre le texte juridique (le visa) concerné par la solution si nécessaire.

Références : {$references}
Matière : {$matiere}

Rédige la fiche en respectant EXACTEMENT cette structure avec les marqueurs ===XXX===
PROMPT;

    return $prompt;
}

/**
 * Construire le prompt pour un PLAN DÉTAILLÉ
 */
function aga_construire_prompt_plan_detaille($sujet, $matiere, $niveau = 'L3', $indications = '') {
    $prompt = "Tu es professeur de droit français spécialisé en {$matiere}.\n\n";
    $prompt .= "⚠️ IMPÉRATIF ABSOLU : Le sujet de cette dissertation est « {$sujet} ». Tu dois traiter UNIQUEMENT et EXCLUSIVEMENT ce sujet précis. Ne traite AUCUN autre sujet.\n\n";
    $prompt .= "⚠️ CRUCIAL : Adapte tout le contenu à {$matiere}. Jurisprudences et concepts spécifiques à cette branche du droit.\n\n";
    $prompt .= "SUJET À TRAITER : {$sujet}\n";
    $prompt .= "MATIÈRE : {$matiere}\n";
    $prompt .= "LONGUEUR : 800-1000 mots\n\n";
    
    if ($indications) {
        $prompt .= "INDICATIONS : {$indications}\n\n";
    }
    
    $prompt .= <<<PROMPT

❌ INTERDICTION ABSOLUE : Utiliser le verbe "stipuler" pour une loi/un article/un texte/un code
✅ OBLIGATION : "stipuler" uniquement pour les contrats (actes bilatéraux entre deux parties)
✅ Pour les textes juridiques, utiliser : "prévoir", "disposer", "énoncer", "établir", "prescrire", "définir"
Exemples CORRECTS : "L'article 1240 du Code civil dispose que", "La loi prévoit que"
Exemples INTERDITS : "L'article stipule", "La loi stipule"

STRUCTURE OBLIGATOIRE :

Introduction

IMPORTANT : Chaque étape doit commencer par son nom entre parenthèses suivi du texte.

Exemple de format attendu :
(Accroche) [ton texte d'accroche ici...]
(Définitions) [tes définitions ici...]
(Intérêts / Impératifs) [le contexte ici...]
(Problématique) [ta question ici...]
(Annonce de plan) [ton annonce ici...]

Rédige l'introduction sur « {$sujet} » en respectant CE FORMAT EXACT :

(Accroche) [Accroche pertinente sur « {$sujet} »]

(Définitions) [Définitions des termes clés de « {$sujet} »]

(Intérêts / Impératifs) [Contexte et enjeux de « {$sujet} »]

(Problématique) [Question interrogative sur « {$sujet} »]

(Annonce de plan) [Annonce naturelle. Ex: "Si X demeure Y (I), il convient d'analyser Z (II)"]

I. [Titre problématisé sur {$sujet}]

A. [Sous-titre problématisé sur {$sujet}]

- Argument 1 avec exemple juridique en {$matiere} sur {$sujet}
- Argument 2 avec jurisprudence pertinente sur {$sujet}
- Argument 3 avec doctrine sur {$sujet}

B. [Sous-titre problématisé sur {$sujet}]

- Argument 1 sur {$sujet}
- Argument 2 sur {$sujet}
- Argument 3 sur {$sujet}

II. [Titre problématisé sur {$sujet}]

A. [Sous-titre problématisé sur {$sujet}]

- Argument 1 sur {$sujet}
- Argument 2 sur {$sujet}
- Argument 3 sur {$sujet}

B. [Sous-titre problématisé sur {$sujet}]

- Argument 1 sur {$sujet}
- Argument 2 sur {$sujet}
- Argument 3 sur {$sujet}
STOP ICI : Le dernier tiret (-) du II.B est la FIN du plan. Ne rien écrire après. Pas de conclusion, pas de phrase finale, RIEN du tout.

RÈGLES ABSOLUES :
- TRAITER UNIQUEMENT LE SUJET : « {$sujet} »
- Écrire les marqueurs : (Accroche), (Définitions), (Intérêts / Impératifs), (Problématique), (Annonce de plan)
- Guillemets français « » uniquement
- Titres PROBLÉMATISÉS adaptés à {$sujet}
- Tirets (-) pour arguments, PAS astérisques (*)
- Contenu en {$matiere} spécifiquement sur {$sujet}
- ❌ INTERDICTION : Ne JAMAIS traiter un autre sujet que « {$sujet} »
- ❌ INTERDICTION : Ne RIEN écrire après le dernier argument du II.B (pas de phrase de conclusion, pas de commentaire final, le texte s'arrête immédiatement après le dernier tiret)

Génère le plan détaillé sur « {$sujet} » maintenant.
PROMPT;

    return $prompt;
}

/**
 * Construire le prompt pour une DISSERTATION COMPLÈTE
 */
function aga_construire_prompt_dissertation_complete($sujet, $matiere, $niveau = 'L3', $indications = '') {
    $prompt = "Tu es professeur de droit français spécialisé en {$matiere}.\n\n";
    $prompt .= "⚠️ IMPÉRATIF ABSOLU : Le sujet de cette dissertation est « {$sujet} ». Tu dois traiter UNIQUEMENT et EXCLUSIVEMENT ce sujet précis. Ne traite AUCUN autre sujet.\n\n";
    $prompt .= "⚠️ CRUCIAL : Adapte tout à {$matiere}. Jurisprudences et concepts spécifiques à cette branche du droit.\n\n";
    $prompt .= "SUJET À TRAITER : {$sujet}\n";
    $prompt .= "MATIÈRE : {$matiere}\n";
    $prompt .= "LONGUEUR : 2500-3000 mots\n";
    $prompt .= "⚠️ IMPORTANT : Générer TOUTES les parties jusqu'à la fin du II.B inclus, avec transition après II.A\n\n";
    
    if ($indications) {
        $prompt .= "INDICATIONS : {$indications}\n\n";
    }
    
    $prompt .= <<<PROMPT

❌ INTERDICTION ABSOLUE : Utiliser le verbe "stipuler" pour une loi/un article/un texte/un code
✅ OBLIGATION : "stipuler" uniquement pour les contrats (actes bilatéraux)
✅ Pour les textes juridiques : "prévoir", "disposer", "énoncer", "établir", "prescrire", "définir"

STRUCTURE OBLIGATOIRE :

Introduction

IMPORTANT : Chaque étape doit commencer par son nom entre parenthèses suivi du texte.

Exemple de format attendu :
(Accroche) [ton texte d'accroche ici...]
(Définitions) [tes définitions ici...]
(Intérêts / Impératifs) [le contexte ici...]
(Problématique) [ta question ici...]
(Annonce de plan) [ton annonce ici...]

Rédige l'introduction sur « {$sujet} » en respectant CE FORMAT EXACT :

(Accroche) [Accroche pertinente sur « {$sujet} »]

(Définitions) [Définitions précises des termes clés de « {$sujet} »]

(Intérêts / Impératifs) [Contexte juridique et enjeux de « {$sujet} »]

(Problématique) [Question interrogative sur « {$sujet} »]

(Annonce de plan) [Annonce naturelle. Ex: "Si X demeure Y (I), il convient d'analyser Z (II)"]

I. [Titre problématisé sur {$sujet}]

IMPORTANT : Écris (Annonce de plan interne) suivi du texte.

(Annonce de plan interne) [Annonce naturelle. Ex: "Si X demeure Y (A), Z s'avère crucial (B)"]

A. [Sous-titre problématisé sur {$sujet}]

[3-4 paragraphes RÉDIGÉS avec arguments, jurisprudence en {$matiere} sur {$sujet}, exemples, analyse critique]

B. [Sous-titre problématisé sur {$sujet}]

[3-4 paragraphes RÉDIGÉS sur {$sujet}]

IMPORTANT : Écris (Transition) suivi du texte.

(Transition) [Phrase naturelle vers II. Ex: "Cependant, cette analyse de {$sujet} appelle à examiner..."]

II. [Titre problématisé sur {$sujet}]

(Annonce de plan interne) [Annonce naturelle avec A et B sur {$sujet}]

A. [Sous-titre problématisé sur {$sujet}]

[3-4 paragraphes RÉDIGÉS sur {$sujet}]

(Transition) [Phrase naturelle vers II.B sur {$sujet}]

B. [Sous-titre problématisé sur {$sujet}]

[3-4 paragraphes RÉDIGÉS sur {$sujet}]

⚠️ STOP ICI : Le dernier paragraphe du II.B est la FIN de la dissertation. Ne rien écrire après. Pas de transition, pas de conclusion, RIEN du tout.

⚠️ RÈGLES ABSOLUES :
- TRAITER UNIQUEMENT LE SUJET : « {$sujet} »
- Écrire les marqueurs : (Accroche), (Définitions), (Intérêts / Impératifs), (Problématique), (Annonce de plan), (Annonce de plan interne), (Transition)
- Paragraphes RÉDIGÉS, jamais de tirets
- Guillemets français « » uniquement
- Titres PROBLÉMATISÉS adaptés à {$sujet}
- Annonces naturelles : "Si X (A), alors Y (B)"
- Contenu en {$matiere} spécifiquement sur {$sujet}
- ❌ INTERDICTION ABSOLUE : Ne RIEN écrire après le dernier paragraphe du II.B (pas de transition, pas de conclusion, le texte s'arrête immédiatement)
- ❌ INTERDICTION : Ne JAMAIS traiter un autre sujet que « {$sujet} »

Génère la dissertation complète sur « {$sujet} » maintenant.
PROMPT;

    return $prompt;
}

/**
 * Construire le prompt pour un commentaire de texte
 */
function aga_construire_prompt_commentaire_texte($matiere, $auteur, $references_oeuvre, $texte, $type_generation) {
    $type_texte = ($type_generation === 'plan_detaille') ? 'PLAN DÉTAILLÉ UNIQUEMENT' : 'COMMENTAIRE COMPLET';
    $taille = ($type_generation === 'plan_detaille') ? '800-1000 mots' : '2500-3000 mots';
    
    $prompt = <<<PROMPT
Tu es professeur de droit français. Rédige un commentaire de texte juridique selon la méthodologie universitaire française exacte.

IMPORTANT : Analyse le texte suivant
Auteur : {$auteur}
Références : {$references_oeuvre}
Matière : {$matiere}
Type demandé : {$type_texte}

===DEBUT_TEXTE===
{$texte}
===FIN_TEXTE===

CONTRAINTES IMPÉRATIVES :
- Style RÉDACTIONNEL FLUIDE uniquement (jamais de listes, tirets, puces)
- Français juridique précis et académique
- Paragraphes séparés par une ligne vide
- AUCUN formatage (pas de **, #, ##, tirets)
- Texte brut uniquement
- Respecter EXACTEMENT la structure demandée avec les marqueurs
- Taille : {$taille}
- ❌ INTERDICTION : Ne jamais utiliser le verbe "stipuler" pour une loi/un article/un texte/un code
- ✅ OBLIGATION : "stipuler" uniquement pour les contrats (actes bilatéraux)
- ✅ Pour les textes juridiques : "prévoir", "disposer", "énoncer", "établir", "prescrire", "définir"

STRUCTURE OBLIGATOIRE AVEC MARQUEURS :

===INTRODUCTION===
[Introduction de 400-500 mots comprenant OBLIGATOIREMENT dans l'ordre :
1. Accroche sur l'auteur ou le contexte historique/juridique
2. Présentation de l'auteur et de l'œuvre
3. Contexte de rédaction du texte
4. Idée générale du texte
5. Problématique sous forme interrogative
6. Annonce de plan naturelle]

===PLAN===
PROMPT;

    if ($type_generation === 'plan_detaille') {
        $prompt .= <<<PROMPT
[Plan structuré avec titres + idées principales]

I. [Titre du I - première idée du texte]
Chapeau introductif
A. [Titre du A]
[3-4 idées principales en 2-3 phrases : explication du texte, citations]
B. [Titre du B]
[3-4 idées principales en 2-3 phrases : analyse, portée]

II. [Titre du II - seconde idée du texte]
Chapeau introductif
A. [Titre du A]
[3-4 idées principales en 2-3 phrases : critique, actualité]
B. [Titre du B]
[3-4 idées principales en 2-3 phrases : limites, évolutions]
PROMPT;
    } else {
        $prompt .= <<<PROMPT
[Développement complet]

I. [Titre du I - première idée du texte]
Chapeau introductif
A. [Titre du A]
[4 paragraphes : explication du passage, citations du texte, définitions]
Transition vers B
B. [Titre du B]
[4 paragraphes : analyse de la pensée de l'auteur, portée théorique]
Transition vers II

II. [Titre du II - seconde idée du texte]
Chapeau introductif
A. [Titre du A]
[4 paragraphes : critique de la thèse, actualité juridique]
Transition vers B
B. [Titre du B]
[4 paragraphes : limites, évolutions du droit, débats contemporains]
PROMPT;
    }

    $prompt .= <<<PROMPT

MÉTHODE D'ANALYSE :
- Citer le texte régulièrement (entre guillemets)
- Expliquer chaque citation utilisée
- Ne pas paraphraser : analyser
- Contextualiser historiquement
- Confronter avec le droit positif actuel

RÈGLES SPÉCIFIQUES :
- Citations du texte obligatoires toutes les 5-6 lignes
- Distinguer l'explication (ce que dit l'auteur) de l'analyse (ce que vous en pensez)
- Mobiliser doctrine et jurisprudence actuelles
- Pas de conclusion sauf instruction contraire

INTERDICTIONS :
- Ne jamais paraphraser sans analyser
- Ne jamais oublier de citer le texte
- Ne jamais faire de listes
- Ne jamais disserter sans lien avec le texte

Rédige le commentaire en respectant EXACTEMENT cette structure avec les marqueurs ===XXX===
PROMPT;

    return $prompt;
}

/**
 * Construire le prompt pour un cas pratique
 */
function aga_construire_prompt_cas_pratique($matiere, $cas_pratique) {
    $prompt = <<<PROMPT
Tu es professeur de droit français. Rédige une correction de cas pratique selon la méthodologie universitaire française exacte.

IMPORTANT : Analyse le cas suivant : {$cas_pratique}
Matière : {$matiere}

CONTRAINTES IMPÉRATIVES :
- Style RÉDACTIONNEL FLUIDE uniquement (jamais de listes, tirets, puces)
- Français juridique précis et académique
- Paragraphes séparés par une ligne vide
- AUCUN formatage (pas de **, #, ##, tirets)
- Texte brut uniquement
- Respecter EXACTEMENT la structure demandée avec les marqueurs
- Utiliser le syllogisme juridique strict (majeure, mineure, conclusion)
- ❌ INTERDICTION : Ne jamais utiliser le verbe "stipuler" pour une loi/un article/un texte/un code
- ✅ OBLIGATION : "stipuler" uniquement pour les contrats (actes bilatéraux)
- ✅ Pour les textes juridiques : "prévoir", "disposer", "énoncer", "établir", "prescrire", "définir"

STRUCTURE OBLIGATOIRE AVEC MARQUEURS :

===PLAN===
I. [Titre de la première partie]
II. [Titre de la deuxième partie]
III. [Titre de la troisième partie] (si nécessaire)

===CONTENU===

I. [Titre de la première partie]

FAITS : [Résumer en 2-3 phrases UNIQUEMENT les faits pertinents pour cette partie, formulés en termes juridiques. NE PAS citer d'articles de loi ici.]

PROBLÈME DE DROIT : [Une question précise sous forme interrogative]

SOLUTION EN DROIT :
[Exposé DÉTAILLÉ et COMPLET des règles applicables en plusieurs paragraphes (adapté à la complexité du problème) :

Paragraphe 1 : Énoncer le principe général et l'article de loi principal (ex: "En vertu de l'article X du Code civil...")

Paragraphe 2 : Définir les notions juridiques clés et leur portée

Paragraphes suivants : Détailler CHAQUE condition d'application avec précision (ex: "La première condition exige que...", "La deuxième condition impose..."). Expliquer chaque condition EN DROIT, sans référence aux faits du cas.

Paragraphe final : Évoquer les effets juridiques / le régime / les sanctions applicables

Si pertinent : Mentionner les exceptions ou cas particuliers prévus par la loi

INTERDICTION ABSOLUE : Ne JAMAIS inventer de jurisprudence. Si tu ne connais pas avec certitude une décision jurisprudentielle précise (date, numéro, formation), ne cite AUCUNE jurisprudence. Mentionne uniquement les articles de loi du Code civil, Code pénal, Code du travail, etc.

INTERDICTION : Ne JAMAIS faire référence aux faits du cas pratique dans cette section. Aucune phrase comme "comme c'est le cas ici", "en l'espèce", "dans notre cas". Cette section est PUREMENT théorique.]

SOLUTION EN L'ESPÈCE :
[Application MÉTHODIQUE de CHAQUE condition aux faits (adapté au nombre de conditions à vérifier) :

Reprendre CHAQUE condition énoncée dans la solution en droit et vérifier SA SATISFACTION dans les faits.

Structure type :
"S'agissant de la première condition [reformuler], en l'espèce [application aux faits]. Cette condition est donc satisfaite/non satisfaite."

"Concernant la deuxième condition [reformuler], les faits révèlent que [analyse]. Par conséquent, cette condition est remplie/non remplie."

Poursuivre pour TOUTES les conditions.

Conclure sur la qualification juridique globale : "Ainsi, toutes les conditions étant réunies/certaines conditions faisant défaut, [conclusion juridique]"]

CONCLUSION : [Une phrase synthétique indiquant les droits/actions possibles de la personne concernée]

II. [Titre de la deuxième partie]

FAITS : [Résumer en 2-3 phrases UNIQUEMENT les faits pertinents pour cette partie, formulés en termes juridiques. NE PAS citer d'articles de loi ici.]

PROBLÈME DE DROIT : [Une question précise sous forme interrogative]

SOLUTION EN DROIT :
[Même structure détaillée que pour la partie I]

SOLUTION EN L'ESPÈCE :
[Même structure méthodique que pour la partie I]

CONCLUSION : [Une phrase synthétique indiquant les droits/actions possibles]

III. [Titre de la troisième partie] (si nécessaire)

FAITS : [Résumer en 2-3 phrases UNIQUEMENT les faits pertinents pour cette partie, formulés en termes juridiques. NE PAS citer d'articles de loi ici.]

PROBLÈME DE DROIT : [Une question précise sous forme interrogative]

SOLUTION EN DROIT :
[Même structure détaillée que pour les parties précédentes]

SOLUTION EN L'ESPÈCE :
[Même structure méthodique que pour les parties précédentes]

CONCLUSION : [Une phrase synthétique indiquant les droits/actions possibles]

MÉTHODE D'ANALYSE STRICTE :
- Identifier TOUS les problèmes juridiques du cas
- Annoncer le plan COMPLET dans ===PLAN=== (toutes les parties I, II, III)
- Rédiger TOUT le contenu dans ===CONTENU=== (ne JAMAIS répéter les marqueurs ===PLAN=== ou ===CONTENU=== au milieu)
- Dans FAITS : sélectionner uniquement les faits pertinents, les qualifier juridiquement
- Dans SOLUTION EN DROIT : exposer la règle de manière complète et détaillée (adapté à la complexité), SANS AUCUNE référence aux faits
- Dans SOLUTION EN L'ESPÈCE : appliquer CHAQUE élément de la règle aux faits de manière systématique
- Appliquer le syllogisme : Majeure (règle complète) → Mineure (faits qualifiés) → Conclusion (réponse juridique)

RÈGLES SPÉCIFIQUES :
- Solution en droit TOUJOURS plus longue que solution en l'espèce
- Citer UNIQUEMENT des articles de loi certains (Code civil, Code pénal, etc.)
- Ne JAMAIS inventer de références jurisprudentielles
- Une conclusion par problème traité
- Traiter TOUS les problèmes soulevés dans l'ordre logique

INTERDICTIONS ABSOLUES :
- Ne JAMAIS répéter les marqueurs ===PLAN=== ou ===CONTENU=== au milieu du texte (uniquement au début)
- Ne JAMAIS inventer de jurisprudence (dates, numéros, formations de la Cour)
- Ne JAMAIS faire de listes ou énumérations à tirets
- Ne JAMAIS mélanger droit et faits dans "SOLUTION EN DROIT"
- Ne JAMAIS sauter l'application détaillée de chaque condition dans "SOLUTION EN L'ESPÈCE"
- Ne JAMAIS oublier la conclusion
- Ne pas mélanger plusieurs problèmes dans une même partie

IMPORTANT : Les marqueurs ===PLAN=== et ===CONTENU=== n'apparaissent QU'UNE SEULE FOIS chacun, au tout début de ta réponse. Ne les répète JAMAIS au milieu du texte.

Rédige le cas pratique en respectant EXACTEMENT cette structure avec les marqueurs ===XXX===
PROMPT;

    return $prompt;
}

/**
 * Construire le prompt pour un commentaire d'arrêt
 */
function aga_construire_prompt_commentaire_arret($references, $matiere, $contenu_arret) {
    $prompt = <<<PROMPT
Tu es professeur de droit français spécialisé en {$matiere}. Rédige un commentaire d'arrêt selon la méthodologie universitaire française exacte (sens, valeur, portée).

IMPORTANT : Analyse UNIQUEMENT le texte entre ===DEBUT_ARRET=== et ===FIN_ARRET===. 
Si ce n'est PAS un arrêt de justice français, réponds uniquement : "ERREUR_NON_JURIDIQUE: Ce texte n'est pas un arrêt de justice"

===DEBUT_ARRET===
{$contenu_arret}
===FIN_ARRET===

Références : {$references}
Matière : {$matiere}

CONTRAINTES IMPÉRATIVES :
- Traiter l'arrêt sous l'angle de la matière {$matiere}
- Style RÉDACTIONNEL FLUIDE uniquement (jamais de listes, tirets, puces sauf dans plan interne)
- Français juridique précis et élégant
- Paragraphes séparés par une ligne vide
- AUCUN formatage (pas de **, #, ##, tirets dans le texte)
- Texte brut uniquement
- OBLIGATOIRE : Rédiger introduction complète + I (A+B) + II (A+B)
- Respecte la taille de 2500-3000 mots maximum
- INTERDICTION : Ne jamais répéter les références dans le contenu
- OBLIGATION : Citer l'arrêt régulièrement entre guillemets français « » dans CHAQUE paragraphe (ex: « La Cour affirme que... », « Selon les juges... », « L'arrêt précise que... »)
- OBLIGATION : Utiliser UNIQUEMENT des guillemets français « » et JAMAIS des guillemets anglais " "
- ❌ INTERDICTION : Ne jamais utiliser le verbe "stipuler" pour une loi/un article/un texte/un code
- ✅ OBLIGATION : "stipuler" uniquement pour les contrats (actes bilatéraux)
- ✅ Pour les textes juridiques : "prévoir", "disposer", "énoncer", "établir", "prescrire", "définir"

STRUCTURE OBLIGATOIRE :

Introduction

IMPORTANT : Chaque étape commence par son nom entre parenthèses suivi du texte.

Format exact attendu :
(Accroche) [texte accroche]
(Faits) [résumé faits SANS NOMS/PRÉNOMS - utiliser qualifications juridiques]
(Procédure / prétentions) [résumé procédure]
(Problème de droit) [question INTERROGATIVE se terminant par ?]
(Solution) [résumé solution]
(Annonce de plan) [annonce naturelle adaptée à CET arrêt spécifique]

Rédige l'introduction sur « {$references} » en respectant CE FORMAT EXACT :

(Accroche) [Accroche pertinente contextualisant l'arrêt dans le cadre de la matière {$matiere}. Utiliser des guillemets français « » pour toute citation]

(Faits) [Résumé des faits en 3-4 phrases. IMPÉRATIF : Qualifier juridiquement les parties (ex: "le demandeur", "l'employeur", "le vendeur", "le majeur protégé", "la société") et NON par leurs noms/prénoms (ex: JAMAIS "Pierre", "M. Dupont", "la société X")]

(Procédure / prétentions) [Résumé de la procédure et moyens du pourvoi en 4-5 phrases. Identifier précisément la juridiction (cour d'appel de Paris, tribunal judiciaire de...) et les moyens invoqués]

(Problème de droit) [Question juridique PRÉCISE et INTERROGATIVE se terminant par un point d'interrogation. Exemple : "La clause d'exclusivité insérée dans un contrat de travail à temps partiel est-elle valable ?" et NON "La question de la validité de la clause"]

(Solution) [Résumé de la solution rendue par la Cour. Préciser s'il s'agit d'un arrêt de rejet ou de cassation. Citer l'arrêt avec guillemets français « »]

(Annonce de plan) [Annonce PERSONNALISÉE adaptée à CET arrêt. Exemple : "La Cour refuse la validité de la clause d'exclusivité (I), cette solution reposant sur l'exigence de proportionnalité (II)" et NON des formules génériques]

I. [Titre PROBLÉMATISÉ et ADAPTÉ à cet arrêt spécifique sur le SENS - mentionner les NOTIONS JURIDIQUES PRÉCISES de l'arrêt]

EXIGENCE TITRES PARTIE I :
❌ INTERDIT : Tout titre contenant "application", "règles", "principes", "droit", "juridique", "raisonnement", "analyse"
❌ INTERDIT : "L'application du droit positif", "Le raisonnement de la Cour", "Les règles applicables", "L'analyse juridique"
✅ OBLIGATOIRE : Mentionner les NOTIONS/CONCEPTS JURIDIQUES PRÉCIS de l'arrêt
Exemples CORRECTS :
- "L'exigence de proportionnalité de la clause d'exclusivité dans les contrats précaires"
- "Le refus de la dispense en l'absence de motif légitime et impérieux"
- "La nullité du mariage pour vice du consentement résultant d'une altération mentale"
- "L'obligation de sécurité de résultat de l'employeur en matière d'amiante"

(Annonce de plan interne) [Annonce naturelle PERSONNALISÉE avec les concepts juridiques de l'arrêt. Ex: "La Cour rappelle l'exigence de temps partiel significatif (A) et conclut à la disproportion manifeste de la clause (B)"]

A. [Sous-titre avec NOTIONS JURIDIQUES PRÉCISES de l'arrêt - pas de mots génériques]

EXIGENCE SOUS-TITRES :
❌ INTERDIT : Mots génériques comme "fondement", "base", "rappel", "principe"
✅ OBLIGATOIRE : Concepts juridiques précis (ex: "La condition d'altération des facultés mentales", "Le principe de proportionnalité des restrictions contractuelles")

[4-5 paragraphes RÉDIGÉS. CHAQUE paragraphe doit contenir AU MOINS UNE citation de l'arrêt entre guillemets français « ». Exemples : « La Cour affirme que », « L'arrêt précise que », « Les juges retiennent que », « Selon la Cour », « Il est jugé que ». Analyser les fondements textuels, moyens du pourvoi, attendu de principe, visas. Traiter sous l'angle {$matiere}]

B. [Sous-titre avec NOTIONS JURIDIQUES PRÉCISES]

[4-5 paragraphes RÉDIGÉS avec citations systématiques entre « ». Analyser solution, conséquences juridiques, lien avec droit positif en {$matiere}]

(Transition) [Phrase naturelle vers II avec les concepts de l'arrêt. Ex: "Cette application stricte de la proportionnalité soulève la question de son articulation avec la liberté contractuelle"]

II. [Titre PROBLÉMATISÉ sur VALEUR et PORTÉE - avec NOTIONS JURIDIQUES PRÉCISES de l'arrêt]

EXIGENCE TITRES PARTIE II :
❌ INTERDIT ABSOLU : "L'impact...", "Les conséquences...", "La portée...", "Les implications...", "Les effets..."
❌ INTERDIT ABSOLU : Tout titre contenant "droit civil français", "droit français", "ordre juridique"
✅ OBLIGATOIRE : Titre problématisé avec les CONCEPTS JURIDIQUES PRÉCIS
Exemples CORRECTS :
- "La remise en cause des clauses d'exclusivité dans les relations de travail précaires"
- "Le renforcement du formalisme protecteur en matière de consentement matrimonial"
- "L'évolution de la responsabilité contractuelle de l'employeur face aux risques professionnels"
- "La conciliation difficile entre prohibition des alliances et respect de la vie privée"

(Annonce de plan interne) [Annonce avec concepts juridiques précis]

A. [Sous-titre VALEUR avec NOTIONS PRÉCISES - critique juridique adaptée]

EXIGENCE SOUS-TITRE II.A :
❌ INTERDIT : "Critique du raisonnement", "Valeur juridique", "Analyse critique", "Appréciation"
✅ OBLIGATOIRE : Titre avec position juridique précise
Exemples : "La conformité contestable au principe de liberté contractuelle", "L'application rigoureuse du formalisme protecteur", "Le respect discutable du principe de proportionnalité"

[4-5 paragraphes RÉDIGÉS avec citations systématiques entre « ». Analyser : pertinence raisonnement, conformité droit positif en {$matiere}, apport doctrinal, critiques, séparation des pouvoirs, cohérence jurisprudence]

(Transition) [Phrase naturelle vers II.B]

B. [Sous-titre PORTÉE avec NOTIONS PRÉCISES - effets futurs adaptés]

EXIGENCE SOUS-TITRE II.B :
❌ INTERDIT ABSOLU : "Effets futurs", "Portée", "Implications futures", "Conséquences à venir", "Impact sur le droit", "Évolution prévisible", "Évolution future"
❌ INTERDIT : Tout titre commençant par "L'évolution..." ou "La nécessité..."
✅ OBLIGATOIRE : Titre précis mentionnant la RÉFORME/CHANGEMENT CONCRET attendu avec les CONCEPTS de l'arrêt
Exemples CORRECTS : 
- "L'appel à une réforme législative des alliances prohibées"
- "La remise en cause programmée du formalisme du consentement matrimonial"
- "L'extension prévisible du contrôle de proportionnalité aux clauses contractuelles"
- "Le renforcement attendu de la protection des travailleurs précaires"

[4-5 paragraphes RÉDIGÉS avec citations entre « ». Analyser : arrêt de principe/espèce ?, contrôle léger/lourd ?, évolutions législatives en {$matiere}, impact droit positif, alignement droit européen, conséquences pratiques]

⚠️ STOP ICI : Le dernier paragraphe du II.B est la FIN. Ne rien écrire après.

RÈGLES ABSOLUES CRITIQUES :
- DANS TOUT LE COMMENTAIRE (introduction + I + II) : JAMAIS utiliser les noms/prénoms des parties (M. X, Mme Y, Pierre, etc.)
- TOUJOURS utiliser les qualifications juridiques : "le demandeur", "l'employeur", "le vendeur", "la société", "le majeur protégé", "l'époux", "le salarié"
- Cette règle s'applique à TOUS les paragraphes du commentaire, pas seulement aux (Faits)
- SENS (I) : Raisonnement des juges
- VALEUR (II.A) : Critique juridique/économique/sociale
- PORTÉE (II.B) : Effets futurs, principe, évolutions
- Marqueurs : (Accroche), (Faits), (Procédure / prétentions), (Problème de droit), (Solution), (Annonce de plan), (Annonce de plan interne), (Transition)
- OBLIGATION : Citer l'arrêt dans CHAQUE paragraphe entre guillemets français « »
- OBLIGATION : Guillemets français « » UNIQUEMENT, jamais " "
- Titres PROBLÉMATISÉS avec NOTIONS JURIDIQUES PRÉCISES - JAMAIS génériques
- (Faits) : JAMAIS noms/prénoms, TOUJOURS qualifications juridiques
- (Problème de droit) : TOUJOURS interrogatif avec ?
- ❌ INTERDICTION mots génériques dans titres : "impact", "conséquences", "portée", "implications", "effets", "droit français"
- ❌ INTERDICTION : Inventer jurisprudences

ÉLÉMENTS À ANALYSER (matière {$matiere}) :
- Rejet ou cassation ?
- Contrôle léger « a pu » ou lourd « a exactement » ?
- Arrêt principe ? Attendu principe ? Visa ? Bulletin ?
- Droit antérieur {$matiere} : revirement ou constance ?
- Critique : fondement pertinent ? qualification ? effets ?
- Gouvernement des juges ?
- Conséquences économiques/sociétales
- Évolution future ? Réforme en {$matiere} ?

Génère le commentaire complet sur « {$references} » en {$matiere}.
PROMPT;

    return $prompt;
}