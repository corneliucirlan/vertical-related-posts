<?php

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
						wp_register_style('admin-vrp', VRP_URI.'css/admin-vrp.css', array(), VRP_VERSION);
						wp_enqueue_style('admin-vrp');

						wp_enqueue_script('vrp-metabox-jquery', VRP_URI.'js/admin-vrp.js', array('jquery'), VRP_VERSION);
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
			public function vrp_version_check()
			{
				$default = get_option('cc_vrp_options');

				// check if all options are in the database
				if (!array_key_exists('version', $this->cc_vrp_options)) $default['version'] = VRP_VERSION; else $default['version'] = VRP_VERSION;
				if (!array_key_exists('relatedPostsTitle', $this->cc_vrp_options)) $default['relatedPostsTitle'] = VRP_TITLE;
				if (!array_key_exists('defaultNumberOfPosts', $this->cc_vrp_options)) $default['defaultNumberOfPosts'] = VRP_NUMBER_OF_POSTS;
				if (!array_key_exists('fillWithRandomPosts', $this->cc_vrp_options)) $default['fillWithRandomPosts'] = VRP_FILL_WITH_RANDOM_POSTS;
				if (!array_key_exists('checkedPostTypes', $this->cc_vrp_options)) $default['checkedPostTypes'] = VRP_CHECKED_POST_TYPES;
				if (!array_key_exists('featuredImageSize', $this->cc_vrp_options)) $default['featuredImageSize'] = VRP_FEATURED_SIZE;
				if (!array_key_exists('loadDefaultCSS', $this->cc_vrp_options)) $default['loadDefaultCSS'] = VRP_DEFAULT_CHECKBOX;
				if (!array_key_exists('displayTitle', $this->cc_vrp_options)) $default['displayTitle'] = VRP_DEFAULT_CHECKBOX;
				if (!array_key_exists('displayFeaturedImage', $this->cc_vrp_options)) $default['displayFeaturedImage'] = VRP_DEFAULT_CHECKBOX;
				if (!array_key_exists('displayExcerpt', $this->cc_vrp_options)) $default['displayExcerpt'] = VRP_DEFAULT_CHECKBOX;

				// update database with
				update_option('cc_vrp_options', $default);
			}

			/**
			 * Activate the plugin, set up the settings required on the settings page
			 */
			public function activation() 
			{
				// If our option is not stored, add it to the database and output our welcome message
				if (!get_option( 'cc_vrp_activate_flag'))
					update_option('cc_vrp_activate_flag', true);

				// Create default plugin settings
				$default = array();
				$default['version'] = VRP_VERSION;
				$default['relatedPostsTitle'] = VRP_TITLE;
				$default['defaultNumberOfPosts'] = VRP_NUMBER_OF_POSTS;
				$default['fillWithRandomPosts'] = VRP_FILL_WITH_RANDOM_POSTS;
				$default['checkedPostTypes'] = VRP_CHECKED_POST_TYPES;
				$default['featuredImageSize'] = VRP_FEATURED_SIZE;
				$default['loadDefaultCSS'] = VRP_DEFAULT_CHECKBOX;
				$default['displayTitle'] = VRP_DEFAULT_CHECKBOX;
				$default['displayFeaturedImage'] = VRP_DEFAULT_CHECKBOX;
				$default['displayExcerpt'] = VRP_DEFAULT_CHECKBOX;

				// Store default plugin settings
				add_option('cc_vrp_options', $default);
			}

			/**
			 * This function outputs an activation message if the activation flag was just set.
			 */
			public function admin_notices()
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
			public function deactivation()
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
				 * Add Settings sections
				 * id, title, cb, wwich page
				 */
				add_settings_section('cc_vrp_general_section', 'General', array($this, 'setVRPGeneralSettings'), 'vertical-related-posts-options');
				add_settings_section('cc_vrp_posttypes_section', 'Post Types', array($this, 'setVRPTypesSection'), 'vertical-related-posts-options');
				add_settings_section('cc_vrp_customcss_section', 'Stylesheet', array($this, 'setVRPCustomCSS'), 'vertical-related-posts-options');
				
				/*
				 * Add settings fields
				 */
				
				// Title
				add_settings_field('cc_vrp_title', 'Title', array($this, 'getVRPTitle'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['relatedPostsTitle']);

				// Number of Posts
				add_settings_field('cc_vrp_postsnumber', 'Number of Posts', array($this, 'getVRPNumberOfPosts'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['defaultNumberOfPosts']);
			
				// Fill with random posts
				add_settings_field('cc_vrp_fillrandomposts', 'Fill with random posts', array($this, 'getVRPFillRandom'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['fillWithRandomPosts']);

				// Feature Image Size
				add_settings_field('cc_vrp_featureimagesize', 'Feature Image Size', array($this, 'getVRPFeatureImageSize'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['featuredImageSize']);

				// Related posts titles
				add_settings_field('cc_vrp_displaytitle', 'Display Title', array($this, 'getVRPDisplayTitle'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['displayTitle']);

				// Related posts featured image
				add_settings_field('cc_vrp_displayfeaturedimage', 'Display Featured Image', array($this, 'getVRPDisplayFeaturedImage'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['displayFeaturedImage']);

				// Related posts excerpt
				add_settings_field('cc_vrp_displayexcerpt', 'Display Excerpt', array($this, 'getVRPDisplayExcerpt'), 'vertical-related-posts-options', 'cc_vrp_general_section', $this->cc_vrp_options['displayExcerpt']);


				// Post Types
				add_settings_field('cc_vrp_posttypes', 'Post Types', array($this, 'getVRPPostTypes'), 'vertical-related-posts-options', 'cc_vrp_posttypes_section', $this->cc_vrp_options['checkedPostTypes']);

				
				// Custom CSS
				add_settings_field('cc_vrp_customcss', 'Load Plugin\'s Stylesheet', array($this, 'getVRPCustomCSS'), 'vertical-related-posts-options', 'cc_vrp_customcss_section', $this->cc_vrp_options['loadDefaultCSS']);
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
				?><p>The necessary CSS to customize the look of the related posts</p><?php
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

			// Related posts title
			public function getVRPDisplayTitle($options)
			{
				?>
				<div class="onoffswitch">
					<input type="checkbox" name="displayTitle" class="onoffswitch-checkbox" id="displayTitle" <?php if ($options == "on") echo "checked" ?>>
					<label class="onoffswitch-label" for="displayTitle">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>
				<?php
			}

			// Related posts title
			public function getVRPDisplayFeaturedImage($options)
			{
				?>
				<div class="onoffswitch">
					<input type="checkbox" name="displayFeaturedImage" class="onoffswitch-checkbox" id="displayFeaturedImage" <?php if ($options == "on") echo "checked" ?>>
					<label class="onoffswitch-label" for="displayFeaturedImage">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>
				<?php
			}

			// Related posts title
			public function getVRPDisplayExcerpt($options)
			{
				?>
				<div class="onoffswitch">
					<input type="checkbox" name="displayExcerpt" class="onoffswitch-checkbox" id="displayExcerpt" <?php if ($options == "on") echo "checked" ?>>
					<label class="onoffswitch-label" for="displayExcerpt">
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

			// Custom CSS
			public function getVRPCustomCSS($options)
			{
				?>
				<div class="onoffswitch" style="display: inline-block">
					<input type="checkbox" name="loadDefaultCSS" class="onoffswitch-checkbox" id="loadDefaultCSS" <?php if ($options == 'on') echo 'checked'?>>
					<label class="onoffswitch-label" for="loadDefaultCSS">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>

				<div id="custom-css" <?php if ($this->cc_vrp_options['loadDefaultCSS'] == 'on') echo "style='display: none;'" ?>>
					<h4>Add this CSS tags to your theme to customize the layout of Vertical Related Posts</h4>
					<pre>
	div.cc-vertical-related-posts {}
	h1.cc-vrp-title {}
	h1.cc-vrp-article-title {}
	div.cc-vertical-related-posts p {}
					</pre>
				</div>
				<?php
			}

			/**
			 * Validate input and update database
			 */
			public function cc_vrp_validate($input)
			{
				$availablePostTypes = get_post_types();

				foreach($availablePostTypes as $type):
					if (isset($_POST[$type]))
						$checkedPostTypes[] = $type;
				endforeach;

				// dump variables into array for later wp_options update
				$input['relatedPostsTitle'] = wp_kses_post($_POST['relatedPostsTitle']);
				$input['checkedPostTypes'] = $checkedPostTypes;
				$input['defaultNumberOfPosts'] = $_POST['defaultNumberOfPosts'];
				$input['fillWithRandomPosts'] = isset($_POST['fillWithRandomPosts']) ? 'on' : 'off';
				$input['featuredImageSize'] = $_POST['featuredImageSize'];
				$input['loadDefaultCSS'] = isset($_POST['loadDefaultCSS']) ? 'on' : 'off';
				$input['displayTitle'] = isset($_POST['displayTitle']) ? 'on' : 'off';
				$input['displayFeaturedImage'] = isset($_POST['displayFeaturedImage']) ? 'on' : 'off';
				$input['displayExcerpt'] = isset($_POST['displayExcerpt']) ? 'on' : 'off';

				return $input;
			}

			/**
			 * Main function to display all settings
			 */
			public function vertical_related_posts_options()
			{
				?>
				<div class="wrap">
					<?php screen_icon('options-general'); ?>
					<h2>VERTICAL RELATED POSTS</h2>
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