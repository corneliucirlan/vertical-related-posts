<?php
	/**
	 *	Plugin Name: Vertical Related Posts
	 *	Plugin URI: http://www.uncover-romania.com/
	 *	Description: Vertical Related Posts created specially for Uncover Romania's website.
	 *	Author: Corneliu C&icirc;rlan
	 *	License: GPLv2 or later
	 *	Version: 1.0.0
	 *	Author URI: https://linkedin.com/in/corneliucirlan
	 */

	if (!class_exists("VerticalRelatedPostsAdmin")):
		class VerticalRelatedPostsAdmin
		{
			/**
			 * Plugin's settings
			 */
			private $cc_vrp_options;

			/**
			 * Class constructor
			 */
			function __construct()
			{
				/**
				 * Load plugin's settings
				 */
				$this->cc_vrp_options = get_option('cc_vrp_options');

				/**
				 * Hook into init to check plugin changes
				 */
				add_action('init', array($this, 'vrp_version_check'));

				/**
				 * Hook into admin_menu to register the Options page
				 */
				add_action('admin_menu', array($this, 'admin_menu'));

				/**
				 * Hook into admin_init to register the actual settings
				 */
				add_action('admin_init', array($this, 'admin_init')); // Register our settings

				// Plugin Activation
				register_activation_hook(VRP_FILE, array($this, 'activation'));
				add_action('admin_notices', array($this, 'admin_notices')); // We use this hook with the activation to output a message to the user

				// Plugin De-Activation
				register_deactivation_hook(VRP_FILE, array($this, 'deactivation'));

				/**
				 * Load custom CSS for options page
				 */
				add_action('admin_init', function() {
					if (is_admin()):
						wp_register_style('admin-vrp', VRP_URI.'/css/admin-vrp.css', array(), VRP_VERSION);
						wp_enqueue_style('admin-vrp');
					endif;
				});
			}

			public function getSettings()
			{
				return $this->cc_vrp_options;
			}

			/**
			 * Check the database for any changes made and updates it if found any
			 */
			function vrp_version_check()
			{
				$default = get_option('cc_vrp_options');

				// check if all options are in the database
				if (!array_key_exists('version', $this->cc_vrp_options)) $default['version'] = VRP_VERSION; else $default['version'] = VRP_VERSION;
				if (!array_key_exists('relatedPostsTitle', $this->cc_vrp_options)) $default['relatedPostsTitle'] = VRP_TITLE;
				if (!array_key_exists('defaultNumberOfPosts', $this->cc_vrp_options)) $default['defaultNumberOfPosts'] = VRP_NUMBER_OF_POSTS;
				if (!array_key_exists('loadDefaultCss', $this->cc_vrp_options)) $default['loadDefaultCss'] = VRP_DEFAULT_CSS;
				if (!array_key_exists('fillWithRandomPosts', $this->cc_vrp_options)) $default['fillWithRandomPosts'] = VRP_FILL_WITH_RANDOM_POSTS;
				if (!array_key_exists('checkedPostTypes', $this->cc_vrp_options)) $default['checkedPostTypes'] = VRP_CHECKED_POST_TYPES;
				if (!array_key_exists('featuredImageSize', $this->cc_vrp_options)) $default['featuredImageSize'] = VRP_FEATURED_SIZE;

				// update database with
				update_option('cc_vrp_options', $default);
			}

			/**
			 * Activate the plugin, set up the settings required on the settings page
			 */
			function activation() 
			{
				// If our option is not stored, add it to the database and output our welcome message
				if (!get_option( 'cc_vrp_activate_flag'))
					update_option('cc_vrp_activate_flag', true);

				// Create default plugin settings
				$default = array();
				$default['version'] = VRP_VERSION;
				$default['relatedPostsTitle'] = VRP_TITLE;
				$default['defaultNumberOfPosts'] = VRP_NUMBER_OF_POSTS;
				$default['loadDefaultCss'] = VRP_DEFAULT_CSS;
				$default['fillWithRandomPosts'] = VRP_FILL_WITH_RANDOM_POSTS;
				$default['checkedPostTypes'] = VRP_CHECKED_POST_TYPES;
				$default['featuredImageSize'] = VRP_FEATURED_SIZE;

				// Store default plugin settings
				add_option('cc_vrp_options', $default);
			}

			/**
			 * This function outputs an activation message if the activation flag was just set.
			 */
			function admin_notices()
			{
				// If our option is set to true, user just activated the plugin.
				if (get_option('cc_vrp_activate_flag')):
					?>
					<div class="updated" style="background-color: #5f87af; border-color: #354f6b; color:#fff;">
						<p>Thank you for installing Vertical Related Posts! Take a look at the plugin <a href="<?php echo admin_url( 'options-general.php?page=vertical-related-posts-options' ); ?>" style="color:#fff; text-decoration: underline;">settings page</a> for various options.</p>
					</div>
					<?php
					update_option( 'cc_vrp_activate_flag', false ); // Setting the flag to false, ultimately it would be best to remove this option now, however we wanted to include a deactivation hook as well
				endif;
			}

			/**
			 * Clean up on deactivating the plugin
			 */
			function deactivation()
			{
				delete_option('cc_vrp_activate_flag');
			}

			/**
			 * Add plugin's option page in Settings section
			 */
			public function admin_menu()
			{
				//							  Page Title				, Menu Title		 , Capability	   , Menu (Page) Slug		   , Callback Function (used to display the page)
				add_options_page( 'Vertical Related Posts Options', 'Vertical Related Posts', 'manage_options', 'vertical-related-posts-options', array($this, 'vertical_related_posts_options'));
			}

			/**
			 * Register the plugin's settings to use on the Settings page (registered above)
			 */
			public function admin_init()
			{
				/*
				 * Register VRP settings
				 */
				register_setting('cc_vrp_options', 'cc_vrp_options', array($this, 'cc_vrp_validate'));

				/*
				 * Add General Settings section
				 * id, title, cb, wwich page
				 */
				add_settings_section('cc_vrp_general_section', 'General', array($this, 'setVRPGeneralSettings'), 'vertical-related-posts-options');

				add_settings_section('cc_vrp_posttypes_section', 'Post Types', array($this, 'setVRPTypesSection'), 'vertical-related-posts-options');

				add_settings_section('cc_vrp_customcss_section', 'Custom CSS', array($this, 'setVRPCustomCSS'), 'vertical-related-posts-options');
				
				/*
				 * Add settings fields
				 */
				
				// Title
				add_settings_field('cc_vrp_title', 'Title', array($this, 'getVRPTitle'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['relatedPostsTitle']);

				// Number of Posts
				add_settings_field('cc_vrp_postsnumber', 'Number of Posts', array($this, 'getVRPNumberOfPosts'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['defaultNumberOfPosts']);
			
				// Load default stylesheet
				add_settings_field('cc_vrp_defaultstylesheet', 'Default Stylesheet', array($this, 'getVRPDefaultStylesheet'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['loadDefaultCss']);
			
				// Fill with random posts
				add_settings_field('cc_vrp_fillrandomposts', 'Fill with random posts', array($this, 'getVRPFillRandom'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['fillWithRandomPosts']);

				// Feature Image Size
				add_settings_field('cc_vrp_featureimagesize', 'Feature Image Size', array($this, 'getVRPFeatureImageSize'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['featuredImageSize']);

				// Post Types
				add_settings_field('cc_vrp_posttypes', 'Post Types', array($this, 'getVRPPostTypes'), 'vertical-related-posts-options', 'cc_vrp_posttypes_section', $this->cc_vrp_options['checkedPostTypes']);

				// Custom CSS
				add_settings_field('cc_vrp_customcss', 'Custom CSS', array($this, 'getVRPCustomCSS'), 'vertical-related-posts-options', 'cc_vrp_customcss_section');
			}

			/**
			 * This is the callback function, used above, to display our settings section.
			 */
			public function setVRPGeneralSettings()
			{
				?>
				<p>Use this section to adjust the General options for Vertical Related Posts.</p>
				<?php
			}

			public function setVRPTypesSection()
			{
				?><p>Use this section to select what post types to be used</p><?php
			}

			public function setVRPCustomCSS()
			{
				// optional
			}

			// Title
			public function getVRPTitle($options)
			{
				?>
				<input name="relatedPostsTitle" type="text" value="<?php if (isset($options)) echo $options ?>" class="regular-text">
				<?php
			}

			// Number of posts
			public function getVRPNumberOfPosts($options)
			{
				?><input name="defaultNumberOfPosts" type="number" value="<?php if (isset($options)) echo $options ?>" class=""><?php
			}

			// Featured Image size
			public function getVRPFeatureImageSize($options)
			{
				?>
				<select name="featuredImageSize" id="featuredImageSize">
				<option value="thumbnail" <?php if ($options == 'thumbnail') echo 'selected' ?>>Thumbnail</option>
					<option value="medium" <?php if ($options == 'medium') echo 'selected' ?>>Medium</option>
					<option value="large" <?php if ($options == 'large') echo 'selected' ?>>Large</option>
					<option value="full" <?php if ($options == 'full') echo 'selected' ?>>Full</option>
				</select>
				<?php
			}

			// load default stylesheed
			public function getVRPDefaultStylesheet($options)
			{
				?>
				<div class="onoffswitch">
					<input type="checkbox" name="loadDefaultCss" class="onoffswitch-checkbox" id="loadDefaultCss" <?php if ($options == "on") echo 'checked' ?>>
					<label class="onoffswitch-label" for="loadDefaultCss">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>
				<?php
			}

			// Fill with random posts
			public function getVRPFillRandom($options)
			{
				?>
				<div class="onoffswitch">
					<input type="checkbox" name="fillWithRandomPosts" class="onoffswitch-checkbox" id="fillWithRandomPosts" <?php if ($options == "on") echo "checked" ?>>
					<label class="onoffswitch-label" for="fillWithRandomPosts">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>
				<?php
			}

			// Post Types to use
			public function getVRPPostTypes($options)
			{
				?>
				<div>
					<?php
					$availablePostTypes = get_post_types();
					foreach ($availablePostTypes as $type):
						$typeName = get_post_type_object($type);
						$typeName = $typeName->label;
						?>

						<div class="onoffswitch" style="display: inline-block">
							<input type="checkbox" name="<?php echo $type ?>" class="onoffswitch-checkbox" id="<?php echo $type ?>" <?php if (isset($options) && in_array($type, $options)) echo 'checked'?>>
							<label class="onoffswitch-label" for="<?php echo $type ?>">
								<div class="onoffswitch-inner"></div>
								<div class="onoffswitch-switch"></div>
							</label>
						</div>

						<label style="display: inline-block" for="<?php echo $type ?>">
							<?php echo $typeName ?><br><br>
						</label><br>
						<?php
					endforeach;
					?>
				</div>
				<?php
			}

			public function getVRPCustomCSS()
			{
				?>
				<p>#vertical-related-posts { }</p>
				<p>#vertical-related-posts #title { }</p>
				<p>#vertical-related-posts h1 { }</p>
				<p>#vertical-related-posts p { }</p>
				<?php
			}

			/**
			 * Retrieve options from settings page
			 * @param  $input store all the settings
			 * @return all the options set for the plugin
			 */
			public function cc_vrp_validate($input)
			{
				$availablePostTypes = get_post_types();

				foreach($availablePostTypes as $type):
					if (isset($_POST[$type]))
						$checkedPostTypes[] = $type;
				endforeach;

				// dump variables into array for later wp_options update
				$input['relatedPostsTitle'] = mysql_real_escape_string($_POST['relatedPostsTitle']);
				$input['checkedPostTypes'] = $checkedPostTypes;
				$input['defaultNumberOfPosts'] = $_POST['defaultNumberOfPosts'];
				$input['loadDefaultCss'] = isset($_POST['loadDefaultCss']) ? 'on' : 'off';
				$input['fillWithRandomPosts'] = isset($_POST['fillWithRandomPosts']) ? 'on' : 'off';
				$input['featuredImageSize'] = $_POST['featuredImageSize'];

				return $input;
			}

			/**
			 * This function renders (displays) our options panel and is the callback used in the admin_menu hook.
			 *
			 * Notes:
			 *  - Notice the class of .wrap which "wraps" all of our content
			 *  - We're using the options general screen icon @see http://codex.wordpress.org/Function_Reference/screen_icon
			 *  - We're not using settings_errors() in this case because we do not have any custom error mesages
			 *      - settings_errors() is called automatically on options panels (@see http://codex.wordpress.org/Function_Reference/settings_errors)
			 *  - Our HTML form is posting to options.php (wp-admin/options.php)
			 */
			function vertical_related_posts_options()
			{
				?>
				<div class="wrap">
					<?php screen_icon('options-general'); ?>
					<h2 style="color:darkred">VERTICAL RELATED POSTS - UNDER DEVELOPMENT</h2>
					<?php
						$taxonomy_names = get_object_taxonomies( 'post' );
   						echo "<pre>";
   						print_r( $taxonomy_names);
   						echo "</pre>";
					?>
					<ul>
						<h2>TODO List</h2>
						<li> &#10003; Add custom number of posts for every post</li>
						<li> &#10003; Load default CSS if checked, provide textarea for custom CSS</li>
						<li> &#10003; If there aren't enought related posts, fill with random ones</li>
						<li> &#10003; Post specific post-types; if not defined... use global list</li>
						<li> &#10003; Ask user for feature-image size: thumbnail, medium, large, full</li>
						<li> &#9745; Add multiple layouts</li>
						<li> &#9633; Add option for related posts to be displayed on pages, select pages templates</li>
						<li> &#9633; Add option to save settings on plugin uninstalled/deletions</li>
						<li> &#9745; </li>
						<br><li> &#9633; Write documentation</li>
					</ul>
					<?php //settings_errors(); This function is already output on all settings/option pages so we don't need to include it here as it results in two error/saved messages. ?>
					<div class="vrp-settings">
						<form method="post" action="options.php">
							<?php settings_fields('cc_vrp_options'); ?>
							<?php do_settings_sections('vertical-related-posts-options'); ?>
							<?php submit_button(); ?>
						</form>
					</div>
				</div>
				<?php
			}
		}
	endif;
?>