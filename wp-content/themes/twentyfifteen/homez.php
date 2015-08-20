<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
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

$post_cats = get_the_category(); // can be an array
if (!empty($post_cats)) {
	$cat_ids = array();
	foreach ($post_cats as $post_cat) {
		$cat_ids[] = $post_cat->cat_ID;
	}
	$cat_ids_csv = implode(',', $cat_ids);
}

$post_tags = array();
foreach (get_the_tags() as $post_tag) {
	$post_tags[] = $post_tag;
}
$num_post_tags = count($post_tags);


/* $opts = array(
	'posts_per_page'   => 3,
	// 'category'         => '',	// can be comma separated list of cat ids
	// 'category_name'    => '',	// string, name of category
	'orderby'          => 'rand',
	// 'post_type'        => 'post',
	'post_status'      => 'publish',
	// 'suppress_filters' => true 
); */
// $related_posts = get_posts( $opts ); 
$related_posts = get_posts( 'posts_per_page=3&post_status=publish' );
$num_related_posts = count($related_posts);
if ($num_related_posts) {
	for($i=0;$i<$num_related_posts;$i++) {
		$related_posts[$i]->permalink = get_permalink($related_posts[$i]->ID);
		$related_posts[$i]->category = strip_tags( get_the_category_list('/', '', $related_posts[$i]->ID) );
		$related_posts[$i]->featured_image = extract_img_src( get_the_post_thumbnail($related_posts[$i]->ID) );
	}
}
// orderby=rand || not working


$postlist = get_posts( 'sort_column=menu_order&sort_order=asc' );
/* $posts = array();
foreach ( $postlist as $post ) {
	$posts[] += $post->ID;
}

$current = array_search( get_the_ID(), $posts );
$prevID = $posts[$current-1];
$nextID = $posts[$current+1];
*/

?>

<!-- [home] -->

<div class="blog-navigation">
	<a class="back-btn" href="/news"><div class="arrow-left"></div> Back</a>
</div>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		// Post thumbnail.
		// twentyfifteen_post_thumbnail();
	?>

	<header class="entry-header">

		<div class="top-featured-image">
			<?php if (class_exists('MultiPostThumbnails')) :
				MultiPostThumbnails::the_post_thumbnail(
					get_post_type(),
					'secondary-image'
				);
			endif; ?>
		</div>
		
		<?php
			if ( is_single() ) :
				
echo <<<HTML
<div class="ssk-wrapper">
	<div class="ssk-sticky ssk-left">
		<a href="" class="ssk ssk-facebook"></a>
		<a href="" class="ssk ssk-twitter"></a>
		<a href="" class="ssk ssk-pinterest"></a>
		<a href="" class="ssk ssk-tumblr"></a>
		<a href="" class="ssk ssk-comment">
			<span class="fa fa-comment-o"></span>
		</a>
	</div>
</div>
HTML;
				the_title( '<h1 class="entry-title">', '</h1>' );

				echo '<div class="entry-meta">';
				echo '	<span class="category">';
									the_category(', ');
				echo '  </span>';
				echo '	<span class="date-posted">';
									the_date();
				echo '	</span>';
				echo '</div>';

			else :
				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			endif;
		?>
	</header>

	<div class="entry-content">
		<?php
			/* translators: %s: Name of current post */
			the_content( sprintf(
				__( 'Continue reading %s', 'twentyfifteen' ),
				the_title( '<span class="screen-reader-text">', '</span>', false )
			) );

			/* wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfifteen' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) ); */
		?>
	</div>

	<?php
		// Author bio.
		if ( is_single() && get_the_author_meta( 'description' ) ) :
			get_template_part( 'author-bio' );
		endif;
	?>

	<?php if ($num_post_tags > 0): ?>
	<div class="post-tags-container">
		<div class="post-tags">
			<span class="tags-label">In This Story: </span>
			<?php foreach ($post_tags as $i => $post_tag): ?>
				<span class="post-tag">
					<a href="<?=get_tag_link($post_tag->term_id)?>">
						<?=htmlencode($post_tag->name)?>
					</a><?=$i+1 < $num_post_tags ? ', ' : ''?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="facebook-comments-container">
		<div class="facebook-comments">
		<div class="fb-comments" data-href="<?=get_permalink()?>" data-width="100%" data-numposts="5"></div>
		</div>
	</div>

	<? if ($num_related_posts): ?>
	<div class="related-posts-container">
		<div class="related-posts">
			<h4>Related Stories</h4>

			<div class="related-posts-wrap">
				
				<?php foreach ($related_posts as $related_post): ?>
				<a href="<?=$related_post->permalink?>" class="related-post">
					<span style="background-image:url(<?=$related_post->featured_image?>)">
					</span>
					<div class="bottom-text">
						<div class="category">
							<?=htmlencode($related_post->category)?>
						</div>
						<div class="title">
							<?=htmlencode($related_post->post_title)?>
						</div>
					</div>
				</a>
				<?php endforeach; ?>

			</div>
		</div>
	</div>
	<?php endif; ?>

	<footer class="entry-footer">
		<?php twentyfifteen_entry_meta(); ?>
		<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer>

	<script>
	<?php if (!empty($post)): ?>
	var post = <?=json_encode($post)?>;
	var post_category = <?=json_encode(get_the_category())?>;
	var post_tags = <?=json_encode($post_tags)?>;
	<?php endif; ?>

	<?php if (!empty($related_posts)): ?>
	var related_posts = <?=json_encode($related_posts)?>;
	<?php endif; ?>

	var postlist = <?=json_encode($postlist)?>;

	var prev_post_id = <?=json_encode($prevID)?>;
	var next_post_id = <?=json_encode($nextID)?>;

	</script>

	<script>

	var animate_duration = 200;

	$(function() {
		var $window = $(window),
				$ssk = $(".ssk-sticky"),
				ssk_y = $(".entry-title").offset().top,
				top_padding = 60;

		$window.scroll(function() {
			if ($window.scrollTop() > ssk_y - 60) {
				$ssk.stop().animate({
					marginTop: $window.scrollTop() - ssk_y + top_padding
				}, animate_duration);
			}
			else
			{
				$ssk.stop().animate({
					marginTop: 0
				}, animate_duration);
			}
		});

	});

	$(".ssk-comment").on("click", function(e) {
		e.preventDefault();
		$('html,body').animate({
			scrollTop: $(".facebook-comments-container").offset().top
		});
	});
	</script>

</article>
