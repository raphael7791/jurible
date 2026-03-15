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
    private $resultHtml = '';

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
            // Skip short sentences, config blocks, and titles that become block headers
            if (mb_strlen($sentence) > 50
                && stripos($this->resultText, $sentence) === false
                && strpos($sentence, 'CONFIG') === false
                && !preg_match('/^(Aparté|Exemple|À retenir|Le saviez-vous)\s/', $sentence)
            ) {
                $lostSentences[] = mb_substr($sentence, 0, 100) . '...';
            }
        }

        // Check structural elements are present in HTML
        $hasStructure = (
            substr_count($this->resultHtml, 'wp:paragraph') > 5 ||
            substr_count($this->resultHtml, 'wp:heading') > 0
        );

        return [
            'ratio' => $ratio,
            'source_length' => $sourceLen,
            'result_length' => $resultLen,
            'lost_sentences' => array_slice($lostSentences, 0, 10), // Max 10
            // Valid if ratio >= 60% (Thrive has lots of removed markup)
            'is_valid' => $ratio >= 60 && $hasStructure,
        ];
    }

    public function convert(string $html): string {
        $this->content = $html;
        $this->importedImages = [];

        // Store source text for validation
        $this->sourceText = $this->extractText($html);

        // Convert QCM blocks FIRST (before Aparté which would capture 💬 explanations as infoboxes)
        $this->content = $this->convertQcmBlocks($this->content);

        // Convert special blocks (before removing Thrive elements that could break patterns)
        $this->content = $this->convertExempleBlocks($this->content);
        $this->content = $this->convertAparteBlocks($this->content);

        // Remove CTA and Thrive elements
        $this->content = $this->removeCTABlocks($this->content);

        // Normalize HTML
        $this->content = $this->normalizeHtml($this->content);

        // Convert media
        $this->content = $this->convertYouTubeVideos($this->content);
        $this->content = $this->convertPodcastEmbeds($this->content);
        $this->content = $this->convertImages($this->content);

        // Strip Thrive containers
        $this->content = $this->stripThriveContainers($this->content);

        // Convert standard elements
        $this->content = $this->convertBlockquotes($this->content);
        $this->content = $this->convertHeadings($this->content);
        $this->content = $this->convertTables($this->content);
        $this->content = $this->convertLists($this->content);
        $this->content = $this->convertParagraphs($this->content);
        $this->content = $this->convertNBtoInfobox($this->content);
        $this->content = $this->convertARetenirToInfobox($this->content);
        $this->content = $this->convertAvisProfToInfobox($this->content);

        // Restore placeholders
        $this->content = $this->restoreInfoboxes($this->content);

        // Cleanup
        $this->content = $this->cleanupOutput($this->content);

        // Remove CTA paragraphs by text content (safer than HTML structure)
        $this->content = $this->removeCTAByText($this->content);

        // Store result for validation
        $this->resultHtml = $this->content;
        $this->resultText = $this->extractText($this->content);

        return $this->content;
    }

    private function extractText(string $html): string {
        // Remove Thrive config blocks BEFORE counting text
        $html = preg_replace('/__CONFIG_[^_]+__.*?__CONFIG_[^_]+__/s', '', $html);

        // Remove Thrive shortcodes
        $html = preg_replace('/\[thrive_[^\]]+\](?:.*?\[\/thrive_[^\]]+\])?/is', '', $html);

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
        // Remove Thrive Table of Contents block (tve-toc) — contains SVG, anchors, headings text
        // Must be removed early to prevent TOC content leaking into paragraphs and breaking H2 conversion
        // Uses lookahead to match until the next actual content div (thrv_text_element)
        $html = preg_replace('/<div[^>]*class="[^"]*\btve-toc\b[^"]*"[^>]*>[\s\S]*?(?=<div[^>]*class="[^"]*thrv_text_element)/is', '', $html);

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
        // IMPORTANT: Must require tcb-button-link class to avoid matching regular links
        // Pattern handles class before or after href
        $buttonPattern = '/<a[^>]*(?:class="[^"]*tcb-button-link[^"]*"[^>]*href="([^"]*)"|href="([^"]*)"[^>]*class="[^"]*tcb-button-link[^"]*")[^>]*>[\s\S]*?<span[^>]*class="[^"]*tcb-button-text[^"]*"[^>]*>([\s\S]*?)<\/span>[\s\S]*?<\/a>/is';

        $html = preg_replace_callback($buttonPattern, function($matches) {
            // URL is in group 1 or 2 depending on attribute order
            $url = !empty($matches[1]) ? $matches[1] : $matches[2];
            $text = trim(strip_tags($matches[3]));

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
        // Pattern: capture le bloc vidéo Thrive (2 niveaux de div)
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

    private function convertPodcastEmbeds(string $html): string {
        // Spotify iframes → placeholder (episode uses wp:embed, show/track/playlist use wp:html iframe)
        $html = preg_replace_callback(
            '/<iframe[^>]*src="([^"]*spotify[^"]*)"[^>]*>.*?<\/iframe>/is',
            function($matches) {
                $info = $this->extractSpotifyInfo($matches[1]);
                if (!$info) {
                    return '';
                }
                // Episodes support oEmbed → wp:embed
                if ($info['type'] === 'episode') {
                    return "###PODCAST_SPOTIFY###" . base64_encode($info['url']) . "###/PODCAST###";
                }
                // Shows/tracks/playlists don't support oEmbed → wp:html with iframe
                return "###PODCAST_SPOTIFY_IFRAME###" . base64_encode($info['embed_url']) . "###/PODCAST###";
            },
            $html
        );

        // Soundcloud iframes → wp:embed placeholder
        $html = preg_replace_callback(
            '/<iframe[^>]*src="([^"]*soundcloud[^"]*)"[^>]*>.*?<\/iframe>/is',
            function($matches) {
                $url = $this->extractSoundcloudUrl($matches[1]);
                if ($url) {
                    return "###PODCAST_SOUNDCLOUD###" . base64_encode($url) . "###/PODCAST###";
                }
                return '';
            },
            $html
        );

        return $html;
    }

    private function extractSpotifyInfo(string $src): ?array {
        // Extract type and ID from embed or standard Spotify URL
        if (preg_match('#open\.spotify\.com/(?:embed/)?(episode|show|track|playlist)/([a-zA-Z0-9]+)#', $src, $match)) {
            $type = $match[1];
            $id = $match[2];
            return [
                'type' => $type,
                'url' => 'https://open.spotify.com/' . $type . '/' . $id,
                'embed_url' => 'https://open.spotify.com/embed/' . $type . '/' . $id,
            ];
        }
        return null;
    }

    private function extractSoundcloudUrl(string $src): ?string {
        // Soundcloud player iframe: src contains url= parameter with the real URL
        if (preg_match('/[?&]url=([^&]+)/i', $src, $match)) {
            return urldecode($match[1]);
        }
        // Direct soundcloud.com URL
        if (preg_match('#(https?://soundcloud\.com/[^\s"&]+)#i', $src, $match)) {
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
            // Preserve emoji as their alt text character (e.g. ⭐)
            if (preg_match('/alt="([^"]*)"/i', $attributes, $altMatch)) {
                return $altMatch[1];
            }
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

    private function convertQcmBlocks(string $html): string {
        // Quick check: need question headers AND green highlights for correct answers
        if (!preg_match('/<h3[^>]*>.*?Question\s+\d+\s*:/is', $html)
            || strpos($html, 'rgba(56, 203, 105') === false) {
            return $html;
        }

        // Extract title from H2 containing "QCM" (e.g., "I. QCM Droit pénal général (30 questions)")
        $title = 'Quiz';
        if (preg_match('/<h2[^>]*>[^<]*?(QCM\s+[^(<]+)/i', $html, $titleMatch)) {
            $title = trim(html_entity_decode($titleMatch[1], ENT_QUOTES, 'UTF-8'));
        }

        // Match section from H2 "Explication" through all questions to next H2 or end
        if (!preg_match('/(<h2[^>]*>[^<]*Explication[^<]*<\/h2>)(.*?)(?=<h2[^>]*>|\z)/is', $html, $sectionMatch)) {
            return $html;
        }

        $questionsHtml = $sectionMatch[2];
        $fullSection = $sectionMatch[0];

        // Parse individual questions by splitting on <h3>
        $questions = [];
        $blocks = preg_split('/<h3[^>]*>/i', $questionsHtml);

        foreach ($blocks as $block) {
            if (!preg_match('/Question\s+\d+/i', $block)) {
                continue;
            }

            // Extract question text after "Question N :"
            // Handles both <strong>Question N :</strong> Text and Question N : Text
            if (!preg_match('/(?:<strong>\s*)?Question\s+\d+\s*:(?:&nbsp;|\s)*(?:<\/strong>)?\s*(.*?)<\/h3>/is', $block, $qMatch)) {
                continue;
            }
            $questionText = trim(html_entity_decode(strip_tags($qMatch[1]), ENT_QUOTES, 'UTF-8'));
            if (empty($questionText)) {
                continue;
            }

            // Extract all <p> contents
            preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $block, $pMatches);

            $answers = [];
            $correctIndex = 0;
            $explanation = '';
            $answerIdx = 0;
            $foundReponseCorrecteLine = false;

            foreach ($pMatches[1] as $pContent) {
                $cleanText = trim(html_entity_decode(strip_tags($pContent), ENT_QUOTES, 'UTF-8'));

                // Answer line (a-d)
                if (preg_match('/^[a-d]\)\s*(.*)/u', $cleanText, $ansMatch)) {
                    if (strpos($pContent, 'rgba(56, 203, 105') !== false) {
                        $correctIndex = $answerIdx;
                    }
                    $answers[] = trim($ansMatch[1]);
                    $answerIdx++;
                }
                // "Réponse correcte" or "Réponse : X)" marker
                elseif (mb_strpos($cleanText, 'Réponse correcte') !== false
                    || preg_match('/^Réponse\s*:\s*[a-d]\)/u', $cleanText)) {
                    $foundReponseCorrecteLine = true;
                }
                // Explanation text: first substantial <p> after "Réponse correcte"
                // (💬 is in a <div> in Thrive HTML, not a <p>, so we use this marker instead)
                elseif ($foundReponseCorrecteLine && !empty($cleanText) && mb_strlen($cleanText) > 20) {
                    $explanation = $cleanText;
                    $foundReponseCorrecteLine = false;
                }
            }

            if (count($answers) < 2) {
                continue;
            }

            $questions[] = [
                'question' => $questionText,
                'answers' => $answers,
                'correctIndex' => $correctIndex,
                'explanation' => $explanation,
            ];
        }

        if (empty($questions)) {
            return $html;
        }

        // Generate QCM block and use placeholder
        $qcmBlock = $this->createQcmBlock($title, $questions);

        // Remove the QCM title H2 (e.g., "I. QCM Droit pénal général (30 questions et réponses)")
        $html = preg_replace('/<h2[^>]*>[^<]*QCM[^<]*questions[^<]*<\/h2>/i', '', $html);

        // Insert QCM block BEFORE the "Explication" H2 (keep the H2 + questions for SEO)
        $explH2 = $sectionMatch[1];
        $html = str_replace($explH2, '###QCM###' . base64_encode($qcmBlock) . '###/QCM###' . $explH2, $html);

        return $html;
    }

    private function createQcmBlock(string $title, array $questions): string {
        $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Build SEO content (accessible without JS)
        $seoHtml = '<h3 class="qcm-seo-title">' . htmlspecialchars($title) . '</h3>';
        foreach ($questions as $q) {
            $seoHtml .= '<details class="qcm-seo-question"><summary>' . htmlspecialchars($q['question']) . '</summary><ul>';
            foreach ($q['answers'] as $j => $answer) {
                $class = ($j === $q['correctIndex']) ? ' class="qcm-correct"' : '';
                $check = ($j === $q['correctIndex']) ? ' ✓' : '';
                $seoHtml .= '<li' . $class . '>' . htmlspecialchars($answer) . $check . '</li>';
            }
            $seoHtml .= '</ul>';
            if (!empty($q['explanation'])) {
                $seoHtml .= '<p class="qcm-seo-explanation">' . htmlspecialchars($q['explanation']) . '</p>';
            }
            $seoHtml .= '</details>';
        }

        $attrs = json_encode([
            'title' => $title,
            'questions' => $questions,
            'shuffleAnswers' => true,
            'showExplanations' => true,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return sprintf(
            '<!-- wp:jurible/qcm %s -->' . "\n" .
            '<div class="wp-block-jurible-qcm"><div class="jurible-qcm-container" data-questions="%s" data-shuffle="true" data-explanations="true" data-title="%s"><div class="qcm-seo-content">%s</div></div></div>' . "\n" .
            '<!-- /wp:jurible/qcm -->' . "\n\n",
            $attrs,
            htmlspecialchars($questionsJson, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            $seoHtml
        );
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

    private function convertARetenirToInfobox(string $html): string {
        // Match both HTML entity &#x1f4cd; and UTF-8 📍 character
        $pinEmoji = '(?:&#x1f4cd;|\x{1F4CD}|📍|&#x1f4cc;|\x{1F4CC}|📌)';

        // Pattern 1: H4 Gutenberg "📍A retenir sur..." + paragraphs
        $html = preg_replace_callback(
            '/<!-- wp:heading \{"level":4\} -->\s*<h4[^>]*>(?:<strong>)?' . $pinEmoji . '\s*A retenir sur\s*(.*?)(?:<\/strong>)?<\/h4>\s*<!-- \/wp:heading -->\s*((?:<!-- wp:paragraph -->\s*<p>.*?<\/p>\s*<!-- \/wp:paragraph -->\s*)+)/isu',
            function($matches) {
                $name = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8'));
                preg_match_all('/<p>(.*?)<\/p>/is', $matches[2], $pMatches);
                $content = implode('<br><br>', array_map('trim', $pMatches[1]));
                return $this->createInfobox('retenir', "A retenir sur $name", $content);
            },
            $html
        );

        // Pattern 2: Orphan text "📍A retenir sur..." (malformed, no heading wrapper)
        $html = preg_replace_callback(
            '/<strong>' . $pinEmoji . '\s*A retenir sur\s*(.*?)<\/strong>\s*\n\s*(.*?)<\/p>\s*<!-- \/wp:paragraph -->/isu',
            function($matches) {
                $name = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8'));
                $content = trim(strip_tags($matches[2], '<strong><em><a><br>'));
                return $this->createInfobox('retenir', "A retenir sur $name", $content) . "\n";
            },
            $html
        );

        return $html;
    }

    private function convertAvisProfToInfobox(string $html): string {
        return preg_replace_callback(
            '/<!-- wp:heading \{"level":3\} -->\s*<h3[^>]*>L\'avis du prof de droit[^<]*<\/h3>\s*<!-- \/wp:heading -->\s*((?:<!-- wp:paragraph -->\s*<p>.*?<\/p>\s*<!-- \/wp:paragraph -->\s*)+)/is',
            function($matches) {
                // Extract paragraph contents
                preg_match_all('/<p>(.*?)<\/p>/is', $matches[1], $pMatches);
                $content = implode('<br><br>', array_map('trim', $pMatches[1]));
                return $this->createInfobox('retenir', "L'avis du prof de droit \u{1F913}", $content);
            },
            $html
        );
    }

    private function convertNBtoInfobox(string $html): string {
        return preg_replace_callback(
            '/<!-- wp:paragraph -->\s*<p><em>NB\s*:\s*(.*?)<\/em><\/p>\s*<!-- \/wp:paragraph -->/is',
            function($matches) {
                $content = trim($matches[1]);
                return $this->createInfobox('attention', 'NB', $content);
            },
            $html
        );
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

        // Podcast Spotify (episodes only — oEmbed works)
        $html = preg_replace_callback('/###PODCAST_SPOTIFY###([^#]+)###\/PODCAST###/', function($matches) {
            $url = base64_decode($matches[1]);
            return sprintf(
                '<!-- wp:embed {"url":"%s","type":"rich","providerNameSlug":"spotify","responsive":true} -->' . "\n" .
                '<figure class="wp-block-embed is-type-rich is-provider-spotify wp-block-embed-spotify"><div class="wp-block-embed__wrapper">' . "\n" .
                '%s' . "\n" .
                '</div></figure>' . "\n" .
                '<!-- /wp:embed -->' . "\n\n",
                $url, $url
            );
        }, $html);

        // Podcast Spotify iframe (shows/tracks/playlists — oEmbed not supported, use direct iframe)
        $html = preg_replace_callback('/###PODCAST_SPOTIFY_IFRAME###([^#]+)###\/PODCAST###/', function($matches) {
            $embedUrl = base64_decode($matches[1]);
            return sprintf(
                '<!-- wp:html -->' . "\n" .
                '<iframe style="border-radius:12px" src="%s" width="100%%" height="352" frameborder="0" allowfullscreen allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>' . "\n" .
                '<!-- /wp:html -->' . "\n\n",
                htmlspecialchars($embedUrl)
            );
        }, $html);

        // Podcast Soundcloud
        $html = preg_replace_callback('/###PODCAST_SOUNDCLOUD###([^#]+)###\/PODCAST###/', function($matches) {
            $url = base64_decode($matches[1]);
            return sprintf(
                '<!-- wp:embed {"url":"%s","type":"rich","providerNameSlug":"soundcloud","responsive":true} -->' . "\n" .
                '<figure class="wp-block-embed is-type-rich is-provider-soundcloud wp-block-embed-soundcloud"><div class="wp-block-embed__wrapper">' . "\n" .
                '%s' . "\n" .
                '</div></figure>' . "\n" .
                '<!-- /wp:embed -->' . "\n\n",
                $url, $url
            );
        }, $html);

        // QCM blocks
        $html = preg_replace_callback('/###QCM###([^#]+)###\/QCM###/', function($matches) {
            return base64_decode($matches[1]);
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

        // Remove orphan iframes (Leaflet, etc.) - Spotify/Soundcloud are now converted to wp:embed
        $html = preg_replace('/<iframe[^>]*(?:leaflet)[^>]*>.*?<\/iframe>/is', '', $html);

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
        // Convert emoji <img> to their alt text before stripping tags
        $html = preg_replace('/<img[^>]*class="[^"]*emoji[^"]*"[^>]*alt="([^"]*)"[^>]*>/i', '$1', $html);
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
