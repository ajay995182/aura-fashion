<?php
/**
 * Aura Fashion Theme Functions
 * Version: 1.10.6
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

define( 'AURA_VERSION', '1.10.6' );
define( 'AURA_DIR', get_template_directory() );
define( 'AURA_URI', get_template_directory_uri() );

/* --------------------------------------------------------------------------
   Theme Setup
   -------------------------------------------------------------------------- */
function aura_setup() {
	load_theme_textdomain( 'aura-fashion', AURA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 400,
		'single_image_width'    => 700,
		'product_grid'          => array(
			'default_rows'    => 4,
			'min_rows'        => 1,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'aura-fashion' ),
		'footer'  => __( 'Footer Menu', 'aura-fashion' ),
	) );

	// Image sizes
	add_image_size( 'aura-product', 400, 533, true );
	add_image_size( 'aura-product-large', 700, 933, true );
	add_image_size( 'aura-category', 600, 800, true );
}
add_action( 'after_setup_theme', 'aura_setup' );

/* --------------------------------------------------------------------------
   Enqueue Scripts & Styles
   -------------------------------------------------------------------------- */
function aura_scripts() {
	// Google Fonts
	wp_enqueue_style(
		'aura-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@500;600;700&family=Playfair+Display:wght@500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'aura-style', get_stylesheet_uri(), array(), AURA_VERSION );
	wp_enqueue_style( 'aura-ui-refresh', AURA_URI . '/assets/css/ui-refresh.css', array( 'aura-style' ), AURA_VERSION );

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		wp_enqueue_style( 'aura-account', AURA_URI . '/assets/css/account.css', array( 'aura-style', 'aura-ui-refresh' ), AURA_VERSION );
	}

	$need_checkout_css = false;
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		$need_checkout_css = true;
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$need_checkout_css = true;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'view-order' ) ) ) {
		$need_checkout_css = true;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		$need_checkout_css = true;
	}
	if ( $need_checkout_css ) {
		wp_enqueue_style( 'aura-checkout-forms', AURA_URI . '/assets/css/checkout-forms.css', array( 'aura-style', 'aura-ui-refresh' ), AURA_VERSION );
	}

	wp_enqueue_script( 'aura-main', AURA_URI . '/assets/js/main.js', array( 'jquery' ), AURA_VERSION, true );

	$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	wp_localize_script( 'aura-main', 'auraData', array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'aura_nonce' ),
		'cartUrl'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ),
		'accountUrl' => $account_url,
		'wishlist'   => array(),
		'i18n'       => array(
			'addedToCart'     => __( 'Added to cart!', 'aura-fashion' ),
			'viewCart'        => __( 'View Cart', 'aura-fashion' ),
			'addedWishlist'   => __( 'Added to wishlist', 'aura-fashion' ),
			'removedWishlist' => __( 'Removed from wishlist', 'aura-fashion' ),
			'onlyLeft'        => __( 'Only %s left!', 'aura-fashion' ),
			'loading'         => __( 'Loading…', 'aura-fashion' ),
			'error'           => __( 'Something went wrong. Please try again.', 'aura-fashion' ),
			'shareCopied'     => __( 'Link copied!', 'aura-fashion' ),
			'loginToCart'     => __( 'Please create an account or log in to add items to your cart.', 'aura-fashion' ),
			'loginRegister'   => __( 'Log in / Create account', 'aura-fashion' ),
		),
	) );

	if ( is_singular( 'product' ) ) {
		wp_enqueue_script( 'aura-single', AURA_URI . '/assets/js/single-product.js', array( 'jquery', 'aura-main' ), AURA_VERSION, true );
	}

	if ( is_shop() || is_product_category() || is_product_tag() ) {
		wp_enqueue_script( 'aura-shop', AURA_URI . '/assets/js/shop-filters.js', array( 'jquery', 'aura-main' ), AURA_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'aura_scripts' );

/* --------------------------------------------------------------------------
   Security Hardening
   -------------------------------------------------------------------------- */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* --------------------------------------------------------------------------
   Customizer
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/customizer.php';

/* --------------------------------------------------------------------------
   WooCommerce Tweaks
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/woocommerce.php';

/* --------------------------------------------------------------------------
   AJAX Handlers (Newsletter, Wishlist, Quick View, Filters, Recently Viewed)
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/ajax-handlers.php';

/* --------------------------------------------------------------------------
   Marketing & Conversion (ESP, Exit popup, Shipping bar, Countdown, IG, Reviews)
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/marketing.php';

/* --------------------------------------------------------------------------
   User Account & Personalization
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/account.php';

/* --------------------------------------------------------------------------
   Design & Page Improvements (Lookbook, Mega Menu, Mode, Blog, Skeletons)
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/design.php';

/* --------------------------------------------------------------------------
   Performance, SEO, Demo Import
   -------------------------------------------------------------------------- */
require AURA_DIR . '/inc/performance.php';
require AURA_DIR . '/inc/demo-import.php';
require AURA_DIR . '/inc/admin.php';


/* --------------------------------------------------------------------------
   Widgets
   -------------------------------------------------------------------------- */
function aura_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Shop Sidebar', 'aura-fashion' ),
		'id'            => 'shop-sidebar',
		'description'   => __( 'Widgets for the shop sidebar filters.', 'aura-fashion' ),
		'before_widget' => '<div id="%1$s" class="filter-group widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'aura_widgets_init' );

/* --------------------------------------------------------------------------
   Wishlist Shortcode
   -------------------------------------------------------------------------- */
function aura_wishlist_shortcode() {
	ob_start();
	?>
	<div id="aura-wishlist-page" class="aura-container" style="padding:60px 0;">
		<div class="section-header">
			<h1><?php esc_html_e( 'My Wishlist', 'aura-fashion' ); ?></h1>
			<p><?php esc_html_e( 'Items you have saved for later.', 'aura-fashion' ); ?></p>
		</div>
		<div id="wishlist-share-bar" class="wishlist-share" style="justify-content:center;margin-bottom:32px;display:none;">
			<button type="button" class="wishlist-share-btn" data-share="copy" aria-label="<?php esc_attr_e( 'Copy link', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
				<?php esc_html_e( 'Copy Link', 'aura-fashion' ); ?>
			</button>
			<a href="#" class="wishlist-share-btn" data-share="email" aria-label="<?php esc_attr_e( 'Share via email', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/></svg>
				<?php esc_html_e( 'Email', 'aura-fashion' ); ?>
			</a>
			<a href="#" class="wishlist-share-btn" data-share="twitter" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Share on X', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/></svg>
				X
			</a>
			<a href="#" class="wishlist-share-btn" data-share="facebook" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Share on Facebook', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.049c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>
				Facebook
			</a>
			<a href="#" class="wishlist-share-btn" data-share="whatsapp" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Share on WhatsApp', 'aura-fashion' ); ?>">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.228.148-.425.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.245.078.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
				WhatsApp
			</a>
		</div>
		<div id="aura-wishlist-products" class="products-grid">
			<div class="products-loading active">
				<div class="spinner"></div>
				<p><?php esc_html_e( 'Loading your wishlist…', 'aura-fashion' ); ?></p>
			</div>
		</div>
		<div id="aura-wishlist-empty" class="wishlist-empty hidden">
			<svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;opacity:0.4;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			<h2><?php esc_html_e( 'Your wishlist is empty', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Browse our collection and save items you love.', 'aura-fashion' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Shop Now', 'aura-fashion' ); ?></a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aura_wishlist', 'aura_wishlist_shortcode' );

/* --------------------------------------------------------------------------
   Body Classes
   -------------------------------------------------------------------------- */
function aura_body_classes( $classes ) {
	if ( is_shop() || is_product_category() || is_product_tag() ) {
		$classes[] = 'aura-shop';
	}
	if ( is_product() ) {
		$classes[] = 'aura-single-product';
	}
	return $classes;
}
add_filter( 'body_class', 'aura_body_classes' );

/* --------------------------------------------------------------------------
   Content Width
   -------------------------------------------------------------------------- */
if ( ! isset( $content_width ) ) {
	$content_width = 1280;
}

/* --------------------------------------------------------------------------
   Auto-create essential pages + open store to public
   -------------------------------------------------------------------------- */
function aura_ensure_essential_pages() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}

	$pages = array(
		'wishlist' => array(
			'title'   => 'Wishlist',
			'slug'    => 'wishlist',
			'content' => '[aura_wishlist]',
		),
		'about'    => array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '',
		),
		'contact'  => array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '',
		),
		'faq'      => array(
			'title'   => 'FAQ',
			'slug'    => 'faq',
			'content' => '',
		),
	);

	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( $page['slug'] );
		if ( $existing ) {
			// Ensure wishlist has the shortcode
			if ( 'wishlist' === $key && $existing instanceof WP_Post ) {
				if ( false === strpos( (string) $existing->post_content, '[aura_wishlist]' ) ) {
					wp_update_post(
						array(
							'ID'           => $existing->ID,
							'post_content' => '[aura_wishlist]',
							'post_status'  => 'publish',
						)
					);
				}
			}
			continue;
		}
		wp_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_name'    => $page['slug'],
				'post_content' => $page['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => 1,
			)
		);
	}

	// Flush rewrite rules once after creating pages
	if ( ! get_option( 'aura_pages_flushed_v2' ) ) {
		flush_rewrite_rules( false );
		update_option( 'aura_pages_flushed_v2', 1 );
	}
}
add_action( 'after_switch_theme', 'aura_ensure_essential_pages' );
add_action( 'init', 'aura_ensure_essential_pages', 20 );

/**
 * Force WooCommerce store Live — kill Coming Soon for all visitors.
 * Fixes the black "Great things are on the horizon" shop page for guests.
 */
function aura_force_store_live() {
	$keys = array(
		'woocommerce_coming_soon'              => 'no',
		'woocommerce_store_pages_only'         => 'no',
		'woocommerce_private_link'             => 'no',
		'woocommerce_coming_soon_store_pages'  => 'no',
	);
	foreach ( $keys as $key => $val ) {
		if ( get_option( $key ) !== $val ) {
			update_option( $key, $val );
		}
	}
}
add_action( 'after_switch_theme', 'aura_force_store_live' );
add_action( 'admin_init', 'aura_force_store_live' );
add_action( 'init', 'aura_force_store_live', 1 );
add_action( 'woocommerce_init', 'aura_force_store_live', 1 );

/**
 * Always report the store as Live when options are read.
 */
add_filter( 'pre_option_woocommerce_coming_soon', function () {
	return 'no';
}, 1 );
add_filter( 'option_woocommerce_coming_soon', function () {
	return 'no';
}, 1 );
add_filter( 'pre_option_woocommerce_store_pages_only', function () {
	return 'no';
}, 1 );
add_filter( 'option_woocommerce_store_pages_only', function () {
	return 'no';
}, 1 );
add_filter( 'pre_option_woocommerce_private_link', function () {
	return 'no';
}, 1 );

/**
 * Exclude every front-end request from Coming Soon (WC 9.1+).
 * Priority 1 so it wins over other plugins.
 */
add_filter( 'woocommerce_coming_soon_exclude', '__return_true', 1 );

/**
 * Strip WooCommerce Coming Soon hooks so the landing page is never rendered.
 */
function aura_disable_wc_coming_soon_hooks() {
	// Classic template path
	remove_action( 'template_redirect', array( 'Automattic\WooCommerce\Internal\ComingSoon\ComingSoonRequestHandler', 'handle_template_redirect' ), 10 );
	remove_filter( 'template_include', array( 'Automattic\WooCommerce\Internal\ComingSoon\ComingSoonRequestHandler', 'coming_soon_template' ), 10 );

	// If the class is already instantiated, try instance methods via WC container if available
	if ( function_exists( 'wc_get_container' ) ) {
		try {
			$handler = wc_get_container()->get( \Automattic\WooCommerce\Internal\ComingSoon\ComingSoonRequestHandler::class );
			if ( $handler ) {
				remove_action( 'template_redirect', array( $handler, 'handle_template_redirect' ), 10 );
				remove_filter( 'template_include', array( $handler, 'coming_soon_template' ), 10 );
				remove_filter( 'wp_theme_json_data_theme', array( $handler, 'experimental_filter_theme_json_theme' ), 10 );
			}
		} catch ( \Throwable $e ) {
			// Container / class not available on older WC — ignore
		}
	}
}
add_action( 'plugins_loaded', 'aura_disable_wc_coming_soon_hooks', 100 );
add_action( 'init', 'aura_disable_wc_coming_soon_hooks', 0 );
add_action( 'wp_loaded', 'aura_disable_wc_coming_soon_hooks', 0 );

/**
 * Last-resort: never use a coming-soon template file.
 */
function aura_block_coming_soon_template( $template ) {
	if ( ! is_string( $template ) ) {
		return $template;
	}
	$base = strtolower( basename( $template ) );
	if ( false !== strpos( $base, 'coming-soon' ) || false !== strpos( $base, 'coming_soon' ) ) {
		// Fall back to normal shop / home template
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$shop = locate_template( array( 'woocommerce/archive-product.php', 'archive-product.php' ) );
			if ( $shop ) {
				return $shop;
			}
		}
		$home = locate_template( array( 'front-page.php', 'home.php', 'index.php' ) );
		if ( $home ) {
			return $home;
		}
	}
	return $template;
}
add_filter( 'template_include', 'aura_block_coming_soon_template', 999 );

/**
 * Hide Site Visibility badge in admin bar (optional UI cleanup).
 */
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'woocommerce-site-visibility-badge' );
}, 100 );

/**
 * On logout: set a short-lived cookie so front-end JS clears localStorage wishlist.
 * User-meta wishlist stays (reappears on next login); guest session must not keep prior data.
 */
function aura_on_logout_clear_wishlist_cookie() {
	if ( headers_sent() ) {
		return;
	}
	$secure = is_ssl();
	setcookie( 'aura_clear_wishlist', '1', time() + 300, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, true );
	if ( COOKIEPATH !== SITECOOKIEPATH ) {
		setcookie( 'aura_clear_wishlist', '1', time() + 300, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true );
	}
}
add_action( 'wp_logout', 'aura_on_logout_clear_wishlist_cookie' );

/**
 * Ensure WooCommerce core pages exist and are assigned.
 */
function aura_ensure_woocommerce_pages() {
	if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'wc_create_page' ) ) {
		// Fallback: try to install pages via WC helper if available
		if ( function_exists( 'WC' ) && class_exists( 'WC_Install' ) && method_exists( 'WC_Install', 'create_pages' ) ) {
			WC_Install::create_pages();
		}
		return;
	}

	$shop_id = wc_get_page_id( 'shop' );
	if ( $shop_id <= 0 ) {
		if ( class_exists( 'WC_Install' ) && method_exists( 'WC_Install', 'create_pages' ) ) {
			WC_Install::create_pages();
		}
	}
}
add_action( 'after_switch_theme', 'aura_ensure_woocommerce_pages' );
