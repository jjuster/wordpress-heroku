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
	'posts_per_page' => 15
);


// ajax load more posts
if (!empty($_GET['xhr'])) {

	$recent_post_opts['offset'] = (int)$_GET['offset'];
	$recent_post_opts['posts_per_page'] = 10;
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

				$i=0; 

					while ( $featured_posts->have_posts() ) : $featured_posts->the_post();
						
						// top featured posts
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

			<div class="recent-posts-container grid">

				<?php if ( $recent_posts->have_posts() ) : 
					$i = 0;
					while ( $recent_posts->have_posts() ) : $recent_posts->the_post();

						$tall_class = $i%5 == 2 ? 'tall-post' : '';

						if ($i%5 == 0 || $i%5 == 3) {
							echo '<div class="grid-wrap">';
						}

						echo <<<HTML

<div class="homepage-post {$tall_class}">
	<a href="{$post->permalink}">
		<img src="{$post->featured_image}" data-pradux-ignore="true">
	</a>
	<div class="bottom-text">
		<a href="{$post->permalink}" class="category">{$post->category}</a>
		<a href="{$post->permalink}" class="title">{$post->title}</a>
	</div>
</div>
HTML;
						if ($i%1 == 1 || $i%5 == 4) {
							echo '</div>';
						}

						$i++;
					endwhile;
				endif; ?>

			</div>

		</main>
	</div>


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

var $newposts = [];
var load_more_active = true;
var currently_loading_more = false;

function load_more()
{
	if (!load_more_active || currently_loading_more) {
		console.log("ignore loadmore");
		return;
	}

	if ($(window).scrollTop() / $("body").height() < 0.75) {
		return;
	}

	currently_loading_more = true;

	$.ajax({
		url: document.location.toString(),
		data: {
			xhr: 1,
			offset: num_posts_loaded
		},
		dataType: "json",
		success: function(response) {

			var posts = response.posts;
			
			if (posts.length == 0) {
				load_more_active = false;
				currently_loading_more = false;
				return;
			}

			$newposts = [];
			
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
			});

			$(".recent-posts-container").append($newposts);

			

			num_posts_loaded += posts.length;

		}
	});
}

var load_more_debounced = _.debounce(load_more, 500);
$(window).scroll(load_more_debounced);

</script>





<?php get_footer(); ?>
