<?php
/**
 * REST API endpoint for AI SEO Meta & Excerpt plugin.
 *
 * @package AI_SEO_Meta_Excerpt
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Meta_Excerpt_REST_Endpoint {
    const ROUTE = '/ai-seo-meta-excerpt/v1/generate';

    /**
     * Register the REST API endpoint.
     */
    public static function register_endpoint(): void {
        add_action('rest_api_init', function () {
            register_rest_route(
                'ai-seo-meta-excerpt/v1',
                '/generate',
                [
                    'methods'  => 'POST',
                    'callback' => [self::class, 'handle_generate'],
                    'permission_callback' => function () {
                        return current_user_can('edit_posts');
                    },
                    'args' => [
                        'title' => [
                            'required' => true,
                            'type' => 'string',
                        ],
                        'intro' => [
                            'required' => true,
                            'type' => 'string',
                        ],
                    ],
                ]
            );
        });
    }

    /**
     * Handle the generation request.
     */
    public static function handle_generate($request) {
        $title = sanitize_text_field($request->get_param('title'));
        $intro = sanitize_textarea_field($request->get_param('intro'));
        $api_key = get_option(AISEO_Meta_Excerpt_Settings::OPTION_KEY, '');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key is not set.', 'ai-seo-meta-excerpt'), ['status' => 400]);
        }

        if (empty($title) || empty($intro)) {
            return new WP_Error('missing_data', __('Post title or intro is missing.', 'ai-seo-meta-excerpt'), ['status' => 400]);
        }

        // Truncate intro if too long (max 200 words)
        $words = preg_split('/\s+/u', $intro, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 200) {
            $intro = implode(' ', array_slice($words, 0, 200));
        }

        try {
            $result = self::call_openai_api($api_key, $title, $intro);
        } catch (Exception $e) {
            error_log('AI SEO Meta & Excerpt API error: ' . $e->getMessage());
            return new WP_Error('api_error', __('Failed to generate meta/excerpt.', 'ai-seo-meta-excerpt'), ['status' => 500]);
        }

        if (empty($result['meta']) || empty($result['excerpt'])) {
            error_log('AI SEO Meta & Excerpt: Invalid API response.');
            return new WP_Error('invalid_response', __('Invalid response from AI API.', 'ai-seo-meta-excerpt'), ['status' => 500]);
        }

        return [
            'meta' => $result['meta'],
            'excerpt' => $result['excerpt'],
        ];
    }

    /**
     * Call OpenAI API to generate meta and excerpt.
     *
     * @param string $api_key
     * @param string $title
     * @param string $intro (Markdown)
     * @return array ['meta' => string, 'excerpt' => string]
     * @throws Exception
     */
    private static function call_openai_api(string $api_key, string $title, string $intro): array {
        $core_prompt = 'Write a meta description and a post excerpt based on the provided introduction of a blog post.

        The meta description should include a short summary of what the post is about and highlight why it is worth reading. Maximum 158 characters.

        The excerpt serves the same purpose as the meta description but can be up to 320 characters long.

        <response_format>  
        - As the result of your work, return only the meta description and excerpt  
        - Separate these elements with the following labels: {META}, {EXCERPT}
        - Return plain text with no styling elements  
        - DO NOT COMMENT ON WHAT YOU ARE DOING - only return the result of your work  
        </response_format>

        <example_response>  
        {META}

        Post description based on the introduction. Optimized for SEO.

        {EXCERPT}

        Longer form of the meta description.
        </example_response>

        Title: ';

        $prompt = $core_prompt
                    . $title . "\n\n"
                    . "Post intro: \n\n"
                    . $intro;

        $body = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert SEO copywriter.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            throw new Exception('OpenAI API request failed: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200 || empty($body)) {
            throw new Exception('OpenAI API returned an error: ' . $body);
        }

        $data = json_decode($body, true);
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('OpenAI API response missing content.');
        }
        $text = $data['choices'][0]['message']['content'];

        // Parse {META} and {EXCERPT}
        $meta = '';
        $excerpt = '';
        if (preg_match('/\{META\}\s*(.*?)\s*\{EXCERPT\}/is', $text, $matches)) {
            $meta = trim($matches[1]);
            $after = substr($text, strpos($text, '{EXCERPT}') + 9);
            $excerpt = trim($after);
        } else {
            // Fallback: try to split by keywords
            $parts = preg_split('/\{META\}|\{EXCERPT\}/i', $text);
            if (count($parts) >= 3) {
                $meta = trim($parts[1]);
                $excerpt = trim($parts[2]);
            }
        }

        return [
            'meta' => $meta,
            'excerpt' => $excerpt,
        ];
    }
} 