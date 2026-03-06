<?php
/**
 * Thrive to Gutenberg Converter
 *
 * Converts Thrive Architect HTML content to Gutenberg blocks
 * for migrating articles from aideauxtd.com to jurible.com
 *
 * Usage:
 *   php thrive-to-gutenberg.php <post_id>
 *   php thrive-to-gutenberg.php --all
 *   php thrive-to-gutenberg.php --test <post_id>
 */

// Include media import functions
require_once __DIR__ . '/import-media.php';

class ThriveToGutenbergConverter
{
    private $content;
    private $blocks = [];
    private $imagesToImport = [];
    private $localSitePath;
    private $localSiteUrl;
    private $featuredImageId = null;

    public function __construct(string $localSitePath = null, string $localSiteUrl = null)
    {
        $this->localSitePath = $localSitePath ?? getenv('HOME') . '/Local Sites/jurible-local/app/public';
        $this->localSiteUrl = $localSiteUrl ?? 'http://jurible-local.local';
    }

    /**
     * Get list of images that were imported
     */
    public function getImagesToImport(): array
    {
        return $this->imagesToImport;
    }

    /**
     * Get the featured image attachment ID (if set)
     */
    public function getFeaturedImageId(): ?int
    {
        return $this->featuredImageId;
    }

    /**
     * Set featured image from source post
     */
    public function setFeaturedImageFromSource(int $sourcePostId): ?int
    {
        // Get featured image URL from source via SSH
        $cmd = sprintf(
            'ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post meta get %d _thumbnail_id --allow-root 2>/dev/null"',
            $sourcePostId
        );
        $thumbnailId = trim(shell_exec($cmd));

        if (empty($thumbnailId) || !is_numeric($thumbnailId)) {
            return null;
        }

        // Get the image URL
        $cmd = sprintf(
            'ssh aideauxtd@dogfish.o2switch.net "cd /home/aideauxtd/public_html && wp post get %s --field=guid --allow-root 2>/dev/null"',
            $thumbnailId
        );
        $imageUrl = trim(shell_exec($cmd));

        if (empty($imageUrl)) {
            return null;
        }

        // Download and import the featured image
        $localPath = $this->downloadImageViaScp($imageUrl);
        if ($localPath) {
            $attachmentId = import_image_to_media_library($localPath);
            $this->featuredImageId = $attachmentId;
            return $attachmentId;
        }

        return null;
    }

    /**
     * Download image via SCP and return local path
     */
    private function downloadImageViaScp(string $url): ?string
    {
        if (strpos($url, 'aideauxtd.com') === false) {
            return null;
        }

        $urlPath = parse_url($url, PHP_URL_PATH);
        $filename = basename($urlPath);

        if (preg_match('#/uploads/(\d{4}/\d{2})/#', $urlPath, $match)) {
            $yearMonth = $match[1];
        } else {
            $yearMonth = date('Y/m');
        }

        $uploadsDir = $this->localSitePath . '/wp-content/uploads/' . $yearMonth;
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $localPath = $uploadsDir . '/' . $filename;

        if (file_exists($localPath)) {
            return $localPath;
        }

        $remotePath = '/home/aideauxtd/public_html' . $urlPath;
        $scpCommand = sprintf(
            'scp -q aideauxtd@dogfish.o2switch.net:%s %s 2>/dev/null',
            escapeshellarg($remotePath),
            escapeshellarg($localPath)
        );

        exec($scpCommand, $output, $returnCode);
        return ($returnCode === 0 && file_exists($localPath)) ? $localPath : null;
    }

    /**
     * Convert Thrive HTML to Gutenberg blocks
     */
    public function convert(string $html): string
    {
        $this->content = $html;
        $this->blocks = [];

        // Remove CTA blocks first (before any other processing)
        $this->content = $this->removeCTABlocks($this->content);

        // Pre-process: normalize HTML
        $this->content = $this->normalizeHtml($this->content);

        // Convert special blocks first (they contain nested elements)
        $this->content = $this->convertExempleBlocks($this->content);
        $this->content = $this->convertAparteBlocks($this->content);
        $this->content = $this->convertCodePenalBlocks($this->content);

        // Convert media BEFORE stripping containers (URLs are in Thrive attributes)
        $this->content = $this->convertYouTubeVideos($this->content);
        $this->content = $this->convertImages($this->content);

        // Strip Thrive containers
        $this->content = $this->stripThriveContainers($this->content);

        // Convert standard elements
        $this->content = $this->convertHeadings($this->content);
        $this->content = $this->convertTables($this->content);
        $this->content = $this->convertLists($this->content);
        $this->content = $this->convertParagraphs($this->content);

        // Restore infoboxes after paragraph conversion
        $this->content = $this->restoreInfoboxes($this->content);

        // Clean up
        $this->content = $this->cleanupOutput($this->content);

        return $this->content;
    }

    /**
     * Normalize HTML for easier parsing
     */
    private function normalizeHtml(string $html): string
    {
        // Remove Thrive-specific inline styles that we don't need
        $html = preg_replace('/style="[^"]*--tve-[^"]*"/i', '', $html);

        // Remove empty spans
        $html = preg_replace('/<span>\s*<\/span>/i', '', $html);

        // Normalize whitespace
        $html = preg_replace('/\s+/', ' ', $html);

        // Convert highlighted text to mark
        $html = preg_replace(
            '/<span style="[^"]*--tcb-text-highlight-color[^"]*">([^<]+)<\/span>/i',
            '<mark>$1</mark>',
            $html
        );

        return trim($html);
    }

    /**
     * Remove CTA promotional blocks
     * These are content boxes with promotional text for the Académie/courses
     */
    private function removeCTABlocks(string $html): string
    {
        // Pattern to match thrv_contentbox_shortcode blocks containing CTA patterns
        // We match the entire content box and check if it contains promotional content
        $pattern = '/<div[^>]*class="[^"]*thrv_contentbox_shortcode[^"]*"[^>]*>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/i';

        $html = preg_replace_callback($pattern, function($matches) {
            $block = $matches[0];
            // Check if this content box contains CTA patterns
            $ctaPatterns = [
                'Académie',
                'academie',
                'Rejoignez',
                'En savoir plus',
                'cours complet',
                'Besoin d\'un cours',
                'Academie-droit-CTA',
                'fiches de révision',
                'flashcards',
                'annales corrigées',
                'réussir vos partiels',
            ];

            foreach ($ctaPatterns as $ctaPattern) {
                if (stripos($block, $ctaPattern) !== false) {
                    // This is a CTA block, remove it
                    return '';
                }
            }

            // Not a CTA, keep it
            return $block;
        }, $html);

        return $html;
    }

    /**
     * Convert Bloc Exemple (📌)
     * Thrive structure: <img alt="📌"> Exemple</div>...</div><div class="thrv_text_element"><p>content</p></div>
     */
    private function convertExempleBlocks(string $html): string
    {
        // Pattern: emoji image + "Exemple" + closing divs + text element div with paragraphs
        $pattern = '/<img[^>]*alt="📌"[^>]*>\s*Exemple\s*(?:<\/div>)*\s*(?:<\/div>)*\s*<div[^>]*class="[^"]*thrv_text_element[^"]*"[^>]*>((?:\s*<p[^>]*>.+?<\/p>)+)\s*<\/div>/is';

        return preg_replace_callback($pattern, function($matches) {
            $content = $this->extractParagraphsContent($matches[1]);
            // Marquer le bloc pour éviter la conversion en paragraphe
            return "###INFOBOX_EXEMPLE###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);
    }

    /**
     * Restore placeholders after strip/conversion
     */
    private function restoreInfoboxes(string $html): string
    {
        // Restore Exemple blocks
        $html = preg_replace_callback('/###INFOBOX_EXEMPLE###([^#]+)###\/INFOBOX###/', function($matches) {
            $content = base64_decode($matches[1]);
            return $this->createInfobox('exemple', 'Exemple', $content);
        }, $html);

        // Restore Retenir blocks (Aparté → "À retenir")
        $html = preg_replace_callback('/###INFOBOX_RETENIR###([^#]+)###TITLE###([^#]+)###\/INFOBOX###/', function($matches) {
            $content = base64_decode($matches[1]);
            // Titre toujours "À retenir" (défaut du bloc)
            return $this->createInfobox('retenir', 'À retenir', $content);
        }, $html);

        // Restore YouTube embeds
        $html = preg_replace_callback('/###YOUTUBE###([a-zA-Z0-9_-]+)###\/YOUTUBE###/', function($matches) {
            $videoId = $matches[1];
            $youtubeUrl = 'https://www.youtube.com/watch?v=' . $videoId;
            return sprintf(
                '<!-- wp:embed {"url":"%s","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
%s
</div></figure>
<!-- /wp:embed -->

',
                $youtubeUrl,
                $youtubeUrl
            );
        }, $html);

        // Restore Images
        $html = preg_replace_callback('/###IMAGE###([^#]+)###ALT###([^#]*)###\/IMAGE###/', function($matches) {
            $src = base64_decode($matches[1]);
            $alt = base64_decode($matches[2]);
            return $this->createImage($src, $alt);
        }, $html);

        return $html;
    }

    /**
     * Convert Bloc Aparté (💬)
     * Thrive structures:
     *   - 💬 <span></span><span>Title</span></div>...</div><div class="thrv_wrapper thrv_text_element"><p>content</p></div>
     *   - 💬 <span>Title</span></div>...</div><div class="thrv_wrapper thrv_text_element"><p>content</p></div>
     */
    private function convertAparteBlocks(string $html): string
    {
        // Pattern: 💬 emoji + optional empty spans + span with title + multiple closing divs + thrv_wrapper text element
        $pattern = '/💬\s*(?:<span[^>]*><\/span>\s*)*<span[^>]*>([^<]+)<\/span>(?:\s*<\/div>)+\s*<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:\s*<p[^>]*>.+?<\/p>)+)\s*<\/div>/is';

        return preg_replace_callback($pattern, function($matches) {
            $title = trim($matches[1]);
            $content = $this->extractParagraphsContent($matches[2]);
            // Marquer le bloc pour éviter la conversion en paragraphe
            return "###INFOBOX_RETENIR###" . base64_encode($content) . "###TITLE###" . base64_encode($title) . "###/INFOBOX###";
        }, $html);
    }

    /**
     * Convert Bloc Code Pénal (🔎)
     * Pattern: 🔎 Reference<p style="font-size..."><em>quote</em></p>
     */
    private function convertCodePenalBlocks(string $html): string
    {
        $pattern = '/🔎\s*([^<]+?)(<p[^>]*>(?:\s*<em>)?(.+?)(?:<\/em>\s*)?<\/p>)+/is';

        return preg_replace_callback($pattern, function($matches) {
            $source = trim($matches[1]);
            $citation = $this->cleanInlineHtml($matches[3] ?? '');
            return $this->createCitation($citation, $source);
        }, $html);
    }

    /**
     * Convert headings
     */
    private function convertHeadings(string $html): string
    {
        // H2 with Thrive ID
        $html = preg_replace_callback(
            '/<h2[^>]*(?:id="[^"]*")?[^>]*>(.+?)<\/h2>/is',
            function($matches) {
                $content = $this->cleanInlineHtml($matches[1]);
                return $this->createHeading(2, $content);
            },
            $html
        );

        // H3
        $html = preg_replace_callback(
            '/<h3[^>]*>(.+?)<\/h3>/is',
            function($matches) {
                $content = $this->cleanInlineHtml($matches[1]);
                return $this->createHeading(3, $content);
            },
            $html
        );

        // H4
        $html = preg_replace_callback(
            '/<h4[^>]*>(.+?)<\/h4>/is',
            function($matches) {
                $content = $this->cleanInlineHtml($matches[1]);
                return $this->createHeading(4, $content);
            },
            $html
        );

        return $html;
    }

    /**
     * Convert images
     */
    private function convertImages(string $html): string
    {
        // Images wrapped in Thrive span (tve_image_frame or plain span)
        $html = preg_replace_callback(
            '/<span[^>]*(?:class="[^"]*tve_image[^"]*")?[^>]*>\s*<img([^>]*)>\s*<\/span>/is',
            function($matches) {
                return $this->extractAndCreateImage($matches[1]);
            },
            $html
        );

        // Standalone images from aideauxtd.com (not already converted, not emojis)
        $html = preg_replace_callback(
            '/<img([^>]*aideauxtd\.com[^>]*)>/is',
            function($matches) {
                // Skip emoji images
                if (strpos($matches[1], 'emoji') !== false) {
                    return $matches[0];
                }
                return $this->extractAndCreateImage($matches[1]);
            },
            $html
        );

        return $html;
    }

    /**
     * Extract src and alt from img attributes and create image block
     */
    private function extractAndCreateImage(string $attributes): string
    {
        // Extract src
        if (!preg_match('/src="([^"]+)"/i', $attributes, $srcMatch)) {
            return ''; // No src, skip
        }
        $src = $srcMatch[1];

        // Skip emoji images
        if (strpos($src, 'emoji') !== false || strpos($src, 's.w.org') !== false) {
            return '';
        }

        // Generate alt/caption from filename (remove Aideauxtd and after)
        $filename = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_FILENAME);
        $alt = str_replace(['-', '_'], ' ', $filename);
        $alt = preg_replace('/\s*Aideauxtd.*$/i', '', $alt);
        $alt = ucfirst(trim($alt));

        // Download and import image
        $localSrc = $this->downloadAndImportImage($src);
        if (!$localSrc) {
            // Fallback to just changing domain if download fails
            $localSrc = str_replace('aideauxtd.com', 'jurible.com', $src);
        }

        // Use placeholder to protect from strip (alt is also used as caption)
        return "###IMAGE###" . base64_encode($localSrc) . "###ALT###" . base64_encode($alt) . "###/IMAGE###";
    }

    /**
     * Download image from source via SCP and save to local uploads
     */
    private function downloadAndImportImage(string $url): ?string
    {
        // Only process aideauxtd.com images
        if (strpos($url, 'aideauxtd.com') === false) {
            return null;
        }

        // Extract path from URL
        $urlPath = parse_url($url, PHP_URL_PATH);
        $filename = basename($urlPath);
        if (empty($filename)) {
            return null;
        }

        // Keep original year/month structure from source
        if (preg_match('#/uploads/(\d{4}/\d{2})/#', $urlPath, $match)) {
            $yearMonth = $match[1];
        } else {
            $yearMonth = date('Y/m');
        }

        $uploadsDir = $this->localSitePath . '/wp-content/uploads/' . $yearMonth;
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $localPath = $uploadsDir . '/' . $filename;
        $localUrl = $this->localSiteUrl . '/wp-content/uploads/' . $yearMonth . '/' . $filename;

        // Skip download if already exists, but still ensure it's in media library
        if (file_exists($localPath)) {
            $attachmentId = import_image_to_media_library($localPath);
            $this->imagesToImport[] = ['source' => $url, 'local' => $localUrl, 'path' => $localPath, 'attachment_id' => $attachmentId];
            return $localUrl;
        }

        // Download via SCP from O2switch server
        $remotePath = '/home/aideauxtd/public_html' . $urlPath;
        $scpCommand = sprintf(
            'scp -q aideauxtd@dogfish.o2switch.net:%s %s 2>/dev/null',
            escapeshellarg($remotePath),
            escapeshellarg($localPath)
        );

        exec($scpCommand, $output, $returnCode);
        if ($returnCode !== 0 || !file_exists($localPath)) {
            error_log("Failed to download image via SCP: $url");
            return null;
        }

        // Import into WordPress media library
        $attachmentId = import_image_to_media_library($localPath);
        $this->imagesToImport[] = ['source' => $url, 'local' => $localUrl, 'path' => $localPath, 'attachment_id' => $attachmentId];
        return $localUrl;
    }

    /**
     * Convert YouTube videos (Thrive responsive video blocks)
     */
    private function convertYouTubeVideos(string $html): string
    {
        // Pattern for complete Thrive responsive video block with data-url
        // Captures the entire block including all nested content
        $pattern = '/<div[^>]*class="[^"]*thrv_responsive_video[^"]*"[^>]*data-url="([^"]+)"[^>]*>[\s\S]*?<\/iframe>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/is';

        $html = preg_replace_callback($pattern, function($matches) {
            $url = $matches[1];
            return $this->createYouTubeEmbed($url);
        }, $html);

        // Clean up any remaining video containers
        $html = preg_replace('/<div[^>]*class="[^"]*tve_responsive_video_container[^"]*"[^>]*>[\s\S]*?<\/div>\s*<\/div>/is', '', $html);
        $html = preg_replace('/<div[^>]*class="[^"]*video_overlay[^"]*"[^>]*>.*?<\/div>/is', '', $html);

        return $html;
    }

    private function createYouTubeEmbed(string $url): string
    {
        // Normalize URL to standard YouTube format
        $videoId = '';
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $match)) {
            $videoId = $match[1];
        } elseif (preg_match('/youtube\.com.*[?&]v=([a-zA-Z0-9_-]+)/', $url, $match)) {
            $videoId = $match[1];
        } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $match)) {
            $videoId = $match[1];
        }

        if (empty($videoId)) {
            return '';
        }

        // Use placeholder to protect from strip
        return "###YOUTUBE###" . $videoId . "###/YOUTUBE###";
    }

    /**
     * Convert tables
     */
    private function convertTables(string $html): string
    {
        $pattern = '/<table[^>]*>(.+?)<\/table>/is';

        return preg_replace_callback($pattern, function($matches) {
            $tableContent = $matches[1];

            // Remove all attributes from tags
            $tableContent = preg_replace('/<(thead|tbody|tr|th|td|tfoot)([^>]*)>/i', '<$1>', $tableContent);

            // Clean inline content in cells
            $tableContent = preg_replace_callback('/<(td|th)>(.*?)<\/\1>/is', function($cell) {
                $content = $this->cleanInlineHtml($cell[2]);
                return '<' . $cell[1] . '>' . $content . '</' . $cell[1] . '>';
            }, $tableContent);

            // Ensure tbody exists - wrap rows if no tbody
            if (stripos($tableContent, '<tbody') === false) {
                // Extract thead if present
                $thead = '';
                if (preg_match('/<thead[^>]*>.*?<\/thead>/is', $tableContent, $theadMatch)) {
                    $thead = $theadMatch[0];
                    $tableContent = str_replace($thead, '', $tableContent);
                }

                // Wrap remaining content in tbody
                $tableContent = $thead . '<tbody>' . trim($tableContent) . '</tbody>';
            }

            return $this->createTable($tableContent);
        }, $html);
    }

    /**
     * Convert lists
     */
    private function convertLists(string $html): string
    {
        // Unordered lists
        $html = preg_replace_callback(
            '/<ul[^>]*>(.+?)<\/ul>/is',
            function($matches) {
                $items = $this->extractListItems($matches[1]);
                return $this->createList($items, false);
            },
            $html
        );

        // Ordered lists
        $html = preg_replace_callback(
            '/<ol[^>]*>(.+?)<\/ol>/is',
            function($matches) {
                $items = $this->extractListItems($matches[1]);
                return $this->createList($items, true);
            },
            $html
        );

        return $html;
    }

    /**
     * Convert paragraphs
     */
    private function convertParagraphs(string $html): string
    {
        return preg_replace_callback(
            '/<p[^>]*>(.+?)<\/p>/is',
            function($matches) {
                $content = $this->cleanInlineHtml($matches[1]);
                // Skip empty paragraphs
                if (empty(trim(strip_tags($content)))) {
                    return '';
                }
                return $this->createParagraph($content);
            },
            $html
        );
    }

    // =========================================================================
    // Block Generators
    // =========================================================================

    private function createInfobox(string $type, string $title, string $content): string
    {
        $icons = [
            'exemple' => '💡',
            'attention' => '⚠️',
            'definition' => '📖',
            'important' => '📌',
            'astuce' => '🎯',
            'retenir' => '🌟',
            'conditions' => '📌',
        ];
        $icon = $icons[$type] ?? '💡';

        // Nettoyer le contenu (pas de HTML sauf inline)
        $cleanContent = strip_tags($content, '<strong><em><a><mark><br>');

        return sprintf(
            '<!-- wp:jurible/infobox {"type":"%s","title":"%s","content":"%s"} -->
<div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-%s"><div class="jurible-infobox-header"><span class="jurible-infobox-icon">%s</span><span class="jurible-infobox-title">%s</span></div><p class="jurible-infobox-content">%s</p></div>
<!-- /wp:jurible/infobox -->

',
            $type,
            $this->escapeAttribute($title),
            $this->escapeAttribute($cleanContent),
            $type,
            $icon,
            htmlspecialchars($title),
            $cleanContent
        );
    }

    private function createCitation(string $citation, string $source): string
    {
        return sprintf(
            '<!-- wp:jurible/citation {"citation":"%s","source":"%s"} -->
<blockquote class="wp-block-jurible-citation jurible-citation">
<p class="jurible-citation-text">%s</p>
<cite class="jurible-citation-source">%s</cite>
</blockquote>
<!-- /wp:jurible/citation -->

',
            $this->escapeAttribute($citation),
            $this->escapeAttribute($source),
            $citation,
            htmlspecialchars($source)
        );
    }

    private function createHeading(int $level, string $content): string
    {
        return sprintf(
            '<!-- wp:heading {"level":%d} -->
<h%d class="wp-block-heading">%s</h%d>
<!-- /wp:heading -->

',
            $level,
            $level,
            $content,
            $level
        );
    }

    private function createParagraph(string $content): string
    {
        return sprintf(
            '<!-- wp:paragraph -->
<p>%s</p>
<!-- /wp:paragraph -->

',
            $content
        );
    }

    private function createImage(string $src, string $alt): string
    {
        $caption = $alt; // Use alt as caption
        if (!empty($caption)) {
            return sprintf(
                '<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="%s" alt="%s"/><figcaption class="wp-element-caption">%s</figcaption></figure>
<!-- /wp:image -->

',
                htmlspecialchars($src),
                htmlspecialchars($alt),
                htmlspecialchars($caption)
            );
        }
        return sprintf(
            '<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="%s" alt="%s"/></figure>
<!-- /wp:image -->

',
            htmlspecialchars($src),
            htmlspecialchars($alt)
        );
    }

    private function createTable(string $content): string
    {
        return sprintf(
            '<!-- wp:table -->
<figure class="wp-block-table"><table>%s</table></figure>
<!-- /wp:table -->

',
            $content
        );
    }

    private function createList(array $items, bool $ordered): string
    {
        $tag = $ordered ? 'ol' : 'ul';
        $listItems = '';
        foreach ($items as $item) {
            $listItems .= sprintf('<!-- wp:list-item -->
<li>%s</li>
<!-- /wp:list-item -->', $item);
        }

        return sprintf(
            '<!-- wp:list %s-->
<%s>%s</%s>
<!-- /wp:list -->

',
            $ordered ? '{"ordered":true} ' : '',
            $tag,
            $listItems,
            $tag
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function cleanInlineHtml(string $html): string
    {
        // Keep allowed tags
        $html = strip_tags($html, '<strong><em><a><mark><br>');

        // Clean up links
        $html = preg_replace('/<a[^>]*href="([^"]*)"[^>]*>/', '<a href="$1">', $html);

        // Convert aideauxtd.com links to jurible.com
        $html = str_replace('aideauxtd.com', 'jurible.com', $html);

        return trim($html);
    }

    private function extractParagraphsContent(string $html): string
    {
        preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $matches);
        $content = implode("\n", $matches[1] ?? []);
        return $this->cleanInlineHtml($content);
    }

    private function extractListItems(string $html): array
    {
        preg_match_all('/<li[^>]*>(.+?)<\/li>/is', $html, $matches);
        return array_map([$this, 'cleanInlineHtml'], $matches[1] ?? []);
    }

    private function escapeAttribute(string $value): string
    {
        return str_replace(['"', "\n", "\r"], ['\"', ' ', ''], $value);
    }

    /**
     * Strip Thrive container divs while preserving content
     */
    private function stripThriveContainers(string $html): string
    {
        // Remove Thrive container opening tags
        $html = preg_replace('/<div[^>]*class="[^"]*(?:thrv_|tcb-|tve-|kbu)[^"]*"[^>]*>/i', '', $html);

        // Remove closing div tags (they're now orphaned)
        $html = preg_replace('/<\/div>/i', '', $html);

        // Remove Thrive spans with only styling (preserve content)
        $html = preg_replace('/<span[^>]*(?:data-css|tcb-)[^>]*>([^<]*)<\/span>/i', '$1', $html);

        // Remove style tags
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

        return $html;
    }

    private function cleanupOutput(string $html): string
    {
        // Remove any remaining Thrive artifacts
        $html = preg_replace('/<img[^>]*emoji[^>]*>/i', '', $html);
        $html = preg_replace('/<img[^>]*s\.w\.org[^>]*>/i', '', $html);

        // Remove empty lines and excessive whitespace
        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        $html = preg_replace('/^\s+$/m', '', $html);

        // Remove any leftover emoji characters not in blocks
        $html = preg_replace('/^[📌💬🔎]\s*/m', '', $html);

        // Remove leftover "Exemple" and "Aparté" text
        $html = preg_replace('/^\s*Exemple\s*$/m', '', $html);
        $html = preg_replace('/^\s*Aparté\s*$/m', '', $html);

        return trim($html);
    }
}

// =============================================================================
// CLI Interface
// =============================================================================

// CLI usage - only when run directly (not when included)
if (php_sapi_name() === 'cli' && realpath($argv[0]) === realpath(__FILE__)) {
    $converter = new ThriveToGutenbergConverter();

    // Test mode with sample content
    if (isset($argv[1]) && $argv[1] === '--test-sample') {
        $sample = '<p>Introduction paragraph.</p>
<h2 id="t-123">I. First Section</h2>
<p>Some content here.</p>
<img decoding="async" role="img" alt="📌" src="https://s.w.org/images/core/emoji/15.0.3/svg/1f4cc.svg" loading="lazy"> Exemple
<p style="font-size: var(--tve-font-size, 16px);">This is an example of something important.</p>
<p>More content after the example.</p>
💬 <span style="--tcb-applied-color: var$(--tcb-color-6)"></span><span style="--tcb-applied-color: var$(--tcb-color-0)">Aparté important</span>
<p style="font-size: var(--tve-font-size, 16px);">This is an aside with additional information.</p>';

        echo "=== INPUT ===\n";
        echo $sample . "\n\n";
        echo "=== OUTPUT ===\n";
        echo $converter->convert($sample) . "\n";
        exit(0);
    }

    // Convert from file
    if (isset($argv[1]) && file_exists($argv[1])) {
        $html = file_get_contents($argv[1]);
        echo $converter->convert($html);
        exit(0);
    }

    // Convert from stdin
    if (isset($argv[1]) && $argv[1] === '--stdin') {
        $html = file_get_contents('php://stdin');
        echo $converter->convert($html);
        exit(0);
    }

    echo "Usage:\n";
    echo "  php thrive-to-gutenberg.php --test-sample     # Test avec exemple\n";
    echo "  php thrive-to-gutenberg.php fichier.html      # Convertir un fichier\n";
    echo "  cat fichier.html | php thrive-to-gutenberg.php --stdin  # Depuis stdin\n";
}
