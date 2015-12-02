<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package kamome-note
 */

if ( ! function_exists( 'kamome_note_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function kamome_note_posted_on( $post ) {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( $post->post_date !== $post->post_modified ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c', $post->ID ) ),
		esc_html( get_the_date( get_option('date_format'), $post->ID ) ),
		esc_attr( get_the_modified_date( 'c', $post->ID ) ),
		esc_html( get_the_modified_date( get_option('date_format'), $post->ID ) )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'kamome-note' ),
		'<a href="' . esc_url( get_permalink( $post->ID ) ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'kamome-note' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) ) ) . '">' . esc_html( get_the_author_meta( 'user_nicename', $post->post_author ) ) . '</a></span>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

}
endif;


if ( ! function_exists( 'kamome_note_thumbnail' ) ) :
/**
 * Prints post thumbnail or noimage if not exist.
 */
function kamome_note_post_thumbnail( $post ) {
	if ( has_post_thumbnail( $post->ID ) ) {
		echo get_the_post_thumbnail( $post->ID );
	} else {
		echo '<img src="' . get_template_directory_uri() . '/img/noimage.png' . '" />';
	}
}
endif;


if ( ! function_exists( 'kamome_note_tag_and_category' ) ) :
/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function kamome_note_tag_and_category( $post ) {
	// Hide category and tag text for pages.
	if ( 'post' === $post->post_type ) {
		$taxonomies = array( 'category', 'post_tag' );
		foreach ($taxonomies as $taxonomy) {
			$terms = wp_get_post_terms( $post->ID, $taxonomy );
			if ( empty( $terms ) ) {
				continue;
			}
			echo '<h3 class="taxonomy-title">' . get_taxonomy( $taxonomy )->label . '</h3>';
			echo "<ul class=\"taxonomy-list ${taxonomy}\">";
			foreach ( $terms as $term ) {
				echo '<li class="taxonomy-list-item"><a href="' . get_term_link( $term, $taxonomy ) . '">';
				echo esc_html( $term->name );
				echo '</a></li>';
			}
			echo '</ul>';
		}
	}

	if ( ! is_single( $post ) && ! post_password_required( $post ) && ( comments_open( $post->ID ) || get_comments_number( $post->ID ) ) ) {
		$comments_num = ( int )get_comments_number( $post->ID );
		echo '<p class="comments-link"><a href="' . get_permalink( $post->ID ) . '#comments">';
		if ( $comments_num === 0 ) {
			echo esc_html__( 'Leave a comment', 'kamome-note' );
		} elseif ( $comments_num === 1 ) {
			echo esc_html__( '1 Comment', 'kamome-note' );
		} else {
			echo sprintf( esc_html__( '%d Comments', 'kamome-note' ), $comments_num );
		}
		echo '</a></p>';
	}

	edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			esc_html__( 'Edit %s', 'kamome-note' ),
			'<span class="screen-reader-text">"' . $post->title . '"</span>'
		),
		'<span class="edit-link">',
		'</span>',
		$post->ID
	);
}
endif;





function kamome_note_load_more_navigation( $stickies ) {

	$args = kamome_note_ajax_acceptable_queries();//defined in functions.php
	$query = array();
	//filter the query
	foreach ( $args as $arg ) {
		$var = get_query_var( $arg );
		if ( $var ) {
			$query[$arg] = get_query_var( $arg );
		}
	}
	if (! isset( $query['paged'] ) ) {
		$query['paged'] = 1;
	}

	global $wp_query;

	printf( '<p id="end-of-articles" data-query="%s">',esc_attr( json_encode ( $query ) ) );
	printf( '<input type="hidden" id="ids_of_stickies" value="%s">', json_encode( $stickies ) );
	printf( '<input type="hidden" id="published_posts" value="%s">', $wp_query->found_posts );
	wp_nonce_field( KAMOME_NOTE_AJAX_LOAD_MORE_ACTION,'ajax-nonce' ,false ,true );
	printf('<a id="loadmore-button">%s</a>', esc_html__( 'LOAD MORE', 'kamome-note' ) );
	echo '</p>';
}


function kamome_note_abbr_post( $post ) {
	$thumbnail_class = has_post_thumbnail( $post->ID ) ? 'has-thumb' : 'no-thumb';
	// ?>
	<article id="post-<?php echo $post->ID; ?>" <?php post_class( 'post-grid_wrapper ' . $thumbnail_class, $post->ID ); ?>>
		<header class="entry-header">
			<?php echo sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink( $post->ID ) ) ) . esc_html( $post->post_title ) . '</a></h2>'; ?>
			<?php if ( 'post' === $post->post_type ) : ?>
			<div class="entry-meta">
				<?php kamome_note_posted_on( $post ); ?>
				<?php kamome_note_tag_and_category( $post ); ?>
			</div><!-- .entry-meta -->
			<?php endif; ?>
		</header><!-- .entry-header -->
		<?php kamome_note_post_thumbnail( $post ); ?>
	</article><!-- #post-## -->
	<?php
}
