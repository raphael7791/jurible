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
    private $sourceText = '';
    private $resultText = '';

    public function __construct() {
        $this->sourceUploadsPath = JURIBLE_AIDEAUXTD_PATH . '/wp-content/uploads';
        $this->destUploadsPath = ABSPATH . 'wp-content/uploads';
        $this->destUploadsUrl = home_url('/wp-content/uploads');
    }

    public function getImportedImages(): array {
        return $this->importedImages;
    }

    /**
     * Validate conversion - returns array with ratio and lost sentences
     */
    public function validateConversion(): array {
        $sourceLen = mb_strlen($this->sourceText);
        $resultLen = mb_strlen($this->resultText);

        $ratio = $sourceLen > 0 ? round($resultLen / $sourceLen * 100) : 0;

        // Find lost sentences (sentences in source but not in result)
        $lostSentences = [];
        $sourceSentences = preg_split('/[.!?]+/', $this->sourceText);
        foreach ($sourceSentences as $sentence) {
            $sentence = trim($sentence);
            if (mb_strlen($sentence) > 30 && stripos($this->resultText, $sentence) === false) {
                $lostSentences[] = mb_substr($sentence, 0, 100) . '...';
            }
        }

        return [
            'ratio' => $ratio,
            'source_length' => $sourceLen,
            'result_length' => $resultLen,
            'lost_sentences' => array_slice($lostSentences, 0, 10), // Max 10
            'is_valid' => $ratio >= 90, // At least 90% text retained
        ];
    }

    public function convert(string $html): string {
        $this->content = $html;
        $this->importedImages = [];

        // Store source text for validation
        $this->sourceText = $this->extractText($html);

        // Convert special blocks FIRST (before removing Thrive elements that could break patterns)
        $this->content = $this->convertExempleBlocks($this->content);
        $this->content = $this->convertAparteBlocks($this->content);

        // Remove CTA and Thrive elements
        $this->content = $this->removeCTABlocks($this->content);

        // Normalize HTML
        $this->content = $this->normalizeHtml($this->content);

        // Convert media
        $this->content = $this->convertYouTubeVideos($this->content);
        $this->content = $this->convertImages($this->content);

        // Strip Thrive containers
        $this->content = $this->stripThriveContainers($this->content);

        // Convert standard elements
        $this->content = $this->convertBlockquotes($this->content);
        $this->content = $this->convertHeadings($this->content);
        $this->content = $this->convertTables($this->content);
        $this->content = $this->convertLists($this->content);
        $this->content = $this->convertParagraphs($this->content);

        // Restore placeholders
        $this->content = $this->restoreInfoboxes($this->content);

        // Cleanup
        $this->content = $this->cleanupOutput($this->content);

        // Remove CTA paragraphs by text content (safer than HTML structure)
        $this->content = $this->removeCTAByText($this->content);

        // Store result text for validation
        $this->resultText = $this->extractText($this->content);

        return $this->content;
    }

    private function extractText(string $html): string {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function removeCTAByText(string $html): string {
        $ctaTexts = [
            "Rejoignez l'Académie",
            "fiches de révision",
            "flashcards",
            "annales corrigées",
            "réussir vos partiels",
            "Besoin d'un cours",
            "Accéder au cours complet",
        ];

        // Remove paragraphs containing CTA text
        $html = preg_replace_callback('/<!-- wp:paragraph -->\s*<p[^>]*>([\s\S]*?)<\/p>\s*<!-- \/wp:paragraph -->/i', function($matches) use ($ctaTexts) {
            $content = $matches[1];
            foreach ($ctaTexts as $cta) {
                if (stripos($content, $cta) !== false) {
                    return ''; // Remove this paragraph
                }
            }
            return $matches[0]; // Keep
        }, $html);

        return $html;
    }

    private function removeCTABlocks(string $html): string {
        // Remove ALL Thrive config blocks (they break rendering)
        $html = preg_replace('/<div[^>]*class="[^"]*thrive-[^"]*config[^"]*"[^>]*>.*?(?:<\/div>|(?=<!-- wp:))/is', '', $html);

        // Remove __CONFIG__ blocks (can span multiple lines and contain HTML)
        $html = preg_replace('/__CONFIG_[^_]+__.*?__CONFIG_[^_]+__/s', '', $html);

        // Remove Thrive shortcodes (lead_lock, leads, symbol, etc.)
        $html = preg_replace('/\[thrive_lead_lock[^\]]*\].*?\[\/thrive_lead_lock\]/is', '', $html);
        $html = preg_replace('/\[thrive_[^\]]+\](?:.*?\[\/thrive_[^\]]+\])?/is', '', $html);

        // Remove Thrive symbol blocks (empty divs after config removal)
        $html = preg_replace('/<div[^>]*class="[^"]*thrv_symbol[^"]*"[^>]*>\s*<\/div>/is', '', $html);

        // Remove Thrive Leads shortcode blocks (email capture forms)
        $html = preg_replace('/<div[^>]*class="[^"]*thrive_leads_shortcode[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);

        // Remove Thrive Quiz blocks
        $html = preg_replace('/<div[^>]*class="[^"]*thrive-quiz-builder[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);

        // Remove Toggle/FAQ/Accordion blocks (interactive elements not convertible)
        $html = preg_replace('/<div[^>]*class="[^"]*thrv_toggle[^"]*"[^>]*>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/is', '', $html);
        $html = preg_replace('/<div[^>]*class="[^"]*tve_toggle[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);
        $html = preg_replace('/<div[^>]*class="[^"]*tve_faq[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);
        $html = preg_replace('/<div[^>]*class="[^"]*faq-container[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);

        // Remove Thrive icon elements
        $html = preg_replace('/<div[^>]*class="[^"]*thrv_icon[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);
        $html = preg_replace('/<span[^>]*class="[^"]*tcb-icon[^"]*"[^>]*>[\s\S]*?<\/span>/is', '', $html);

        // Remove flex containers (keep content inside)
        $html = preg_replace('/<div[^>]*class="[^"]*tcb-flex-(?:col|row)[^"]*"[^>]*>/is', '', $html);

        // Remove custom HTML shortcode wrappers (but keep content)
        $html = preg_replace('/<div[^>]*class="[^"]*thrv_custom_html_shortcode[^"]*"[^>]*>/is', '', $html);

        // Convert "Lire aussi" buttons to wp:button, remove other promo buttons
        $buttonPattern = '/<a[^>]*href="([^"]*)"[^>]*>[\s\S]*?<span[^>]*class="[^"]*tcb-button-text[^"]*"[^>]*>([\s\S]*?)<\/span>[\s\S]*?<\/a>/is';

        $html = preg_replace_callback($buttonPattern, function($matches) {
            $url = $matches[1];
            $text = trim(strip_tags($matches[2]));

            // Convert "Lire aussi" to wp:button
            if (stripos($text, 'Lire aussi') !== false) {
                // Replace aideauxtd.com with jurible.com in URL
                $url = str_replace('aideauxtd.com', 'jurible.com', $url);
                return "###BUTTON###" . base64_encode($url . '|' . $text) . "###/BUTTON###";
            }

            // Remove other promo buttons
            $removePatterns = ['Voir plus de', 'Accéder au cours', 'Fiches vidéo', 'accroche-citation', 'Trouver une accroche'];
            foreach ($removePatterns as $pattern) {
                if (stripos($text, $pattern) !== false) {
                    return '';
                }
            }

            return $matches[0];
        }, $html);

        // Note: CTA contentbox removal disabled - pattern too greedy, captures unrelated content
        // The CTA blocks will be cleaned up by stripThriveContainers instead

        return $html;
    }

    private function normalizeHtml(string $html): string {
        $html = preg_replace('/style="[^"]*--tve-[^"]*"/i', '', $html);
        $html = preg_replace('/<span>\s*<\/span>/i', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        return trim($html);
    }

    private function convertExempleBlocks(string $html): string {
        // Pattern 1: Exemple avec emoji 📌 dans <img alt="📌">
        $pattern1 = '/<img[^>]*alt="📌"[^>]*>[\s\S]{0,500}?Exemple[\s\S]{0,500}?<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:<p[^>]*>[\s\S]*?<\/p>\s*)+)<\/div>/is';

        $html = preg_replace_callback($pattern1, function($matches) {
            $content = $this->extractParagraphsContent($matches[1]);
            return "###INFOBOX_EXEMPLE###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);

        // Pattern 2: Exemple avec emoji 📌 en texte brut
        $pattern2 = '/📌\s*<span[^>]*>Exemple[^<]*<\/span>[\s\S]{0,500}?<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:<p[^>]*>[\s\S]*?<\/p>\s*)+)<\/div>/is';

        $html = preg_replace_callback($pattern2, function($matches) {
            $content = $this->extractParagraphsContent($matches[1]);
            return "###INFOBOX_EXEMPLE###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);

        return $html;
    }

    private function convertAparteBlocks(string $html): string {
        // Pattern 1: Tous les blocs avec emoji 💬 dans <img alt="💬"> (capture <p> et <ul>)
        $pattern1 = '/<img[^>]*alt="💬"[^>]*>[\s\S]{0,500}?<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:(?:<p[^>]*>[\s\S]*?<\/p>|<ul[^>]*>[\s\S]*?<\/ul>)\s*)+)<\/div>/is';

        $html = preg_replace_callback($pattern1, function($matches) {
            $content = $this->extractParagraphsContent($matches[1]);
            return "###INFOBOX_RETENIR###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);

        // Pattern 2: Blocs avec emoji 💬 en texte brut (pas dans <img>)
        $pattern2 = '/💬\s*<span[^>]*>[^<]*<\/span>[\s\S]{0,500}?<div[^>]*class="[^"]*thrv_wrapper[^"]*thrv_text_element[^"]*"[^>]*>((?:(?:<p[^>]*>[\s\S]*?<\/p>|<ul[^>]*>[\s\S]*?<\/ul>)\s*)+)<\/div>/is';

        $html = preg_replace_callback($pattern2, function($matches) {
            $content = $this->extractParagraphsContent($matches[1]);
            return "###INFOBOX_RETENIR###" . base64_encode($content) . "###/INFOBOX###";
        }, $html);

        return $html;
    }

    private function convertYouTubeVideos(string $html): string {
        // Pattern complet - capture tout le bloc vidéo Thrive avec son contenu
        $pattern = '/<div[^>]*class="[^"]*thrv_responsive_video[^"]*"[^>]*data-url="([^"]+)"[^>]*>[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/is';

        $html = preg_replace_callback($pattern, function($matches) {
            $videoId = $this->extractYouTubeId($matches[1]);
            if ($videoId) {
                return "###YOUTUBE###" . $videoId . "###/YOUTUBE###";
            }
            return '';
        }, $html);

        // Fallback: pattern simplifié pour les vidéos avec structure différente
        $patternSimple = '/<div[^>]*class="[^"]*thrv_responsive_video[^"]*"[^>]*data-url="([^"]+)"[^>]*>[\s\S]*?<\/div>\s*<\/div>/is';

        $html = preg_replace_callback($patternSimple, function($matches) {
            $videoId = $this->extractYouTubeId($matches[1]);
            if ($videoId) {
                return "###YOUTUBE###" . $videoId . "###/YOUTUBE###";
            }
            return '';
        }, $html);

        // Nettoyer les conteneurs vidéo restants
        $html = preg_replace('/<div[^>]*class="[^"]*tve_responsive_video_container[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);
        $html = preg_replace('/<div[^>]*class="[^"]*tcb-video-float-container[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);
        $html = preg_replace('/<iframe[^>]*tcb-responsive-video[^>]*>[^<]*<\/iframe>/is', '', $html);

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
        $html = preg_replace('/<div[^>]*class="[^"]*(?:thrv_|tcb-|tve-|kbu|container|table-header)[^"]*"[^>]*>/i', '', $html);
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

    private function convertBlockquotes(string $html): string {
        // Convert blockquote elements to Gutenberg quote blocks
        return preg_replace_callback('/<blockquote[^>]*>(.+?)<\/blockquote>/is', function($matches) {
            $content = $this->cleanInlineHtml($matches[1]);
            // Remove trailing <br> tags
            $content = preg_replace('/<br\s*\/?>\s*$/i', '', $content);
            if (empty(trim(strip_tags($content)))) {
                return '';
            }
            return $this->createQuote($content);
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

        // Buttons (Lire aussi)
        $html = preg_replace_callback('/###BUTTON###([^#]+)###\/BUTTON###/', function($matches) {
            $data = base64_decode($matches[1]);
            list($url, $text) = explode('|', $data, 2);
            return $this->createButton($url, $text);
        }, $html);

        return $html;
    }

    private function createButton(string $url, string $text): string {
        return sprintf(
            '<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="%s">%s</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

',
            htmlspecialchars($url),
            htmlspecialchars($text)
        );
    }

    private function cleanupOutput(string $html): string {
        $html = preg_replace('/<img[^>]*emoji[^>]*>/i', '', $html);
        $html = preg_replace('/<img[^>]*s\.w\.org[^>]*>/i', '', $html);

        // Remove floating Thrive content box titles (text on its own line before blocks)
        $html = preg_replace('/\n\s{2,}[A-ZÀ-Ü][^<\n]{0,60}(?=\s*<!-- wp:)/u', "\n", $html);

        // Remove &nbsp; titles before blocks
        $html = preg_replace('/\n\s*&nbsp;[^<\n]{0,100}(?=\s*<!-- wp:)/u', "\n", $html);

        // Remove all SVG elements (Thrive icons)
        $html = preg_replace('/<svg[^>]*>[\s\S]*?<\/svg>/i', '', $html);

        // Remove ALL Thrive button links (tcb-button-link)
        $html = preg_replace('/<a[^>]*class="[^"]*tcb-button-link[^"]*"[^>]*>[\s\S]*?<\/a>/i', '', $html);

        // Remove empty spans (leftover from button icons)
        $html = preg_replace('/<span[^>]*class="[^"]*tcb-button-icon[^"]*"[^>]*>\s*<\/span>/i', '', $html);

        // Remove [citation] placeholders (content issue from source)
        $html = preg_replace("/'\[citation\]'/", '« ... »', $html);

        // Note: YouTube URLs are handled via placeholder system in convertYouTubeVideos()
        // Don't remove orphan URLs here as it breaks the embed blocks

        // Clean up <br> at start of paragraphs (often in quotes)
        $html = preg_replace('/<p><br>\s*/i', '<p>', $html);
        $html = preg_replace('/<p>\s*<br>/i', '<p>', $html);

        // Remove Thrive timeline elements (inline styled divs with year comments)
        $html = preg_replace('/<!--\s*\d{4}\s*-->\s*<div[^>]*style="[^"]*"[^>]*>[\s\S]*?(?=<!--\s*\d{4}\s*-->|<!-- wp:|$)/i', '', $html);

        // Remove Timeline line comments and their following divs
        $html = preg_replace('/<!--\s*Timeline[^>]*-->\s*<div[^>]*style="[^"]*"[^>]*>/i', '', $html);

        // Remove Thrive Poppins-styled containers (timelines, etc.) - often unclosed
        $html = preg_replace('/<div[^>]*style="[^"]*font-family:\s*[\'"]?Poppins[^"]*"[^>]*>/i', '', $html);

        // Remove Thrive iframe covers
        $html = preg_replace('/<div[^>]*class="[^"]*tve_iframe_cover[^"]*"[^>]*>/i', '', $html);

        // Remove divs with position:relative or position:absolute in inline style
        $html = preg_replace('/<div[^>]*style="[^"]*position:\s*(?:relative|absolute)[^"]*"[^>]*>/i', '', $html);

        // Remove orphan iframes (Spotify, Soundcloud, Leaflet, etc.) - outside of Gutenberg blocks
        $html = preg_replace('/<iframe[^>]*(?:spotify|soundcloud)[^>]*>.*?<\/iframe>/is', '', $html);

        // Remove Leaflet map scripts and CSS
        $html = preg_replace('/<!--\s*Leaflet[^>]*-->[\s\S]*?(?=<!-- wp:|$)/i', '', $html);

        // Remove data-css and other Thrive data attributes from remaining elements
        $html = preg_replace('/\s*data-css="[^"]*"/i', '', $html);
        $html = preg_replace('/\s*data-ct="[^"]*"/i', '', $html);
        $html = preg_replace('/\s*data-ct-name="[^"]*"/i', '', $html);
        $html = preg_replace('/\s*data-element-name="[^"]*"/i', '', $html);
        $html = preg_replace('/\s*data-selector="[^"]*"/i', '', $html);

        // Remove empty Thrive wrapper divs
        $html = preg_replace('/<div[^>]*class="[^"]*thrv_wrapper[^"]*"[^>]*>\s*<\/div>/is', '', $html);

        // Remove tcb-clear divs
        $html = preg_replace('/<div[^>]*class="[^"]*tcb-clear[^"]*"[^>]*>\s*<\/div>/is', '', $html);

        // Remove tve_empty_dropzone elements
        $html = preg_replace('/<div[^>]*class="[^"]*tve_empty_dropzone[^"]*"[^>]*>[\s\S]*?<\/div>/is', '', $html);

        // Fix orphan "Conclusion" text - wrap in heading
        $html = preg_replace('/\n\s*Conclusion\s*\n/i', "\n\n<!-- wp:heading {\"level\":2} -->\n<h2 class=\"wp-block-heading\">Conclusion</h2>\n<!-- /wp:heading -->\n\n", $html);

        // Fix paragraphs that start without <p> tag (orphan text after removed elements)
        $html = preg_replace('/<!-- \/wp:paragraph -->\s*\n\s*([A-ZÀ-Ü][^<]{20,}?)<\/p>/u', "<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>$1</p>", $html);

        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        $html = preg_replace('/^\s+$/m', '', $html);
        $html = preg_replace('/^[📌💬🔎]\s*/m', '', $html);

        // Balance unclosed divs - safety net for Thrive remnants
        $openDivs = preg_match_all('/<div[^>]*>/i', $html);
        $closeDivs = preg_match_all('/<\/div>/i', $html);
        if ($openDivs > $closeDivs) {
            $html .= str_repeat('</div>', $openDivs - $closeDivs);
        } elseif ($closeDivs > $openDivs) {
            // Remove excess closing divs from the end
            for ($i = 0; $i < ($closeDivs - $openDivs); $i++) {
                $html = preg_replace('/<\/div>\s*$/i', '', $html);
            }
        }

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

    private function createQuote(string $content): string {
        return sprintf(
            '<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>%s</p></blockquote>
<!-- /wp:quote -->

',
            $content
        );
    }

    private function createImage(string $src, string $alt): string {
        $caption = $alt;
        if (!empty($caption)) {
            return sprintf(
                '<!-- wp:image {"align":"center","sizeSlug":"large"} -->
<figure class="wp-block-image aligncenter size-large"><img src="%s" alt="%s"/><figcaption class="wp-element-caption">%s</figcaption></figure>
<!-- /wp:image -->

',
                htmlspecialchars($src),
                htmlspecialchars($alt),
                htmlspecialchars($caption)
            );
        }
        return sprintf(
            '<!-- wp:image {"align":"center","sizeSlug":"large"} -->
<figure class="wp-block-image aligncenter size-large"><img src="%s" alt="%s"/></figure>
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
        $parts = [];

        // Extract paragraphs
        preg_match_all('/<p[^>]*>(.+?)<\/p>/is', $html, $pMatches);
        foreach ($pMatches[1] ?? [] as $p) {
            $parts[] = $this->cleanInlineHtml($p);
        }

        // Extract lists (convert to text with bullet points)
        preg_match_all('/<ul[^>]*>([\s\S]*?)<\/ul>/is', $html, $ulMatches);
        foreach ($ulMatches[1] ?? [] as $ul) {
            preg_match_all('/<li[^>]*>(.+?)<\/li>/is', $ul, $liMatches);
            foreach ($liMatches[1] ?? [] as $li) {
                $parts[] = '• ' . $this->cleanInlineHtml($li);
            }
        }

        return implode("\n", $parts);
    }

    private function extractListItems(string $html): array {
        preg_match_all('/<li[^>]*>(.+?)<\/li>/is', $html, $matches);
        return array_map([$this, 'cleanInlineHtml'], $matches[1] ?? []);
    }

    private function escapeAttribute(string $value): string {
        return str_replace(['"', "\n", "\r"], ['\"', ' ', ''], $value);
    }
}
