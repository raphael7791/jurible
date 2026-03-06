<?php
/**
 * Convertisseur Thrive → Gutenberg
 */

defined('ABSPATH') || exit;

class Jurible_Migration_Converter {

    private $content;
    private $sourceUploadsPath;
    private $destUploadsPath;
    private $destUploadsUrl;
    private $importedImages = [];

    public function __construct() {
        $this->sourceUploadsPath = JURIBLE_AIDEAUXTD_PATH . '/wp-content/uploads';
        $this->destUploadsPath = ABSPATH . 'wp-content/uploads';
        $this->destUploadsUrl = home_url('/wp-content/uploads');
    }

    public function getImportedImages(): array {
        return $this->importedImages;
    }

    public function convert(string $html): string {
        $this->content = $html;
        $this->importedImages = [];

        // Remove CTA blocks first
        $this->content = $this->removeCTABlocks($this->content);

        // Normalize HTML
        $this->content = $this->normalizeHtml($this->content);

        // Convert special blocks
        $this->content = $this->convertExempleBlocks($this->content);
        $this->content = $this->convertAparteBlocks($this->content);

        // Convert media
        $this->content = $this->convertYouTubeVideos($this->content);
        $this->content = $this->convertImages($this->content);

        // Strip Thrive containers
        $this->content = $this->stripThriveContainers($this->content);

        // Convert standard elements
        $this->content = $this->convertHeadings($this->content);
        $this->content = $this->convertTables($this->content);
        $this->content = $this->convertLists($this->content);
        $this->content = $this->convertParagraphs($this->content);

        // Restore placeholders
        $this->content = $this->restoreInfoboxes($this->content);

        // Cleanup
        $this->content = $this->cleanupOutput($this->content);

        return $this->content;
    }

    private function removeCTABlocks(string $html): string {
        $pattern = '/<div[^>]*class="[^"]*thrv_contentbox_shortcode[^"]*"[^>]*>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/i';

        return preg_replace_callback($pattern, function($matches) {
            $block = $matches[0];
            $ctaPatterns = [
                'Académie', 'academie', 'Rejoignez', 'En savoir plus',
                'cours complet', 'Besoin d\'un cours', 'Academie-droit-CTA',
                'fiches de révision', 'flashcards', 'annales corrigées',
                'réussir vos partiels',
            ];

            foreach ($ctaPatterns as $ctaPattern) {
                if (stripos($block, $ctaPattern) !== false) {
                    return '';
                }
            }
            return $block;
        }, $html);
    }

    private function normalizeHtml(string $html): string {
        $html = preg_replace('/style="[^"]*--tve-[^"]*"/i', '', $html);
        $html = preg_replace('/<span>\s*<\/span>/i', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        return trim($html);
    }

    private function convertExempleBlocks(string $html): string {
        $pattern = '/<img[^>]*alt="📌"[^>]*>\s*Exemple\s*(?:<\/div>)*\s*(?:<\/div>)*\s*<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:\s*<p[^>]*>.+?<\/p>)+)\s*<\/div>/is';

        return preg_replace_callback($pattern, function($matches) {
            $content = $this->extractParagraphsContent($matches[1]);
            return "###INFOBOX_EXEMPLE###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);
    }

    private function convertAparteBlocks(string $html): string {
        $pattern = '/💬\s*(?:<span[^>]*><\/span>\s*)*<span[^>]*>([^<]+)<\/span>(?:\s*<\/div>)+\s*<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:\s*<p[^>]*>.+?<\/p>)+)\s*<\/div>/is';

        return preg_replace_callback($pattern, function($matches) {
            $content = $this->extractParagraphsContent($matches[2]);
            return "###INFOBOX_RETENIR###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);
    }

    private function convertYouTubeVideos(string $html): string {
        $pattern = '/<div[^>]*class="[^"]*thrv_responsive_video[^"]*"[^>]*data-url="([^"]+)"[^>]*>[\s\S]*?<\/iframe>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/is';

        $html = preg_replace_callback($pattern, function($matches) {
            $videoId = $this->extractYouTubeId($matches[1]);
            if ($videoId) {
                return "###YOUTUBE###" . $videoId . "###/YOUTUBE###";
            }
            return '';
        }, $html);

        // Clean remaining video containers
        $html = preg_replace('/<div[^>]*class="[^"]*tve_responsive_video_container[^"]*"[^>]*>[\s\S]*?<\/div>\s*<\/div>/is', '', $html);

        return $html;
    }

    private function extractYouTubeId(string $url): ?string {
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $match)) {
            return $match[1];
        }
        if (preg_match('/youtube\.com.*[?&]v=([a-zA-Z0-9_-]+)/', $url, $match)) {
            return $match[1];
        }
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $match)) {
            return $match[1];
        }
        return null;
    }

    private function convertImages(string $html): string {
        // Images in Thrive spans
        $html = preg_replace_callback(
            '/<span[^>]*(?:class="[^"]*tve_image[^"]*")?[^>]*>\s*<img([^>]*)>\s*<\/span>/is',
            function($matches) {
                return $this->processImage($matches[1]);
            },
            $html
        );

        // Standalone images from aideauxtd.com
        $html = preg_replace_callback(
            '/<img([^>]*aideauxtd\.com[^>]*)>/is',
            function($matches) {
                if (strpos($matches[1], 'emoji') !== false) {
                    return $matches[0];
                }
                return $this->processImage($matches[1]);
            },
            $html
        );

        return $html;
    }

    private function processImage(string $attributes): string {
        if (!preg_match('/src="([^"]+)"/i', $attributes, $srcMatch)) {
            return '';
        }
        $src = $srcMatch[1];

        if (strpos($src, 'emoji') !== false || strpos($src, 's.w.org') !== false) {
            return '';
        }

        // Generate alt from filename
        $filename = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_FILENAME);
        $alt = str_replace(['-', '_'], ' ', $filename);
        $alt = preg_replace('/\s*Aideauxtd.*$/i', '', $alt);
        $alt = ucfirst(trim($alt));

        // Copy image and get new URL
        $newSrc = $this->copyImage($src);
        if (!$newSrc) {
            return '';
        }

        return "###IMAGE###" . base64_encode($newSrc) . "###ALT###" . base64_encode($alt) . "###/IMAGE###";
    }

    private function copyImage(string $url): ?string {
        if (strpos($url, 'aideauxtd.com') === false) {
            return null;
        }

        $urlPath = parse_url($url, PHP_URL_PATH);
        $filename = basename($urlPath);

        // Get year/month from source path
        if (preg_match('#/uploads/(\d{4}/\d{2})/#', $urlPath, $match)) {
            $yearMonth = $match[1];
        } else {
            $yearMonth = date('Y/m');
        }

        $destDir = $this->destUploadsPath . '/' . $yearMonth;
        $destFile = $destDir . '/' . $filename;
        $sourceFile = $this->sourceUploadsPath . '/' . $yearMonth . '/' . $filename;

        // Create directory if needed
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Copy file if not exists
        if (!file_exists($destFile) && file_exists($sourceFile)) {
            copy($sourceFile, $destFile);
        }

        if (file_exists($destFile)) {
            $newUrl = $this->destUploadsUrl . '/' . $yearMonth . '/' . $filename;
            $this->importedImages[] = [
                'path' => $destFile,
                'url' => $newUrl,
                'alt' => '',
            ];
            return $newUrl;
        }

        return null;
    }

    private function stripThriveContainers(string $html): string {
        $html = preg_replace('/<div[^>]*class="[^"]*(?:thrv_|tcb-|tve-|kbu)[^"]*"[^>]*>/i', '', $html);
        $html = preg_replace('/<\/div>/i', '', $html);
        $html = preg_replace('/<span[^>]*(?:data-css|tcb-)[^>]*>([^<]*)<\/span>/i', '$1', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        return $html;
    }

    private function convertHeadings(string $html): string {
        for ($level = 2; $level <= 4; $level++) {
            $html = preg_replace_callback(
                '/<h' . $level . '[^>]*>(.+?)<\/h' . $level . '>/is',
                function($matches) use ($level) {
                    $content = $this->cleanInlineHtml($matches[1]);
                    return $this->createHeading($level, $content);
                },
                $html
            );
        }
        return $html;
    }

    private function convertTables(string $html): string {
        return preg_replace_callback('/<table[^>]*>(.+?)<\/table>/is', function($matches) {
            $content = $matches[1];
            $content = preg_replace('/<(thead|tbody|tr|th|td|tfoot)([^>]*)>/i', '<$1>', $content);

            $content = preg_replace_callback('/<(td|th)>(.*?)<\/\1>/is', function($cell) {
                $cellContent = $this->cleanInlineHtml($cell[2]);
                return '<' . $cell[1] . '>' . $cellContent . '</' . $cell[1] . '>';
            }, $content);

            if (stripos($content, '<tbody') === false) {
                $thead = '';
                if (preg_match('/<thead[^>]*>.*?<\/thead>/is', $content, $theadMatch)) {
                    $thead = $theadMatch[0];
                    $content = str_replace($thead, '', $content);
                }
                $content = $thead . '<tbody>' . trim($content) . '</tbody>';
            }

            return $this->createTable($content);
        }, $html);
    }

    private function convertLists(string $html): string {
        // Unordered
        $html = preg_replace_callback('/<ul[^>]*>(.+?)<\/ul>/is', function($matches) {
            $items = $this->extractListItems($matches[1]);
            return $this->createList($items, false);
        }, $html);

        // Ordered
        $html = preg_replace_callback('/<ol[^>]*>(.+?)<\/ol>/is', function($matches) {
            $items = $this->extractListItems($matches[1]);
            return $this->createList($items, true);
        }, $html);

        return $html;
    }

    private function convertParagraphs(string $html): string {
        return preg_replace_callback('/<p[^>]*>(.+?)<\/p>/is', function($matches) {
            $content = $this->cleanInlineHtml($matches[1]);
            if (empty(trim(strip_tags($content)))) {
                return '';
            }
            return $this->createParagraph($content);
        }, $html);
    }

    private function restoreInfoboxes(string $html): string {
        // Exemple
        $html = preg_replace_callback('/###INFOBOX_EXEMPLE###([^#]+)###\/INFOBOX###/', function($matches) {
            $content = base64_decode($matches[1]);
            return $this->createInfobox('exemple', 'Exemple', $content);
        }, $html);

        // Retenir
        $html = preg_replace_callback('/###INFOBOX_RETENIR###([^#]+)###\/INFOBOX###/', function($matches) {
            $content = base64_decode($matches[1]);
            return $this->createInfobox('retenir', 'À retenir', $content);
        }, $html);

        // YouTube
        $html = preg_replace_callback('/###YOUTUBE###([a-zA-Z0-9_-]+)###\/YOUTUBE###/', function($matches) {
            $videoId = $matches[1];
            $url = 'https://www.youtube.com/watch?v=' . $videoId;
            return sprintf(
                '<!-- wp:embed {"url":"%s","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
%s
</div></figure>
<!-- /wp:embed -->

',
                $url, $url
            );
        }, $html);

        // Images
        $html = preg_replace_callback('/###IMAGE###([^#]+)###ALT###([^#]*)###\/IMAGE###/', function($matches) {
            $src = base64_decode($matches[1]);
            $alt = base64_decode($matches[2]);
            return $this->createImage($src, $alt);
        }, $html);

        return $html;
    }

    private function cleanupOutput(string $html): string {
        $html = preg_replace('/<img[^>]*emoji[^>]*>/i', '', $html);
        $html = preg_replace('/<img[^>]*s\.w\.org[^>]*>/i', '', $html);
        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        $html = preg_replace('/^\s+$/m', '', $html);
        $html = preg_replace('/^[📌💬🔎]\s*/m', '', $html);
        return trim($html);
    }

    // Block creators
    private function createInfobox(string $type, string $title, string $content): string {
        $icons = [
            'exemple' => '💡', 'attention' => '⚠️', 'definition' => '📖',
            'important' => '📌', 'astuce' => '🎯', 'retenir' => '🌟', 'conditions' => '📌',
        ];
        $icon = $icons[$type] ?? '💡';
        $cleanContent = strip_tags($content, '<strong><em><a><mark><br>');

        return sprintf(
            '<!-- wp:jurible/infobox {"type":"%s","title":"%s","content":"%s"} -->
<div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-%s"><div class="jurible-infobox-header"><span class="jurible-infobox-icon">%s</span><span class="jurible-infobox-title">%s</span></div><p class="jurible-infobox-content">%s</p></div>
<!-- /wp:jurible/infobox -->

',
            $type,
            $this->escapeAttribute($title),
            $this->escapeAttribute($cleanContent),
            $type, $icon,
            htmlspecialchars($title),
            $cleanContent
        );
    }

    private function createHeading(int $level, string $content): string {
        return sprintf(
            '<!-- wp:heading {"level":%d} -->
<h%d class="wp-block-heading">%s</h%d>
<!-- /wp:heading -->

',
            $level, $level, $content, $level
        );
    }

    private function createParagraph(string $content): string {
        return sprintf(
            '<!-- wp:paragraph -->
<p>%s</p>
<!-- /wp:paragraph -->

',
            $content
        );
    }

    private function createImage(string $src, string $alt): string {
        $caption = $alt;
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

    private function createTable(string $content): string {
        return sprintf(
            '<!-- wp:table -->
<figure class="wp-block-table"><table>%s</table></figure>
<!-- /wp:table -->

',
            $content
        );
    }

    private function createList(array $items, bool $ordered): string {
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
            $tag, $listItems, $tag
        );
    }

    // Helpers
    private function cleanInlineHtml(string $html): string {
        $html = strip_tags($html, '<strong><em><a><mark><br>');
        $html = preg_replace('/<a[^>]*href="([^"]*)"[^>]*>/', '<a href="$1">', $html);
        $html = str_replace('aideauxtd.com', 'jurible.com', $html);
        return trim($html);
    }

    private function extractParagraphsContent(string $html): string {
        preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $matches);
        $content = implode("\n", $matches[1] ?? []);
        return $this->cleanInlineHtml($content);
    }

    private function extractListItems(string $html): array {
        preg_match_all('/<li[^>]*>(.+?)<\/li>/is', $html, $matches);
        return array_map([$this, 'cleanInlineHtml'], $matches[1] ?? []);
    }

    private function escapeAttribute(string $value): string {
        return str_replace(['"', "\n", "\r"], ['\"', ' ', ''], $value);
    }
}
