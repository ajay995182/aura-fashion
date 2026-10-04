<?php
/**
 * User Account & Personalization
 * – Wishlist sync to database (logged-in users)
 * – Order tracking page
 * – Buy Again / Recently Ordered
 * – Personal Size Profile
 * – Saved addresses (WooCommerce native + enhancements)
 * – One-click reorder
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   1. WISHLIST SYNC – localStorage ↔ user meta when logged in
   ========================================================================== */

/**
 * AJAX: Save wishlist to user meta (logged-in) or return guest status.
 */
function aura_sync_wishlist() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
	$ids = array_values( array_unique( array_filter( $ids ) ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), 'aura_wishlist', $ids );
		wp_send_json_success( array(
			'synced' => true,
			'ids'    => $ids,
			'count'  => count( $ids ),
		) );
	}

	// Guest – just acknowledge (localStorage is source of truth)
	wp_send_json_success( array(
		'synced' => false,
		'ids'    => $ids,
		'count'  => count( $ids ),
		'guest'  => true,
	) );
}
add_action( 'wp_ajax_aura_sync_wishlist', 'aura_sync_wishlist' );
add_action( 'wp_ajax_nopriv_aura_sync_wishlist', 'aura_sync_wishlist' );

/**
 * AJAX: Load wishlist – merge user meta + posted localStorage IDs for logged-in users.
 */
function aura_get_wishlist_ids() {
	check_ajax_referer( 'aura_nonce', 'nonce' );

	$local_ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
	$local_ids = array_values( array_unique( array_filter( $local_ids ) ) );

	if ( is_user_logged_in() ) {
		$saved = get_user_meta( get_current_user_id(), 'aura_wishlist', true );
		$saved = is_array( $saved ) ? array_map( 'absint', $saved ) : array();

		// Merge: localStorage wins for any new items, then persist
		$merged = array_values( array_unique( array_merge( $saved, $local_ids ) ) );
		update_user_meta( get_current_user_id(), 'aura_wishlist', $merged );

		wp_send_json_success( array(
			'ids'    => $merged,
			'synced' => true,
		) );
	}

	wp_send_json_success( array(
		'ids'    => $local_ids,
		'synced' => false,
		'guest'  => true,
	) );
}
add_action( 'wp_ajax_aura_get_wishlist', 'aura_get_wishlist_ids' );
add_action( 'wp_ajax_nopriv_aura_get_wishlist', 'aura_get_wishlist_ids' );

/**
 * On login: merge any guest localStorage wishlist (sent via JS after login).
 */
function aura_localize_user_state() {
	wp_localize_script( 'aura-main', 'auraUser', array(
		'isLoggedIn' => is_user_logged_in(),
		'userId'     => get_current_user_id(),
	) );
}
add_action( 'wp_enqueue_scripts', 'aura_localize_user_state', 25 );

/* ==========================================================================
   2. ORDER TRACKING PAGE
   ========================================================================== */

/**
 * Shortcode: [aura_order_tracking]
 * Nice standalone tracking form + results.
 */
function aura_order_tracking_shortcode() {
	ob_start();

	$order_id    = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$order_email = isset( $_GET['order_email'] ) ? sanitize_email( wp_unslash( $_GET['order_email'] ) ) : '';
	$found       = null;
	$error       = '';

	if ( $order_id && $order_email ) {
		$order = wc_get_order( $order_id );
		if ( $order && strtolower( $order->get_billing_email() ) === strtolower( $order_email ) ) {
			$found = $order;
		} else {
			$error = __( 'Order not found. Please check your order number and email.', 'aura-fashion' );
		}
	}
	?>
	<div class="aura-tracking-wrap">
		<?php if ( ! $found ) : ?>
			<div class="aura-tracking-form-card">
				<h2><?php esc_html_e( 'Track Your Order', 'aura-fashion' ); ?></h2>
				<p><?php esc_html_e( 'Enter your order number and billing email to see the latest status.', 'aura-fashion' ); ?></p>
				<?php if ( $error ) : ?>
					<div class="woocommerce-error" role="alert"><?php echo esc_html( $error ); ?></div>
				<?php endif; ?>
				<form method="get" class="aura-tracking-form" action="">
					<p>
						<label for="aura-order-id"><?php esc_html_e( 'Order Number', 'aura-fashion' ); ?></label>
						<input type="text" id="aura-order-id" name="order_id" value="<?php echo esc_attr( $order_id ?: '' ); ?>" required placeholder="1234">
					</p>
					<p>
						<label for="aura-order-email"><?php esc_html_e( 'Billing Email', 'aura-fashion' ); ?></label>
						<input type="email" id="aura-order-email" name="order_email" value="<?php echo esc_attr( $order_email ); ?>" required placeholder="you@example.com">
					</p>
					<button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Track Order', 'aura-fashion' ); ?></button>
				</form>
			</div>
		<?php else : ?>
			<div class="aura-tracking-result">
				<div class="tracking-header">
					<h2><?php printf( esc_html__( 'Order #%s', 'aura-fashion' ), esc_html( $found->get_order_number() ) ); ?></h2>
					<span class="tracking-status status-<?php echo esc_attr( $found->get_status() ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $found->get_status() ) ); ?>
					</span>
				</div>

				<!-- Status timeline -->
				<div class="tracking-timeline">
					<?php
					$steps = array(
						'pending'    => __( 'Order Placed', 'aura-fashion' ),
						'processing' => __( 'Processing', 'aura-fashion' ),
						'on-hold'    => __( 'On Hold', 'aura-fashion' ),
						'completed'  => __( 'Completed', 'aura-fashion' ),
						'shipped'    => __( 'Shipped', 'aura-fashion' ), // custom if used
					);
					$status = $found->get_status();
					$completed_statuses = array( 'pending', 'processing', 'completed' );
					// Simple progress
					$progress_map = array(
						'pending'    => 1,
						'on-hold'    => 1,
						'processing' => 2,
						'completed'  => 4,
						'cancelled'  => 0,
						'refunded'   => 0,
						'failed'     => 0,
					);
					$current_step = isset( $progress_map[ $status ] ) ? $progress_map[ $status ] : 1;
					$labels = array(
						1 => __( 'Order Placed', 'aura-fashion' ),
						2 => __( 'Processing', 'aura-fashion' ),
						3 => __( 'Shipped', 'aura-fashion' ),
						4 => __( 'Delivered', 'aura-fashion' ),
					);
					foreach ( $labels as $step => $label ) :
						$done = $current_step >= $step;
						$active = $current_step === $step;
						?>
						<div class="timeline-step <?php echo $done ? 'done' : ''; ?> <?php echo $active ? 'active' : ''; ?>">
							<div class="timeline-dot"></div>
							<span><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="tracking-meta">
					<p><strong><?php esc_html_e( 'Date:', 'aura-fashion' ); ?></strong> <?php echo esc_html( wc_format_datetime( $found->get_date_created() ) ); ?></p>
					<p><strong><?php esc_html_e( 'Total:', 'aura-fashion' ); ?></strong> <?php echo wp_kses_post( $found->get_formatted_order_total() ); ?></p>
					<?php if ( $found->get_billing_first_name() ) : ?>
						<p><strong><?php esc_html_e( 'Ship to:', 'aura-fashion' ); ?></strong>
							<?php echo esc_html( $found->get_formatted_shipping_full_name() ?: $found->get_formatted_billing_full_name() ); ?>
						</p>
					<?php endif; ?>
				</div>

				<h3><?php esc_html_e( 'Items', 'aura-fashion' ); ?></h3>
				<ul class="tracking-items">
					<?php foreach ( $found->get_items() as $item ) :
						$product = $item->get_product();
						?>
						<li>
							<?php if ( $product ) : ?>
								<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
									<?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore ?>
								</a>
							<?php endif; ?>
							<div>
								<strong><?php echo esc_html( $item->get_name() ); ?></strong>
								<span>× <?php echo esc_html( $item->get_quantity() ); ?></span>
							</div>
							<span><?php echo wp_kses_post( $found->get_formatted_line_subtotal( $item ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="tracking-actions">
					<a href="?order_id=&order_email=" class="btn btn-outline btn-sm"><?php esc_html_e( 'Track Another Order', 'aura-fashion' ); ?></a>
					<?php if ( is_user_logged_in() && $found->get_user_id() === get_current_user_id() ) : ?>
						<a href="<?php echo esc_url( $found->get_view_order_url() ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( 'View Full Details', 'aura-fashion' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aura_order_tracking', 'aura_order_tracking_shortcode' );

/**
 * Add "Order Tracking" endpoint / link on My Account.
 */
function aura_add_tracking_endpoint() {
	add_rewrite_endpoint( 'order-tracking', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'aura_add_tracking_endpoint' );

function aura_tracking_query_vars( $vars ) {
	$vars[] = 'order-tracking';
	return $vars;
}
add_filter( 'query_vars', 'aura_tracking_query_vars' );

function aura_tracking_menu_item( $items ) {
	// Insert before customer-logout
	$new = array();
	foreach ( $items as $key => $label ) {
		if ( 'customer-logout' === $key ) {
			$new['order-tracking'] = __( 'Order Tracking', 'aura-fashion' );
		}
		$new[ $key ] = $label;
	}
	return $new;
}
add_filter( 'woocommerce_account_menu_items', 'aura_tracking_menu_item' );

/**
 * My Account → Order Tracking:
 * Show the customer's own orders with status + button to view details.
 * Guest form remains available via shortcode on the public Order Tracking page.
 */
function aura_tracking_endpoint_content() {
	if ( ! is_user_logged_in() ) {
		echo do_shortcode( '[aura_order_tracking]' );
		return;
	}

	$customer_id = get_current_user_id();
	$orders      = wc_get_orders(
		array(
			'customer_id' => $customer_id,
			'limit'       => 20,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'return'      => 'objects',
		)
	);

	echo '<div class="aura-my-orders-tracking">';
	echo '<h2>' . esc_html__( 'Your Orders', 'aura-fashion' ) . '</h2>';
	echo '<p class="aura-tracking-intro">' . esc_html__( 'Track the status of your recent orders. Click View status for full details.', 'aura-fashion' ) . '</p>';

	if ( empty( $orders ) ) {
		echo '<div class="woocommerce-info">' . esc_html__( 'You have not placed any orders yet.', 'aura-fashion' ) . '</div>';
		echo '<p><a class="button" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Browse products', 'aura-fashion' ) . '</a></p>';
		echo '</div>';
		return;
	}

	echo '<div class="aura-orders-track-list">';
	foreach ( $orders as $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			continue;
		}
		$status      = $order->get_status();
		$status_name = wc_get_order_status_name( $status );
		$view_url    = $order->get_view_order_url();
		$date        = $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) : '';
		$total       = $order->get_formatted_order_total();
		$item_count  = $order->get_item_count();

		echo '<div class="aura-order-track-card status-' . esc_attr( $status ) . '">';
		echo '<div class="aura-order-track-main">';
		echo '<div class="aura-order-track-id"><strong>#' . esc_html( $order->get_order_number() ) . '</strong>';
		if ( $date ) {
			echo ' <span class="aura-order-track-date">' . esc_html( $date ) . '</span>';
		}
		echo '</div>';
		echo '<div class="aura-order-track-meta">';
		echo '<span class="aura-order-track-status status-' . esc_attr( $status ) . '">' . esc_html( $status_name ) . '</span>';
		echo '<span class="aura-order-track-total">' . wp_kses_post( $total ) . '</span>';
		echo '<span class="aura-order-track-items">' . esc_html( sprintf( _n( '%d item', '%d items', $item_count, 'aura-fashion' ), $item_count ) ) . '</span>';
		echo '</div>';
		echo '</div>';
		echo '<div class="aura-order-track-actions">';
		echo '<a class="button aura-btn-view-status" href="' . esc_url( $view_url ) . '">' . esc_html__( 'View status', 'aura-fashion' ) . '</a>';
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';

	/* Optional: guest-style lookup for another order (e.g. gift) */
	echo '<details class="aura-track-other" style="margin-top:28px;">';
	echo '<summary style="cursor:pointer;color:#c9a227;">' . esc_html__( 'Track a different order (order number + email)', 'aura-fashion' ) . '</summary>';
	echo '<div style="margin-top:16px;">' . do_shortcode( '[aura_order_tracking]' ) . '</div>';
	echo '</details>';

	echo '</div>';
}
add_action( 'woocommerce_account_order-tracking_endpoint', 'aura_tracking_endpoint_content' );

/* ==========================================================================
   3. BUY AGAIN / RECENTLY ORDERED
   ========================================================================== */

function aura_buy_again_section() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$customer_orders = wc_get_orders( array(
		'customer_id' => get_current_user_id(),
		'status'      => array( 'wc-completed', 'wc-processing' ),
		'limit'       => 10,
		'orderby'     => 'date',
		'order'       => 'DESC',
	) );

	if ( empty( $customer_orders ) ) {
		return;
	}

	// Collect unique product IDs from recent orders
	$product_ids = array();
	foreach ( $customer_orders as $order ) {
		foreach ( $order->get_items() as $item ) {
			$pid = $item->get_product_id();
			if ( $pid && ! in_array( $pid, $product_ids, true ) ) {
				$product_ids[] = $pid;
			}
		}
		if ( count( $product_ids ) >= 8 ) {
			break;
		}
	}

	if ( empty( $product_ids ) ) {
		return;
	}

	$q = new WP_Query( array(
		'post_type'      => 'product',
		'post__in'       => $product_ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 8,
	) );

	if ( ! $q->have_posts() ) {
		return;
	}
	?>
	<section class="aura-buy-again section">
		<div class="aura-container">
			<div class="section-header">
				<h2><?php esc_html_e( 'Buy Again', 'aura-fashion' ); ?></h2>
				<p><?php esc_html_e( 'Items you ordered before – reorder in one click.', 'aura-fashion' ); ?></p>
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

// Show on My Account dashboard
add_action( 'woocommerce_account_dashboard', 'aura_buy_again_section', 20 );

// Also shortcode for any page
add_shortcode( 'aura_buy_again', function() {
	ob_start();
	aura_buy_again_section();
	return ob_get_clean();
} );

/**
 * One-click reorder from order view.
 */
function aura_reorder_button( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	$url = wp_nonce_url(
		add_query_arg( array(
			'aura_reorder' => $order->get_id(),
		), wc_get_cart_url() ),
		'aura_reorder_' . $order->get_id()
	);
	printf(
		'<a href="%s" class="btn btn-primary btn-sm aura-reorder-btn">%s</a>',
		esc_url( $url ),
		esc_html__( 'Buy Again', 'aura-fashion' )
	);
}
add_action( 'woocommerce_order_details_after_order_table', 'aura_reorder_button', 15 );

function aura_process_reorder() {
	if ( empty( $_GET['aura_reorder'] ) ) {
		return;
	}
	$order_id = absint( $_GET['aura_reorder'] );
	if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'aura_reorder_' . $order_id ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// Security: only owner or guest with matching session
	if ( is_user_logged_in() && $order->get_user_id() !== get_current_user_id() ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( ! $product || ! $product->is_purchasable() ) {
			continue;
		}
		$qty = $item->get_quantity();
		$variation_id = $item->get_variation_id();
		$variation = array();
		if ( $variation_id ) {
			foreach ( $item->get_meta_data() as $meta ) {
				if ( taxonomy_is_product_attribute( $meta->key ) || strpos( $meta->key, 'pa_' ) === 0 ) {
					$variation[ $meta->key ] = $meta->value;
				}
			}
		}
		WC()->cart->add_to_cart( $item->get_product_id(), $qty, $variation_id, $variation );
	}

	wc_add_notice( __( 'Items from your previous order have been added to the cart.', 'aura-fashion' ), 'success' );
	wp_safe_redirect( wc_get_cart_url() );
	exit;
}
add_action( 'template_redirect', 'aura_process_reorder' );

/* ==========================================================================
   4. PERSONAL SIZE PROFILE
   ========================================================================== */

function aura_size_profile_fields() {
	$user_id = get_current_user_id();
	$sizes = array(
		'aura_size_tops'   => __( 'Tops / Dresses', 'aura-fashion' ),
		'aura_size_bottoms'=> __( 'Bottoms', 'aura-fashion' ),
		'aura_size_shoes'  => __( 'Shoes', 'aura-fashion' ),
		'aura_size_notes'  => __( 'Notes (fit preferences)', 'aura-fashion' ),
	);
	$options = array( '', 'XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL' );
	$shoe_options = array( '', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46' );
	?>
	<h3><?php esc_html_e( 'My Size Profile', 'aura-fashion' ); ?></h3>
	<p class="description"><?php esc_html_e( 'Save your usual sizes so we can highlight better fits and speed up shopping.', 'aura-fashion' ); ?></p>
	<div class="aura-size-profile">
		<?php foreach ( $sizes as $key => $label ) :
			$val = get_user_meta( $user_id, $key, true );
			$is_notes = ( $key === 'aura_size_notes' );
			$is_shoes = ( $key === 'aura_size_shoes' );
			?>
			<p class="form-row">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
				<?php if ( $is_notes ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" rows="3"><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
					<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
						<?php
						$opts = $is_shoes ? $shoe_options : $options;
						foreach ( $opts as $o ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $o ),
								selected( $val, $o, false ),
								$o === '' ? esc_html__( '— Select —', 'aura-fashion' ) : esc_html( $o )
							);
						}
						?>
					</select>
				<?php endif; ?>
			</p>
		<?php endforeach; ?>
	</div>
	<?php
}
add_action( 'woocommerce_edit_account_form', 'aura_size_profile_fields' );

function aura_save_size_profile( $user_id ) {
	$keys = array( 'aura_size_tops', 'aura_size_bottoms', 'aura_size_shoes', 'aura_size_notes' );
	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_user_meta( $user_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
}
add_action( 'woocommerce_save_account_details', 'aura_save_size_profile' );

/**
 * Show size badge hint on product if user has matching size in profile.
 */
function aura_size_match_hint() {
	if ( ! is_user_logged_in() || ! is_product() ) {
		return;
	}
	global $product;
	$tops = get_user_meta( get_current_user_id(), 'aura_size_tops', true );
	$bottoms = get_user_meta( get_current_user_id(), 'aura_size_bottoms', true );
	if ( ! $tops && ! $bottoms ) {
		return;
	}

	$attrs = $product->get_attributes();
	$has_size = false;
	foreach ( $attrs as $attr ) {
		if ( stripos( $attr->get_name(), 'size' ) !== false ) {
			$has_size = true;
			break;
		}
	}
	if ( ! $has_size ) {
		return;
	}

	$hint = $tops ?: $bottoms;
	printf(
		'<p class="aura-size-hint"><svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg> %s <strong>%s</strong></p>',
		esc_html__( 'Your usual size:', 'aura-fashion' ),
		esc_html( $hint )
	);
}
add_action( 'woocommerce_single_product_summary', 'aura_size_match_hint', 25 );

/* ==========================================================================
   5. SAVED ADDRESSES & ONE-CLICK CHECKOUT ENHANCEMENTS
   (WooCommerce already supports multiple addresses via My Account.
    We improve the UI and add a “Use default address” quick action.)
   ========================================================================== */

function aura_address_book_styles() {
	// Extra clarity on address cards
	?>
	<style>
	.woocommerce-Address {
		background: var(--aura-black-soft);
		border: 1px solid rgba(255,255,255,0.08);
		border-radius: var(--aura-radius-lg);
		padding: 24px;
		margin-bottom: 20px;
	}
	.woocommerce-Address-title h3 { color: var(--aura-gold); margin-bottom: 12px; }
	.woocommerce-Address address { color: var(--aura-gray-light); line-height: 1.7; font-style: normal; }
	.aura-size-profile select,
	.aura-size-profile textarea {
		width: 100%;
		max-width: 320px;
		background: var(--aura-black-soft);
		border: 1px solid rgba(255,255,255,0.12);
		color: var(--aura-white);
		padding: 10px 14px;
		border-radius: var(--aura-radius);
	}
	.aura-size-hint {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: rgba(201,162,39,0.12);
		color: var(--aura-gold);
		padding: 8px 14px;
		border-radius: var(--aura-radius);
		font-size: 0.9rem;
		margin: 12px 0;
	}
	</style>
	<?php
}
add_action( 'wp_head', 'aura_address_book_styles' );

/**
 * Ensure default address is pre-selected at checkout when customer has one.
 * (WooCommerce does this natively; we just make the notice clearer.)
 */
function aura_checkout_address_notice() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$customer = new WC_Customer( get_current_user_id() );
	if ( $customer->get_billing_address_1() ) {
		echo '<p class="aura-saved-address-notice" style="color:var(--aura-gold);font-size:0.9rem;margin-bottom:16px;">';
		echo esc_html__( 'Your saved address has been loaded. You can edit it below or change it in My Account → Addresses.', 'aura-fashion' );
		echo '</p>';
	}
}
add_action( 'woocommerce_before_checkout_billing_form', 'aura_checkout_address_notice' );


/**
 * Flush rewrite rules when theme is activated (for order-tracking endpoint).
 */
function aura_flush_rewrites() {
	aura_add_tracking_endpoint();
	flush_rewrite_rules();
}
add_action( "after_switch_theme", "aura_flush_rewrites" );
