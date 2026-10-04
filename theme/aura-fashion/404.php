<?php
/**
 * Custom 404 – Fashion styled
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="aura-404">
	<div class="aura-container text-center">
		<div class="aura-404-code">404</div>
		<h1><?php esc_html_e( 'Page not found', 'aura-fashion' ); ?></h1>
		<p><?php esc_html_e( 'This look has left the collection. Let’s get you back to something beautiful.', 'aura-fashion' ); ?></p>
		<div class="aura-404-actions">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Back Home', 'aura-fashion' ); ?></a>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Shop Collection', 'aura-fashion' ); ?></a>
		</div>
		<form role="search" method="get" class="aura-404-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search products…', 'aura-fashion' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'aura-fashion' ); ?>">
			<input type="hidden" name="post_type" value="product">
			<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Search', 'aura-fashion' ); ?></button>
		</form>
	</div>
</section>

<?php get_footer(); ?>
