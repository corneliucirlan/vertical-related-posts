<?php

	if (!class_exists("VerticalRelatedPosts")):
		class VerticalRelatedPosts
		{
			/**
			 * Plugin's settings
			 */
			private $cc_vrp_options;
			private $values;

			public function __construct()
			{
				global $post;
				$this->cc_vrp_options = get_option('cc_vrp_options');
				$this->values = get_post_custom($post->id);
			}

			/**
			 * Get the tags for the current page
			 */
			private function getCurrentTags()
			{
				// Get all tags by ID from the current page 
				$currentPostTags = get_the_tags();
				$tags = array();
				if ($currentPostTags)
					foreach($currentPostTags as $tag)
						$tags[] = $tag->term_id;

				unset($currentPostTags);
				return $tags;
			}

			/**
			 * Get the number of posts to display
			 */
			private function getNumberToDisplay()
			{
				// Set the number of posts to be displayed 
				$numberOfPosts = $this->cc_vrp_options['defaultNumberOfPosts'];
				if (isset($this->values['numberOfDisplayedPosts'])):
					$numberOfPosts = $this->values['numberOfDisplayedPosts'];
					$numberOfPosts = (int)$numberOfPosts[0];
				endif;
				return $numberOfPosts;
			}

			/**
			 * Query the WP DB for the posts
			 */
			private function getQuery($posts_per_page, $orderby, $post_type, $post__not_in, $post__in, $post_status)
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
			 * Get the post types selected
			 */
			private function getSelectedPostTypes()
			{
				$postTypes = $this->cc_vrp_options['checkedPostTypes'];
				if (isset($this->values['customPostTypesToUse'])):
					if ($this->values['customPostTypesToUse'][0] == "on"):
						unset($postTypes);
						$availablePostTypes = get_post_types();
						foreach ($availablePostTypes as $type)
							if (strpos($this->values['checkedTypes'][0], $type)):
								$postTypes[] = $type;
							endif;
					endif;
				endif;
				return $postTypes;
			}

			/**
			 * Get all eligible posts for use
			 */
			private function getAllPosts($the_query, $tags)
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
			private function displayArticle()
			{
				global $post;
				$image = wp_get_attachment_image_src(get_post_thumbnail_id( $post->ID ), $this->cc_vrp_options['featuredImageSize']);
				?>
				<article>
					<a href="<?php the_permalink() ?>" title="<?php the_title() ?>" rel="bookmark">
						<h1 class='cc-vrp-article-title'><?php the_title() ?></h1>
						<div><img src="<?php echo $image[0]; ?>"></div>
					</a>
					<?php the_excerpt(); ?>
				</article>
				<?php
			}


			/**
			 *	DISPLAY VERTICAL RELATED POSTS 
			 */
			public function displayVerticalRelatedPosts()
			{
				// get all tags from current page
				$tags = $this->getCurrentTags();

				// get number of posts to be displayed
				$numberOfPosts = $this->getNumberToDisplay();
				
				// get what post types to use
				$postTypes = $this->getSelectedPostTypes();
				
				// Create the tags in common query
				$the_query = $this->getQuery(-1, 'rand', $postTypes, array(get_the_ID()), null, 'publish');
				
				// get an array of all posts with common tags as current post
				$postsArray = $this->getAllPosts($the_query, $tags);
				
				// Create the Related Posts Query
				$the_query = $this->getQuery($numberOfPosts, 'rand', $postTypes, array(get_the_id()), $postsArray, 'publish');

				// Main VRP block
				$numberOfAvailablePosts = (have_posts()) ? sizeof($the_query->posts) : 0;
				?>
				<div class="cc-vertical-related-posts">
					<h1 class="cc-vrp-title"><?php echo $this->cc_vrp_options['relatedPostsTitle'] ?></h1>
					<?php
					/* Display the Related Posts */
					if ($the_query->have_posts()): 
						while ($the_query->have_posts()):
							$the_query->the_post();
							$this->displayArticle();
						endwhile; 
					endif;

				// if there aren't enough posts, add random ones
				if ($numberOfPosts > $numberOfAvailablePosts)
					if ($this->cc_vrp_options['fillWithRandomPosts'] == 'on'):
						$the_query = $this->getQuery($numberOfPosts-$numberOfAvailablePosts, 'rand', $this->cc_vrp_options['checkedPostTypes'], array(get_the_ID(), $postsArray), $postsArray, 'publish');
						if ($the_query->have_posts()):
							while ($the_query->have_posts()):
								$the_query->the_post();
								$this->displayArticle();
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