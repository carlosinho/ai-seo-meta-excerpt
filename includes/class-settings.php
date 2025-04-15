<?php
/**
 * Settings page for AI SEO Meta & Excerpt plugin.
 *
 * @package AI_SEO_Meta_Excerpt
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Meta_Excerpt_Settings {
    const OPTION_KEY = 'aiseo_meta_excerpt_api_key';

    /**
     * Register the settings page in the admin menu.
     */
    public static function register_settings_page(): void {
        add_options_page(
            __('AI SEO Meta & Excerpt', 'ai-seo-meta-excerpt'),
            __('AI SEO Meta & Excerpt', 'ai-seo-meta-excerpt'),
            'manage_options',
            'ai-seo-meta-excerpt',
            [self::class, 'render_settings_page']
        );
    }

    /**
     * Register the plugin settings.
     */
    public static function register_settings(): void {
        register_setting('ai-seo-meta-excerpt-settings', self::OPTION_KEY, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
    }

    /**
     * Render the settings page HTML.
     */
    public static function render_settings_page(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('AI SEO Meta & Excerpt Settings', 'ai-seo-meta-excerpt'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ai-seo-meta-excerpt-settings');
                do_settings_sections('ai-seo-meta-excerpt-settings');
                ?>
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row">
                            <label for="<?php echo esc_attr(self::OPTION_KEY); ?>">
                                <?php esc_html_e('OpenAI API Key', 'ai-seo-meta-excerpt'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" id="<?php echo esc_attr(self::OPTION_KEY); ?>" name="<?php echo esc_attr(self::OPTION_KEY); ?>" value="<?php echo esc_attr(get_option(self::OPTION_KEY, '')); ?>" class="regular-text" />
                            <p class="description">
                                <?php esc_html_e('Enter your OpenAI API key. This is required for generating meta descriptions and excerpts.', 'ai-seo-meta-excerpt'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
