<?php
	/**
	 *	Plugin Name: Vertical Related Posts
	 *	Plugin URI: https://github.com/corneliucirlan/vertical-related-posts
	 *	Description: Wordpress plugin for displaying related posts.
	 *	Author: Corneliu C&icirc;rlan
	 *	License: GPLv2 or later
	 *	Version: 1.2.5
	 *	Author URI: https://linkedin.com/in/corneliucirlan
	 */
	

	@define('VRP_VERSION', '1.2.5'); // Plugin version
	@define('VRP_FILE', __FILE__); // Reference to this plugin's file
	@define('VRP_DIR', plugin_dir_path(__FILE__)); // Plugin directory path
	@define('VRP_URI', trailingslashit(plugins_url('', __FILE__))); // plugin url

	@define('VRP_TITLE', 'RETATED POSTS'); // default title
	@define('VRP_NUMBER_OF_POSTS', 3); // default number of posts to be displayed
	@define('VRP_DEFAULT_CHECKBOX', 'on'); // load the default stylesheet
	@define('VRP_FILL_WITH_RANDOM_POSTS', 'on'); // fill with random posts
	@define('VRP_CHECKED_POST_TYPES', array('post')); // post types enabled by default
	@define('VRP_FEATURED_SIZE', 'medium'); // featured image default size


	/**
	 * INCLUDE ADMIN API
	 */
	require_once(VRP_DIR.'/api/vrp-admin.php');

	/**
	 * INCLUDE METABOX API
	 */
	require_once(VRP_DIR.'/api/vrp-metabox.php');

	/**
	 * INCLUDE DISPLAY API
	 */
	require_once(VRP_DIR.'/api/vrp-display.php');

	/**
	 * ADD SETTINGS BUTTON ON "PLUGINS" PAGE
	 */
	add_filter('plugin_action_links', 'VRPSettingsButton', 10, 2);
	function VRPSettingsButton($links, $file)
	{
	    if ($file == 'vertical-related-posts/vertical-related-posts.php')
	        $links['settings'] = sprintf('<a href="%s"> %s </a>', admin_url('options-general.php?page=vertical-related-posts-options'), __('Settings', 'plugin_domain'));
	    return $links;
	}

	// CREATE INSTANCES
	$VRPAdmin = new VerticalRelatedPostsAdmin();
	$VRPSettings = $VRPAdmin->getSettings();
	$VRPMetabox = new VerticalRelatedPostsMetabox($VRPSettings);


	// LOAD NECESSARY CSS ON SELECTED POST TYPES
	if ($VRPSettings['loadDefaultCSS'] == 'on')
		add_action('wp_enqueue_scripts', function() {
			global $VRPSettings;
			if (in_array(get_post_type(), $VRPSettings['checkedPostTypes']))
				wp_enqueue_style('cc-vrp-style', VRP_URI.'css/vertical-related-posts.css', array(), VRP_VERSION);
		});


	/**
	 * DISPLAY RELATED POSTS
	 */
	function displayVerticalRelatedPosts()
	{
		$vrp_new = new VerticalRelatedPosts();
		return $vrp_new->displayVerticalRelatedPosts();
	}

?>