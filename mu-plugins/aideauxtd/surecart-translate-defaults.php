<?php
/**
 * Plugin Name: SureCart — Traduction des defaults block.json
 * Description: Traduit les attributs par défaut anglais des blocs SureCart (secure notice, etc.)
 */
if (!defined('ABSPATH')) exit;

add_filter('render_block', function($content, $block) {
    if (strpos($block['blockName'] ?? '', 'surecart/') !== 0) return $content;

    $replacements = [
        'This is a secure, encrypted payment.' => __('This is a secure, encrypted payment.', 'surecart'),
    ];

    foreach ($replacements as $en => $fr) {
        if ($en !== $fr) {
            $content = str_replace(
                esc_attr($en),
                esc_attr($fr),
                $content
            );
        }
    }

    return $content;
}, 10, 2);
