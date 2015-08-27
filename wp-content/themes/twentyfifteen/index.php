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
	$post->is_top_featured = get_post_meta($post->ID, 'my_top_featured_post_field', true);
	$post->is_middle_featured = get_post_meta($post->ID, 'my_middle_featured_post_field', true);
}

$featured_post_top_opts = array(
	'posts_per_page' => 3,
	// 'post__in' => array(1, 232, 247)
	'meta_key' => 'my_top_featured_post_field',
	'meta_value' => 1
);

$featured_post_middle_opts = array(
	'posts_per_page' => 3,
	// 'post__in' => array(1, 232, 247)
	'meta_key' => 'my_middle_featured_post_field',
	'meta_value' => 1
);

$recent_post_opts = array(
	'cat' => '-1',
	'posts_per_page' => 12
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


// load TOP featured posts
$featured_posts_top = new WP_Query( $featured_post_top_opts );
if ($featured_posts_top->have_posts()) {
	while ($featured_posts_top->have_posts()) {
		$featured_posts_top->the_post();
		grabExtraPostData($post);
		
		// $post_ids[] = $post->ID;
		// $post_debug[] = $post;
	}
}

$recent_posts = array();
$featured_posts_middle = array();

// load MIDDLE featured posts
$featured_posts_middle_query = new WP_Query( $featured_post_middle_opts );
if ($featured_posts_middle_query->have_posts()) {
	while ($featured_posts_middle_query->have_posts()) {
		$featured_posts_middle_query->the_post();
		grabExtraPostData($post);
		$featured_posts_middle[] = (object)$post;
		
	}
}

// load rest of posts
$recent_posts_query = new WP_Query( $recent_post_opts );
if ($recent_posts_query->have_posts()) {
	while ($recent_posts_query->have_posts()) {
		$recent_posts_query->the_post();
		grabExtraPostData($post);
		$recent_posts[] = (object)$post;
		
		// $post_ids[] = $post->ID;
		// $post_debug[] = $post;
		// $posts_loaded++;
	}
}

// jack featured_middle_posts into recent_posts
/* $recent_posts = 
	array_slice($recent_posts, 0, 2) +
	$featured_posts_middle[0] +
	array_slice($recent_posts, offset)*/

echo '<!-- *1* ';
echo print_r($recent_posts[0],1);
echo ' -->';


// $recent_posts[] = 
// 	$recent_posts[0] + $recent_posts[1] + 
// 	$featured_posts_middle[0] + 
// 	$recent_posts[2] + $recent_posts[3] + 

// 	$recent_posts[4] + $recent_posts[5] + 
// 	$featured_posts_middle[1] + 
// 	$recent_posts[6] + $recent_posts[7] + 

// 	$recent_posts[8] + $recent_posts[9] + 
// 	$featured_posts_middle[2] + 
// 	$recent_posts[10] + $recent_posts[11];

echo '<!--  *2* ';
echo print_r($recent_posts[0],1);
echo ' -->';

get_header(); ?>

	<!-- [index] -->
	<div id="primary" class="content-area homepage-content">
		<main id="main" class="site-main" role="main">

			<div class="featured-post-container">

				<?php if ( $featured_posts_top->have_posts() ) :

				$i=0; 

					while ( $featured_posts_top->have_posts() ) : $featured_posts_top->the_post();
						
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

			<div class="recent-posts-container">

				<?php if ( count($recent_posts) ) : 

					// while ( $recent_posts->have_posts() ) : $recent_posts->the_post();
					foreach ($recent_posts as $post_i => $post):

						if ($post_i%5 == 2) {
							// featured middle
							echo <<<HTML
<div class="homepage-post tall-post">
	<a href="{$post->permalink}">
		<img src="{$post->featured_image}" data-pradux-ignore="true">
	</a>
	<div class="bottom-text">
		<a href="{$post->permalink}" class="category">{$post->category}</a>
		<a href="{$post->permalink}" class="title">{$post->title}</a>
	</div>
</div>
HTML;
						}

						else
						{
							if ($post_i%5 == 0 || $post_i%5 == 3) {
								echo '<div class="grid-wrap">';
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
							if ($post_i%5 == 1 || $post_i%5 == 4) {
								echo '</div>';
							}

						}


					endforeach;
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
var posts = <?=json_encode($recent_posts)?>;
var recent_post_opts = <?=json_encode($recent_post_opts)?>;
// var num_posts_loaded = <?=$posts_loaded?>;
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

// var load_more_debounced = _.debounce(load_more, 500);
// $(window).scroll(load_more_debounced);

</script>


<?php get_footer(); ?>
