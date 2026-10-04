<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 *
 * Aura Fashion – Product card template
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<article <?php wc_product_class( 'product-card', $product ); ?>>
	<div class="product-card-image">
		<a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
			<?php
			if ( has_post_thumbnail() ) {
				echo get_the_post_thumbnail( $product->get_id(), 'aura-product' );
			} else {
				echo aura_placeholder_img( 'aura-product', 'aura-' . $product->get_id() ); // phpcs:ignore
			}
			?>
		</a>

		<?php
		/* Custom badges from meta */
		$badges = get_post_meta( $product->get_id(), '_aura_badges', true );
		if ( is_array( $badges ) && ! empty( $badges ) ) :
			echo '<div class="aura-badges">';
			foreach ( $badges as $b ) {
				$b = sanitize_key( $b );
				$labels = array(
					'new'        => __( 'New', 'aura-fashion' ),
					'bestseller' => __( 'Bestseller', 'aura-fashion' ),
					'limited'    => __( 'Limited', 'aura-fashion' ),
					'eco'        => __( 'Eco', 'aura-fashion' ),
				);
				if ( isset( $labels[ $b ] ) ) {
					echo '<span class="aura-badge aura-badge--' . esc_attr( $b ) . '">' . esc_html( $labels[ $b ] ) . '</span>';
				}
			}
			echo '</div>';
		endif;
		?>

		<?php if ( $product->is_on_sale() ) : ?>
			<span class="product-card-badge sale"><?php esc_html_e( 'Sale', 'aura-fashion' ); ?></span>
		<?php endif; ?>

		<?php
		$stock = $product->get_stock_quantity();
		if ( $product->managing_stock() && $stock !== null && $stock <= 10 && $stock > 0 ) :
			?>
			<span class="product-card-badge stock-low"><?php printf( esc_html__( 'Only %s left', 'aura-fashion' ), intval( $stock ) ); ?></span>
		<?php endif; ?>

		<div class="product-card-actions">
			<button type="button" class="product-action-btn quick-view-btn" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php esc_attr_e( 'Quick View', 'aura-fashion' ); ?>" title="<?php esc_attr_e( 'Quick View', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
			</button>
			<button type="button" class="product-action-btn wishlist-toggle" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php esc_attr_e( 'Add to Wishlist', 'aura-fashion' ); ?>" aria-pressed="false" title="<?php esc_attr_e( 'Wishlist', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			</button>
			<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
				<button type="button" class="product-action-btn ajax_add_to_cart" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-quantity="1" aria-label="<?php esc_attr_e( 'Add to Cart', 'aura-fashion' ); ?>" title="<?php esc_attr_e( 'Add to Cart', 'aura-fashion' ); ?>">
					<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<div class="product-card-body">
		<h3 class="product-card-title">
			<a href="<?php the_permalink(); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>
		<div class="product-card-price">
			<?php echo $product->get_price_html(); // phpcs:ignore ?>
		</div>
	</div>
</article>
