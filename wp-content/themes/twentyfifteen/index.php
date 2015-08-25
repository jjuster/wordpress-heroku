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

$featured_post_opts = array(
	'posts_per_page' => 3,
	'post__in' => array(1, 232, 247)
);

$recent_post_opts = array(
	'cat' => '-1',
	'posts_per_page' => 24
);


// ajax load more posts
if (!empty($_GET['xhr'])) {

	$recent_post_opts['offset'] = (int)$_GET['offset'];
	$posts_query = new WP_Query($recent_post_opts);
	$posts = array();

	if ($posts_query->have_posts()) {
		while ($posts_query->have_posts()) {
			$posts_query->the_post();
			grabExtraPostData($post);
			$posts[] = $post;
		}
	}

	$response = array(
		'success' => 1,
		'posts' => $posts
	);

	echo json_encode($response);
	die;
}

// load top 3 featured posts


$featured_posts = new WP_Query( $featured_post_opts );
if ($featured_posts->have_posts()) {
	while ($featured_posts->have_posts()) {
		$featured_posts->the_post();
		grabExtraPostData($post);
		
		$post_ids[] = $post->ID;
		$post_debug[] = $post;
	}
}

// load rest of posts

$posts_loaded = 0;
$post_ids = array();
$post_debug = array();

$recent_posts = new WP_Query( $recent_post_opts );
if ($recent_posts->have_posts()) {
	while ($recent_posts->have_posts()) {
		$recent_posts->the_post();
		grabExtraPostData($post);
		
		$post_ids[] = $post->ID;
		$post_debug[] = $post;
		$posts_loaded++;
	}
}


get_header(); ?>

	<!-- [index] -->
	<div id="primary" class="content-area homepage-content">
		<main id="main" class="site-main" role="main">

			<div class="featured-post-container">

				<?php if ( $featured_posts->have_posts() ) : 

					while ( $featured_posts->have_posts() ) : $featured_posts->the_post();

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
			<h4>Most Recent Posts</h4>
			<!-- <div class="recent-posts-container js-masonry" data-masonry-options='{"itemSelector": ".homepage-post"}'> -->
			<div class="recent-posts-container">

				<?php if ( $recent_posts->have_posts() ) : 

					while ( $recent_posts->have_posts() ) : $recent_posts->the_post();

						if (empty($post->featured_image)) {
							// continue;
						}

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

		</main>
	</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/3.1.8/imagesloaded.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.2/masonry.pkgd.min.js"></script>

<script type="text/x-underscore" id="tmpl-post">
<div class="homepage-post">
	<a href="<%=post.permalink%>">
		<img src="<%=post.featured_image%>" data-pradux-ignore="true">
	</a>
	<div class="bottom-text">
		<a href="<%=post.permalink%>" class="category"><%=post.category%></a>
		<a href="<%=post.permalink%>" class="title"><%=post.title%></a>
	</div>
</div>
</script>

<script>
var post_ids = <?=json_encode($post_ids)?>;
var post_debug = <?=json_encode($post_debug)?>;
var recent_post_opts = <?=json_encode($recent_post_opts)?>;
var num_posts_loaded = <?=$posts_loaded?>;
var post_template = _.template( $("#tmpl-post").html() );
</script>

<script>
var $newposts = [];
function load_more()
{
	$.ajax({
		url: document.location.toString(),
		data: {
			xhr: 1,
			offset: num_posts_loaded
		},
		dataType: "json",
		success: function(response) {
			console.log("got response: ", response);

			var posts = response.posts;
			// var $posts = [];
			
			$.each(posts, function(i, post) {

				var $post = $(post_template({
					post: {
						permalink: post.permalink,
						featured_image: post.featured_image,
						category: post.category,
						title: post.title
					}
				}));

				$newposts.push($post);

				// $(".recent-posts-container").append($post);
				// $(".recent-posts-container").append($post).masonry('appended', $post);
				// $(".recent-posts-container").masonry('appended', $post);

			});
			/* $(".recent-posts-container").append($posts).imagesLoaded(function() {
				$(".recent-posts-container").masonry('appended', $posts);
			}); */

			/* $posts.imagesLoaded(function() {
				$(".recent-posts-container").append($posts).masonry('appended', $posts);
			}); */
			
			// $(".recent-posts-container").masonry('appended', $posts);
			// $(".recent-posts-container").masonry('appended', $(".recent-posts-container .homepage-post"));


		}
	});
}
</script>

<script>
function enable_masonry()
{
	// data-masonry-options='{"itemSelector": ".homepage-post"}'
	
	window.masonry = $('.recent-posts-container').masonry({
		itemSelector: '.homepage-post'
	});

	/* var $grid = $('.recent-posts-container').masonry({
		percentPosition: true,
		columnWidth: '.grid-sizer',
		itemSelector: '.homepage-post.masonry'
	}); */

	// $grid.imagesLoaded().progress( function() {
	// 	$grid.masonry('layout');
	// });
}

// enable_masonry();
</script>



<?php get_footer(); ?>
