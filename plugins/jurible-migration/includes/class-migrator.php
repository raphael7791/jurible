<?php
/**
 * Migrateur - Utilise WP-CLI pour créer les posts
 */

defined('ABSPATH') || exit;

class Jurible_Migration_Migrator {

    private $converter;

    public function __construct() {
        $this->converter = new Jurible_Migration_Converter();
    }

    /**
     * Migrer un article de aideauxtd.com vers jurible.com
     */
    public function migrate(int $sourcePostId): int|WP_Error {
        // 1. Récupérer les infos du post source
        $sourcePost = $this->getSourcePost($sourcePostId);
        if (is_wp_error($sourcePost)) {
            return $sourcePost;
        }

        // 2. Récupérer le contenu Thrive
        $thriveContent = $this->getSourceThriveContent($sourcePostId);
        if (empty($thriveContent)) {
            return new WP_Error('no_content', 'Aucun contenu Thrive trouvé pour cet article');
        }

        // 3. Convertir en Gutenberg
        $gutenbergContent = $this->converter->convert($thriveContent);

        // 4. Importer les images dans la médiathèque via WP-CLI
        $importedImages = $this->converter->getImportedImages();
        $imageIds = $this->importImagesToMediaLibrary($importedImages);

        // 5. Créer le post via WP-CLI
        $newPostId = $this->createPost([
            'title' => $sourcePost['post_title'],
            'content' => $gutenbergContent,
            'status' => 'draft',
            'date' => $sourcePost['post_date'],
            'author' => get_current_user_id(),
        ]);

        if (is_wp_error($newPostId)) {
            return $newPostId;
        }

        // 6. Importer l'image à la une
        $this->importFeaturedImage($sourcePostId, $newPostId);

        // 7. Copier les catégories/tags si nécessaire
        $this->copyTaxonomies($sourcePostId, $newPostId);

        // 8. Migrer les données SEO (Yoast → Rank Math)
        $this->migrateSeoData($sourcePostId, $newPostId);

        return $newPostId;
    }

    /**
     * Récupérer les infos du post source via WP-CLI
     */
    private function getSourcePost(int $postId): array|WP_Error {
        $command = sprintf(
            'cd %s && wp post get %d --fields=ID,post_title,post_date,post_name --format=json --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $postId
        );

        $output = shell_exec($command);
        $post = json_decode($output, true);

        if (!$post) {
            return new WP_Error('post_not_found', 'Article source non trouvé');
        }

        return $post;
    }

    /**
     * Récupérer le contenu Thrive via WP-CLI
     */
    private function getSourceThriveContent(int $postId): string {
        $command = sprintf(
            'cd %s && wp post meta get %d tve_updated_post --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $postId
        );

        return trim(shell_exec($command) ?? '');
    }

    /**
     * Importer les images dans la médiathèque via WP-CLI
     */
    private function importImagesToMediaLibrary(array $images): array {
        $ids = [];

        foreach ($images as $image) {
            if (!file_exists($image['path'])) {
                continue;
            }

            // Générer le titre depuis le nom de fichier
            $filename = pathinfo($image['path'], PATHINFO_FILENAME);
            $title = str_replace(['-', '_'], ' ', $filename);
            $title = preg_replace('/\s*Aideauxtd.*$/i', '', $title);
            $title = ucfirst(trim($title));

            // Utiliser wp media import
            $command = sprintf(
                'cd %s && wp media import %s --title=%s --caption=%s --porcelain --allow-root 2>/dev/null',
                escapeshellarg(ABSPATH),
                escapeshellarg($image['path']),
                escapeshellarg($title),
                escapeshellarg($title)
            );

            $attachmentId = trim(shell_exec($command));
            if (is_numeric($attachmentId)) {
                $ids[] = (int) $attachmentId;
            }
        }

        return $ids;
    }

    /**
     * Créer le post via WP-CLI
     */
    private function createPost(array $args): int|WP_Error {
        // Sauvegarder le contenu dans un fichier temporaire
        $contentFile = tempnam(sys_get_temp_dir(), 'jurible_content_');
        file_put_contents($contentFile, $args['content']);

        $authorId = $args['author'] ?? 1;

        $command = sprintf(
            'cd %s && wp post create %s --post_title=%s --post_status=%s --post_date=%s --post_author=%d --porcelain --allow-root 2>/dev/null',
            escapeshellarg(ABSPATH),
            escapeshellarg($contentFile),
            escapeshellarg($args['title']),
            escapeshellarg($args['status']),
            escapeshellarg($args['date']),
            $authorId
        );

        $postId = trim(shell_exec($command));

        // Nettoyer le fichier temporaire
        unlink($contentFile);

        if (!is_numeric($postId)) {
            return new WP_Error('create_failed', 'Échec de la création du post');
        }

        return (int) $postId;
    }

    /**
     * Importer l'image à la une
     */
    private function importFeaturedImage(int $sourcePostId, int $newPostId): void {
        // Récupérer l'ID de l'image à la une source
        $command = sprintf(
            'cd %s && wp post meta get %d _thumbnail_id --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $sourcePostId
        );

        $thumbnailId = trim(shell_exec($command));
        if (empty($thumbnailId) || !is_numeric($thumbnailId)) {
            return;
        }

        // Récupérer le chemin de l'image
        $command = sprintf(
            'cd %s && wp post meta get %d _wp_attached_file --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $thumbnailId
        );

        $attachedFile = trim(shell_exec($command));
        if (empty($attachedFile)) {
            return;
        }

        $sourcePath = JURIBLE_AIDEAUXTD_PATH . '/wp-content/uploads/' . $attachedFile;
        $destPath = ABSPATH . '/wp-content/uploads/' . $attachedFile;

        // Créer le répertoire de destination si nécessaire
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Copier l'image
        if (file_exists($sourcePath) && !file_exists($destPath)) {
            copy($sourcePath, $destPath);
        }

        if (!file_exists($destPath)) {
            return;
        }

        // Importer dans la médiathèque
        $filename = pathinfo($destPath, PATHINFO_FILENAME);
        $title = str_replace(['-', '_'], ' ', $filename);
        $title = preg_replace('/\s*Aideauxtd.*$/i', '', $title);
        $title = ucfirst(trim($title));

        $command = sprintf(
            'cd %s && wp media import %s --title=%s --caption=%s --porcelain --allow-root 2>/dev/null',
            escapeshellarg(ABSPATH),
            escapeshellarg($destPath),
            escapeshellarg($title),
            escapeshellarg($title)
        );

        $newThumbnailId = trim(shell_exec($command));

        if (is_numeric($newThumbnailId)) {
            // Définir comme image à la une
            $command = sprintf(
                'cd %s && wp post meta update %d _thumbnail_id %d --allow-root 2>/dev/null',
                escapeshellarg(ABSPATH),
                $newPostId,
                $newThumbnailId
            );
            shell_exec($command);
        }
    }

    /**
     * Copier les taxonomies (catégories, tags)
     */
    private function copyTaxonomies(int $sourcePostId, int $newPostId): void {
        // Récupérer les catégories source
        $command = sprintf(
            'cd %s && wp post term list %d category --field=name --format=csv --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $sourcePostId
        );

        $categories = trim(shell_exec($command));
        if (!empty($categories)) {
            $catArray = array_filter(explode("\n", $categories));
            foreach ($catArray as $cat) {
                $cat = trim($cat);
                if (empty($cat) || $cat === 'name') continue;

                // Ajouter la catégorie au nouveau post (la créer si elle n'existe pas)
                $command = sprintf(
                    'cd %s && wp post term add %d category %s --allow-root 2>/dev/null',
                    escapeshellarg(ABSPATH),
                    $newPostId,
                    escapeshellarg($cat)
                );
                shell_exec($command);
            }
        }

        // Récupérer les tags source
        $command = sprintf(
            'cd %s && wp post term list %d post_tag --field=name --format=csv --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $sourcePostId
        );

        $tags = trim(shell_exec($command));
        if (!empty($tags)) {
            $tagArray = array_filter(explode("\n", $tags));
            foreach ($tagArray as $tag) {
                $tag = trim($tag);
                if (empty($tag) || $tag === 'name') continue;

                $command = sprintf(
                    'cd %s && wp post term add %d post_tag %s --allow-root 2>/dev/null',
                    escapeshellarg(ABSPATH),
                    $newPostId,
                    escapeshellarg($tag)
                );
                shell_exec($command);
            }
        }
    }

    /**
     * Migrer les données SEO de Yoast vers Rank Math
     */
    private function migrateSeoData(int $sourcePostId, int $newPostId): void {
        // Mapping Yoast → Rank Math
        $metaMapping = [
            '_yoast_wpseo_title' => 'rank_math_title',
            '_yoast_wpseo_metadesc' => 'rank_math_description',
            '_yoast_wpseo_focuskw' => 'rank_math_focus_keyword',
            '_yoast_wpseo_opengraph-title' => 'rank_math_facebook_title',
            '_yoast_wpseo_opengraph-description' => 'rank_math_facebook_description',
            '_yoast_wpseo_twitter-title' => 'rank_math_twitter_title',
            '_yoast_wpseo_twitter-description' => 'rank_math_twitter_description',
        ];

        foreach ($metaMapping as $yoastKey => $rankMathKey) {
            $value = $this->getSourceMeta($sourcePostId, $yoastKey);
            if (!empty($value)) {
                // Remplacer aideauxtd.com par jurible.com dans les valeurs
                $value = str_replace('aideauxtd.com', 'jurible.com', $value);
                $this->setDestMeta($newPostId, $rankMathKey, $value);
            }
        }

        // Migrer l'image Open Graph
        $this->migrateSeoImage($sourcePostId, $newPostId);

        // Activer les robots index par défaut
        $this->setDestMeta($newPostId, 'rank_math_robots', 'a:1:{i:0;s:5:"index";}');
    }

    /**
     * Migrer l'image sociale (Open Graph) de Yoast vers Rank Math
     */
    private function migrateSeoImage(int $sourcePostId, int $newPostId): void {
        // Récupérer l'URL de l'image OG depuis Yoast
        $ogImageUrl = $this->getSourceMeta($sourcePostId, '_yoast_wpseo_opengraph-image');

        if (empty($ogImageUrl)) {
            return;
        }

        // Extraire le chemin du fichier
        $urlPath = parse_url($ogImageUrl, PHP_URL_PATH);
        if (empty($urlPath)) {
            return;
        }

        $filename = basename($urlPath);

        // Récupérer year/month depuis le chemin
        if (preg_match('#/uploads/(\d{4}/\d{2})/#', $urlPath, $match)) {
            $yearMonth = $match[1];
        } else {
            $yearMonth = date('Y/m');
        }

        $sourcePath = JURIBLE_AIDEAUXTD_PATH . '/wp-content/uploads/' . $yearMonth . '/' . $filename;
        $destPath = ABSPATH . 'wp-content/uploads/' . $yearMonth . '/' . $filename;

        // Créer le répertoire si nécessaire
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Copier l'image si elle n'existe pas
        if (file_exists($sourcePath) && !file_exists($destPath)) {
            copy($sourcePath, $destPath);
        }

        if (!file_exists($destPath)) {
            return;
        }

        // Importer dans la médiathèque
        $title = str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME));
        $title = preg_replace('/\s*Aideauxtd.*$/i', '', $title);
        $title = ucfirst(trim($title));

        $command = sprintf(
            'cd %s && wp media import %s --title=%s --porcelain --allow-root 2>/dev/null',
            escapeshellarg(ABSPATH),
            escapeshellarg($destPath),
            escapeshellarg($title)
        );

        $attachmentId = trim(shell_exec($command));

        if (is_numeric($attachmentId)) {
            // Définir l'image pour Facebook/Twitter dans Rank Math
            $newImageUrl = home_url('/wp-content/uploads/' . $yearMonth . '/' . $filename);
            $this->setDestMeta($newPostId, 'rank_math_facebook_image', $newImageUrl);
            $this->setDestMeta($newPostId, 'rank_math_facebook_image_id', $attachmentId);
            $this->setDestMeta($newPostId, 'rank_math_twitter_use_facebook', 'on');
        }
    }

    /**
     * Récupérer une meta du post source via WP-CLI
     */
    private function getSourceMeta(int $postId, string $metaKey): string {
        $command = sprintf(
            'cd %s && wp post meta get %d %s --allow-root 2>/dev/null',
            escapeshellarg(JURIBLE_AIDEAUXTD_PATH),
            $postId,
            escapeshellarg($metaKey)
        );

        return trim(shell_exec($command) ?? '');
    }

    /**
     * Définir une meta sur le post destination via WP-CLI
     */
    private function setDestMeta(int $postId, string $metaKey, string $value): void {
        $command = sprintf(
            'cd %s && wp post meta update %d %s %s --allow-root 2>/dev/null',
            escapeshellarg(ABSPATH),
            $postId,
            escapeshellarg($metaKey),
            escapeshellarg($value)
        );

        shell_exec($command);
    }
}
