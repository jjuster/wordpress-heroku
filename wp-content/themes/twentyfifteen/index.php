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

function grabExtraPostData(&$post)
{
	$post->featured_image = extract_img_src(get_the_post_thumbnail($post->ID));
	$post->permalink = get_permalink($post->ID);
	$post->category = htmlencode(strip_tags( get_the_category_list('/', '', $post->ID) ));
	$post->title = htmlencode($post->post_title);
}

$recent_posts = new WP_Query('posts_per_page=24');
if ($recent_posts->have_posts()) {
	while ($recent_posts->have_posts()) {
		$recent_posts->the_post();
		grabExtraPostData($post);
		
		$post_ids[] = $post->ID;
		$post_debug[] = $post;
	}
}


// $featured_posts = array($posts[4], $posts[7], $posts[9]);

$post_ids = array();
$post_debug = array();

get_header(); ?>

	<!-- [index] -->
	<div id="primary" class="content-area homepage-content">
		<main id="main" class="site-main" role="main">

			<div class="featured-post-container">

				<?php if ( $recent_posts->have_posts() ) : 

					while ( $recent_posts->have_posts() ) : $recent_posts->the_post();

						// $post->featured_image = extract_img_src(get_the_post_thumbnail($post->ID));
						// $post->permalink = get_permalink($post->ID);
						// $post->category = htmlencode(strip_tags( get_the_category_list('/', '', $post->ID) ));
						// $post->title = htmlencode($post->post_title);
						// $post_ids[] = $post->ID;
						// $post_debug[] = $post;

						echo <<<HTML
<a class="homepage-post featured" href="{$post->permalink}">
	<span class="post-image" style="background-image:url({$post->featured_image})"></span>
	<div class="bottom-text">
		<div class="category">{$post->category}</div>
		<div class="title">{$post->title}</div>
	</div>
</a>
HTML;

					endwhile; 
				endif; ?>
				

			</div>

			<div class="separator-wrap">
				<hr>
			</div>

			<!-- Recent Posts -->
			<div class="recent-posts-container js-masonry" 
				data-masonry-options='{"itemSelector": ".homepage-post"}'>

				<?php if ( $recent_posts->have_posts() ) : 

					while ( $recent_posts->have_posts() ) : $recent_posts->the_post();

						// $post->featured_image = extract_img_src(get_the_post_thumbnail($post->ID));
						// $post->permalink = get_permalink($post->ID);
						// $post->category = htmlencode(strip_tags( get_the_category_list('/', '', $post->ID) ));
						// $post->title = htmlencode($post->post_title);
						// $post_ids[] = $post->ID;
						// $post_debug[] = $post;

						echo <<<HTML

<div class="homepage-post">
	<a href="{$post->permalink}">
		<img src="{$post->featured_image}" data-pradux-ignore="true">
	</a>
	<div class="bottom-text">
		<a href="{$post->permalink}" class="category">{$post->category}</a>
		<a href="{$post->permalink}" class="title">{$post->title}</a>
	</div>
</div>
HTML;

					endwhile; 
				endif; ?>

			</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/3.1.8/imagesloaded.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.2/masonry.pkgd.min.js"></script>

<script>
var post_ids = <?=json_encode($post_ids)?>;
var post_debug = <?=json_encode($post_debug)?>;
</script>

<script>
function enable_masonry()
{
	var $grid = $('.recent-posts-container').masonry({
		percentPosition: true,
		columnWidth: '.grid-sizer',
		itemSelector: '.homepage-post.masonry'
	});

	// $grid.imagesLoaded().progress( function() {
	// 	$grid.masonry('layout');
	// });
}

// enable_masonry();
</script>

<?php get_footer(); ?>
