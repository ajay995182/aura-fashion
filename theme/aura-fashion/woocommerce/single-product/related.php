<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 *
 * Aura Fashion – Related Products card grid
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $related_products ) ) {
	return;
}
?>

<section class="aura-related-products">
	<div class="aura-container">
		<div class="section-header">
			<h2><?php esc_html_e( 'Related Products', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'You may also like these pieces.', 'aura-fashion' ); ?></p>
		</div>
		<div class="aura-related-grid">
			<?php
			$shown = 0;
			foreach ( $related_products as $related_product ) {
				if ( $shown >= 4 ) {
					break;
				}
				if ( ! is_object( $related_product ) || ! is_a( $related_product, 'WC_Product' ) ) {
					continue;
				}
				$post_object = get_post( $related_product->get_id() );
				if ( ! $post_object ) {
					continue;
				}
				setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				$GLOBALS['product'] = $related_product;
				wc_get_template_part( 'content', 'product' );
				$shown++;
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
