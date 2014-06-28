=== Vertical Related Posts ===
Contributors: corneliucirlan
Tags: wordpress, related posts, vertical, responsive, mobile friendly, light, white, sidebar, vertical related posts, posts, custom post types, pages
Requires at least: 3.3
Tested up to: 3.9.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wordpress plugin for displaying related posts.

== Description ==

Vertical Related Posts displays pages, posts and even custom post type articles related with the current post, thus increasing your user's engagement with your site.

= Features =

* related posts searched by tags
* specify how many posts should be displayed, option available both as general rule and as per post rule
* the ability to fill with random posts if no match found
* the ability to select the size of the photo
* choose witch post types are to be included

Just install, activate and add the code into your template.

== Installation ==

= Automatic instalation =

1. Login into your Wordpress admin panel
2. Navigate to Plugins->Add New
3. Search for "Vertical Related Posts" and click install
4. Activate the plugin
5. Place \'<?php if (function_exists('displayVerticalRelatedPosts')) displayVerticalRelatedPosts(); ?>\' into your template

= Manual instalation =

1. Download .zip file from https://github.com/corneliucirlan/vertical-related-posts
2. Extract archive into wp-content/plugins folder
3. Login into your admin panel and navigate to Plugins
4. Activate the plugin
5. Place \'<?php if (function_exists('displayVerticalRelatedPosts')) displayVerticalRelatedPosts(); ?>\' into your template

== Frequently Asked Questions ==

= I activated the plugin, why isn't it working ? =

After activation, you have to add the code \'<?php if (function_exists('displayVerticalRelatedPosts')) displayVerticalRelatedPosts(); ?>\' into your template.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, \`/tags/4.3/screenshot-1.png\` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==

= 1.0.1 =
* Fixed excerpt alignment

= 1.0.0 =
* Initial release.