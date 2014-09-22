=== Vertical Related Posts ===
Contributors: corneliucirlan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=corneliucirlan%40gmail%2ecom&lc=RO&item_name=Corneliu%20Cirlan&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: wordpress, related posts, vertical, responsive, mobile friendly, light, white, sidebar, vertical related posts, posts, custom post types, pages
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin that displays related posts vertically.

== Description ==

Increase your visitors engagement by providing them with related articles from your website.

VRP uses tags to find related posts and then displays them according to your conditions.

Being responsive, it will automatically adjust to your layout. So you can insert VRP anywhere in your template

= Features =

* vertically display related posts
* choose a title for the VRP section
* choose how many posts to be displayed (both globally and as per post)
* choose the size of the featured image (based on WP sizes)
* fill with random posts if available posts aren't enough (posts with no tags in common)
* choose what to display (title/featured image/excerpt)
* choose the post types on which VRP are displayed
* CSS tags for customization

Just install, activate and add the code into your template.

If you have any suggestions/requests, don't hesitate to contact me at <code><a href="mailto:corneliucirlan@gmail.com">corneliucirlan@gmail.com</a></code>

== Installation ==

= Automatic instalation =

1. Login into your Wordpress admin panel
2. Navigate to Plugins->Add New
3. Search for "Vertical Related Posts" and click install
4. Activate the plugin
5. Place <code><?php if (function_exists('displayVerticalRelatedPosts')) displayVerticalRelatedPosts(); ?></code> into your template

= Manual instalation =

1. Download .zip file from https://github.com/corneliucirlan/vertical-related-posts
2. Extract archive into wp-content/plugins folder
3. Login into your admin panel and navigate to Plugins
4. Activate the plugin
5. Place <code><?php if (function_exists('displayVerticalRelatedPosts')) displayVerticalRelatedPosts(); ?></code> into your template

== Frequently Asked Questions ==

= I activated the plugin, why isn't it working ? =

After activation, you have to add the code <code><?php if (function_exists('displayVerticalRelatedPosts')) displayVerticalRelatedPosts(); ?></code> into your template.

== Screenshots ==

1. Settings Page for Vertical Related Posts
2. Vertical Related Posts in action (screenshot taken from www.uncover-romania.com)

== Changelog ==

= 1.2 - 9.22.2014 =
* new: choose what to be displayed (title/featured image/excerpt)
* fix: custom css tags on settings page
* mod: description rewrite
* mod: file system change

= 1.1 - 07.08.2014 =
* new: added the ability to disable VRP from current page
* mod: re-styled VRP Metabox

= 1.0.2 - 07.08.2014 =
* fix: corrected CSS tags on settings page

= 1.0.1 - 07.02.2014=
* fix: fixed excerpt alignment

= 1.0.0 - 06.30.2014 =
* Initial release.