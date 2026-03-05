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

class ThriveToGutenbergConverter
{
    private $content;
    private $blocks = [];

    /**
     * Convert Thrive HTML to Gutenberg blocks
     */
    public function convert(string $html): string
    {
        $this->content = $html;
        $this->blocks = [];

        // Pre-process: normalize HTML
        $this->content = $this->normalizeHtml($this->content);

        // Convert special blocks first (they contain nested elements)
        $this->content = $this->convertExempleBlocks($this->content);
        $this->content = $this->convertAparteBlocks($this->content);
        $this->content = $this->convertCodePenalBlocks($this->content);

        // Convert standard elements
        $this->content = $this->convertHeadings($this->content);
        $this->content = $this->convertImages($this->content);
        $this->content = $this->convertTables($this->content);
        $this->content = $this->convertLists($this->content);
        $this->content = $this->convertParagraphs($this->content);

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
     * Convert Bloc Exemple (📌)
     * Pattern: <img alt="📌"...> Exemple<p style="font-size: var(--tve-font-size...">content</p>
     */
    private function convertExempleBlocks(string $html): string
    {
        // Pattern for emoji image followed by "Exemple" and content paragraph
        $pattern = '/<img[^>]*alt="📌"[^>]*>\s*Exemple\s*<p[^>]*>(.+?)<\/p>/is';

        return preg_replace_callback($pattern, function($matches) {
            $content = $this->cleanInlineHtml($matches[1]);
            return $this->createInfobox('exemple', 'Exemple', $content);
        }, $html);
    }

    /**
     * Convert Bloc Aparté (💬)
     * Pattern: 💬 <span...>Title</span><p style="font-size...">content</p>
     */
    private function convertAparteBlocks(string $html): string
    {
        // Pattern for 💬 emoji followed by title span and content paragraphs
        $pattern = '/💬\s*<span[^>]*>[^<]*<\/span>\s*<span[^>]*>([^<]+)<\/span>((?:\s*<p[^>]*>.+?<\/p>)+)/is';

        return preg_replace_callback($pattern, function($matches) {
            $title = trim($matches[1]);
            $content = $this->extractParagraphsContent($matches[2]);
            return $this->createInfobox('astuce', $title, $content);
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
        // Images wrapped in span
        $pattern = '/<span>\s*<img[^>]*src="([^"]+)"[^>]*alt="([^"]*)"[^>]*>\s*<\/span>/is';

        $html = preg_replace_callback($pattern, function($matches) {
            $src = $matches[1];
            $alt = $matches[2];
            // Convert aideauxtd.com URLs to jurible.com
            $src = str_replace('aideauxtd.com', 'jurible.com', $src);
            return $this->createImage($src, $alt);
        }, $html);

        // Standalone images (not emojis)
        $pattern = '/<img[^>]*src="(https:\/\/aideauxtd\.com[^"]+)"[^>]*alt="([^"]*)"[^>]*>/is';

        return preg_replace_callback($pattern, function($matches) {
            $src = str_replace('aideauxtd.com', 'jurible.com', $matches[1]);
            $alt = $matches[2];
            return $this->createImage($src, $alt);
        }, $html);
    }

    /**
     * Convert tables
     */
    private function convertTables(string $html): string
    {
        $pattern = '/<table[^>]*>(.+?)<\/table>/is';

        return preg_replace_callback($pattern, function($matches) {
            $tableContent = $matches[1];
            // Clean up table content
            $tableContent = preg_replace('/\s*style="[^"]*"\s*/i', '', $tableContent);
            $tableContent = preg_replace('/\s*data-[a-z-]+="[^"]*"\s*/i', '', $tableContent);
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

        return sprintf(
            '<!-- wp:jurible/infobox {"type":"%s","title":"%s","content":"%s"} -->
<div class="wp-block-jurible-infobox jurible-infobox jurible-infobox-%s">
<div class="jurible-infobox-header">
<span class="jurible-infobox-icon">%s</span>
<span class="jurible-infobox-title">%s</span>
</div>
<p class="jurible-infobox-content">%s</p>
</div>
<!-- /wp:jurible/infobox -->

',
            $type,
            $this->escapeAttribute($title),
            $this->escapeAttribute($content),
            $type,
            $icon,
            htmlspecialchars($title),
            $content
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
            $listItems .= sprintf('<li>%s</li>', $item);
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

    private function cleanupOutput(string $html): string
    {
        // Remove any remaining Thrive artifacts
        $html = preg_replace('/<img[^>]*emoji[^>]*>/i', '', $html);

        // Remove empty lines
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        // Remove any leftover emoji characters not in blocks
        $html = preg_replace('/^[📌💬🔎]\s*/m', '', $html);

        return trim($html);
    }
}

// =============================================================================
// CLI Interface
// =============================================================================

if (php_sapi_name() === 'cli') {
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
