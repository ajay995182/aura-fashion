<?php
/**
 * Performance, SEO & Technical – v1.6.0
 * – Lazy loading + WebP hints
 * – Resource hints / defer non-critical JS
 * – Schema markup (Product, FAQ, Organization, Breadcrumb)
 * – Multilingual readiness (hreflang hooks, language attributes)
 * – Currency switcher plugin notice / compatibility
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   1. IMAGE OPTIMIZATION – native lazy load + decoding + sizes
   ========================================================================== */

/**
 * Ensure all content images get loading="lazy" and decoding="async"
 * (WordPress 5.5+ already does lazy for content; we reinforce + add decoding).
 */
function aura_img_lazy_attrs( $attr, $attachment = null, $size = null ) {
	if ( empty( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}
	// First image on single product / front hero should be eager for LCP
	if ( is_singular( 'product' ) || is_front_page() ) {
		static $first = true;
		if ( $first && ( is_singular( 'product' ) || is_front_page() ) ) {
			// Keep first product main image eager – handled in template; skip here
		}
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'aura_img_lazy_attrs', 10, 3 );

/**
 * Add fetchpriority="high" to the main product image for LCP.
 */
function aura_product_main_image_priority( $html, $post_id ) {
	if ( is_singular( 'product' ) && has_post_thumbnail( $post_id ) ) {
		$html = str_replace( ' loading="lazy"', ' loading="eager" fetchpriority="high"', $html );
		$html = str_replace( '<img ', '<img fetchpriority="high" ', $html );
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'aura_product_main_image_priority', 10, 2 );

/**
 * Encourage WebP: add accepts header note via picture support when browser sends image/webp.
 * WordPress core + modern hosts often convert; we add type hints in srcset when available.
 */
function aura_webp_upload_mimes( $mimes ) {
	$mimes['webp'] = 'image/webp';
	$mimes['svg']  = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'aura_webp_upload_mimes' );

/* ==========================================================================
   2. PERFORMANCE – defer JS, preconnect, remove emoji bloat
   ========================================================================== */

function aura_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'aura_resource_hints', 10, 2 );

/** Defer theme scripts (keep jQuery dependency order via WP). */
function aura_defer_scripts( $tag, $handle, $src ) {
	$defer = array( 'aura-main', 'aura-shop', 'aura-single' );
	if ( in_array( $handle, $defer, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'aura_defer_scripts', 10, 3 );

/** Remove emoji scripts/styles for leaner head. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/** Disable embeds if not needed. */
function aura_disable_embeds() {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'aura_disable_embeds' );

/**
 * Inline a tiny critical CSS bootstrap (above-the-fold header + body bg)
 * Full stylesheet still loads; this reduces FOUC.
 */
function aura_critical_css() {
	?>
	<style id="aura-critical-css">
		:root{--aura-black:#0a0a0a;--aura-gold:#c4a574;--aura-white:#f5f5f5;--aura-header-height:68px}
		body{margin:0;background:#0a0a0a;color:#f5f5f5;font-family:system-ui,-apple-system,sans-serif}
		.site-header{position:fixed;top:0;left:0;right:0;height:68px;background:rgba(10,10,10,.88);z-index:1000}
		.site-main{padding-top:var(--aura-header-height);min-height:50vh}
		.skip-link{position:absolute;left:-9999px}
		.skip-link:focus{left:16px;top:16px;z-index:100001;background:var(--aura-gold);color:#000;padding:12px 24px}
	</style>
	<?php
}
add_action( 'wp_head', 'aura_critical_css', 1 );

/* ==========================================================================
   3. SCHEMA MARKUP – Product, FAQ, Organization, BreadcrumbList
   ========================================================================== */

function aura_schema_organization() {
	if ( is_admin() ) {
		return;
	}
	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_theme_mod( 'aura_store_name', get_bloginfo( 'name' ) ),
		'url'      => home_url( '/' ),
	);
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo_url ) {
			$data['logo'] = $logo_url;
		}
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'aura_schema_organization', 5 );

function aura_schema_product() {
	if ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	/* Always resolve a real WC_Product – global $product can be a string or wrong type */
	$product = null;
	if ( function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product || ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$short = $product->get_short_description();
	$long  = $product->get_description();
	$desc  = $short ? $short : $long;

	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'description' => wp_strip_all_tags( (string) $desc ),
		'sku'         => $product->get_sku(),
		'url'         => get_permalink( $product->get_id() ),
	);

	$image_id = $product->get_image_id();
	if ( $image_id ) {
		$img = wp_get_attachment_url( $image_id );
		if ( $img ) {
			$data['image'] = $img;
		}
	}

	$price = $product->get_price();
	if ( $price !== '' && $price !== null ) {
		$data['offers'] = array(
			'@type'         => 'Offer',
			'url'           => get_permalink( $product->get_id() ),
			'priceCurrency' => get_woocommerce_currency(),
			'price'         => (string) $price,
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'itemCondition' => 'https://schema.org/NewCondition',
		);
	}

	if ( $product->get_review_count() > 0 ) {
		$data['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $product->get_average_rating(),
			'reviewCount' => $product->get_review_count(),
		);
	}

	$brand = wp_get_post_terms( $product->get_id(), 'product_brand' );
	if ( ! is_wp_error( $brand ) && ! empty( $brand ) ) {
		$data['brand'] = array( '@type' => 'Brand', 'name' => $brand[0]->name );
	} else {
		$data['brand'] = array( '@type' => 'Brand', 'name' => get_bloginfo( 'name' ) );
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'aura_schema_product', 6 );

function aura_schema_faq() {
	if ( ! is_page_template( 'page-templates/template-faq.php' ) ) {
		return;
	}
	// Default FAQs from template – keep in sync with template-faq.php
	$faqs = array(
		array( 'q' => 'What is your return policy?', 'a' => 'We accept returns within 30 days of delivery. Items must be unworn, unwashed, and in original packaging with tags attached.' ),
		array( 'q' => 'How long does shipping take?', 'a' => 'Standard shipping takes 5–7 business days. Express shipping (2–3 business days) is available at checkout.' ),
		array( 'q' => 'Do you offer free shipping?', 'a' => 'Yes! Free standard shipping on all orders over $100.' ),
		array( 'q' => 'How do I find my size?', 'a' => 'Each product page has a Size Guide button with detailed measurements.' ),
		array( 'q' => 'Can I modify or cancel my order?', 'a' => 'Orders can be modified or cancelled within 1 hour of placement.' ),
		array( 'q' => 'Are your materials sustainable?', 'a' => 'We prioritise certified organic cotton, recycled polyester, and responsibly sourced wool.' ),
	);

	$entities = array();
	foreach ( $faqs as $faq ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $faq['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['a'],
			),
		);
	}

	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'aura_schema_faq', 7 );

function aura_schema_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}
	$items   = array();
	$pos     = 1;
	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => __( 'Home', 'aura-fashion' ),
		'item'     => home_url( '/' ),
	);

	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() || is_product() ) ) {
		$shop_id = wc_get_page_id( 'shop' );
		if ( $shop_id > 0 ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => get_the_title( $shop_id ),
				'item'     => get_permalink( $shop_id ),
			);
		}
	}

	if ( is_product_category() ) {
		$term = get_queried_object();
		if ( $term ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => $term->name,
				'item'     => get_term_link( $term ),
			);
		}
	}

	if ( is_singular( 'product' ) ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} elseif ( is_singular() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	}

	if ( count( $items ) < 2 ) {
		return;
	}

	$data = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'aura_schema_breadcrumb', 8 );

/* ==========================================================================
   4. MULTILINGUAL – WPML / Polylang readiness
   ========================================================================== */

/**
 * Declare text domain path for language packs.
 * Load theme textdomain already in aura_setup().
 * Register strings that Customizer uses for WPML String Translation.
 */
function aura_wpml_register_strings() {
	if ( ! has_action( 'wpml_register_single_string' ) && ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$strings = array(
		'aura_hero_badge'  => get_theme_mod( 'aura_hero_badge', '' ),
		'aura_hero_title'  => get_theme_mod( 'aura_hero_title', '' ),
		'aura_hero_desc'   => get_theme_mod( 'aura_hero_desc', '' ),
		'aura_store_name'  => get_theme_mod( 'aura_store_name', '' ),
	);
	foreach ( $strings as $name => $value ) {
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( $name, $value, 'Aura Fashion' );
		}
		do_action( 'wpml_register_single_string', 'Aura Fashion', $name, $value );
	}
}
add_action( 'init', 'aura_wpml_register_strings', 20 );

/**
 * Filter Customizer output through WPML/Polylang when available.
 */
function aura_translate_mod( $value, $name ) {
	if ( function_exists( 'pll__' ) ) {
		return pll__( $value );
	}
	return apply_filters( 'wpml_translate_single_string', $value, 'Aura Fashion', $name );
}

/* ==========================================================================
   5. CURRENCY SWITCHER – compatibility + admin tip
   ========================================================================== */

/**
 * Works with WOOCS, Aelia, CURCY, etc. – no conflict with our price HTML filters.
 * Show a one-time admin notice recommending a free switcher if none active.
 */
function aura_currency_plugin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'aura_currency_notice_dismissed' ) ) {
		return;
	}
	$active = array(
		'woocommerce-currency-switcher/index.php',
		'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php',
		'currency-switcher-woocommerce/currency-switcher-woocommerce.php',
		'woo-multi-currency/woo-multi-currency.php',
	);
	foreach ( $active as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			return;
		}
	}
	?>
	<div class="notice notice-info is-dismissible aura-currency-notice">
		<p>
			<strong><?php esc_html_e( 'Aura Fashion:', 'aura-fashion' ); ?></strong>
			<?php esc_html_e( 'For multi-currency sales, install a free plugin such as “Currency Switcher for WooCommerce” or “FOX – Currency Switcher”. The theme is compatible with standard price filters.', 'aura-fashion' ); ?>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=currency+switcher+woocommerce&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Browse plugins', 'aura-fashion' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'aura_currency_plugin_notice' );

function aura_dismiss_currency_notice() {
	if ( isset( $_GET['aura_dismiss_currency'] ) && current_user_can( 'manage_options' ) ) {
		update_option( 'aura_currency_notice_dismissed', 1 );
	}
}
add_action( 'admin_init', 'aura_dismiss_currency_notice' );
