<?php
/**
 * AJAX Handlers – Newsletter, Wishlist, Quick View, Shop Filters
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Newsletter
   -------------------------------------------------------------------------- */
function aura_newsletter_subscribe() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'aura-fashion' ) ) );
	}

	// Real ESP integration (Mailchimp / Klaviyo / Brevo) + local backup
	$result = aura_subscribe_to_esp( $email, 'newsletter' );

	if ( ! empty( $result['success'] ) ) {
		wp_send_json_success( array( 'message' => $result['message'] ) );
	}

	wp_send_json_error( array( 'message' => isset( $result['message'] ) ? $result['message'] : __( 'Subscription failed.', 'aura-fashion' ) ) );
}
add_action( 'wp_ajax_aura_newsletter', 'aura_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_aura_newsletter', 'aura_newsletter_subscribe' );

/* --------------------------------------------------------------------------
   Quick View – return product HTML
   -------------------------------------------------------------------------- */
function aura_quick_view() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error();
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error();
	}

	ob_start();
	?>
	<div class="quick-view-image">
		<?php
		$img_id = $product->get_image_id();
		if ( $img_id ) {
			echo wp_get_attachment_image( $img_id, 'aura-product-large' );
		} else {
			echo aura_placeholder_img(); // phpcs:ignore
		}
		?>
	</div>
	<div class="quick-view-details">
		<h2><?php echo esc_html( $product->get_name() ); ?></h2>
		<div class="quick-view-price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>
		<?php
		$stock = $product->get_stock_quantity();
		if ( $product->managing_stock() && $stock !== null && $stock <= 10 ) {
			$class = $stock <= 3 ? 'critical' : '';
			printf(
				'<div class="stock-countdown %s">%s</div>',
				esc_attr( $class ),
				sprintf( esc_html__( 'Only %s left!', 'aura-fashion' ), intval( $stock ) )
			);
		}
		?>
		<div class="quick-view-desc"><?php echo wp_kses_post( $product->get_short_description() ); ?></div>

		<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
			<form class="cart" method="post" enctype="multipart/form-data">
				<div class="quantity-wrap">
					<div class="quantity">
						<button type="button" class="qty-minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'aura-fashion' ); ?>">−</button>
						<input type="number" name="quantity" value="1" min="1" max="<?php echo esc_attr( $product->get_max_purchase_quantity() ); ?>" class="qty" />
						<button type="button" class="qty-plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'aura-fashion' ); ?>">+</button>
					</div>
				</div>
				<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" class="btn btn-primary btn-block ajax_add_to_cart" data-product_id="<?php echo esc_attr( $product_id ); ?>">
					<?php esc_html_e( 'Add to Cart', 'aura-fashion' ); ?>
				</button>
			</form>
		<?php elseif ( $product->is_type( 'variable' ) ) : ?>
			<p><a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn-primary btn-block"><?php esc_html_e( 'Select Options', 'aura-fashion' ); ?></a></p>
		<?php else : ?>
			<p><a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn-outline btn-block"><?php esc_html_e( 'View Product', 'aura-fashion' ); ?></a></p>
		<?php endif; ?>

		<p class="mt-2"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'View full details →', 'aura-fashion' ); ?></a></p>
	</div>
	<?php
	$html = ob_get_clean();
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_aura_quick_view', 'aura_quick_view' );
add_action( 'wp_ajax_nopriv_aura_quick_view', 'aura_quick_view' );

/* --------------------------------------------------------------------------
   Wishlist – load products by IDs
   -------------------------------------------------------------------------- */
function aura_load_wishlist() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
	$ids = array_filter( $ids );

	if ( empty( $ids ) ) {
		wp_send_json_success( array( 'html' => '', 'count' => 0 ) );
	}

	$q = new WP_Query( array(
		'post_type'      => 'product',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 50,
	) );

	ob_start();
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();
	}
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html, 'count' => $q->post_count ) );
}
add_action( 'wp_ajax_aura_load_wishlist', 'aura_load_wishlist' );
add_action( 'wp_ajax_nopriv_aura_load_wishlist', 'aura_load_wishlist' );

/* --------------------------------------------------------------------------
   AJAX Shop Filters
   -------------------------------------------------------------------------- */
function aura_filter_products() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	$paged     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$orderby   = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'menu_order';
	$min_price = isset( $_POST['min_price'] ) ? floatval( $_POST['min_price'] ) : 0;
	$max_price = isset( $_POST['max_price'] ) ? floatval( $_POST['max_price'] ) : 999999;
	$categories = isset( $_POST['categories'] ) ? array_map( 'sanitize_title', (array) $_POST['categories'] ) : array();
	$attributes = isset( $_POST['attributes'] ) ? (array) $_POST['attributes'] : array();
	$on_sale   = ! empty( $_POST['on_sale'] );

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'paged'          => $paged,
	);

	switch ( $orderby ) {
		case 'popularity':
			$args['meta_key'] = 'total_sales';
			$args['orderby']  = 'meta_value_num';
			break;
		case 'rating':
			$args['meta_key'] = '_wc_average_rating';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'date':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'price':
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'price-desc':
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		default:
			$args['orderby'] = 'menu_order title';
			$args['order']   = 'ASC';
	}

	$tax_query  = array( 'relation' => 'AND' );
	$meta_query = array( 'relation' => 'AND' );

	if ( ! empty( $categories ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $categories,
		);
	}

	foreach ( $attributes as $tax => $terms ) {
		$tax   = sanitize_title( $tax );
		$terms = array_map( 'sanitize_title', (array) $terms );
		if ( empty( $terms ) ) {
			continue;
		}
		$tax_query[] = array(
			'taxonomy' => $tax,
			'field'    => 'slug',
			'terms'    => $terms,
		);
	}

	$meta_query[] = array(
		'key'     => '_price',
		'value'   => array( $min_price, $max_price ),
		'compare' => 'BETWEEN',
		'type'    => 'DECIMAL',
	);

	if ( $on_sale ) {
		$sale_ids = wc_get_product_ids_on_sale();
		$args['post__in'] = ! empty( $sale_ids ) ? $sale_ids : array( 0 );
	}

	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}
	$args['meta_query'] = $meta_query;

	$q = new WP_Query( $args );

	ob_start();
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();
	} else {
		echo '<p class="text-center" style="grid-column:1/-1;padding:40px;">' . esc_html__( 'No products found matching your filters.', 'aura-fashion' ) . '</p>';
	}
	$html = ob_get_clean();

	wp_send_json_success( array(
		'html'     => $html,
		'found'    => $q->found_posts,
		'max_page' => $q->max_num_pages,
	) );
}
add_action( 'wp_ajax_aura_filter_products', 'aura_filter_products' );
add_action( 'wp_ajax_nopriv_aura_filter_products', 'aura_filter_products' );

/* --------------------------------------------------------------------------
   AJAX Add to Cart (enhanced response)
   Guests must log in / register before adding to cart.
   -------------------------------------------------------------------------- */
function aura_ajax_add_to_cart() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
		$redirect    = add_query_arg(
			array(
				'redirect_to' => rawurlencode( wp_get_referer() ? wp_get_referer() : home_url( '/' ) ),
				'aura_login'  => '1',
				'aura_cart'   => '1',
			),
			$account_url
		);
		wp_send_json_error( array(
			'message'    => __( 'Please create an account or log in to add items to your cart.', 'aura-fashion' ),
			'login_url'  => $redirect,
			'require_login' => true,
		) );
	}

	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$variation    = isset( $_POST['variation'] ) ? (array) $_POST['variation'] : array();

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product.', 'aura-fashion' ) ) );
	}

	$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation );
	if ( ! $passed ) {
		wp_send_json_error( array( 'message' => __( 'Could not add to cart.', 'aura-fashion' ) ) );
	}

	$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

	if ( $cart_item_key ) {
		wp_send_json_success( array(
			'message'    => __( 'Added to cart!', 'aura-fashion' ),
			'cart_count' => WC()->cart->get_cart_contents_count(),
			'cart_total' => WC()->cart->get_cart_total(),
		) );
	}

	wp_send_json_error( array( 'message' => __( 'Could not add to cart.', 'aura-fashion' ) ) );
}
add_action( 'wp_ajax_aura_add_to_cart', 'aura_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_aura_add_to_cart', 'aura_ajax_add_to_cart' );
