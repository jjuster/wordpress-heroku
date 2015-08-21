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

function htmlencode($str) {
	return !empty($str) ? htmlspecialchars($str, ENT_QUOTES, 'UTF-8', false) : '';
}
function extract_img_src($html) {
	if (empty($html)) {
		return '';
	}

	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$imgs = $doc->getElementsByTagName('img');
	foreach($imgs as $img) {
		return $img->getAttribute('src');
	}
	return '';
}

$posts = new WP_Query('posts_per_page=10');
$post_ids = array();
$post_debug = array();

get_header(); ?>

	<!-- [index] -->
	<div id="primary" class="content-area homepage-content">
		<main id="main" class="site-main" role="main">

			<div class="featured-post-container">

				<?php if ( $posts->have_posts() ) : ?>
					

					<?php
					// Start the loop.
					while ( $posts->have_posts() ) : $posts->the_post();

						$post->featured_image = extract_img_src(get_the_post_thumbnail($post->ID));
						$post->permalink = get_permalink($post->ID);
						$post->category = htmlencode(strip_tags( get_the_category_list('/', '', $post->ID) ));
						$post->title = htmlencode($post->post_title);
						$post_ids[] = $post->ID;
						$post_debug[] = $post;

						echo <<<HTML
<a class="homepage-post" href="{$post->permalink}">
	<span style="background-image:url({$post->featured_image})"></span>
	<div class="bottom-text">
		<div class="category">{$post->category}</div>
		<div class="title">{$post->title}</div>
	</div>
</a>
HTML;

					endwhile;



				// If no content, include the "No posts found" template.
				else :
					get_template_part( 'content', 'none' );

				?>
				<?php endif; ?>

			</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<script>
	var post_ids = <?=json_encode($post_ids)?>;
	var post_debug = <?=json_encode($post_debug)?>;
</script>

<?php get_footer(); ?>
