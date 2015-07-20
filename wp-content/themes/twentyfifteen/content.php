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

$post_cats = get_the_category(); // can be an array
if (!empty($post_cats)) {
	$cat_ids = array();
	foreach ($post_cats as $post_cat) {
		$cat_ids[] = $post_cat->cat_ID;
	}
	$cat_ids_csv = implode(',', $cat_ids);

}

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

<!-- [content] -->

<div class="blog-navigation">
	<a class="back-btn" href="/news">&larr; Back</a>
</div>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		// Post thumbnail.
		twentyfifteen_post_thumbnail();
	?>

	<header class="entry-header">
		<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
				echo '<div class="entry-meta">';
				echo '	<span class="category">';
									the_category(', ');
				echo '  </span>';
				echo ' | ';
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

	<? if ($num_related_posts): ?>
	<div class="related-posts">
		<h4>Related Posts</h4>
		
		<div class="related-posts-wrap">
			
			<?php foreach ($related_posts as $related_post): ?>
			<a href="#" class="related-post" style="background-image:url(http://s3.amazonaws.com/news-media.pradux.com/wp-content/uploads/2015/04/post_1.jpg)">
				<div class="bottom-text">
					<div class="category">
						Trending
					</div>
					<div class="title">
						<?=htmlencode($related_post->post_title)?>
					</div>
				</div>
			</a>
			<?php endif; ?>

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
	<?php endif; ?>

	<?php if (!empty($related_posts)): ?>
	var related_posts = <?=json_encode($related_posts)?>;
	<?php endif; ?>

	var postlist = <?=json_encode($postlist)?>;

	var prev_post_id = <?=json_encode($prevID)?>;
	var next_post_id = <?=json_encode($nextID)?>;

	</script>

</article>
