<?php
/**
 * Single Product Up-Sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/up-sells.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.6.0
 *
 * Aura Fashion – Upsells card grid
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $upsells ) ) {
	return;
}
?>

<section class="aura-related-products aura-upsells">
	<div class="aura-container">
		<div class="section-header">
			<h2><?php esc_html_e( 'You May Also Like', 'aura-fashion' ); ?></h2>
		</div>
		<div class="aura-related-grid">
			<?php
			$shown = 0;
			foreach ( $upsells as $upsell ) {
				if ( $shown >= 4 ) {
					break;
				}
				if ( ! is_object( $upsell ) || ! is_a( $upsell, 'WC_Product' ) ) {
					continue;
				}
				$post_object = get_post( $upsell->get_id() );
				if ( ! $post_object ) {
					continue;
				}
				setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				$GLOBALS['product'] = $upsell;
				wc_get_template_part( 'content', 'product' );
				$shown++;
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
