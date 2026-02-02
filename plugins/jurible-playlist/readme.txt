=== Jurible Playlist ===
Contributors: jurible
Tags: video, playlist, bunny, stream, e-learning, course
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Affiche des playlists de vidéos hébergées sur Bunny Stream avec tracking de progression pour les utilisateurs connectés.

== Description ==

Jurible Playlist est un plugin WordPress conçu pour afficher des playlists de vidéos hébergées sur Bunny Stream.
Il est particulièrement adapté aux plateformes e-learning et de cours en ligne.

= Fonctionnalités =

* Affichage de playlists vidéo depuis les collections Bunny Stream
* Player vidéo responsive (iframe Bunny)
* Liste de vidéos avec miniatures
* Tracking de progression pour les utilisateurs connectés
* Indicateur visuel pour les vidéos complétées
* Option de lecture automatique (autoplay)
* Nettoyage automatique des titres (retire numéros, tirets, extensions)
* Tri automatique par numéro de vidéo
* Design responsive (mobile-first)
* Suppression automatique des données utilisateur lors de la suppression du compte

= Shortcode =

Utilisez le shortcode suivant pour afficher une playlist :

`[bunny_playlist collection="COLLECTION_ID"]`

Exemple :

`[bunny_playlist collection="f53a2ad0-2ffe-430f-abd8-6004bac5751c"]`

= Configuration =

1. Allez dans Réglages > Jurible Playlist
2. Configurez votre Library ID, API Key et Pull Zone URL
3. Testez la connexion
4. Utilisez le shortcode sur vos pages

== Installation ==

1. Téléchargez le plugin et décompressez-le dans `/wp-content/plugins/`
2. Activez le plugin via le menu 'Extensions' de WordPress
3. Configurez le plugin dans Réglages > Jurible Playlist
4. Utilisez le shortcode `[bunny_playlist collection="ID"]` dans vos pages

== Frequently Asked Questions ==

= Comment obtenir le Collection ID ? =

Dans votre dashboard Bunny Stream, naviguez vers votre bibliothèque, puis vers la collection souhaitée.
L'ID de la collection apparaît dans l'URL ou dans les détails de la collection.

= Comment obtenir l'API Key ? =

Dans Bunny Stream, allez dans Stream > API pour obtenir votre clé API.

= La progression est-elle trackée pour les visiteurs non connectés ? =

Non, le tracking de progression nécessite que l'utilisateur soit connecté à WordPress.

= Comment sont triées les vidéos ? =

Les vidéos sont triées alphabétiquement par leur titre.
Pour un tri numérique, nommez vos vidéos avec des préfixes (01-, 02-, etc.).

== Changelog ==

= 1.0.0 =
* Version initiale
* Affichage de playlists Bunny Stream
* Tracking de progression utilisateur
* Interface responsive
* Page d'administration

== Upgrade Notice ==

= 1.0.0 =
Version initiale du plugin.
