<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * e.g., it puts together the home page when no home.php file exists.
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

$posts = new WP_Query('posts_per_page=10');
$post_ids = array();
$post_debug = array();

get_header(); ?>

	<!-- [index] -->
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( $posts->have_posts() ) : ?>

			

			<?php
			// Start the loop.
			while ( $posts->have_posts() ) : $posts->the_post();

				$post_ids[] = $post->ID;
				$post_debug[] = $post;

			endwhile;



		// If no content, include the "No posts found" template.
		else :
			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<script>
	var post_ids = <?=json_encode($post_ids)?>;
	var post_debug = <?=json_encode($post_debug)?>;
</script>

<?php get_footer(); ?>
