<?php
/**
 * Design & Page Improvements – v1.5.0
 * – Enhanced single product (sticky ATC, gallery, tabs)
 * – Lookbook / Shop the Look
 * – Blog / Style Journal
 * – Mega Menu
 * – Dark / Light mode toggle
 * – Product card animations
 * – Custom 404
 * – Skeleton loaders
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   LOOKBOOK – Custom Post Type + shortcode helpers
   ========================================================================== */

function aura_register_lookbook() {
	register_post_type( 'aura_lookbook', array(
		'labels' => array(
			'name'          => __( 'Lookbooks', 'aura-fashion' ),
			'singular_name' => __( 'Lookbook', 'aura-fashion' ),
			'add_new_item'  => __( 'Add New Lookbook', 'aura-fashion' ),
			'edit_item'     => __( 'Edit Lookbook', 'aura-fashion' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'lookbook' ),
		'menu_icon'    => 'dashicons-camera',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'aura_register_lookbook' );

/**
 * Meta box: linked product IDs for “Shop the Look”
 */
function aura_lookbook_meta_box() {
	add_meta_box( 'aura_lookbook_products', __( 'Shop the Look – Product IDs', 'aura-fashion' ), 'aura_lookbook_meta_box_html', 'aura_lookbook', 'side' );
}
add_action( 'add_meta_boxes', 'aura_lookbook_meta_box' );

function aura_lookbook_meta_box_html( $post ) {
	$ids = get_post_meta( $post->ID, '_aura_lookbook_products', true );
	wp_nonce_field( 'aura_lookbook_save', 'aura_lookbook_nonce' );
	echo '<p><label>' . esc_html__( 'Comma-separated product IDs', 'aura-fashion' ) . '</label></p>';
	echo '<input type="text" name="aura_lookbook_products" value="' . esc_attr( $ids ) . '" style="width:100%" placeholder="12, 45, 78">';
	echo '<p class="description">' . esc_html__( 'Products shown under this look.', 'aura-fashion' ) . '</p>';
}

function aura_lookbook_save_meta( $post_id ) {
	if ( ! isset( $_POST['aura_lookbook_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aura_lookbook_nonce'] ) ), 'aura_lookbook_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( isset( $_POST['aura_lookbook_products'] ) ) {
		update_post_meta( $post_id, '_aura_lookbook_products', sanitize_text_field( wp_unslash( $_POST['aura_lookbook_products'] ) ) );
	}
}
add_action( 'save_post_aura_lookbook', 'aura_lookbook_save_meta' );

/* ==========================================================================
   MEGA MENU – body class + walker helper data
   ========================================================================== */

function aura_mega_menu_body_class( $classes ) {
	if ( get_theme_mod( 'aura_mega_menu', true ) ) {
		$classes[] = 'aura-mega-menu-enabled';
	}
	return $classes;
}
add_filter( 'body_class', 'aura_mega_menu_body_class' );

/**
 * Add product category children as data for mega menu (used by CSS/JS).
 */
function aura_nav_menu_mega_attrs( $atts, $item, $args ) {
	if ( empty( $args->theme_location ) || $args->theme_location !== 'primary' ) {
		return $atts;
	}
	if ( $item->object === 'product_cat' ) {
		$term_id = intval( $item->object_id );
		$children = get_terms( array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term_id,
			'hide_empty' => false,
		) );
		if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
			$atts['data-mega'] = '1';
			$atts['class'] = ( isset( $atts['class'] ) ? $atts['class'] . ' ' : '' ) . 'has-mega';
		}
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'aura_nav_menu_mega_attrs', 10, 3 );

function aura_mega_menu_dropdown_html() {
	if ( ! get_theme_mod( 'aura_mega_menu', true ) ) {
		return;
	}
	$cats = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'number'     => 8,
	) );
	if ( is_wp_error( $cats ) || empty( $cats ) ) {
		return;
	}
	?>
	<div id="aura-mega-panel" class="aura-mega-panel" hidden>
		<div class="aura-container aura-mega-inner">
			<?php foreach ( $cats as $cat ) :
				$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => false ) );
				$thumb_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
				?>
				<div class="mega-col">
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="mega-col-title">
						<?php if ( $thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $thumb_id, 'thumbnail', false, array( 'class' => 'mega-thumb' ) ); ?>
						<?php endif; ?>
						<?php echo esc_html( $cat->name ); ?>
					</a>
					<?php if ( ! is_wp_error( $children ) && $children ) : ?>
						<ul>
							<?php foreach ( array_slice( $children, 0, 6 ) as $child ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'aura_mega_menu_dropdown_html', 4 );

/* ==========================================================================
   DARK / LIGHT MODE
   (Theme is dark by default; toggle switches to a refined light palette)
   ========================================================================== */

function aura_theme_mode_toggle_markup() {
	?>
	<button type="button" class="header-icon aura-mode-toggle" id="aura-mode-toggle" aria-label="<?php esc_attr_e( 'Toggle light/dark mode', 'aura-fashion' ); ?>" title="<?php esc_attr_e( 'Toggle theme', 'aura-fashion' ); ?>">
		<svg class="icon-moon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
		<svg class="icon-sun" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
	</button>
	<?php
}

/* Hook into header actions via filter-friendly function called from header.php */

function aura_mode_body_class( $classes ) {
	// Class set by JS from localStorage; server default is dark
	return $classes;
}
add_filter( 'body_class', 'aura_mode_body_class' );

/* ==========================================================================
   SKELETON LOADER helper class for product grids
   ========================================================================== */

function aura_skeleton_cards( $count = 4 ) {
	ob_start();
	echo '<div class="aura-skeleton-grid products-grid">';
	for ( $i = 0; $i < $count; $i++ ) {
		echo '<div class="skeleton-card"><div class="skeleton-img skeleton-pulse"></div><div class="skeleton-line skeleton-pulse"></div><div class="skeleton-line short skeleton-pulse"></div></div>';
	}
	echo '</div>';
	return ob_get_clean();
}

/* ==========================================================================
   BLOG – Style Journal enhancements
   ========================================================================== */

function aura_blog_excerpt_length( $length ) {
	return 22;
}
add_filter( 'excerpt_length', 'aura_blog_excerpt_length' );

function aura_blog_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'aura_blog_excerpt_more' );
