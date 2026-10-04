<?php
/**
 * WooCommerce Customizations
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Remove default WooCommerce styles (we use our own)
   -------------------------------------------------------------------------- */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/* --------------------------------------------------------------------------
   Product columns
   -------------------------------------------------------------------------- */
add_filter( 'loop_shop_columns', function() { return 4; } );
add_filter( 'loop_shop_per_page', function() { return 12; } );

/* --------------------------------------------------------------------------
   AJAX Add to Cart support
   -------------------------------------------------------------------------- */
function aura_enable_ajax_add_to_cart() {
	if ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_front_page() ) {
		wp_enqueue_script( 'wc-add-to-cart' );
	}
}
add_action( 'wp_enqueue_scripts', 'aura_enable_ajax_add_to_cart', 20 );

/* --------------------------------------------------------------------------
   Stock Countdown – show “Only X left”
   -------------------------------------------------------------------------- */
function aura_stock_countdown() {
	global $product;
	if ( ! $product || ! $product->managing_stock() ) {
		return;
	}
	$stock = $product->get_stock_quantity();
	if ( $stock === null || $stock > 10 ) {
		return;
	}
	$class = $stock <= 3 ? 'critical' : '';
	printf(
		'<div class="stock-countdown %s" role="status"><svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg> %s</div>',
		esc_attr( $class ),
		sprintf( esc_html__( 'Only %s left!', 'aura-fashion' ), intval( $stock ) )
	);
}
add_action( 'woocommerce_single_product_summary', 'aura_stock_countdown', 15 );
add_action( 'woocommerce_after_shop_loop_item_title', 'aura_stock_countdown', 15 );

/* --------------------------------------------------------------------------
   Product Video Support
   Meta key: _aura_product_video (YouTube/Vimeo URL or self-hosted)
   -------------------------------------------------------------------------- */
function aura_product_video_field() {
	woocommerce_wp_text_input( array(
		'id'          => '_aura_product_video',
		'label'       => __( 'Product Video URL', 'aura-fashion' ),
		'description' => __( 'YouTube, Vimeo or direct MP4 URL. Shown on single product page.', 'aura-fashion' ),
		'desc_tip'    => true,
		'placeholder' => 'https://www.youtube.com/watch?v=…',
	) );
}
add_action( 'woocommerce_product_options_general_product_data', 'aura_product_video_field' );

function aura_save_product_video( $post_id ) {
	$video = isset( $_POST['_aura_product_video'] ) ? esc_url_raw( wp_unslash( $_POST['_aura_product_video'] ) ) : '';
	update_post_meta( $post_id, '_aura_product_video', $video );
}
add_action( 'woocommerce_process_product_meta', 'aura_save_product_video' );

function aura_display_product_video() {
	global $product;
	$video = get_post_meta( $product->get_id(), '_aura_product_video', true );
	if ( empty( $video ) ) {
		return;
	}

	$embed = '';
	if ( strpos( $video, 'youtube.com' ) !== false || strpos( $video, 'youtu.be' ) !== false ) {
		preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video, $m );
		if ( ! empty( $m[1] ) ) {
			$embed = '<iframe src="https://www.youtube.com/embed/' . esc_attr( $m[1] ) . '?rel=0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
		}
	} elseif ( strpos( $video, 'vimeo.com' ) !== false ) {
		preg_match( '/vimeo\.com\/(\d+)/', $video, $m );
		if ( ! empty( $m[1] ) ) {
			$embed = '<iframe src="https://player.vimeo.com/video/' . esc_attr( $m[1] ) . '" allowfullscreen></iframe>';
		}
	} else {
		$embed = '<video controls playsinline src="' . esc_url( $video ) . '"></video>';
	}

	if ( $embed ) {
		echo '<div class="product-video-wrapper">' . $embed . '</div>'; // phpcs:ignore
	}
}
add_action( 'woocommerce_before_single_product_summary', 'aura_display_product_video', 5 );

/* --------------------------------------------------------------------------
   Size Guide Button on single product
   -------------------------------------------------------------------------- */
function aura_size_guide_button() {
	global $product;
	// Show only if product has size attribute
	$attrs = $product->get_attributes();
	$has_size = false;
	foreach ( $attrs as $attr ) {
		$name = $attr->get_name();
		if ( stripos( $name, 'size' ) !== false || stripos( $name, 'pa_size' ) !== false ) {
			$has_size = true;
			break;
		}
	}
	if ( ! $has_size && ! get_theme_mod( 'aura_size_guide_content' ) ) {
		return;
	}
	?>
	<button type="button" class="size-guide-btn" id="open-size-guide" aria-haspopup="dialog">
		<svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>
		<?php esc_html_e( 'Size Guide', 'aura-fashion' ); ?>
	</button>
	<?php
}
add_action( 'woocommerce_before_add_to_cart_button', 'aura_size_guide_button', 5 );

/* --------------------------------------------------------------------------
   Variation Swatches – convert selects to buttons
   -------------------------------------------------------------------------- */
function aura_variation_swatches_script() {
	if ( ! is_product() ) {
		return;
	}
	?>
	<script>
	jQuery(function($){
		function buildSwatches() {
			$('.variations_form .variations select').each(function(){
				var $select = $(this);
				if ($select.data('swatches-built')) return;
				$select.data('swatches-built', true);

				var attrName = $select.attr('name');
				var $label = $select.closest('tr').find('.label label');
				var labelText = $label.text().replace('*','').trim();
				var isColor = /color|colour/i.test(labelText) || /color|colour/i.test(attrName);

				var $wrap = $('<div class="aura-swatches" role="listbox" aria-label="'+labelText+'"></div>');
				$select.find('option').each(function(){
					var val = $(this).val();
					if (!val) return;
					var text = $(this).text();
					var $btn = $('<button type="button" class="aura-swatch" role="option" data-value="'+val+'" aria-label="'+text+'">'+text+'</button>');
					if (isColor) {
						$btn.addClass('color-swatch').text('').css('background-color', text.toLowerCase().replace(/\s/g,''));
						// fallback for named colors that browsers understand
					}
					if ($(this).is(':disabled')) $btn.addClass('disabled');
					$wrap.append($btn);
				});
				$select.after($wrap);

				$wrap.on('click', '.aura-swatch:not(.disabled)', function(){
					var val = $(this).data('value');
					$wrap.find('.aura-swatch').removeClass('selected').attr('aria-selected','false');
					$(this).addClass('selected').attr('aria-selected','true');
					$select.val(val).trigger('change');
				});
			});
		}
		buildSwatches();
		$('.variations_form').on('woocommerce_update_variation_values', buildSwatches);
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'aura_variation_swatches_script' );

/* --------------------------------------------------------------------------
   Related / Upsell improvements – more products, better title
   -------------------------------------------------------------------------- */
add_filter( 'woocommerce_output_related_products_args', function( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );

add_filter( 'woocommerce_upsell_display_args', function( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );

/* --------------------------------------------------------------------------
   Sale badge text
   -------------------------------------------------------------------------- */
add_filter( 'woocommerce_sale_flash', function( $html, $post, $product ) {
	return '<span class="product-card-badge sale">' . esc_html__( 'Sale', 'aura-fashion' ) . '</span>';
}, 10, 3 );

/* --------------------------------------------------------------------------
   Placeholder image
   -------------------------------------------------------------------------- */
function aura_placeholder_img( $size = 'aura-product', $seed = '' ) {
	$seed = $seed ? preg_replace( '/[^a-z0-9\-]/i', '', (string) $seed ) : 'fashion';
	/* Deterministic fashion-style photos so cards never look empty */
	$gallery = array(
		'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1467043237213-65f2da53396f?w=400&h=533&fit=crop',
		'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=400&h=533&fit=crop',
	);
	$idx = abs( crc32( $seed ) ) % count( $gallery );
	$url = $gallery[ $idx ];
	return '<img src="' . esc_url( $url ) . '" alt="" class="aura-placeholder-img" width="400" height="533" loading="lazy" decoding="async" />';
}

add_filter( 'woocommerce_placeholder_img', function( $html, $size ) {
	return aura_placeholder_img( $size );
}, 10, 2 );

/* --------------------------------------------------------------------------
   Recently Viewed – track via cookie + display section
   -------------------------------------------------------------------------- */
function aura_track_recently_viewed() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	global $post;
	$viewed = array();
	if ( isset( $_COOKIE['aura_recently_viewed'] ) ) {
		$viewed = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['aura_recently_viewed'] ) ) ) ) );
	}
	$viewed = array_diff( $viewed, array( $post->ID ) );
	array_unshift( $viewed, $post->ID );
	$viewed = array_slice( $viewed, 0, 8 );
	setcookie( 'aura_recently_viewed', implode( ',', $viewed ), time() + WEEK_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
}
add_action( 'template_redirect', 'aura_track_recently_viewed' );

function aura_display_recently_viewed() {
	if ( empty( $_COOKIE['aura_recently_viewed'] ) ) {
		return;
	}
	$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['aura_recently_viewed'] ) ) ) ) );
	if ( is_singular( 'product' ) ) {
		global $post;
		$ids = array_diff( $ids, array( $post->ID ) );
	}
	$ids = array_slice( $ids, 0, 4 );
	if ( empty( $ids ) ) {
		return;
	}

	$q = new WP_Query( array(
		'post_type'      => 'product',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 4,
	) );
	if ( ! $q->have_posts() ) {
		return;
	}
	?>
	<section class="recently-viewed">
		<div class="aura-container">
			<div class="section-header">
				<h2><?php esc_html_e( 'Recently Viewed', 'aura-fashion' ); ?></h2>
			</div>
			<div class="products-grid">
				<?php
				while ( $q->have_posts() ) {
					$q->the_post();
					wc_get_template_part( 'content', 'product' );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_after_single_product', 'aura_display_recently_viewed', 20 );
add_action( 'woocommerce_after_shop_loop', 'aura_display_recently_viewed', 30 );

/* --------------------------------------------------------------------------
   Quick View Modal container in footer
   -------------------------------------------------------------------------- */
function aura_quick_view_modal() {
	?>
	<div id="aura-quick-view" class="quick-view-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Quick View', 'aura-fashion' ); ?>" hidden>
		<div class="quick-view-backdrop"></div>
		<div class="quick-view-content">
			<button type="button" class="quick-view-close" aria-label="<?php esc_attr_e( 'Close', 'aura-fashion' ); ?>">&times;</button>
			<div class="quick-view-body">
				<div class="products-loading active"><div class="spinner"></div></div>
			</div>
		</div>
	</div>

	<div id="aura-size-guide" class="size-guide-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Size Guide', 'aura-fashion' ); ?>" hidden>
		<div class="quick-view-backdrop"></div>
		<div class="size-guide-content">
			<button type="button" class="quick-view-close" aria-label="<?php esc_attr_e( 'Close', 'aura-fashion' ); ?>">&times;</button>
			<h2><?php esc_html_e( 'Size Guide', 'aura-fashion' ); ?></h2>
			<?php
			$content = get_theme_mod( 'aura_size_guide_content' );
			if ( $content ) {
				echo wp_kses_post( $content );
			} else {
				?>
				<table>
					<thead>
						<tr><th><?php esc_html_e( 'Size', 'aura-fashion' ); ?></th><th><?php esc_html_e( 'Bust (cm)', 'aura-fashion' ); ?></th><th><?php esc_html_e( 'Waist (cm)', 'aura-fashion' ); ?></th><th><?php esc_html_e( 'Hips (cm)', 'aura-fashion' ); ?></th></tr>
					</thead>
					<tbody>
						<tr><td>XS</td><td>80-84</td><td>60-64</td><td>86-90</td></tr>
						<tr><td>S</td><td>84-88</td><td>64-68</td><td>90-94</td></tr>
						<tr><td>M</td><td>88-92</td><td>68-72</td><td>94-98</td></tr>
						<tr><td>L</td><td>92-96</td><td>72-76</td><td>98-102</td></tr>
						<tr><td>XL</td><td>96-100</td><td>76-80</td><td>102-106</td></tr>
						<tr><td>XXL</td><td>100-104</td><td>80-84</td><td>106-110</td></tr>
					</tbody>
				</table>
				<p class="mt-2" style="color:var(--aura-gray);font-size:0.85rem;"><?php esc_html_e( 'Measurements are approximate. For best fit, refer to product-specific notes.', 'aura-fashion' ); ?></p>
				<?php
			}
			?>
		</div>
	</div>

	<div id="aura-cart-notice" class="added-to-cart-notice" role="status" aria-live="polite">
		<svg width="20" height="20" fill="var(--aura-gold)" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
		<span class="notice-text"></span>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn-sm btn-primary"><?php esc_html_e( 'View Cart', 'aura-fashion' ); ?></a>
	</div>
	<?php
}
add_action( 'wp_footer', 'aura_quick_view_modal' );


/* --------------------------------------------------------------------------
   Cross-sells on cart
   -------------------------------------------------------------------------- */
add_filter( 'woocommerce_cross_sells_total', function() { return 4; } );
add_filter( 'woocommerce_cross_sells_columns', function() { return 4; } );

function aura_cart_cross_sells_styles() {
	if ( ! is_cart() ) {
		return;
	}
	echo '<style>.cross-sells{margin-top:48px;padding-top:40px;border-top:1px solid rgba(255,255,255,.08)}.cross-sells>h2{text-align:center;margin-bottom:28px;font-family:var(--aura-font-heading)}</style>';
}
add_action( 'wp_head', 'aura_cart_cross_sells_styles' );

/* --------------------------------------------------------------------------
   Require login to view Shop / products, add to cart, and checkout.
   Guests are redirected to My Account with a clear message.
   -------------------------------------------------------------------------- */

/**
 * Redirect guests away from store pages until they log in or register.
 * Covers: Shop, product categories/tags, single products, cart, checkout.
 * Does NOT block: home, blog, account, static pages, AJAX, admin.
 */
function aura_require_login_for_store() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( is_user_logged_in() ) {
		return;
	}
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}

	$need_login = false;
	$reason     = 'shop';

	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		$need_login = true;
		$reason     = 'shop';
	} elseif ( is_product() ) {
		$need_login = true;
		$reason     = 'shop';
	} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
		$need_login = true;
		$reason     = 'cart';
	} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
		/* Allow order-received / order-pay for email links */
		if ( ! is_wc_endpoint_url( 'order-received' ) && ! is_wc_endpoint_url( 'order-pay' ) ) {
			$need_login = true;
			$reason     = 'checkout';
		}
	}

	if ( ! $need_login ) {
		return;
	}

	$return_url = ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' );
	$return_url .= isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	$account_url = wc_get_page_permalink( 'myaccount' );
	$redirect    = add_query_arg(
		array(
			'redirect_to' => rawurlencode( $return_url ? $return_url : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ) ),
			'aura_login'  => '1',
			'aura_shop'   => ( 'shop' === $reason ) ? '1' : '0',
			'aura_cart'   => ( 'cart' === $reason || 'checkout' === $reason ) ? '1' : '0',
		),
		$account_url
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'template_redirect', 'aura_require_login_for_store', 5 );

/**
 * Block add-to-cart for guests (AJAX and non-AJAX safety net).
 */
function aura_require_login_for_add_to_cart( $passed, $product_id, $quantity = 1, $variation_id = 0, $variations = array() ) {
	if ( is_user_logged_in() ) {
		return $passed;
	}
	if ( ! $passed ) {
		return $passed;
	}
	$account_url = wc_get_page_permalink( 'myaccount' );
	$redirect    = add_query_arg(
		array(
			'redirect_to' => rawurlencode( wp_get_referer() ? wp_get_referer() : get_permalink( $product_id ) ),
			'aura_login'  => '1',
			'aura_cart'   => '1',
		),
		$account_url
	);
	wc_add_notice(
		sprintf(
			/* translators: %s: login/register URL */
			__( 'Please <a href="%s">create an account or log in</a> to add items to your cart.', 'aura-fashion' ),
			esc_url( $redirect )
		),
		'notice'
	);
	return false;
}
add_filter( 'woocommerce_add_to_cart_validation', 'aura_require_login_for_add_to_cart', 5, 5 );

/**
 * Disable WooCommerce guest checkout option from theme side.
 */
add_filter( 'pre_option_woocommerce_enable_guest_checkout', function () {
	return 'no';
} );
add_filter( 'pre_option_woocommerce_enable_checkout_login_reminder', function () {
	return 'yes';
} );

/**
 * After login, send user back to checkout if they came from there.
 */
function aura_login_redirect_to_checkout( $redirect, $user ) {
	if ( ! empty( $_GET['redirect_to'] ) ) {
		$url = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
		if ( $url ) {
			return $url;
		}
	}
	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		$url = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) );
		if ( $url ) {
			return $url;
		}
	}
	return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'aura_login_redirect_to_checkout', 10, 2 );
add_filter( 'woocommerce_registration_redirect', function ( $redirect ) {
	if ( ! empty( $_GET['redirect_to'] ) ) {
		$url = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
		if ( $url ) {
			return $url;
		}
	}
	return $redirect;
} );

/**
 * Notice on My Account when redirected from shop / cart / checkout.
 */
function aura_login_required_notice() {
	if ( empty( $_GET['aura_login'] ) ) {
		return;
	}
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}
	if ( ! empty( $_GET['aura_shop'] ) && '1' === $_GET['aura_shop'] ) {
		wc_print_notice(
			__( 'Please log in or create an account to view our products and shop. After signing in you will return to the shop.', 'aura-fashion' ),
			'notice'
		);
		return;
	}
	if ( ! empty( $_GET['aura_cart'] ) && '1' === $_GET['aura_cart'] ) {
		wc_print_notice(
			__( 'Please log in or create an account to continue. After signing in you can complete your order.', 'aura-fashion' ),
			'notice'
		);
		return;
	}
	wc_print_notice(
		__( 'Please log in or create an account to continue.', 'aura-fashion' ),
		'notice'
	);
}
add_action( 'woocommerce_before_customer_login_form', 'aura_login_required_notice' );

/* --------------------------------------------------------------------------
   Ensure customer registration (Sign-up) is enabled
   -------------------------------------------------------------------------- */
add_filter( 'pre_option_woocommerce_enable_myaccount_registration', function () {
	return 'yes';
} );
add_filter( 'pre_option_woocommerce_registration_generate_username', function () {
	return 'yes';
} );
add_filter( 'pre_option_woocommerce_registration_generate_password', function () {
	return 'no'; /* user chooses password */
} );

/**
 * Show a clear heading on the register column.
 */
function aura_register_form_start() {
	echo '<p class="aura-register-intro" style="text-align:center;color:#a3a3a3;font-size:0.9rem;margin-bottom:20px;">' .
		esc_html__( 'Create a free account to order, track shipments, and save your wishlist.', 'aura-fashion' ) .
		'</p>';
}
add_action( 'woocommerce_register_form_start', 'aura_register_form_start' );

function aura_login_form_start() {
	echo '<p class="aura-login-intro" style="text-align:center;color:#a3a3a3;font-size:0.9rem;margin-bottom:20px;">' .
		esc_html__( 'Welcome back. Sign in to continue shopping and manage your orders.', 'aura-fashion' ) .
		'</p>';
}
add_action( 'woocommerce_login_form_start', 'aura_login_form_start' );
