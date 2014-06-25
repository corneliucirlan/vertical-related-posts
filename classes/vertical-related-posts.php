<?php
	/**
	 *	Plugin Name: Vertical Related Posts
	 *	Plugin URI: http://uncover-romania.com/
	 *	Description: Vertical Related Posts created specially for Uncover Romania's website.
	 *	Author: Corneliu C&icirc;rlan
	 *	License: GPLv2 or later
	 *	Version: 1.0
	 *	Author URI: https://linkedin.com/in/corneliucirlan
	 */
	

	if (!class_exists("VerticalRelatedPosts")):
		class VerticalRelatedPosts
		{
			/**
			 * Plugin's settings
			 */
			private static $cc_vrp_options;

			private static $values;

			/**
			 * Preformated text - DEBUG ONLY
			 */
			private static function print_p($data)
			{
				echo "<pre>";
				print_r($data);
				echo "</pre>";
			}

			function __construct()
			{
				global $post;
				/**
				 * Load plugin's settings
				 */
				static::$cc_vrp_options = get_option('cc_vrp_options');
				
				static::$values = get_post_custom($post->id);
			}

			/**
			 * [getCurrentTags description]
			 * @return [list] [tags of the current page]
			 */
			private static function getCurrentTags ()
			{
				// Get all tags by ID from the current page 
				$currentPostTags = get_the_tags();
				$tags = array();
				if ($currentPostTags)
					foreach($currentPostTags as $tag):
						$tags[] = $tag->term_id;
						//echo $tag->name.", ";  // DEBUG ONLY
						//echo $tag->term_id."<br/>";
					endforeach;

				unset($currentPostTags);
				return $tags;
			}

			/**
			 * [getNumberToDisplay description]
			 * @return [int] [number of posts to be displayed]
			 */
			private static function getNumberToDisplay()
			{
				// Set the number of posts to be displayed 
				$numberOfPosts = static::$cc_vrp_options['defaultNumberOfPosts'];
				if (isset(static::$values['numberOfDisplayedPosts'])):
					$numberOfPosts = static::$values['numberOfDisplayedPosts'];
					$numberOfPosts = (int)$numberOfPosts[0];
				endif;
				return $numberOfPosts;
			}

			/**
			 * Create a custom WP_Query
			 * @param  int $posts_per_page    number of posts to retrieve
			 * @param  string $orderby        how to order the posts
			 * @param  string $post_type      type of the posts
			 * @param  array $post__not_in    excluded posts
			 * @param  array $post__in        included posts
			 * @param  string $post_status    status of the posts
			 * @return array                  custom query
			 */
			private static function getQuery($posts_per_page, $orderby, $post_type, $post__not_in, $post__in, $post_status)
			{
				$args = array(
					'posts_per_page' => $posts_per_page,
					'orderby' => $orderby,
					'post_type' => $post_type,
					'post__not_in' => $post__not_in,
					'post__in' => $post__in,
					'post_status' => $post_status
					);
				wp_reset_query();
				return new WP_Query($args);
			}

			/**
			 * [getSelectedPostTypes description]
			 * @return array what post types to be used
			 */
			private static function getSelectedPostTypes()
			{
				$postTypes = static::$cc_vrp_options['checkedPostTypes'];
				if (isset(static::$values['customPostTypesToUse'])):
					if (static::$values['customPostTypesToUse'][0] == "on"):
						unset($postTypes);
						$availablePostTypes = get_post_types();
						foreach ($availablePostTypes as $type)
							if (strpos(static::$values['checkedTypes'][0], $type)):
								$postTypes[] = $type;
							endif;
					endif;
				endif;
				return $postTypes;
			}

			private static function getAllPosts($the_query, $tags)
			{
				$postsArray = array(); // the ids of posts that have 1 or more of current page's tags

				while ($the_query->have_posts()):
					$the_query->the_post();
					$pageTags = get_the_tags();
					if (is_array($pageTags))
						foreach ($pageTags as $tag):
							$t = $tag->term_id;
							if (in_array($t, $tags))
								if (!in_array($the_query->post->ID, $postsArray))
									$postsArray[] = $the_query->post->ID;
						endforeach;
				endwhile;
				return $postsArray;
			}

			/**
			 * Single related post template
			 */
			private static function displayArticle()
			{
				global $post;
				$image = wp_get_attachment_image_src(get_post_thumbnail_id( $post->ID ), static::$cc_vrp_options['featuredImageSize']);
				?>
				<article>
					<a href="<?php the_permalink() ?>" title="<?php the_title() ?>" rel="bookmark">
						<h1><?php the_title() ?></h1>
						<div><img src="<?php echo $image[0]; ?>"></div>
					</a>
					<p><?php the_excerpt(); ?></p>
				</article>
				<?php
			}


			/**
			 *	DISPLAY VERTICAL RELATED POSTS 
			 */
			static function displayVerticalRelatedPosts()
			{
				// get all tags from current page
				$tags = static::getCurrentTags();

				// get number of posts to be displayed
				$numberOfPosts = static::getNumberToDisplay();
				
				// get what post types to use
				$postTypes = static::getSelectedPostTypes();
				
				// Create the tags in common query
				$the_query = static::getQuery(-1, 'rand', $postTypes, array(get_the_ID()), null, 'publish');
				
				// get an array of all posts with common tags as current post
				$postsArray = static::getAllPosts($the_query, $tags);
				
				// Create the Related Posts Query
				$the_query = static::getQuery($numberOfPosts, 'rand', $postTypes, array(get_the_id()), $postsArray, 'publish');

				// Main VRP block
				$numberOfAvailablePosts = (have_posts()) ? sizeof($the_query->posts) : 0;
				?>
				<div id="vertical-related-posts">
					<h1 id="title"><?php echo static::$cc_vrp_options['relatedPostsTitle'] ?></h1>
					<?php
					/* Display the Related Posts */
					if ($the_query->have_posts()): 
						while ($the_query->have_posts()):
							$the_query->the_post();
							static::displayArticle();
						endwhile; 
					endif;

				// if there aren't enough posts, add random ones
				if ($numberOfPosts > $numberOfAvailablePosts)
					if (static::$cc_vrp_options['fillWithRandomPosts'] == 'on'):
						$the_query = static::getQuery($numberOfPosts-$numberOfAvailablePosts, 'rand', static::$cc_vrp_options['checkedPostTypes'], array(get_the_ID(), $postsArray), $postsArray, 'publish');
						if ($the_query->have_posts()):
							while ($the_query->have_posts()):
								$the_query->the_post();
								static::displayArticle();
							endwhile;
						endif;
					endif;
				?></div><?php
				// Reset the WP_Query
				wp_reset_query();
			}
		}
	endif;

	
?>