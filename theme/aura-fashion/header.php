<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'aura-fashion' ); ?></a>

<?php
$aura_opts    = get_option( 'aura_options', array() );
$store_notice = ! empty( $aura_opts['store_notice'] ) ? $aura_opts['store_notice'] : get_theme_mod( 'aura_store_notice', '' );
if ( $store_notice ) :
	?>
	<div class="aura-store-notice" role="region" aria-label="<?php esc_attr_e( 'Store notice', 'aura-fashion' ); ?>">
		<div class="aura-container">
			<p><?php echo esc_html( $store_notice ); ?></p>
		</div>
	</div>
<?php endif; ?>

<header class="site-header" role="banner">
	<div class="aura-container header-inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="site-logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title">
					<?php echo esc_html( get_theme_mod( 'aura_store_name', get_bloginfo( 'name' ) ?: 'Aura' ) ); ?>
				</a>
			<?php endif; ?>
		</div>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'aura-fashion' ); ?>">
			<ul class="primary-menu">
				<li class="menu-item <?php echo is_front_page() ? 'current-menu-item' : ''; ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'aura-fashion' ); ?></a>
				</li>

				<li class="menu-item menu-item-has-children menu-shop <?php echo ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product() ) ) ? 'current-menu-item' : ''; ?>">
					<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>">
						<?php esc_html_e( 'Shop', 'aura-fashion' ); ?>
						<svg class="menu-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
					</a>
					<ul class="sub-menu">
						<li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'All Products', 'aura-fashion' ); ?></a></li>
						<?php
						$shop_cats = array(
							'women'       => __( 'Women', 'aura-fashion' ),
							'men'         => __( 'Men', 'aura-fashion' ),
							'accessories' => __( 'Accessories', 'aura-fashion' ),
							'new-in'      => __( 'New In', 'aura-fashion' ),
							'sale'        => __( 'Sale', 'aura-fashion' ),
						);
						foreach ( $shop_cats as $slug => $label ) {
							$term = get_term_by( 'slug', $slug, 'product_cat' );
							$url  = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/product-category/' . $slug . '/' );
							if ( is_wp_error( $url ) ) {
								$url = home_url( '/product-category/' . $slug . '/' );
							}
							echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
						}
						?>
					</ul>
				</li>

				<li class="menu-item">
					<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'aura-fashion' ); ?></a>
				</li>

				<li class="menu-item">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'aura-fashion' ); ?></a>
				</li>
			</ul>
		</nav>

		<div class="header-actions">
			<button type="button" class="header-icon search-toggle" aria-label="<?php esc_attr_e( 'Open search', 'aura-fashion' ); ?>">
				<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
			</button>

			<a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>" class="header-icon" aria-label="<?php esc_attr_e( 'Wishlist', 'aura-fashion' ); ?>">
				<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
				<span class="wishlist-count" style="display:none;">0</span>
			</a>

			<a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ) ); ?>" class="header-icon cart-icon" aria-label="<?php esc_attr_e( 'Cart', 'aura-fashion' ); ?>">
				<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
				<?php if ( function_exists( 'WC' ) && WC()->cart ) : ?>
					<span class="cart-count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
				<?php else : ?>
					<span class="cart-count" style="display:none;">0</span>
				<?php endif; ?>
			</a>

			<?php
			$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
			if ( is_user_logged_in() ) {
				$current = wp_get_current_user();
				$label   = $current->display_name ? $current->display_name : __( 'Account', 'aura-fashion' );
				?>
				<a href="<?php echo esc_url( $account_url ); ?>" class="header-icon header-account is-logged-in" aria-label="<?php echo esc_attr( sprintf( __( 'My Account (%s)', 'aura-fashion' ), $label ) ); ?>" title="<?php echo esc_attr( $label ); ?>">
					<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 0v2"/><circle cx="12" cy="7" r="4"/></svg>
				</a>
			<?php } else { ?>
				<a href="<?php echo esc_url( $account_url ); ?>" class="header-icon header-account header-login" aria-label="<?php esc_attr_e( 'Login / Register', 'aura-fashion' ); ?>" title="<?php esc_attr_e( 'Login / Register', 'aura-fashion' ); ?>">
					<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 0v2"/><circle cx="12" cy="7" r="4"/></svg>
				</a>
			<?php } ?>

			<button type="button" class="header-icon mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'aura-fashion' ); ?>" aria-expanded="false" aria-controls="aura-mobile-nav">
				<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
			</button>
		</div>
	</div>
</header>

<div class="mobile-nav-overlay" aria-hidden="true"></div>
<nav id="aura-mobile-nav" class="mobile-nav" aria-label="<?php esc_attr_e( 'Mobile menu', 'aura-fashion' ); ?>">
	<button type="button" class="mobile-nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'aura-fashion' ); ?>">&times;</button>
	<div class="mobile-nav-brand">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title"><?php echo esc_html( get_theme_mod( 'aura_store_name', get_bloginfo( 'name' ) ?: 'Aura' ) ); ?></a>
	</div>
	<ul class="mobile-menu">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'aura-fashion' ); ?></a></li>
		<li class="mobile-has-children">
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Shop', 'aura-fashion' ); ?></a>
			<ul class="mobile-sub-menu">
				<li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'All Products', 'aura-fashion' ); ?></a></li>
				<?php
				foreach ( $shop_cats as $slug => $label ) {
					$term = get_term_by( 'slug', $slug, 'product_cat' );
					$url  = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/product-category/' . $slug . '/' );
					if ( is_wp_error( $url ) ) {
						$url = home_url( '/product-category/' . $slug . '/' );
					}
					echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
				}
				?>
			</ul>
		</li>
		<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'aura-fashion' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'aura-fashion' ); ?></a></li>
	</ul>
	<div class="mobile-nav-account">
		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( $account_url ); ?>" class="btn btn-outline btn-block"><?php esc_html_e( 'My Account', 'aura-fashion' ); ?></a>
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="btn btn-text btn-block"><?php esc_html_e( 'Log out', 'aura-fashion' ); ?></a>
		<?php else : ?>
			<a href="<?php echo esc_url( $account_url ); ?>" class="btn btn-primary btn-block"><?php esc_html_e( 'Login / Register', 'aura-fashion' ); ?></a>
		<?php endif; ?>
	</div>
</nav>

<div class="search-overlay" aria-hidden="true">
	<button type="button" class="search-close" aria-label="<?php esc_attr_e( 'Close search', 'aura-fashion' ); ?>">&times;</button>
	<div class="search-form-wrap">
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search products…', 'aura-fashion' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'aura-fashion' ); ?>" autocomplete="off">
			<input type="hidden" name="post_type" value="product">
		</form>
	</div>
</div>

<main id="main" class="site-main">
