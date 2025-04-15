# AI SEO Meta & Excerpt

**Experimental WordPress plugin built with GPT-4.1.**

This plugin was created by following a single, comprehensive prompt (shown below) and is intended as a proof-of-concept for AI-assisted plugin development with the new model GPT-4.1.

After this original prompt, I went back and forth a lot to iron out the bugs and get the main functionality implemented.

All explained on YouTube: TK

My main takeaways are:

- GPT-4.1 has a lot stricter approach to development than Claude. It will ask a lot more questions before coding anything. 
- This is both good and bad. Good because you have more control over what's going on. And bad because you also have to know what you're actually doing. 😅
- It's less creative in its debug, unfortunately. This means it will more likely run in circles.
- Needs more working code examples to model its code after.

Full development time to get it to this initial push stage was about 2 hours. 👍

---

## Original Prompt

```
Help me build a WordPress plugin. 

## Overview

A plugin to help users create post excerpt and SEO meta description with the help of AI - based on the contents of the post the user is working on.

Name: AI SEO Meta & Excerpt

## Features and logic

The plugin should allow the user to generate the post excerpt and the SEO meta description based on the topic of the post. 

Both the excerpt and SEO description should be generated using a call to the OpenAI API. 

That API call is what's going to take care of the entire generation. Treat the API call as a black box. Create a placeholder prompt that will be send off to OpenAI, and expect the server's response to be a valid excerpt and SEO description.

As part of the prompt, send the post's title and the intro sections of the post - everything before the first level 2 subhead in the post. Take this content from the editor window of the post (classic and block) - the current state, which might include changes that haven't yet been saved by the user.

OpenAI will return the generated text in the following format:

~~~
{META}

The text of the meta description... up to 158 chars

{EXCERPT}

The excerpt text - up to 320 chars
~~~

Both {META} and {EXCERPT} are actual keywords that OpenAI will use to separate the SEO description from the excerpt.

Take the SEO description and the excerpt and save them in the right fields in the WordPress post editing interface (both classic and block editor).

- For the post excerpt - use the native fields in WordPress
- For the SEO meta description - set the correct field based on the SEO plugin installed on the site - should work with Yoast SEO, SEOPress, Rank Math, AISEO

## UI 

Add a button on the post editing screen to "Generate Meta & Excerpt" - in classic and block editor.

After a successful generation, make sure that both the excerpt and the SEO description appear on the screen (in the post editing interface) immediately when they are received - not after page refresh.

Create a section for the plugin in the WordPress dashboard - let the user add their OpenAI API key there.

## Other requirements

Adhere to WordPress coding standards (documentation in context).

Put all JS files in the "js" directory in the main plugin dir.

Put all CSS files in the "css" directory in the main plugin dir.

Take care of edge cases - what happens if there's no content in the post, if the intro is super long, if generating meta fails, etc. Cover all the most probable scenarios based on the rest of the codebase.

Make sure that clicking the button is not possible until the previous call finishes (a call to OpenAI can take a couple of seconds).

## Next steps

How would you approach building such a plugin? Does anything need clarification before you can start working?
```

---

## Installation

1. Download or clone this repository into your `/wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin dashboard.
3. Go to the plugin settings page in the dashboard and enter your OpenAI API key.

## Usage

- Edit any post.
- Click the **Generate Meta & Excerpt** button in the editor (classic or block).
- The plugin will use AI to generate a meta description and excerpt based on your post's title and introduction.
- The generated content will appear instantly in the editor fields.

## Notes

- (Should) work with major SEO plugins: SEOPress (tested), Yoast SEO, Rank Math, AISEO.
- Handles edge cases (empty content, long intros, API errors, etc.).

## Disclaimer

This is an experimental, AI-generated plugin. Use at your own risk. Not recommended for production without further review and testing.

## License

GPLv2 or later.