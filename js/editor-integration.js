// AI SEO Meta & Excerpt button logic for both Classic and Block Editor
jQuery(document).ready(function($) {
    // Ensure the button exists in the meta box
    var $container = $('#aiseo-meta-excerpt-sidebar-container');
    if ($container.length && !$container.find('#aiseo_generate_button').length) {
        $container.append('<button id="aiseo_generate_button" class="button button-primary" type="button">Generate Meta & Excerpt</button><div id="aiseo_generate_status"></div>');
    }

    $('#aiseo_generate_button').off('click').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#aiseo_generate_status');
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Generating...');

        // Add Turndown integration
        if (typeof TurndownService === 'undefined' && typeof window.TurndownService === 'undefined') {
            console.error('TurndownService is not loaded. Please ensure turndown.js is enqueued.');
        }

        function extractIntroHtml(contentHtml) {
            // Find the first <h2> and return everything before it
            var div = document.createElement('div');
            div.innerHTML = contentHtml;
            var children = Array.from(div.childNodes);
            var introNodes = [];
            for (var i = 0; i < children.length; i++) {
                var node = children[i];
                if (node.nodeType === 1 && node.tagName && node.tagName.toLowerCase() === 'h2') {
                    break;
                }
                introNodes.push(node);
            }
            var introDiv = document.createElement('div');
            introNodes.forEach(function(node) {
                introDiv.appendChild(node.cloneNode(true));
            });
            return introDiv.innerHTML;
        }

        // Get post title and content for both editors
        var postTitle = '';
        var postContent = '';
        if ($('#title').length) {
            // Classic Editor
            postTitle = $('#title').val();
            postContent = '';
            if (typeof tinymce !== 'undefined' && tinymce.get('content') && !tinymce.get('content').isHidden()) {
                postContent = tinymce.get('content').getContent();
            } else if ($('#content').length) {
                postContent = $('#content').val();
            }
        } else if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            // Block Editor
            postTitle = wp.data.select('core/editor').getEditedPostAttribute('title');
            postContent = wp.data.select('core/editor').getEditedPostAttribute('content');
        }

        if (!postTitle || !postContent) {
            $status.text('Error: Post title and content are required');
            $btn.prop('disabled', false).html(originalText);
            return;
        }

        // Extract intro HTML before first <h2>
        var introHtml = extractIntroHtml(postContent);
        // Convert intro HTML to Markdown using Turndown
        var turndownService = new (window.TurndownService || TurndownService)();
        var introMarkdown = turndownService.turndown(introHtml);

        $status.text('Generating content, please wait...');

        jQuery.ajax({
            url: AISEO_META_EXCERPT.restUrl,
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', AISEO_META_EXCERPT.nonce);
            },
            data: {
                title: postTitle,
                intro: introMarkdown
            },
            success: function(response) {
                if (response.excerpt) {
                    // Classic Editor: update excerpt field
                    if ($('#excerpt').length) {
                        $('#excerpt').val(response.excerpt);
                    }
                    // Block Editor: update excerpt
                    if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
                        wp.data.dispatch('core/editor').editPost({ excerpt: response.excerpt });
                    }
                }
                if (response.meta && AISEO_META_EXCERPT.metaFieldSelector) {
                    // Use the exact working pattern for SEOPress meta description
                    var $seopressMeta = $('#seopress_titles_desc_meta');
                    if ($seopressMeta.length) {
                        $seopressMeta.val(response.meta).trigger('change');
                    } else {
                        // Fallback: update the field by selector if not SEOPress
                        var $metaField = $(AISEO_META_EXCERPT.metaFieldSelector);
                        if ($metaField.length) {
                            $metaField.val(response.meta).trigger('change');
                        }
                    }
                }
                $status.text('Meta & Excerpt generated successfully.');
            },
            error: function(xhr) {
                let msg = 'Failed to generate meta/excerpt.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $status.text(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
}); 