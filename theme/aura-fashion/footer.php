</main><!-- #main -->

<footer class="site-footer" role="contentinfo">
	<div class="aura-container">
		<div class="footer-grid">
			<div class="footer-brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title">
						<?php echo esc_html( get_theme_mod( 'aura_store_name', get_bloginfo( 'name' ) ) ); ?>
					</a>
				<?php endif; ?>
				<p><?php bloginfo( 'description' ); ?></p>
			</div>

			<div class="footer-col">
				<h4><?php esc_html_e( 'Shop', 'aura-fashion' ); ?></h4>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All Products', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/women/' ) ); ?>"><?php esc_html_e( 'Women', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/men/' ) ); ?>"><?php esc_html_e( 'Men', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/accessories/' ) ); ?>"><?php esc_html_e( 'Accessories', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/sale/' ) ); ?>"><?php esc_html_e( 'Sale', 'aura-fashion' ); ?></a>
			</div>

			<div class="footer-col">
				<h4><?php esc_html_e( 'Help', 'aura-fashion' ); ?></h4>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQ', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Us', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My Account', 'aura-fashion' ); ?></a>
			</div>

			<div class="footer-col">
				<h4><?php esc_html_e( 'Connect', 'aura-fashion' ); ?></h4>
				<a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>"><?php esc_html_e( 'Wishlist', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'aura-fashion' ); ?></a>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'aura-fashion' ); ?></a>
			</div>
		</div>

		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'aura_store_name', get_bloginfo( 'name' ) ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'aura-fashion' ); ?></span>
			<span><?php esc_html_e( 'Crafted with care.', 'aura-fashion' ); ?></span>
		</div>
	</div>
</footer>

<?php
/* Mobile bottom navigation bar */
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$wish_url    = home_url( '/wishlist/' );
$cart_count  = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?>
<nav class="aura-mobile-bottom-bar" aria-label="<?php esc_attr_e( 'Mobile bottom navigation', 'aura-fashion' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="aura-mbb-item <?php echo is_front_page() ? 'is-active' : ''; ?>">
		<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>
		<span><?php esc_html_e( 'Home', 'aura-fashion' ); ?></span>
	</a>
	<a href="<?php echo esc_url( $shop_url ); ?>" class="aura-mbb-item <?php echo ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product() ) ) ? 'is-active' : ''; ?>">
		<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16l-1.5 12.5a1 1 0 0 1-1 .9H6.5a1 1 0 0 1-1-.9L4 7z"/><path d="M8 7V5a4 4 0 0 1 8 0v2"/></svg>
		<span><?php esc_html_e( 'Shop', 'aura-fashion' ); ?></span>
	</a>
	<a href="<?php echo esc_url( $wish_url ); ?>" class="aura-mbb-item">
		<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
		<span><?php esc_html_e( 'Wishlist', 'aura-fashion' ); ?></span>
	</a>
	<a href="<?php echo esc_url( $cart_url ); ?>" class="aura-mbb-item aura-mbb-cart <?php echo ( function_exists( 'is_cart' ) && is_cart() ) ? 'is-active' : ''; ?>">
		<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
		<?php if ( $cart_count > 0 ) : ?>
			<span class="aura-mbb-badge"><?php echo esc_html( $cart_count ); ?></span>
		<?php endif; ?>
		<span><?php esc_html_e( 'Cart', 'aura-fashion' ); ?></span>
	</a>
	<a href="<?php echo esc_url( $account_url ); ?>" class="aura-mbb-item <?php echo ( function_exists( 'is_account_page' ) && is_account_page() ) ? 'is-active' : ''; ?>">
		<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
		<span><?php esc_html_e( 'Account', 'aura-fashion' ); ?></span>
	</a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
