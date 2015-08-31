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
	$post->is_regular = get_post_meta($post->ID, 'my_regular_post_field', true);
	unset($post->post_content);
}

$featured_post_top_opts = array(
	'posts_per_page' => 3,
	'meta_key' => 'my_top_featured_post_field',
	'meta_value' => 1
);

$featured_post_middle_opts = array(
	'posts_per_page' => 2,
	'meta_key' => 'my_middle_featured_post_field',
	'meta_value' => 1
);

$recent_post_opts = array(
	'cat' => '-1',
	'posts_per_page' => 8,
	'meta_key' => 'my_regular_post_field',
	'meta_value' => 1
);


// ajax load more posts
if (!empty($_GET['xhr'])) {

	// offsets for regular/featured
	$featured_post_middle_opts['offset'] = (int)$_GET['offset'] * 0.2;
	$featured_post_middle_opts['posts_per_page'] = 1;

	$recent_post_opts['offset'] = (int)$_GET['offset'] * 0.8;
	$recent_post_opts['posts_per_page'] = 4; 
	
	// load middle col featured posts
	$featured_posts_middle = get_posts($featured_post_middle_opts);
	foreach ($featured_posts_middle as &$post) {
		grabExtraPostData($post);
	}

	// load rest of posts
	$recent_posts = get_posts($recent_post_opts);
	foreach ($recent_posts as &$post) {
		grabExtraPostData($post);
	}

	$recent_posts_combined = array();
	if (!empty($recent_posts[0])) { $recent_posts_combined[] = $recent_posts[0]; }
	if (!empty($recent_posts[1])) { $recent_posts_combined[] = $recent_posts[1]; }
	if (!empty($featured_posts_middle[0])) { $recent_posts_combined[] = $featured_posts_middle[0]; }
	if (!empty($recent_posts[2])) { $recent_posts_combined[] = $recent_posts[2]; }
	if (!empty($recent_posts[3])) { $recent_posts_combined[] = $recent_posts[3]; }

	$response = array(
		'success' => 1,
		'posts' => $recent_posts_combined
	);

	echo json_encode($response);
	die;
}


// load TOP featured posts
$featured_posts_top = get_posts($featured_post_top_opts);
foreach ($featured_posts_top as &$post) {
	grabExtraPostData($post);
}

// load middle col featured posts
$featured_posts_middle = get_posts($featured_post_middle_opts);
foreach ($featured_posts_middle as &$post) {
	grabExtraPostData($post);
}

// load rest of posts
$recent_posts = get_posts($recent_post_opts);
foreach ($recent_posts as &$post) {
	grabExtraPostData($post);
}

$recent_posts_combined = array();
array_push($recent_posts_combined,
	$recent_posts[0], $recent_posts[1],
	$featured_posts_middle[0],
	$recent_posts[2], $recent_posts[3],

	$recent_posts[4], $recent_posts[5],
	$featured_posts_middle[1],
	$recent_posts[6], $recent_posts[7]

	// $recent_posts[8], $recent_posts[9],
	// $featured_posts_middle[2],
	// $recent_posts[10], $recent_posts[11]
);

get_header(); ?>

	<!-- [index] -->
	<div id="primary" class="content-area homepage-content">
		<main id="main" class="site-main" role="main">

			<div class="featured-post-container">

				<?php if ( count($featured_posts_top) ) :

					foreach ($featured_posts_top as $post):
						
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
					endforeach; 
				endif; ?>
			
			</div>

			<div class="separator-wrap">
				<hr>
			</div>

			<!-- Recent Posts -->
			<h4>Most Recent Posts</h4>

			<div class="recent-posts-container">

				<?php if ( count($recent_posts_combined) ) : 

					foreach ($recent_posts_combined as $post_i => $post):

						if ($post_i%5 == 2) {
							// featured middle
							echo <<<HTML
<div class="homepage-post tall-post">
	<a href="{$post->permalink}">
		<img src="https://s3.amazonaws.com/cdn.pradux.com/uploads/1441053815_blank-768x1152.png" data-pradux-ignore="true" style="background-image:url({$post->featured_image})">
	</a>
	<div class="bottom-text">
		<div class="category">{$post->category}</div>
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
		<img src="https://s3.amazonaws.com/cdn.pradux.com/uploads/1441048219_blank-768x576.png" data-pradux-ignore="true" style="background-image:url({$post->featured_image})">
	</a>
	<div class="bottom-text">
		<div class="category">{$post->category}</div>
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
		<img src="https://s3.amazonaws.com/cdn.pradux.com/uploads/1441048219_blank-768x576.png" data-pradux-ignore="true" style="background-image:url(<%=post.featured_image%>)">
	</a>
	<div class="bottom-text">
		<div class="category"><%=post.category%></div>
		<a href="<%=post.permalink%>" class="title"><%=post.title%></a>
	</div>
</div>
</script>

<script type="text/x-underscore" id="tmpl-post-tall">
<div class="homepage-post tall-post">
	<a href="<%=post.permalink%>">
		<img src="https://s3.amazonaws.com/cdn.pradux.com/uploads/1441053815_blank-768x1152.png" data-pradux-ignore="true" style="background-image:url(<%=post.featured_image%>)">
	</a>
	<div class="bottom-text">
		<div class="category"><%=post.category%></div>
		<a href="<%=post.permalink%>" class="title"><%=post.title%></a>
	</div>
</div>
</script>

<script>
var posts = <?=json_encode($recent_posts_combined)?>;
var post_template = _.template( $("#tmpl-post").html() );
var post_tall_template = _.template( $("#tmpl-post-tall").html() );

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
			offset: posts.length
		},
		dataType: "json",
		success: function(response) {

			var newposts = response.posts;
			var append_html = '';
			
			if (newposts.length == 0) {
				load_more_active = false;
				currently_loading_more = false;
				return;
			}

			// place first/second post
			if (newposts[0]) {
				append_html += '<div class="grid-wrap">';
				append_html += post_template({post: newposts[0]});
				posts.push(newposts[0]);
				
				if (newposts[1]) {
					append_html += post_template({post: newposts[1]});
					posts.push(newposts[1]);
				}

				append_html += '</div>'; // </.grid-wrap>

				if (newposts[2])
				{
					append_html += post_tall_template({post: newposts[2]});
					posts.push(newposts[2]);

					if (newposts[3])
					{
						append_html += '<div class="grid-wrap">';
						append_html += post_template({post: newposts[3]});
						posts.push(newposts[3]);

						if (newposts[4]) {
							append_html += post_template({post: newposts[4]});
							posts.push(newposts[4]);
						}

						append_html += '</div>'; // </.grid-wrap>
					}
				}

				$(".recent-posts-container").append( $(append_html) );
			}

			if (newposts.length < 5) {
				load_more_active = false;
			}
			
			currently_loading_more = false;
		}
	});
}

var load_more_debounced = _.debounce(load_more, 500);
$(window).scroll(load_more_debounced);

</script>


<?php get_footer(); ?>
