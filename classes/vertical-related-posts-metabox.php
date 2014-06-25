<?php
	/*
	 *	Plugin Name: Vertical Related Posts
	 *	Plugin URI: http://www.uncover-romania.com/
	 *	Description: Vertical Related Posts created specially for Uncover Romania's website.
	 *	Author: Corneliu C&icirc;rlan
	 *	License: GPLv2 or later
	 *	Version: 1.0
	 *	Author URI: https://linkedin.com/in/corneliucirlan
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
				self::$cc_vrp_options = $settings;
				/**
				 * Hook into add_meta_boxes to register the plugin's metabox
				 */
				add_action('add_meta_boxes', array(__CLASS__, 'addRelatedMetaBox'));

				/**
				 * Hook into save_post to save metebox's data
				 */
				add_action('save_post', array($this, 'cd_meta_box_save'));

				/**
				 * Load custom CSS for options page
				 */
				add_action('admin_init', function() {
					wp_register_style('admin-vrp', VRP_URI.'/admin/css/admin-vrp.css', array(), VRP_VERSION);
					wp_enqueue_style('admin-vrp');
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
			    
			    $numberOfPosts = self::$cc_vrp_options['defaultNumberOfPosts'];
			    $availablePostTypes = get_post_types();
			    if (isset($values['numberOfDisplayedPosts'])):
				    $numberOfPosts = $values['numberOfDisplayedPosts'];  
			  		$numberOfPosts = (int)$numberOfPosts[0];
			  	endif;

			  	$checked = 'off';
			  	if (isset($values['customPostTypesToUse'])) $checked = $values['customPostTypesToUse'][0];
			   
			    // We'll use this nonce field later on when saving.  
			    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' ); 
			    ?> 
			    <p> 
			        <label for="numberOfDisplayedPosts">Number of Posts to display
			        	<input type="number" style="width: 50px;" name="numberOfDisplayedPosts" id="numberOfDisplayedPosts" value="<?php echo $numberOfPosts; ?>" /> 
			    	</label>
			    </p>

			    <p>
			    	<div class="onoffswitch">
			    		<input style="display: none;" type="checkbox" name="customPostTypesToUse" class="onoffswitch-checkbox" id="customPostTypesToUse" <?php if ($checked == "on") echo "checked" ?>>
			    		<label class="onoffswitch-label" for="customPostTypesToUse">
			    			<div class="onoffswitch-inner"></div>
			    			<div class="onoffswitch-switch"></div>
			    		</label>
			    	</div>
			    	<span>Enable custom post types&nbsp;&nbsp;&nbsp;</span>
			    </p>

			    <script>
			    	jQuery(document).ready(function(){
						jQuery('#customPostTypesToUse').click(function(){
					        jQuery('.customPostTypesToUse').slideToggle();
						});
					});
			    </script>

			    <p>
			    	<label class="customPostTypesToUse" <?php if ($checked == "off") echo "style='display: none;'"; ?>>Post Types to use</label>
			    	<div class="customPostTypesToUse" <?php if ($checked == "off") echo "style='display: none;'" ?> style="overflow: scroll; height: 200px; width: 220px;">
					    <?php
					    foreach ($availablePostTypes as $type):
					    	$typeName = get_post_type_object($type);
							$typeName = $typeName->label;
					    	?>
					    	<label for="<?php echo $type ?>">
					    		<input type="checkbox" name='<?php echo $type ?>' id='<?php echo $type ?>' value="<?php echo $type ?>" <?php if (array_key_exists('checkedTypes', $values) && strpos($values['checkedTypes'][0], $type)) echo 'checked'?>>
					    		<?php echo $typeName ?><br>
					    	</label>
					    <?php endforeach; ?>
					</div>
				</p>
				<?php
			}

			/**
			 * Save Metabox data
			 */
			function cd_meta_box_save($post_id)
			{
				$availablePostTypes = get_post_types();

			    // Bail if we're doing an auto save  
			    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id; 
			     
			    // if our nonce isn't there, or we can't verify it, bail 
			    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return $post_id; 
			     
			    // if our current user can't edit this post, bail
			    if( !current_user_can( 'edit_post' ) ) return $post_id;
			      			      
			    // Make sure your data is set before trying to save it
			    if (isset($_POST['numberOfDisplayedPosts'])) update_post_meta($post_id, 'numberOfDisplayedPosts', wp_kses( $_POST['numberOfDisplayedPosts']));
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