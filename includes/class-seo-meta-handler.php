<?php
/**
 * SEO Meta Handler for AI SEO Meta & Excerpt plugin.
 *
 * @package AI_SEO_Meta_Excerpt
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Meta_Excerpt_SEO_Meta_Handler {
    /**
     * Detect which supported SEO plugin is active.
     *
     * @return string|null One of: 'yoast', 'seopress', 'rankmath', 'aiseo', or null if none found.
     */
    public static function get_active_seo_plugin(): ?string {
        if (defined('WPSEO_VERSION')) {
            return 'yoast';
        }
        if (defined('SEOPRESS_VERSION')) {
            return 'seopress';
        }
        if (defined('RANK_MATH_VERSION')) {
            return 'rankmath';
        }
        if (defined('AIOSEO_VERSION')) {
            return 'aiseo';
        }
        return null;
    }

    /**
     * Get the meta description field selector for JS, based on the active SEO plugin.
     *
     * @return string|null CSS selector for the meta description field, or null if not found.
     */
    public static function get_meta_field_selector(): ?string {
        $plugin = self::get_active_seo_plugin();
        switch ($plugin) {
            case 'yoast':
                // Classic: #yoast_wpseo_metadesc, Block: input[name="yoast_wpseo_metadesc"]
                return '#yoast_wpseo_metadesc, input[name="yoast_wpseo_metadesc"]';
            case 'seopress':
                // Classic: #seopress_titles_desc, Block: input[name="seopress_titles_desc"]
                return '#seopress_titles_desc, input[name="seopress_titles_desc"]';
            case 'rankmath':
                // Classic: #rank_math_description, Block: textarea[name="rank_math_description"]
                return '#rank_math_description, textarea[name="rank_math_description"]';
            case 'aiseo':
                // Classic: #aioseo-description, Block: textarea[name="aioseo-description"]
                return '#aioseo-description, textarea[name="aioseo-description"]';
            default:
                return null;
        }
    }
} 