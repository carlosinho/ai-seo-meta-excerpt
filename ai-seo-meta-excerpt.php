<?php
/**
 * Plugin Name: AI SEO Meta & Excerpt
 * Description: Generate post excerpt and SEO meta description using AI, based on post content. Supports major SEO plugins.
 * Plugin URI: https://wpwork.shop/
 * Version: 0.1.0
 * Author: Karol K
 * Author URI: https://wpwork.shop/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-seo-meta-excerpt
 * Domain Path: /languages
 */

declare(strict_types=1);

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('AISEO_META_EXCERPT_PATH', plugin_dir_path(__FILE__));
define('AISEO_META_EXCERPT_URL', plugin_dir_url(__FILE__));

autoload_ai_seo_meta_excerpt_classes();

function autoload_ai_seo_meta_excerpt_classes() {
    require_once AISEO_META_EXCERPT_PATH . 'includes/class-settings.php';
    require_once AISEO_META_EXCERPT_PATH . 'includes/class-rest-endpoint.php';
    require_once AISEO_META_EXCERPT_PATH . 'includes/class-seo-meta-handler.php';
}

// Initialize settings page.
add_action('admin_menu', ['AISEO_Meta_Excerpt_Settings', 'register_settings_page']);
add_action('admin_init', ['AISEO_Meta_Excerpt_Settings', 'register_settings']);

add_action('init', ['AISEO_Meta_Excerpt_REST_Endpoint', 'register_endpoint']);

remove_action('admin_enqueue_scripts', 'aiseo_meta_excerpt_enqueue_editor_assets');
add_action('admin_enqueue_scripts', 'aiseo_meta_excerpt_enqueue_classic_editor_assets');

function aiseo_meta_excerpt_enqueue_classic_editor_assets(
    $hook
) {
    // Only enqueue for post.php or post-new.php and if not block editor
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    // Check if block editor is NOT active
    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor()) {
            return;
        }
    }
    wp_enqueue_script(
        'aiseo-meta-excerpt-turndown',
        AISEO_META_EXCERPT_URL . 'js/turndown.js',
        [],
        '7.1.2',
        true
    );
    wp_enqueue_script(
        'aiseo-meta-excerpt-editor',
        AISEO_META_EXCERPT_URL . 'js/editor-integration.js',
        ['jquery', 'aiseo-meta-excerpt-turndown'],
        '1.0.0',
        true
    );
    wp_localize_script('aiseo-meta-excerpt-editor', 'AISEO_META_EXCERPT', [
        'restUrl' => esc_url_raw(rest_url('ai-seo-meta-excerpt/v1/generate')),
        'nonce' => wp_create_nonce('wp_rest'),
        'metaFieldSelector' => AISEO_Meta_Excerpt_SEO_Meta_Handler::get_meta_field_selector(),
    ]);
}

add_action('add_meta_boxes', 'aiseo_meta_excerpt_add_meta_box');

function aiseo_meta_excerpt_add_meta_box() {
    add_meta_box(
        'aiseo-meta-excerpt-box',
        __('AI SEO Meta & Excerpt', 'ai-seo-meta-excerpt'),
        'aiseo_meta_excerpt_render_meta_box',
        'post',
        'side',
        'high'
    );
}

function aiseo_meta_excerpt_render_meta_box() {
    echo '<div id="aiseo-meta-excerpt-sidebar-container"></div>';
}

// Remove enqueue_block_editor_assets for block-sidebar.js
remove_action('enqueue_block_editor_assets', 'aiseo_meta_excerpt_enqueue_block_editor_sidebar');
// Enqueue editor-integration.js for both editors
add_action('admin_enqueue_scripts', 'aiseo_meta_excerpt_enqueue_editor_assets');

function aiseo_meta_excerpt_enqueue_editor_assets(
    $hook
) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    wp_enqueue_script(
        'aiseo-meta-excerpt-turndown',
        AISEO_META_EXCERPT_URL . 'js/turndown.js',
        [],
        '7.1.2',
        true
    );
    wp_enqueue_script(
        'aiseo-meta-excerpt-editor',
        AISEO_META_EXCERPT_URL . 'js/editor-integration.js',
        ['jquery', 'aiseo-meta-excerpt-turndown'],
        '1.0.0',
        true
    );
    wp_enqueue_style(
        'aiseo-meta-excerpt-editor-style',
        AISEO_META_EXCERPT_URL . 'css/aiseometa.css',
        [],
        '1.0.0'
    );
    wp_localize_script('aiseo-meta-excerpt-editor', 'AISEO_META_EXCERPT', [
        'restUrl' => esc_url_raw(rest_url('ai-seo-meta-excerpt/v1/generate')),
        'nonce' => wp_create_nonce('wp_rest'),
        'metaFieldSelector' => AISEO_Meta_Excerpt_SEO_Meta_Handler::get_meta_field_selector(),
    ]);
}
