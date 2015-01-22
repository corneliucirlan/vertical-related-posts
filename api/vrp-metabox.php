<?php
	/**
	 * 							ADD BUTTON TO SHOW/HIDE RELATED POSTS ON EVERY PAGE
	 */

	if (!class_exists("VerticalRelatedPostsMetabox")):
		class VerticalRelatedPostsMetabox
		{
			/**
			 * Plugin's settings
			 */
			public static $cc_vrp_options;

			/**
			 * Class constructor
			 */
			public function __construct($settings)
			{
				static::$cc_vrp_options = $settings;
				/**
				 * Hook into add_meta_boxes to register the plugin's metabox
				 */
				add_action('add_meta_boxes', array(__CLASS__, 'addRelatedMetaBox'));

				/**
				 * Hook into save_post to save metebox's data
				 */
				add_action('save_post', array($this, 'saveVRPMetabox'));

				/**
				 * Load custom CSS for options page
				 */
				add_action('admin_init', function() {
					wp_register_style('admin-vrp', VRP_URI.'/css/admin-vrp.css', array(), VRP_VERSION);
					wp_enqueue_style('admin-vrp');

					wp_enqueue_script('vrp-metabox-jquery', VRP_URI.'/js/admin-vrp.js', array('jquery'), VRP_VERSION);
				});
			}

			/**
			 * Register the metabox
			 */
			public static function addRelatedMetaBox()
			{
				//var_dump(self::$cc_vrp_options);
			    // get current post's type
				if (in_array(get_post_type(get_the_ID()), self::$cc_vrp_options['checkedPostTypes'])):
					$postType = get_post_type(get_the_ID());
					add_meta_box('vertical-related-posts', 'Vertical Related Posts', array(__CLASS__, 'verticalRelatedPostsMetaBox'), $postType, 'normal', 'default' );  
				endif;
			}

			/**
			 * Create metabox
			 */
			static function verticalRelatedPostsMetaBox()  
			{  
			    // $post is already set, and contains an object: the WordPress post  
			    global $post;
			    $values = get_post_custom($post->id);
			    
			    $numberOfPosts = static::$cc_vrp_options['defaultNumberOfPosts'];
			    $availablePostTypes = get_post_types();
			    if (isset($values['numberOfDisplayedPosts'])):
				    $numberOfPosts = $values['numberOfDisplayedPosts'];  
			  		$numberOfPosts = (int)$numberOfPosts[0];
			  	endif;

			  	$checked = 'off';
			  	if (isset($values['customPostTypesToUse'])) $checked = $values['customPostTypesToUse'][0];

			  	$disabled = 'off';
			  	if (isset($values['disableVRPOnPage'])) $disabled = $values['disableVRPOnPage'][0];
			   
			    // We'll use this nonce field later on when saving.  
			    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' ); 
			    ?> 
				
				<div id="cc-vrp-wrapper">
					<section class="cc-vrp-section">
						<div class="cc-vrp-label">
							Disable Vertical Related Posts for this page
						</div>
						<div class="cc-vrp-input">
							<div class="onoffswitch" style="clear: right;">
								<input style="display: none;" type="checkbox" name="disableVRPOnPage" class="onoffswitch-checkbox" id="disableVRPOnPage" <?php if ($disabled == "on") echo "checked" ?>>
								<label class="onoffswitch-label" for="disableVRPOnPage">
									<div class="onoffswitch-inner"></div>
									<div class="onoffswitch-switch"></div>
								</label>
							</div>
						</div>
					</section>

					<section class="cc-vrp-section">
						<div class="cc-vrp-label">
							Number of posts to display
						</div>
						<div class="cc-vrp-input">
							<input type="number" style="width: 50px;" name="numberOfDisplayedPosts" id="numberOfDisplayedPosts" value="<?php echo $numberOfPosts; ?>" /> 
						</div>
					</section>
					
					<section class="cc-vrp-section">
						<div class="cc-vrp-label">
							Only display specific post types
						</div>
						<div class="cc-vrp-input">
							<div class="onoffswitch" style="clear: both;">
								<input style="display: none;" type="checkbox" name="customPostTypesToUse" class="onoffswitch-checkbox" id="customPostTypesToUse" <?php if ($checked == "on") echo "checked" ?>>
								<label class="onoffswitch-label" for="customPostTypesToUse">
									<div class="onoffswitch-inner"></div>
									<div class="onoffswitch-switch"></div>
								</label>
							</div>
						</div>
					</section>

					<section class="cc-vrp-section customPostTypesToUse" <?php if ($checked == "off") echo "style='display: none;'"; ?>>
						<div class="cc-vrp-label">
							Post Types to use
						</div>
						<div class="cc-vrp-input">
							<?php
							foreach ($availablePostTypes as $type):
								$typeName = get_post_type_object($type);
								$typeName = $typeName->label;
								?>
								<label for="<?php echo $type ?>">
									<input type="checkbox" name='<?php echo $type ?>' id='<?php echo $type ?>' value="<?php echo $type ?>" <?php if (array_key_exists('checkedTypes', $values) && strpos($values['checkedTypes'][0], $type)) echo 'checked'?>>
									<?php echo $typeName ?><br>
								</label>
								<!-- <div class="onoffswitch" style="clear: both; float: left">
									<input style="display: none;" type="checkbox" name="<?php echo $type ?>" class="onoffswitch-checkbox" id="<?php echo $type ?>" <?php if (array_key_exists('checkedTypes', $values) && strpos($values['checkedTypes'][0], $type)) echo 'checked'?>>
									<label class="onoffswitch-label" for="<?php echo $type ?>">
										<div class="onoffswitch-inner"></div>
										<div class="onoffswitch-switch"></div>
									</label>
								</div>
								<label style="display: inline-block; padding-left: 10px;" for="<?php echo $type ?>">
									<?php echo $typeName ?>
								</label><br> -->
							<?php endforeach; ?>
						</div>	
					</section>
				</div>
				<?php
			}

			/**
			 * Save Metabox data
			 */
			function saveVRPMetabox($post_id)
			{
				$availablePostTypes = get_post_types();

			    // Bail if we're doing an auto save  
			    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id; 
			     
			    // if our nonce isn't there, or we can't verify it, bail 
			    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return $post_id; 
			     
			    // if our current user can't edit this post, bail
			    if( !current_user_can( 'edit_post' ) ) return $post_id;
			      			      
			    // get wherever to display the related posts or not
			   	$display = isset($_POST['disableVRPOnPage']) ? "on" : "off";
			   	update_post_meta($post_id, 'disableVRPOnPage', $display);

			    // Get number of posts to display
			    if (isset($_POST['numberOfDisplayedPosts'])) update_post_meta($post_id, 'numberOfDisplayedPosts', esc_attr($_POST['numberOfDisplayedPosts']));
			    	else update_post_meta($post_id, 'numberOfDisplayedPosts', $this->cc_vrp_options['defaultNumberOfPosts']);
			
				// verify if checked to use custom post types for current article
			    $checked = isset($_POST['customPostTypesToUse']) ? "on" : "off";
			    update_post_meta($post_id, 'customPostTypesToUse', $checked);

			    // if is checked... save all post types
			    if ($checked == 'on'):
			    	$checkedPostTypes = array();
			    	foreach($availablePostTypes as $type):
						if (isset($_POST[$type]))
							$checkedPostTypes[] = $type;
					endforeach;
					update_post_meta($post_id, 'checkedTypes', $checkedPostTypes);
			    endif;
			}
		}
	endif;
	
?>