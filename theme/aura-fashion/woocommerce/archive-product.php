<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 *
 * Aura Fashion – Shop / Category archive with AJAX filters
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<div class="aura-container" style="padding:40px 0 80px;">
	<header class="page-hero" style="padding:40px 0 30px;margin-bottom:32px;border-radius:var(--aura-radius-lg);">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>
		<?php do_action( 'woocommerce_archive_description' ); ?>
	</header>

	<div class="shop-layout">
		<!-- Sidebar Filters -->
		<aside class="shop-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'aura-fashion' ); ?>">
			<div class="filter-group">
				<h4><?php esc_html_e( 'Categories', 'aura-fashion' ); ?></h4>
				<?php
				$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ) );
				if ( ! is_wp_error( $cats ) ) {
					foreach ( $cats as $cat ) {
						printf(
							'<label class="filter-option"><input type="checkbox" class="filter-category" value="%s"> %s <span style="color:var(--aura-gray);font-size:0.8rem;">(%d)</span></label>',
							esc_attr( $cat->slug ),
							esc_html( $cat->name ),
							intval( $cat->count )
						);
					}
				}
				?>
			</div>

			<div class="filter-group">
				<h4><?php esc_html_e( 'Price', 'aura-fashion' ); ?></h4>
				<div class="price-inputs">
					<input type="number" id="min-price" placeholder="<?php esc_attr_e( 'Min', 'aura-fashion' ); ?>" min="0" step="1">
					<input type="number" id="max-price" placeholder="<?php esc_attr_e( 'Max', 'aura-fashion' ); ?>" min="0" step="1">
				</div>
			</div>

			<?php
			// Attribute filters (Color, Size, Material…)
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			foreach ( $attribute_taxonomies as $tax ) {
				$taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );
				$terms    = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					continue;
				}
				?>
				<div class="filter-group">
					<h4><?php echo esc_html( $tax->attribute_label ); ?></h4>
					<?php
					foreach ( $terms as $term ) {
						printf(
							'<label class="filter-option"><input type="checkbox" class="filter-attr" data-taxonomy="%s" value="%s"> %s</label>',
							esc_attr( $taxonomy ),
							esc_attr( $term->slug ),
							esc_html( $term->name )
						);
					}
					?>
				</div>
				<?php
			}
			?>

			<div class="filter-group">
				<label class="filter-option">
					<input type="checkbox" id="filter-on-sale" value="1">
					<?php esc_html_e( 'On Sale', 'aura-fashion' ); ?>
				</label>
			</div>

			<button type="button" class="btn btn-outline btn-sm filter-reset btn-block"><?php esc_html_e( 'Reset Filters', 'aura-fashion' ); ?></button>
		</aside>

		<!-- Products -->
		<div class="shop-results">
			<div class="shop-results-header">
				<span class="shop-results-count">
					<?php
					global $wp_query;
					printf( esc_html__( '%d products', 'aura-fashion' ), $wp_query->found_posts );
					?>
				</span>
				<form class="woocommerce-ordering" method="get">
					<select id="aura-orderby" name="orderby" class="orderby" aria-label="<?php esc_attr_e( 'Sort by', 'aura-fashion' ); ?>">
						<option value="menu_order"><?php esc_html_e( 'Default sorting', 'aura-fashion' ); ?></option>
						<option value="popularity"><?php esc_html_e( 'Popularity', 'aura-fashion' ); ?></option>
						<option value="rating"><?php esc_html_e( 'Average rating', 'aura-fashion' ); ?></option>
						<option value="date"><?php esc_html_e( 'Latest', 'aura-fashion' ); ?></option>
						<option value="price"><?php esc_html_e( 'Price: low to high', 'aura-fashion' ); ?></option>
						<option value="price-desc"><?php esc_html_e( 'Price: high to low', 'aura-fashion' ); ?></option>
					</select>
				</form>
			</div>

			<div class="products-loading">
				<div class="spinner"></div>
				<p><?php esc_html_e( 'Loading…', 'aura-fashion' ); ?></p>
			</div>

			<div class="products-grid">
				<?php
				if ( woocommerce_product_loop() ) {
					while ( have_posts() ) {
						the_post();
						wc_get_template_part( 'content', 'product' );
					}
				} else {
					echo '<p class="text-center" style="grid-column:1/-1;padding:40px;">' . esc_html__( 'No products found.', 'aura-fashion' ) . '</p>';
				}
				?>
			</div>

			<nav class="aura-pagination" style="display:flex;gap:8px;justify-content:center;margin-top:40px;" aria-label="<?php esc_attr_e( 'Pagination', 'aura-fashion' ); ?>">
				<?php
				echo paginate_links( array(
					'total'   => $wp_query->max_num_pages,
					'current' => max( 1, get_query_var( 'paged' ) ),
					'type'    => 'plain',
					'prev_text' => '←',
					'next_text' => '→',
				) );
				?>
			</nav>
		</div>
	</div>
</div>

<?php
get_footer( 'shop' );
