<?php
/**
 * Fonctions communes réutilisables par tous les générateurs
 * Sécurité, validation, formatage, parsers basiques
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// VALIDATION ET SÉCURITÉ
// ============================================================================

/**
 * Valider qu'un texte est juridique (contient des mots-clés)
 */
function aga_est_texte_juridique($texte) {
    $mots_juridiques = [
        'cour', 'cassation', 'arrêt', 'tribunal', 'juge', 'juridiction', 
        'attendu', 'motifs', 'pourvoi', 'appel', 'conseil', 'droit',
        'article', 'loi', 'code', 'jurisprudence', 'constitutionnel'
    ];
    
    $texte_lower = mb_strtolower($texte);
    
    foreach ($mots_juridiques as $mot) {
        if (strpos($texte_lower, $mot) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Détecter une tentative d'injection de prompt
 */
function aga_detecter_injection_prompt($texte) {
    $patterns_injection = [
        '/ignore[^r]*les\s+instructions/i',
        '/oublie[^r]*tout/i',
        '/tu\s+es\s+maintenant/i',
        '/nouveau\s+rôle/i',
        '/system\s*:/i',
        '/assistant\s*:/i'
    ];
    
    foreach ($patterns_injection as $pattern) {
        if (preg_match($pattern, $texte)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Valider les données d'un formulaire de génération
 * 
 * @return array Liste des erreurs (vide si tout est OK)
 */
function aga_valider_donnees_formulaire($data, $type_generateur) {
    $erreurs = array();
    
    // Validation commune à tous les générateurs
    if (empty($data['matiere'])) {
        $erreurs[] = 'La matière est obligatoire.';
    }
    
    // Validations spécifiques selon le type
    switch ($type_generateur) {
        case 'fiche_arret':
            if (empty($data['references'])) {
                $erreurs[] = 'Les références de l\'arrêt sont obligatoires.';
            } elseif (strlen($data['references']) < 8) {
                $erreurs[] = 'Veuillez saisir des références plus détaillées (ex: Cass. Civ. 1ère, 12 juillet 2023).';
            } elseif (!preg_match('/\d/', $data['references'])) {
                $erreurs[] = 'Les références doivent contenir au moins une date ou un numéro de pourvoi.';
            }
            
            if (empty($data['contenu'])) {
                $erreurs[] = 'Le contenu de l\'arrêt est obligatoire.';
            } elseif (strlen($data['contenu']) < 100) {
                $erreurs[] = 'Le contenu de l\'arrêt doit faire au moins 100 caractères.';
            } elseif (strlen($data['contenu']) > 15000) {
                $erreurs[] = 'Le contenu de l\'arrêt ne peut pas dépasser 15 000 caractères.';
            } elseif (aga_detecter_injection_prompt($data['contenu'])) {
                $erreurs[] = 'Le contenu semble contenir des instructions non autorisées.';
            } elseif (!aga_est_texte_juridique($data['contenu'])) {
                $erreurs[] = 'Le texte ne semble pas être un arrêt de justice. Veuillez vérifier votre saisie.';
            }
            break;
            
        case 'dissertation':
            if (empty($data['sujet'])) {
                $erreurs[] = 'Le sujet de dissertation est obligatoire.';
            } elseif (strlen($data['sujet']) < 10) {
                $erreurs[] = 'Le sujet doit faire au moins 10 caractères.';
            } elseif (strlen($data['sujet']) > 500) {
                $erreurs[] = 'Le sujet ne peut pas dépasser 500 caractères.';
            }
            
            if (empty($data['type_generation']) || !in_array($data['type_generation'], ['plan_detaille', 'dissertation_complete'])) {
                $erreurs[] = 'Veuillez choisir entre plan détaillé ou dissertation complète.';
            }
            break;
            
        case 'commentaire_arret':
            if (empty($data['references'])) {
                $erreurs[] = 'Les références de l\'arrêt sont obligatoires.';
            } elseif (strlen($data['references']) < 8) {
                $erreurs[] = 'Veuillez saisir des références plus détaillées (ex: Cass. Civ. 1ère, 12 juillet 2023).';
            } elseif (!preg_match('/\d/', $data['references'])) {
                $erreurs[] = 'Les références doivent contenir au moins une date ou un numéro de pourvoi.';
            }
            
            if (empty($data['contenu'])) {
                $erreurs[] = 'Le contenu de l\'arrêt est obligatoire.';
            } elseif (strlen($data['contenu']) < 100) {
                $erreurs[] = 'Le contenu de l\'arrêt doit faire au moins 100 caractères.';
            } elseif (strlen($data['contenu']) > 15000) {
                $erreurs[] = 'Le contenu de l\'arrêt ne peut pas dépasser 15 000 caractères.';
            } elseif (aga_detecter_injection_prompt($data['contenu'])) {
                $erreurs[] = 'Le contenu semble contenir des instructions non autorisées.';
            } elseif (!aga_est_texte_juridique($data['contenu'])) {
                $erreurs[] = 'Le texte ne semble pas être un arrêt de justice. Veuillez vérifier votre saisie.';
            }
            break;
            
        case 'cas_pratique':
            if (empty($data['cas_pratique'])) {
                $erreurs[] = 'Le sujet du cas pratique est obligatoire.';
            } elseif (strlen($data['cas_pratique']) < 50) {
                $erreurs[] = 'Le cas pratique doit faire au moins 50 caractères pour être pertinent.';
            } elseif (strlen($data['cas_pratique']) > 10000) {
                $erreurs[] = 'Le cas pratique ne peut pas dépasser 10 000 caractères.';
            }
            break;
            
        case 'commentaire_texte':
            if (empty($data['auteur'])) {
                $erreurs[] = 'L\'auteur du texte est obligatoire.';
            }
            
            if (empty($data['references_oeuvre'])) {
                $erreurs[] = 'Les références de l\'œuvre sont obligatoires.';
            }
            
            if (empty($data['texte'])) {
                $erreurs[] = 'Le texte à commenter est obligatoire.';
            } elseif (strlen($data['texte']) < 50) {
                $erreurs[] = 'Le texte doit faire au moins 50 caractères.';
            } elseif (strlen($data['texte']) > 8000) {
                $erreurs[] = 'Le texte ne peut pas dépasser 8 000 caractères.';
            }
            
            if (empty($data['type_generation']) || !in_array($data['type_generation'], ['plan_detaille', 'commentaire_complete'])) {
                $erreurs[] = 'Veuillez choisir entre plan détaillé ou commentaire complet.';
            }
            break;
    }
    
    return $erreurs;
}

// ============================================================================
// FORMATAGE DES MATIÈRES
// ============================================================================

/**
 * Formater le nom d'une matière juridique
 */
function aga_formater_matiere($matiere) {
    $matieres = array(
        // Licence 1
        'introduction-droit' => 'Introduction au droit',
        'droit-constitutionnel' => 'Droit constitutionnel',
        'droit-civil-personnes' => 'Droit civil - Personnes',
        'droit-civil-famille' => 'Droit civil - Famille',
        'histoire-droit' => 'Histoire du droit',
        'institutions-judiciaires' => 'Institutions judiciaires',
        'institutions-administratives' => 'Institutions administratives',
        
        // Licence 2
        'droit-obligations' => 'Droit des obligations',
        'droit-penal' => 'Droit pénal général',
        'droit-administratif' => 'Droit administratif',
        'droit-biens' => 'Droit des biens',
        'droit-europeen' => 'Droit européen',
        'droit-fiscal' => 'Droit fiscal',
        'procedure-civile' => 'Procédure civile',
        'droit-commercial' => 'Droit commercial',
        
        // Licence 3
        'droit-societes' => 'Droit des sociétés',
        'droit-travail' => 'Droit du travail',
        'droit-contrats-speciaux' => 'Droit des contrats spéciaux',
        'droit-suretes' => 'Droit des sûretés',
        'libertes-fondamentales' => 'Libertés fondamentales',
        'droit-international-public' => 'Droit international public',
        'droit-international-prive' => 'Droit international privé',
        'procedure-penale' => 'Procédure pénale',
        'droit-affaires' => 'Droit des affaires',
        'responsabilite-civile' => 'Responsabilité civile',
        'contrats-speciaux' => 'Contrats spéciaux',
        
        // Autres
        'philosophie-droit' => 'Philosophie du droit',
        'droit-penal-special' => 'Droit pénal spécial'
    );
    
    return isset($matieres[$matiere]) ? $matieres[$matiere] : ucfirst(str_replace('-', ' ', $matiere));
}

// ============================================================================
// PARSERS BASIQUES
// ============================================================================

/**
 * Parser basique pour détecter les sections avec marqueurs ===XXX===
 * Utilisé par tous les nouveaux générateurs
 * 
 * @param string $contenu Contenu brut généré par l'IA
 * @param array $marqueurs Liste des marqueurs attendus (ex: ['INTRODUCTION', 'PLAN'])
 * @return array ['sections' => array, 'parsing_reussi' => bool]
 */
function aga_parser_basique($contenu, $marqueurs) {
    $sections = array();
    $parsing_reussi = true;
    
    // Tenter de parser avec les marqueurs
    foreach ($marqueurs as $index => $marqueur) {
        $pattern_debut = '/==='. $marqueur . '===\s*/';
        
        // Déterminer le marqueur suivant ou fin de texte
        if (isset($marqueurs[$index + 1])) {
            $marqueur_suivant = $marqueurs[$index + 1];
            $pattern = '/==='. $marqueur . '===\s*(.*?)==='. $marqueur_suivant . '===/s';
        } else {
            // Dernier marqueur : capturer jusqu'à la fin
            $pattern = '/==='. $marqueur . '===\s*(.*?)$/s';
        }
        
        if (preg_match($pattern, $contenu, $matches)) {
            $sections[strtolower($marqueur)] = trim($matches[1]);
        } else {
            $parsing_reussi = false;
            break;
        }
    }
    
    // Si le parsing échoue, tout le contenu dans une seule clé
    if (!$parsing_reussi) {
        $sections['contenu_complet'] = $contenu;
    }
    
    return array(
        'sections' => $sections,
        'parsing_reussi' => $parsing_reussi
    );
}

// ============================================================================
// NORMALISATION DES RÉFÉRENCES (pour fiche d'arrêt)
// ============================================================================

/**
 * Normaliser les références juridiques
 * Conservé de votre code original
 */
function aga_normaliser_references_prudent($input) {
    $input = trim($input);
    
    // 1. Nettoyer les espaces multiples
    $input = preg_replace('/\s+/', ' ', $input);
    
    // 2. Normaliser tirets et traits d'union
    $input = preg_replace('/–|—/', '-', $input);
    
    // 3. Tout mettre en minuscules d'abord
    $input = mb_strtolower($input, 'UTF-8');
    
    // 4. JURIDICTIONS SUPRÊMES FRANÇAISES
    
    // Cour de cassation
    $input = preg_replace('/^(cass\.|cassation|cour de cassation)\s*/i', 'Cass. ', $input);
    
    // Conseil d'État
    $input = preg_replace('/^(ce|conseil d\'état|conseil d etat)\s*/i', 'CE ', $input);
    $input = preg_replace('/^ce\s+sect\./i', 'CE Sect.', $input);
    $input = preg_replace('/^ce\s+ass\./i', 'CE Ass.', $input);
    
    // Conseil constitutionnel
    $input = preg_replace('/^(cons\.\s*const\.|conseil constitutionnel)\s*/i', 'Cons. const. ', $input);
    
    // Tribunal des conflits
    $input = preg_replace('/^(t\.\s*confl\.|tribunal des conflits)\s*/i', 'T. confl. ', $input);
    
    // 5. JURIDICTIONS D'APPEL
    
    // Cour d'appel
    $input = preg_replace('/^(ca|cour d\'appel|cour d appel)\s*/i', 'CA ', $input);
    
    // Cour administrative d'appel
    $input = preg_replace('/^(caa|cour administrative d\'appel|cour administrative d appel)\s*/i', 'CAA ', $input);
    
    // 6. JURIDICTIONS DE PREMIÈRE INSTANCE - Ordre judiciaire
    
    // Tribunal judiciaire
    $input = preg_replace('/^(tj|tribunal judiciaire)\s*/i', 'TJ ', $input);
    
    // Tribunal de grande instance
    $input = preg_replace('/^(tgi|tribunal de grande instance)\s*/i', 'TGI ', $input);
    
    // Tribunal d'instance
    $input = preg_replace('/^(ti|tribunal d\'instance|tribunal d instance)\s*/i', 'TI ', $input);
    
    // Tribunal de commerce
    $input = preg_replace('/^(tc|tribunal de commerce)\s*/i', 'TC ', $input);
    
    // Conseil de prud'hommes
    $input = preg_replace('/^(cph|conseil de prud\'hommes|conseil de prudhommes)\s*/i', 'CPH ', $input);
    
    // Tribunal de police
    $input = preg_replace('/^(t\.\s*pol\.|tribunal de police)\s*/i', 'T. pol. ', $input);
    
    // Tribunal correctionnel
    $input = preg_replace('/^(t\.\s*corr\.|tribunal correctionnel)\s*/i', 'T. corr. ', $input);
    
    // Cour d'assises
    $input = preg_replace('/^(cour d\'assises|cour d assises)\s*/i', 'Cour d\'assises ', $input);
    
    // 7. JURIDICTIONS DE PREMIÈRE INSTANCE - Ordre administratif
    
    // Tribunal administratif
    $input = preg_replace('/^(ta|tribunal administratif)\s*/i', 'TA ', $input);
    
    // 8. JURIDICTIONS EUROPÉENNES ET INTERNATIONALES
    
    // CJUE
    $input = preg_replace('/^(cjue|cour de justice de l\'union européenne|cour de justice de l union européenne)\s*/i', 'CJUE ', $input);
    
    // CJCE (ancien nom)
    $input = preg_replace('/^(cjce|cour de justice des communautés européennes|cour de justice des communautes europeennes)\s*/i', 'CJCE ', $input);
    
    // Tribunal de l'Union européenne
    $input = preg_replace('/^(trib\.\s*ue|tribunal de l\'union européenne|tribunal de l union européenne)\s*/i', 'Trib. UE ', $input);
    
    // CEDH
    $input = preg_replace('/^(cedh|cour européenne des droits de l\'homme|cour europeenne des droits de l homme)\s*/i', 'CEDH ', $input);
    
    // 9. CHAMBRES DE LA COUR DE CASSATION (seulement si Cass. présent)
    if (stripos($input, 'cass') !== false) {
        $input = preg_replace('/\bciv\.?\s*1(ere|ère|re)?\b/i', 'civ. 1ère', $input);
        $input = preg_replace('/\bciv\.?\s*2(e|ème)?\b/i', 'civ. 2ème', $input);
        $input = preg_replace('/\bciv\.?\s*3(e|ème)?\b/i', 'civ. 3ème', $input);
        $input = preg_replace('/\bcom\.?\b/i', 'com.', $input);
        $input = preg_replace('/\bcrim\.?\b/i', 'crim.', $input);
        $input = preg_replace('/\bsoc\.?\b/i', 'soc.', $input);
        $input = preg_replace('/\bass\.\s*plén\.?\b/i', 'Ass. plén.', $input);
        $input = preg_replace('/\bch\.\s*mixte\b/i', 'Ch. mixte', $input);
        $input = preg_replace('/\bassemblée plénière\b/i', 'Ass. plén.', $input);
        $input = preg_replace('/\bchambre mixte\b/i', 'Ch. mixte', $input);
    }
    
    // 10. Normaliser les mois (tous en minuscules)
    $mois = [
        'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        'janv\.?', 'févr\.?', 'avr\.?', 'juil\.?', 'sept\.?', 'oct\.?', 'nov\.?', 'déc\.?'
    ];
    foreach ($mois as $m) {
        $input = preg_replace('/\b' . $m . '\b/i', strtolower($m), $input);
    }
    
    // 11. Normaliser le numéro de pourvoi
    $input = preg_replace('/(n°|numero|n° de pourvoi|n de pourvoi)\s*/i', 'n° ', $input);
    // Détecter pattern numérique sans "n°" et l'ajouter
    $input = preg_replace('/,\s*(\d{2})[\s\-]?(\d{2})[\s\.\-]?(\d{3})\b/', ', n° $1-$2.$3', $input);
    
    // 12. Nettoyer la ponctuation excessive
    $input = preg_replace('/,\s*,/', ',', $input);
    $input = preg_replace('/\s+,/', ',', $input);
    $input = preg_replace('/,(\S)/', ', $1', $input);
    $input = preg_replace('/\s+/', ' ', $input); // Re-nettoyer les espaces
    
    return trim($input);
}

// ============================================================================
// GESTION DES AVIS TRUSTPILOT
// ============================================================================

/**
 * Handler AJAX - Enregistrer l'avis utilisateur
 */
function aga_ajax_enregistrer_avis() {
    // Vérification de sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'aga_avis_nonce')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité'));
        return;
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Utilisateur non connecté'));
        return;
    }
    
    // Vérifier si déjà donné un avis
    if (aga_utilisateur_a_deja_avis($user_id)) {
        wp_send_json_error(array('message' => 'Vous avez déjà donné un avis'));
        return;
    }
    
    $note = intval($_POST['note']);
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'feedback';
    
    if ($note < 1 || $note > 5) {
        wp_send_json_error(array('message' => 'Note invalide'));
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'aga_avis_utilisateurs';
    
    // Préparer les données
    $data = array(
        'user_id' => $user_id,
        'note' => $note,
        'date_creation' => current_time('mysql'),
        'statut' => 'pending',
        'credits_attribues' => 0
    );

    if ($type === 'feedback' && isset($_POST['feedback'])) {
        // Feedback interne (note ≤ 3)
        $feedback = sanitize_textarea_field($_POST['feedback']);
        $data['feedback_texte'] = $feedback;
    }

    // Format explicite pour MySQL
    $format = array('%d', '%d', '%s', '%s', '%d');
    if (isset($data['feedback_texte'])) {
        $format[] = '%s';
    }
    
    // Insérer en BDD
    $inserted = $wpdb->insert($table_name, $data, $format);
    
    if ($inserted) {
        // Envoyer email à l'admin si TrustPilot
        if ($type === 'trustpilot') {
            $user = get_userdata($user_id);
            $admin_email = get_option('admin_email');
            
            $subject = '🌟 Nouvel avis TrustPilot en attente - Générateur';
            $message = "Un utilisateur souhaite laisser un avis TrustPilot :\n\n";
            $message .= "Utilisateur : " . $user->display_name . "\n";
            $message .= "Email : " . $user->user_email . "\n";
            $message .= "Note donnée : " . $note . "/5 ⭐\n";
            $message .= "Date : " . current_time('d/m/Y à H:i') . "\n\n";
            $message .= "Action à faire :\n";
            $message .= "1. Vérifier sur TrustPilot : https://fr.trustpilot.com/evaluate/aideauxtd.com\n";
            $message .= "2. Valider et attribuer 3 crédits depuis l'admin WordPress\n\n";
            $message .= "User ID : " . $user_id;
            
            wp_mail($admin_email, $subject, $message);
        }
        
        wp_send_json_success(array('message' => 'Avis enregistré'));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de l\'enregistrement'));
    }
}
add_action('wp_ajax_aga_enregistrer_avis', 'aga_ajax_enregistrer_avis');

/**
 * Vérifier si l'utilisateur doit voir la modal d'avis
 */
function aga_doit_afficher_modal_avis($user_id) {
    // Ne pas afficher si déjà donné un avis
    if (aga_utilisateur_a_deja_avis($user_id)) {
        return false;
    }

    // Ne pas afficher si l'utilisateur n'a jamais généré (nouveau user à 0 crédits)
    if (empty(get_user_meta($user_id, 'aga_last_generation', true))) {
        return false;
    }

    // Afficher uniquement si plus de crédits
    $verification = aga_peut_generer($user_id, 1);

    return !$verification['autorise'];
}