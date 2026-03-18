<?php
// This file is generated. Do not modify it manually.
return array(
	'alert' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/alert',
		'version' => '1.0.0',
		'title' => 'Alert',
		'category' => 'text',
		'icon' => 'warning',
		'description' => 'Bloc d\'alerte avec différents types et variantes.',
		'keywords' => array(
			'alert',
			'alerte',
			'notification',
			'info',
			'warning',
			'error',
			'success'
		),
		'attributes' => array(
			'type' => array(
				'type' => 'string',
				'default' => 'info'
			),
			'variant' => array(
				'type' => 'string',
				'default' => 'full'
			),
			'title' => array(
				'type' => 'string',
				'default' => 'Information'
			),
			'description' => array(
				'type' => 'string',
				'default' => ''
			),
			'primaryButtonText' => array(
				'type' => 'string',
				'default' => 'Découvrir'
			),
			'primaryButtonUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'secondaryButtonText' => array(
				'type' => 'string',
				'default' => 'En savoir plus'
			),
			'secondaryButtonUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'showClose' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'article-sommaire' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/article-sommaire',
		'version' => '1.0.0',
		'title' => 'Sommaire Article',
		'category' => 'jurible',
		'icon' => 'list-view',
		'description' => 'Sommaire dynamique généré à partir des titres H2 de l\'article.',
		'keywords' => array(
			'sommaire',
			'table',
			'contents',
			'toc',
			'article'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js'
	),
	'assessment' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/assessment',
		'version' => '1.0.0',
		'title' => 'Assessment Jurible',
		'category' => 'text',
		'icon' => 'clipboard',
		'description' => 'Bloc de soumission et affichage des notes pour les assessments',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'assessmentId' => array(
				'type' => 'number',
				'default' => 0
			),
			'assessmentTitle' => array(
				'type' => 'string',
				'default' => ''
			),
			'courseId' => array(
				'type' => 'number',
				'default' => 0
			),
			'lessonId' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'badge-trust' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/badge-trust',
		'version' => '1.0.0',
		'title' => 'Badge Trust',
		'category' => 'jurible',
		'icon' => 'awards',
		'description' => 'Badge de confiance social proof',
		'keywords' => array(
			'trust',
			'badge',
			'social proof',
			'avis',
			'rating'
		),
		'supports' => array(
			'html' => false,
			'align' => false
		),
		'attributes' => array(
			'variant' => array(
				'type' => 'string',
				'default' => 'full'
			),
			'icon' => array(
				'type' => 'string',
				'default' => '🎓'
			),
			'title' => array(
				'type' => 'string',
				'default' => 'École en ligne depuis 2018'
			),
			'subtitle' => array(
				'type' => 'string',
				'default' => '25 000+ étudiants formés'
			),
			'rating' => array(
				'type' => 'string',
				'default' => '4.8'
			),
			'reviewCount' => array(
				'type' => 'string',
				'default' => '+150 avis'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'bouton' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/bouton',
		'version' => '1.0.0',
		'title' => 'Bouton Jurible',
		'category' => 'design',
		'icon' => 'button',
		'description' => 'Bouton personnalisé avec 3 styles',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'text' => array(
				'type' => 'string',
				'default' => 'Mon bouton'
			),
			'url' => array(
				'type' => 'string',
				'default' => ''
			),
			'style' => array(
				'type' => 'string',
				'default' => 'primary'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'breadcrumb' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/breadcrumb',
		'version' => '1.0.0',
		'title' => 'Fil d\'Ariane',
		'category' => 'widgets',
		'icon' => 'arrow-right-alt',
		'description' => 'Fil d\'Ariane dynamique basé sur la hiérarchie WordPress.',
		'keywords' => array(
			'breadcrumb',
			'fil',
			'ariane',
			'navigation'
		),
		'attributes' => array(
			'homeIconOnMobile' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showCurrentPage' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSchema' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'card-cours' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/card-cours',
		'version' => '1.0.0',
		'title' => 'Card Cours',
		'category' => 'jurible',
		'icon' => 'welcome-learn-more',
		'description' => 'Carte de cours pour le catalogue Jurible',
		'keywords' => array(
			'card',
			'cours',
			'catalogue',
			'formation'
		),
		'supports' => array(
			'html' => false,
			'align' => false
		),
		'attributes' => array(
			'imageUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'imageId' => array(
				'type' => 'number',
				'default' => 0
			),
			'badgeIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBadge' => array(
				'type' => 'boolean',
				'default' => false
			),
			'title' => array(
				'type' => 'string',
				'default' => 'Titre du cours'
			),
			'videosCount' => array(
				'type' => 'number',
				'default' => 0
			),
			'fichesCount' => array(
				'type' => 'number',
				'default' => 0
			),
			'qcmCount' => array(
				'type' => 'number',
				'default' => 0
			),
			'flashcardsCount' => array(
				'type' => 'number',
				'default' => 0
			),
			'annalesCount' => array(
				'type' => 'number',
				'default' => 0
			),
			'mindmapsCount' => array(
				'type' => 'number',
				'default' => 0
			),
			'showIncludedLabel' => array(
				'type' => 'boolean',
				'default' => true
			),
			'includedLabelText' => array(
				'type' => 'string',
				'default' => 'Inclus dans l\'Academie'
			),
			'linkText' => array(
				'type' => 'string',
				'default' => 'Voir le cours'
			),
			'linkUrl' => array(
				'type' => 'string',
				'default' => '#'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'card-formule-reussite' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/card-formule-reussite',
		'version' => '1.0.0',
		'title' => 'Card Formule Réussite',
		'category' => 'jurible',
		'icon' => 'awards',
		'description' => 'Card premium pour présenter la formule Réussite avec countdown et features',
		'keywords' => array(
			'formule',
			'réussite',
			'pricing',
			'premium',
			'countdown'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'ribbonText' => array(
				'type' => 'string',
				'default' => 'Édition Limitée'
			),
			'badgeText' => array(
				'type' => 'string',
				'default' => 'Formule Premium'
			),
			'title' => array(
				'type' => 'string',
				'default' => 'Réussite'
			),
			'seasonBadgeText' => array(
				'type' => 'string',
				'default' => 'Offre estivale été 2026'
			),
			'showSeasonBadge' => array(
				'type' => 'boolean',
				'default' => true
			),
			'originalValue' => array(
				'type' => 'string',
				'default' => 'Valeur 580€'
			),
			'price' => array(
				'type' => 'string',
				'default' => '397'
			),
			'pricePeriod' => array(
				'type' => 'string',
				'default' => '/ accès 12 mois'
			),
			'savingsText' => array(
				'type' => 'string',
				'default' => 'Économisez 183€'
			),
			'countdownLabel' => array(
				'type' => 'string',
				'default' => 'Ouverture des inscriptions dans'
			),
			'countdownDate' => array(
				'type' => 'string',
				'default' => '2026-06-01T00:00:00'
			),
			'showCountdown' => array(
				'type' => 'boolean',
				'default' => true
			),
			'socialProofCount' => array(
				'type' => 'string',
				'default' => '143'
			),
			'socialProofText' => array(
				'type' => 'string',
				'default' => 'étudiants sur liste d\'attente'
			),
			'headerTitle' => array(
				'type' => 'string',
				'default' => 'Tout pour réussir vos examens'
			),
			'headerSubtitle' => array(
				'type' => 'string',
				'default' => 'La formule complète avec accompagnement personnalisé'
			),
			'feature1Icon' => array(
				'type' => 'string',
				'default' => '🎬'
			),
			'feature1Title' => array(
				'type' => 'string',
				'default' => '1 devoir corrigé en vidéo'
			),
			'feature1Desc' => array(
				'type' => 'string',
				'default' => 'Par un enseignant en droit'
			),
			'feature2Icon' => array(
				'type' => 'string',
				'default' => '💬'
			),
			'feature2Title' => array(
				'type' => 'string',
				'default' => '5 questions à un juriste'
			),
			'feature2Desc' => array(
				'type' => 'string',
				'default' => 'Réponses sous 48h'
			),
			'feature3Icon' => array(
				'type' => 'string',
				'default' => '📚'
			),
			'feature3Title' => array(
				'type' => 'string',
				'default' => 'Pack Fiches PDF'
			),
			'feature3Desc' => array(
				'type' => 'string',
				'default' => 'Téléchargeables'
			),
			'feature4Icon' => array(
				'type' => 'string',
				'default' => '🎓'
			),
			'feature4Title' => array(
				'type' => 'string',
				'default' => 'Accès Académie 12 mois'
			),
			'feature4Desc' => array(
				'type' => 'string',
				'default' => '20 matières complètes'
			),
			'includesTitle' => array(
				'type' => 'string',
				'default' => 'Également inclus :'
			),
			'includesTags' => array(
				'type' => 'string',
				'default' => 'Cours vidéo, QCM, Flashcards, Mindmaps, Annales'
			),
			'ctaText' => array(
				'type' => 'string',
				'default' => 'S\'inscrire sur la liste d\'attente'
			),
			'ctaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'noticeText' => array(
				'type' => 'string',
				'default' => 'Je souhaite être prévenu en avant-première'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'card-pricing-suite-ia' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/card-pricing-suite-ia',
		'version' => '1.0.0',
		'title' => 'Card Pricing Suite IA',
		'category' => 'jurible',
		'icon' => 'superhero-alt',
		'description' => 'Cards de pricing pour la suite IA Minos avec 3 formules',
		'keywords' => array(
			'pricing',
			'minos',
			'ia',
			'crédits',
			'suite'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'card1CreditsText' => array(
				'type' => 'string',
				'default' => '30 crédits'
			),
			'card1Title' => array(
				'type' => 'string',
				'default' => 'Standard'
			),
			'card1BadgeText' => array(
				'type' => 'string',
				'default' => 'Populaire'
			),
			'card1ShowBadge' => array(
				'type' => 'boolean',
				'default' => true
			),
			'card1Description' => array(
				'type' => 'string',
				'default' => 'Pour une utilisation régulière.'
			),
			'card1Price' => array(
				'type' => 'string',
				'default' => '5€'
			),
			'card1Includes' => array(
				'type' => 'string',
				'default' => 'Accès aux 4 générateurs
~20 devoirs générés
Sauvegarde illimitée
Crédits sans expiration'
			),
			'card1CtaText' => array(
				'type' => 'string',
				'default' => 'Acheter 30 crédits'
			),
			'card1CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card2CreditsText' => array(
				'type' => 'string',
				'default' => '100 crédits'
			),
			'card2Title' => array(
				'type' => 'string',
				'default' => 'Pro'
			),
			'card2RibbonText' => array(
				'type' => 'string',
				'default' => 'Meilleure valeur'
			),
			'card2Description' => array(
				'type' => 'string',
				'default' => 'Pour les gros consommateurs.'
			),
			'card2Price' => array(
				'type' => 'string',
				'default' => '17€'
			),
			'card2DiscountText' => array(
				'type' => 'string',
				'default' => '-30%'
			),
			'card2ShowDiscount' => array(
				'type' => 'boolean',
				'default' => true
			),
			'card2Includes' => array(
				'type' => 'string',
				'default' => 'Accès aux 4 générateurs
~70 devoirs générés
Sauvegarde illimitée
Crédits sans expiration'
			),
			'card2CtaText' => array(
				'type' => 'string',
				'default' => 'Acheter 100 crédits'
			),
			'card2CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card3CreditsText' => array(
				'type' => 'string',
				'default' => 'Bonus Académie'
			),
			'card3Title' => array(
				'type' => 'string',
				'default' => 'Abonnés'
			),
			'card3Description' => array(
				'type' => 'string',
				'default' => 'Avantage réservé aux abonnés.'
			),
			'card3Price' => array(
				'type' => 'string',
				'default' => 'Gratuit'
			),
			'card3PriceInfo' => array(
				'type' => 'string',
				'default' => '10 crédits offerts / mois'
			),
			'card3Includes' => array(
				'type' => 'string',
				'default' => '10 crédits IA offerts
Renouvelés chaque mois
Cumulables avec achats'
			),
			'card3CtaText' => array(
				'type' => 'string',
				'default' => 'S\'abonner à l\'Académie'
			),
			'card3CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card3NoteText' => array(
				'type' => 'string',
				'default' => 'Déjà abonné ?'
			),
			'card3NoteLinkText' => array(
				'type' => 'string',
				'default' => 'Connectez-vous'
			),
			'card3NoteLinkUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card3ShowNote' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'card-produits-comparatif' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/card-produits-comparatif',
		'version' => '1.0.0',
		'title' => 'Card Produits Comparatif',
		'category' => 'jurible',
		'icon' => 'grid-view',
		'description' => 'Comparatif des 4 produits Jurible : Académie, Prépas, Fiches PDF, Minos',
		'keywords' => array(
			'produits',
			'comparatif',
			'académie',
			'prépas',
			'fiches',
			'minos'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'card1RibbonText' => array(
				'type' => 'string',
				'default' => 'Offre principale'
			),
			'card1TypeText' => array(
				'type' => 'string',
				'default' => 'Abonnement'
			),
			'card1Title' => array(
				'type' => 'string',
				'default' => 'Académie'
			),
			'card1Description' => array(
				'type' => 'string',
				'default' => 'Cours vidéo, fiches, QCM et flashcards pour toutes les matières.'
			),
			'card1Features' => array(
				'type' => 'string',
				'default' => '20 matières complètes
500+ heures de vidéo
QCM, flashcards, mindmaps'
			),
			'card1Price' => array(
				'type' => 'string',
				'default' => '29€'
			),
			'card1PriceSuffix' => array(
				'type' => 'string',
				'default' => '/mois'
			),
			'card1PriceInfo' => array(
				'type' => 'string',
				'default' => 'ou 140€ pour 6 mois (-20%)'
			),
			'card1SocialProof' => array(
				'type' => 'string',
				'default' => '25 000+ étudiants inscrits'
			),
			'card1ShowSocialProof' => array(
				'type' => 'boolean',
				'default' => true
			),
			'card1TargetText' => array(
				'type' => 'string',
				'default' => 'Étudiants en L1, L2, L3 ou Capacité'
			),
			'card1CtaText' => array(
				'type' => 'string',
				'default' => 'S\'abonner à l\'Académie'
			),
			'card1CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card2TypeText' => array(
				'type' => 'string',
				'default' => 'Formation intensive'
			),
			'card2Title' => array(
				'type' => 'string',
				'default' => 'Prépas'
			),
			'card2Description' => array(
				'type' => 'string',
				'default' => 'Formation intensive avec accompagnement personnalisé.'
			),
			'card2Features' => array(
				'type' => 'string',
				'default' => 'Coaching individuel
Corrections personnalisées
Accès Académie inclus'
			),
			'card2Price' => array(
				'type' => 'string',
				'default' => '1 300€'
			),
			'card2PriceInfo' => array(
				'type' => 'string',
				'default' => 'Formation complète'
			),
			'card2TargetText' => array(
				'type' => 'string',
				'default' => 'Reconversion, redoublants, concours'
			),
			'card2CtaText' => array(
				'type' => 'string',
				'default' => 'Découvrir les Prépas'
			),
			'card2CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card3TypeText' => array(
				'type' => 'string',
				'default' => 'Achat unique'
			),
			'card3Title' => array(
				'type' => 'string',
				'default' => 'Fiches PDF'
			),
			'card3Description' => array(
				'type' => 'string',
				'default' => 'Fiches téléchargeables et imprimables.'
			),
			'card3Features' => array(
				'type' => 'string',
				'default' => 'Format PDF imprimable
Mindmaps incluses
Accès à vie'
			),
			'card3Price' => array(
				'type' => 'string',
				'default' => '19€'
			),
			'card3PriceInfo' => array(
				'type' => 'string',
				'default' => 'par matière • Accès à vie'
			),
			'card3TargetText' => array(
				'type' => 'string',
				'default' => 'Ceux qui préfèrent réviser sur papier'
			),
			'card3CtaText' => array(
				'type' => 'string',
				'default' => 'Voir les Fiches PDF'
			),
			'card3CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'card4TypeText' => array(
				'type' => 'string',
				'default' => 'Crédits IA'
			),
			'card4Title' => array(
				'type' => 'string',
				'default' => 'Minos'
			),
			'card4Description' => array(
				'type' => 'string',
				'default' => '4 outils IA pour générer vos exercices juridiques.'
			),
			'card4Features' => array(
				'type' => 'string',
				'default' => 'Fiche d\'arrêt
Dissertation
Cas pratique
Commentaire d\'arrêt'
			),
			'card4Price' => array(
				'type' => 'string',
				'default' => '5€'
			),
			'card4PriceInfo' => array(
				'type' => 'string',
				'default' => 'à partir de • 30 crédits'
			),
			'card4BonusText' => array(
				'type' => 'string',
				'default' => '10 crédits offerts aux abonnés Académie'
			),
			'card4ShowBonus' => array(
				'type' => 'boolean',
				'default' => true
			),
			'card4TargetText' => array(
				'type' => 'string',
				'default' => 'Ceux qui veulent gagner du temps'
			),
			'card4CtaText' => array(
				'type' => 'string',
				'default' => 'Découvrir Minos'
			),
			'card4CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'card-testimonial' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/card-testimonial',
		'version' => '1.0.0',
		'title' => 'Card Testimonial',
		'category' => 'jurible',
		'icon' => 'format-quote',
		'description' => 'Carte de témoignage avec variante Standard ou Hero',
		'keywords' => array(
			'testimonial',
			'témoignage',
			'avis',
			'quote',
			'review'
		),
		'supports' => array(
			'html' => false,
			'align' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => false
			)
		),
		'attributes' => array(
			'variant' => array(
				'type' => 'string',
				'default' => 'standard'
			),
			'rating' => array(
				'type' => 'number',
				'default' => 5
			),
			'quote' => array(
				'type' => 'string',
				'default' => 'J\'ai validé ma L1 du premier coup grâce à Jurible. Les vidéos sont super claires et les fiches m\'ont fait gagner un temps fou.'
			),
			'showBadge' => array(
				'type' => 'boolean',
				'default' => true
			),
			'badgeText' => array(
				'type' => 'string',
				'default' => 'L1 validée avec mention'
			),
			'avatarType' => array(
				'type' => 'string',
				'default' => 'initials'
			),
			'avatarUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'avatarId' => array(
				'type' => 'number',
				'default' => 0
			),
			'avatarInitials' => array(
				'type' => 'string',
				'default' => 'ML'
			),
			'authorName' => array(
				'type' => 'string',
				'default' => 'Marie L.'
			),
			'authorSubtitle' => array(
				'type' => 'string',
				'default' => 'L1 Droit — Paris 1'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'checkout-included' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/checkout-included',
		'version' => '1.0.0',
		'title' => 'Checkout - Ce qui est inclus',
		'category' => 'jurible',
		'icon' => 'yes-alt',
		'description' => 'Card avec liste des éléments inclus pour sidebar checkout',
		'keywords' => array(
			'checkout',
			'inclus',
			'included',
			'liste',
			'features'
		),
		'supports' => array(
			'html' => false,
			'align' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => false
			),
			'typography' => array(
				'textAlign' => true
			)
		),
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => 'Ce qui est inclus'
			),
			'items' => array(
				'type' => 'array',
				'default' => array(
					'Tous les cours en vidéo (20 matières)',
					'Par des enseignants en droit',
					'Fiches de révision consultables',
					'QCM et flashcards',
					'Méthodologie complète',
					'10 crédits IA offerts / mois',
					'Mises à jour continues'
				)
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'checkout-reassurance' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/checkout-reassurance',
		'version' => '1.0.0',
		'title' => 'Checkout - Réassurance',
		'category' => 'jurible',
		'icon' => 'shield',
		'description' => 'Card avec badges de réassurance pour sidebar checkout',
		'keywords' => array(
			'checkout',
			'reassurance',
			'trust',
			'badges',
			'sécurité'
		),
		'supports' => array(
			'html' => false,
			'align' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => false
			),
			'typography' => array(
				'textAlign' => true
			)
		),
		'attributes' => array(
			'items' => array(
				'type' => 'array',
				'default' => array(
					array(
						'icon' => '🔒',
						'title' => 'Paiement 100% sécurisé',
						'description' => 'Cryptage SSL via Stripe'
					),
					array(
						'icon' => '⚡',
						'title' => 'Accès immédiat',
						'description' => 'Commencez à réviser tout de suite'
					),
					array(
						'icon' => '💬',
						'title' => 'Service client disponible',
						'description' => 'Réponse rapide par email'
					),
					array(
						'icon' => '🎓',
						'title' => 'Conforme au programme',
						'description' => 'Cours conformes au programme universitaire'
					)
				)
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'checkout-social-proof' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/checkout-social-proof',
		'version' => '1.0.0',
		'title' => 'Checkout - Social Proof',
		'category' => 'jurible',
		'icon' => 'groups',
		'description' => 'Card avec statistiques et note pour sidebar checkout',
		'keywords' => array(
			'checkout',
			'social proof',
			'stats',
			'rating',
			'avis'
		),
		'supports' => array(
			'html' => false,
			'align' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => false
			),
			'typography' => array(
				'textAlign' => true
			)
		),
		'attributes' => array(
			'icon' => array(
				'type' => 'string',
				'default' => '🎓'
			),
			'label' => array(
				'type' => 'string',
				'default' => 'École en ligne depuis 2018'
			),
			'stats' => array(
				'type' => 'string',
				'default' => '25 000+ étudiants formés'
			),
			'rating' => array(
				'type' => 'number',
				'default' => 5
			),
			'score' => array(
				'type' => 'string',
				'default' => '4.8/5'
			),
			'reviewCount' => array(
				'type' => 'string',
				'default' => '(150+ avis)'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'checkout-testimonial' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/checkout-testimonial',
		'version' => '1.0.0',
		'title' => 'Checkout - Témoignage',
		'category' => 'jurible',
		'icon' => 'format-quote',
		'description' => 'Card témoignage simple pour sidebar checkout',
		'keywords' => array(
			'checkout',
			'testimonial',
			'témoignage',
			'avis',
			'quote'
		),
		'supports' => array(
			'html' => false,
			'align' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => false
			),
			'typography' => array(
				'textAlign' => true
			)
		),
		'attributes' => array(
			'rating' => array(
				'type' => 'number',
				'default' => 5
			),
			'quote' => array(
				'type' => 'string',
				'default' => 'L\'Académie a changé ma façon de réviser. Les cours en vidéo sont ultra clairs et les fiches me font gagner un temps fou.'
			),
			'authorName' => array(
				'type' => 'string',
				'default' => 'Thomas R.'
			),
			'authorRole' => array(
				'type' => 'string',
				'default' => 'L3 Droit — Lyon III'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'citation' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/citation',
		'version' => '1.0.0',
		'title' => 'Citation',
		'category' => 'text',
		'icon' => 'format-quote',
		'description' => 'Citation avec barre grise',
		'attributes' => array(
			'citation' => array(
				'type' => 'string',
				'default' => ''
			),
			'source' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'contact-form' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/contact-form',
		'version' => '1.0.0',
		'title' => 'Formulaire de Contact',
		'category' => 'jurible',
		'icon' => 'email-alt',
		'description' => 'Formulaire de contact avec envoi par email (prénom, nom, email, sujet, message)',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'recipientEmail' => array(
				'type' => 'string',
				'default' => ''
			),
			'description' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonText' => array(
				'type' => 'string',
				'default' => 'Envoyer mon message →'
			),
			'successMessage' => array(
				'type' => 'string',
				'default' => 'Votre message a bien été envoyé. Nous vous répondrons rapidement.'
			),
			'subjects' => array(
				'type' => 'string',
				'default' => 'Question générale,Support technique,Partenariat,Autre'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	),
	'cta-banner' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/cta-banner',
		'version' => '1.0.0',
		'title' => 'CTA Bannière',
		'category' => 'jurible',
		'icon' => 'megaphone',
		'description' => 'Bannière d\'appel à l\'action inline avec 3 variantes (gradient, blanc, noir)',
		'keywords' => array(
			'cta',
			'call to action',
			'bannière',
			'promo',
			'marketing'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'variant' => array(
				'type' => 'string',
				'default' => 'gradient',
				'enum' => array(
					'gradient',
					'white',
					'dark'
				)
			),
			'icon' => array(
				'type' => 'string',
				'default' => '🎓'
			),
			'title' => array(
				'type' => 'string',
				'default' => 'Rejoins l\'Académie Jurible'
			),
			'description' => array(
				'type' => 'string',
				'default' => 'Accède à +500h de cours, fiches et QCM pour réussir ta licence de droit.'
			),
			'buttonText' => array(
				'type' => 'string',
				'default' => 'Découvrir →'
			),
			'buttonUrl' => array(
				'type' => 'string',
				'default' => '/tarifs'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'flashcards' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/flashcards',
		'version' => '1.0.0',
		'title' => 'Flashcards Jurible',
		'category' => 'text',
		'icon' => 'welcome-learn-more',
		'description' => 'Affiche les flashcards d\'une leçon pour réviser',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'courseId' => array(
				'type' => 'number',
				'default' => 0
			),
			'lessonId' => array(
				'type' => 'number',
				'default' => 0
			),
			'courseTitle' => array(
				'type' => 'string',
				'default' => ''
			),
			'lessonTitle' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'footer-accordion' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/footer-accordion',
		'version' => '1.0.0',
		'title' => 'Footer Accordion',
		'category' => 'widgets',
		'icon' => 'list-view',
		'description' => 'Section accordéon pour le footer mobile',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => 'Section'
			),
			'isOpenByDefault' => array(
				'type' => 'boolean',
				'default' => false
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'hero-dashboard' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/hero-dashboard',
		'version' => '1.0.0',
		'title' => 'Hero Dashboard',
		'category' => 'jurible',
		'icon' => 'dashboard',
		'description' => 'Dashboard visuel style Stripe avec cartes flottantes et effet 3D',
		'keywords' => array(
			'hero',
			'dashboard',
			'stripe',
			'mockup'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'align' => array(
				'type' => 'string',
				'default' => 'full'
			),
			'userName' => array(
				'type' => 'string',
				'default' => 'Marie'
			),
			'userInitials' => array(
				'type' => 'string',
				'default' => 'ML'
			),
			'currentCourse' => array(
				'type' => 'string',
				'default' => 'Droit constitutionnel'
			),
			'lessonTitle' => array(
				'type' => 'string',
				'default' => 'La séparation des pouvoirs'
			),
			'lessonMeta' => array(
				'type' => 'string',
				'default' => 'Leçon 8 · 18 min · Raphaël Briguet-Lamarre'
			),
			'progressPercent' => array(
				'type' => 'number',
				'default' => 67
			),
			'qcmCount' => array(
				'type' => 'number',
				'default' => 84
			),
			'fichesCount' => array(
				'type' => 'number',
				'default' => 12
			),
			'fichesTotal' => array(
				'type' => 'number',
				'default' => 16
			),
			'gradeValue' => array(
				'type' => 'number',
				'default' => 15
			),
			'gradeImprovement' => array(
				'type' => 'string',
				'default' => '+3 pts vs dernier semestre'
			),
			'enableParallax' => array(
				'type' => 'boolean',
				'default' => true
			),
			'enableAnimations' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'viewScript' => 'file:./view.js',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'infobox' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/infobox',
		'version' => '1.0.0',
		'title' => 'Infobox',
		'category' => 'text',
		'icon' => 'info-outline',
		'description' => 'Bloc d\'information avec différents styles.',
		'keywords' => array(
			'infobox',
			'alerte',
			'info',
			'attention',
			'conseil'
		),
		'attributes' => array(
			'type' => array(
				'type' => 'string',
				'default' => 'retenir'
			),
			'title' => array(
				'type' => 'string',
				'default' => 'À retenir'
			),
			'content' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'lien-lecon' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/lien-lecon',
		'version' => '1.0.0',
		'title' => 'Lien Leçon',
		'category' => 'text',
		'icon' => 'welcome-learn-more',
		'description' => 'Affiche un lien vers une leçon Fluent Community.',
		'keywords' => array(
			'lien',
			'lecon',
			'cours',
			'fluent'
		),
		'attributes' => array(
			'courseId' => array(
				'type' => 'integer',
				'default' => 0
			),
			'courseName' => array(
				'type' => 'string',
				'default' => ''
			),
			'lessonId' => array(
				'type' => 'integer',
				'default' => 0
			),
			'lessonTitle' => array(
				'type' => 'string',
				'default' => ''
			),
			'lessonUrl' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'method-tabs' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/method-tabs',
		'version' => '2.0.0',
		'title' => 'Bloc Méthode (7 onglets)',
		'category' => 'jurible',
		'icon' => 'welcome-learn-more',
		'description' => 'Section interactive avec 7 onglets présentant les différentes méthodes d\'apprentissage (Vidéo, Cours écrits, Mindmap, QCM, Flashcards, Annales, Fiche vidéo)',
		'keywords' => array(
			'tabs',
			'méthode',
			'onglets',
			'vidéo',
			'qcm',
			'flashcards',
			'annales'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'full',
				'wide'
			)
		),
		'attributes' => array(
			'sectionBadge' => array(
				'type' => 'string',
				'default' => 'Extrait gratuit'
			),
			'sectionTitle' => array(
				'type' => 'string',
				'default' => 'Découvrez notre <mark>méthode</mark>'
			),
			'sectionSubtitle' => array(
				'type' => 'string',
				'default' => 'Accédez à un extrait du cours sans inscription'
			),
			'videoUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'ctaText' => array(
				'type' => 'string',
				'default' => 'Débloquer le cours complet'
			),
			'ctaUrl' => array(
				'type' => 'string',
				'default' => '#pricing'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'render' => 'file:./render.php',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'newsletter' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/newsletter',
		'version' => '1.0.0',
		'title' => 'Newsletter Jurible',
		'category' => 'widgets',
		'icon' => 'email',
		'description' => 'Formulaire d\'inscription newsletter avec input email et bouton',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => 'Newsletter'
			),
			'description' => array(
				'type' => 'string',
				'default' => 'Recevez nos conseils et actualités pour réussir vos études de droit.'
			),
			'placeholder' => array(
				'type' => 'string',
				'default' => 'Votre email'
			),
			'buttonText' => array(
				'type' => 'string',
				'default' => 'S\'inscrire'
			),
			'variant' => array(
				'type' => 'string',
				'default' => 'dark'
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'horizontal'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'playlist' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/playlist',
		'version' => '1.0.0',
		'title' => 'Playlist Jurible',
		'category' => 'media',
		'icon' => 'playlist-video',
		'description' => 'Affiche une playlist de vidéos Bunny Stream.',
		'keywords' => array(
			'playlist',
			'video',
			'bunny',
			'cours'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'collectionId' => array(
				'type' => 'string',
				'default' => ''
			),
			'collectionName' => array(
				'type' => 'string',
				'default' => ''
			),
			'videoCount' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'textdomain' => 'jurible-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'pricing-duration-selector' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/pricing-duration-selector',
		'version' => '1.0.0',
		'title' => 'Pricing Duration Selector',
		'category' => 'jurible',
		'icon' => 'money-alt',
		'description' => 'Sélecteur de durée d\'abonnement avec 4 options de pricing',
		'keywords' => array(
			'pricing',
			'tarif',
			'abonnement',
			'durée',
			'price'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'option1Duration' => array(
				'type' => 'string',
				'default' => '1 mois'
			),
			'option1Price' => array(
				'type' => 'string',
				'default' => '29'
			),
			'option1OriginalPrice' => array(
				'type' => 'string',
				'default' => ''
			),
			'option1MonthlyPrice' => array(
				'type' => 'string',
				'default' => '29€/mois'
			),
			'option1SavingsPercent' => array(
				'type' => 'string',
				'default' => ''
			),
			'option1SavingsAmount' => array(
				'type' => 'string',
				'default' => ''
			),
			'option1IsPopular' => array(
				'type' => 'boolean',
				'default' => false
			),
			'option1CtaText' => array(
				'type' => 'string',
				'default' => 'Commencer'
			),
			'option1CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'option2Duration' => array(
				'type' => 'string',
				'default' => '3 mois'
			),
			'option2Price' => array(
				'type' => 'string',
				'default' => '78'
			),
			'option2OriginalPrice' => array(
				'type' => 'string',
				'default' => '87€'
			),
			'option2MonthlyPrice' => array(
				'type' => 'string',
				'default' => '26€/mois'
			),
			'option2SavingsPercent' => array(
				'type' => 'string',
				'default' => '-10%'
			),
			'option2SavingsAmount' => array(
				'type' => 'string',
				'default' => 'Économisez 9€'
			),
			'option2IsPopular' => array(
				'type' => 'boolean',
				'default' => false
			),
			'option2CtaText' => array(
				'type' => 'string',
				'default' => 'Commencer'
			),
			'option2CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'option3Duration' => array(
				'type' => 'string',
				'default' => '6 mois'
			),
			'option3Price' => array(
				'type' => 'string',
				'default' => '140'
			),
			'option3OriginalPrice' => array(
				'type' => 'string',
				'default' => '174€'
			),
			'option3MonthlyPrice' => array(
				'type' => 'string',
				'default' => '23€/mois'
			),
			'option3SavingsPercent' => array(
				'type' => 'string',
				'default' => '-20%'
			),
			'option3SavingsAmount' => array(
				'type' => 'string',
				'default' => 'Économisez 34€'
			),
			'option3IsPopular' => array(
				'type' => 'boolean',
				'default' => true
			),
			'option3CtaText' => array(
				'type' => 'string',
				'default' => 'Commencer'
			),
			'option3CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'option4Duration' => array(
				'type' => 'string',
				'default' => '12 mois'
			),
			'option4Price' => array(
				'type' => 'string',
				'default' => '240'
			),
			'option4OriginalPrice' => array(
				'type' => 'string',
				'default' => '348€'
			),
			'option4MonthlyPrice' => array(
				'type' => 'string',
				'default' => '20€/mois'
			),
			'option4SavingsPercent' => array(
				'type' => 'string',
				'default' => '-31%'
			),
			'option4SavingsAmount' => array(
				'type' => 'string',
				'default' => 'Économisez 108€'
			),
			'option4IsPopular' => array(
				'type' => 'boolean',
				'default' => false
			),
			'option4CtaText' => array(
				'type' => 'string',
				'default' => 'Commencer'
			),
			'option4CtaUrl' => array(
				'type' => 'string',
				'default' => '#'
			),
			'noticeText' => array(
				'type' => 'string',
				'default' => 'Tous les abonnements sont renouvelables automatiquement et résiliables en 1 clic'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'qcm' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/qcm',
		'version' => '1.0.0',
		'title' => 'QCM Jurible',
		'category' => 'jurible',
		'icon' => 'editor-help',
		'description' => 'Quiz à choix multiples interactif pour les articles SEO',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => 'Quiz'
			),
			'questions' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'shuffleAnswers' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showExplanations' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'solution-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/solution-card',
		'version' => '1.0.0',
		'title' => 'Solution Card',
		'category' => 'jurible',
		'icon' => 'lightbulb',
		'description' => 'Carte solution/bénéfice avec icône, titre et description',
		'keywords' => array(
			'solution',
			'bénéfice',
			'feature',
			'avantage',
			'card'
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'layout' => array(
				'type' => 'string',
				'default' => 'horizontal',
				'enum' => array(
					'horizontal',
					'centered'
				)
			),
			'icon' => array(
				'type' => 'string',
				'default' => '📚'
			),
			'title' => array(
				'type' => 'string',
				'default' => 'Titre de la solution'
			),
			'description' => array(
				'type' => 'string',
				'default' => 'Description de la solution ou du bénéfice.'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'sommaire' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/sommaire',
		'version' => '1.0.0',
		'title' => 'Sommaire',
		'category' => 'text',
		'icon' => 'list-view',
		'description' => 'Génère automatiquement un sommaire à partir des titres H2.',
		'keywords' => array(
			'sommaire',
			'table',
			'contents',
			'toc'
		),
		'attributes' => array(
			'headings' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'step-indicator' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jurible/step-indicator',
		'version' => '1.0.0',
		'title' => 'Step Indicator',
		'category' => 'widgets',
		'icon' => 'editor-ol',
		'description' => 'Indicateur d\'étapes pour les parcours.',
		'keywords' => array(
			'step',
			'etape',
			'progress',
			'indicator',
			'stepper'
		),
		'attributes' => array(
			'totalSteps' => array(
				'type' => 'number',
				'default' => 5
			),
			'currentStep' => array(
				'type' => 'number',
				'default' => 1
			),
			'showLabels' => array(
				'type' => 'boolean',
				'default' => false
			),
			'labels' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'textdomain' => 'jurible-blocks-react',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);
