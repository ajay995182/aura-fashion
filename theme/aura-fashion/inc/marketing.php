<?php
/**
 * Marketing & Conversion Features
 * – Newsletter → Mailchimp / Klaviyo / Brevo
 * – Exit-intent / Discount popup
 * – Free shipping progress bar
 * – Per-product sale countdown
 * – Instagram feed
 * – Review photo support
 * – Abandoned cart & Loyalty hooks/docs
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   1. REAL EMAIL MARKETING – Mailchimp / Klaviyo / Brevo
   ========================================================================== */

/**
 * Send subscriber to the configured ESP (Email Service Provider).
 * Falls back to local storage if no API key is set.
 */
function aura_subscribe_to_esp( $email, $source = 'newsletter' ) {
	$provider = get_theme_mod( 'aura_esp_provider', 'local' );
	$api_key  = get_theme_mod( 'aura_esp_api_key', '' );
	$list_id  = get_theme_mod( 'aura_esp_list_id', '' );

	// Always store locally as backup
	$subscribers = get_option( 'aura_newsletter_emails', array() );
	if ( ! in_array( $email, $subscribers, true ) ) {
		$subscribers[] = $email;
		update_option( 'aura_newsletter_emails', $subscribers, false );
	}

	if ( empty( $api_key ) || $provider === 'local' ) {
		return array( 'success' => true, 'message' => __( 'Thank you for subscribing!', 'aura-fashion' ), 'provider' => 'local' );
	}

	$result = array( 'success' => false, 'message' => __( 'Subscription failed. Please try again.', 'aura-fashion' ) );

	switch ( $provider ) {
		case 'mailchimp':
			$result = aura_mailchimp_subscribe( $email, $api_key, $list_id );
			break;
		case 'klaviyo':
			$result = aura_klaviyo_subscribe( $email, $api_key, $list_id );
			break;
		case 'brevo':
			$result = aura_brevo_subscribe( $email, $api_key, $list_id );
			break;
	}

	return $result;
}

function aura_mailchimp_subscribe( $email, $api_key, $list_id ) {
	// API key format: xxxxx-us21  → datacenter is after the dash
	$parts = explode( '-', $api_key );
	$dc    = isset( $parts[1] ) ? $parts[1] : 'us1';
	$url   = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members";

	$body = wp_json_encode( array(
		'email_address' => $email,
		'status'        => 'subscribed',
		'tags'          => array( 'aura-theme' ),
	) );

	$response = wp_remote_post( $url, array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
			'Content-Type'  => 'application/json',
		),
		'body'    => $body,
		'timeout' => 15,
	) );

	if ( is_wp_error( $response ) ) {
		return array( 'success' => false, 'message' => $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code === 200 || $code === 201 ) {
		return array( 'success' => true, 'message' => __( 'Thank you for subscribing!', 'aura-fashion' ), 'provider' => 'mailchimp' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $data['title'] ) && stripos( $data['title'], 'Member Exists' ) !== false ) {
		return array( 'success' => true, 'message' => __( 'You are already subscribed!', 'aura-fashion' ), 'provider' => 'mailchimp' );
	}

	return array( 'success' => false, 'message' => isset( $data['detail'] ) ? $data['detail'] : __( 'Mailchimp error.', 'aura-fashion' ) );
}

function aura_klaviyo_subscribe( $email, $api_key, $list_id ) {
	// Klaviyo Lists API (revision 2024-10-15)
	$url = 'https://a.klaviyo.com/api/profile-subscription-bulk-create-jobs/';

	$body = wp_json_encode( array(
		'data' => array(
			'type' => 'profile-subscription-bulk-create-job',
			'attributes' => array(
				'profiles' => array(
					'data' => array(
						array(
							'type'       => 'profile',
							'attributes' => array(
								'email' => $email,
								'subscriptions' => array(
									'email' => array(
										'marketing' => array( 'consent' => 'SUBSCRIBED' ),
									),
								),
							),
						),
					),
				),
			),
			'relationships' => array(
				'list' => array(
					'data' => array(
						'type' => 'list',
						'id'   => $list_id,
					),
				),
			),
		),
	) );

	$response = wp_remote_post( $url, array(
		'headers' => array(
			'Authorization' => 'Klaviyo-API-Key ' . $api_key,
			'Content-Type'  => 'application/json',
			'revision'      => '2024-10-15',
		),
		'body'    => $body,
		'timeout' => 15,
	) );

	if ( is_wp_error( $response ) ) {
		return array( 'success' => false, 'message' => $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code >= 200 && $code < 300 ) {
		return array( 'success' => true, 'message' => __( 'Thank you for subscribing!', 'aura-fashion' ), 'provider' => 'klaviyo' );
	}

	return array( 'success' => false, 'message' => __( 'Klaviyo error. Check API key & List ID.', 'aura-fashion' ) );
}

function aura_brevo_subscribe( $email, $api_key, $list_id ) {
	$url = 'https://api.brevo.com/v3/contacts';

	$body = wp_json_encode( array(
		'email'         => $email,
		'listIds'       => array( intval( $list_id ) ),
		'updateEnabled' => true,
	) );

	$response = wp_remote_post( $url, array(
		'headers' => array(
			'api-key'      => $api_key,
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		),
		'body'    => $body,
		'timeout' => 15,
	) );

	if ( is_wp_error( $response ) ) {
		return array( 'success' => false, 'message' => $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code === 201 || $code === 204 ) {
		return array( 'success' => true, 'message' => __( 'Thank you for subscribing!', 'aura-fashion' ), 'provider' => 'brevo' );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $data['code'] ) && $data['code'] === 'duplicate_parameter' ) {
		return array( 'success' => true, 'message' => __( 'You are already subscribed!', 'aura-fashion' ), 'provider' => 'brevo' );
	}

	return array( 'success' => false, 'message' => isset( $data['message'] ) ? $data['message'] : __( 'Brevo error.', 'aura-fashion' ) );
}

/* ==========================================================================
   2. EXIT-INTENT / DISCOUNT POPUP
   ========================================================================== */

function aura_exit_intent_markup() {
	if ( is_admin() || ! get_theme_mod( 'aura_exit_popup_enable', true ) ) {
		return;
	}

	// Don't show if cookie already set
	if ( isset( $_COOKIE['aura_exit_shown'] ) ) {
		return;
	}

	$title    = get_theme_mod( 'aura_exit_popup_title', __( 'Wait! Don\'t leave empty-handed', 'aura-fashion' ) );
	$desc     = get_theme_mod( 'aura_exit_popup_desc', __( 'Get 10% off your first order. Enter your email below.', 'aura-fashion' ) );
	$code     = get_theme_mod( 'aura_exit_popup_code', 'WELCOME10' );
	$btn_text = get_theme_mod( 'aura_exit_popup_btn', __( 'Get My Discount', 'aura-fashion' ) );
	?>
	<div id="aura-exit-popup" class="aura-exit-popup" role="dialog" aria-modal="true" aria-labelledby="aura-exit-title" hidden>
		<div class="aura-exit-backdrop"></div>
		<div class="aura-exit-content">
			<button type="button" class="aura-exit-close" aria-label="<?php esc_attr_e( 'Close', 'aura-fashion' ); ?>">&times;</button>
			<div class="aura-exit-icon">
				<svg width="48" height="48" fill="none" stroke="var(--aura-gold)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			</div>
			<h2 id="aura-exit-title"><?php echo esc_html( $title ); ?></h2>
			<p><?php echo esc_html( $desc ); ?></p>
			<?php if ( $code ) : ?>
				<div class="aura-exit-code">
					<span><?php esc_html_e( 'Use code:', 'aura-fashion' ); ?></span>
					<strong id="aura-discount-code"><?php echo esc_html( $code ); ?></strong>
					<button type="button" class="aura-copy-code" data-code="<?php echo esc_attr( $code ); ?>" aria-label="<?php esc_attr_e( 'Copy code', 'aura-fashion' ); ?>">
						<svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
					</button>
				</div>
			<?php endif; ?>
			<form class="aura-exit-form newsletter-form" action="#" method="post">
				<input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'aura-fashion' ); ?>" required aria-label="<?php esc_attr_e( 'Email', 'aura-fashion' ); ?>">
				<button type="submit" class="btn btn-primary"><?php echo esc_html( $btn_text ); ?></button>
			</form>
			<div class="newsletter-message" role="status"></div>
			<button type="button" class="aura-exit-dismiss"><?php esc_html_e( 'No thanks', 'aura-fashion' ); ?></button>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'aura_exit_intent_markup', 5 );

/* ==========================================================================
   3. FREE SHIPPING PROGRESS BAR
   ========================================================================== */

function aura_free_shipping_threshold() {
	// Prefer Aura Options panel, then Customizer
	if ( function_exists( 'aura_get_option' ) ) {
		$v = aura_get_option( 'free_shipping_threshold', null );
		if ( $v !== null && $v !== '' ) {
			return floatval( $v );
		}
	}
	return floatval( get_theme_mod( 'aura_free_shipping_amount', 100 ) );
}

function aura_get_free_shipping_progress() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return null;
	}

	$threshold = aura_free_shipping_threshold();
	if ( $threshold <= 0 ) {
		return null;
	}

	$subtotal = floatval( WC()->cart->get_subtotal() );
	$remaining = max( 0, $threshold - $subtotal );
	$percent   = min( 100, ( $subtotal / $threshold ) * 100 );

	return array(
		'threshold' => $threshold,
		'subtotal'  => $subtotal,
		'remaining' => $remaining,
		'percent'   => round( $percent, 1 ),
		'unlocked'  => $remaining <= 0,
	);
}

function aura_free_shipping_bar_html() {
	$data = aura_get_free_shipping_progress();
	if ( ! $data ) {
		return '';
	}

	ob_start();
	?>
	<div class="aura-shipping-bar" role="status" aria-live="polite">
		<?php if ( $data['unlocked'] ) : ?>
			<p class="shipping-unlocked">
				<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
				<?php esc_html_e( 'Congratulations! You\'ve unlocked FREE shipping.', 'aura-fashion' ); ?>
			</p>
		<?php else : ?>
			<p class="shipping-progress-text">
				<?php
				printf(
					/* translators: %s = remaining amount */
					esc_html__( 'Add %s more to get FREE shipping!', 'aura-fashion' ),
					'<strong>' . wp_kses_post( wc_price( $data['remaining'] ) ) . '</strong>'
				);
				?>
			</p>
		<?php endif; ?>
		<div class="shipping-progress-track">
			<div class="shipping-progress-fill" style="width:<?php echo esc_attr( $data['percent'] ); ?>%;"></div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

// Cart page
function aura_shipping_bar_cart() {
	echo aura_free_shipping_bar_html(); // phpcs:ignore
}
add_action( 'woocommerce_before_cart_table', 'aura_shipping_bar_cart' );

// Mini-cart / fragments
function aura_shipping_bar_fragment( $fragments ) {
	$fragments['div.aura-shipping-bar'] = aura_free_shipping_bar_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'aura_shipping_bar_fragment' );

// Also show in checkout
add_action( 'woocommerce_before_checkout_form', 'aura_shipping_bar_cart', 5 );

/* ==========================================================================
   4. PER-PRODUCT SALE COUNTDOWN TIMER
   ========================================================================== */

function aura_sale_end_date_field() {
	woocommerce_wp_text_input( array(
		'id'          => '_aura_sale_end',
		'label'       => __( 'Sale End Date (Countdown)', 'aura-fashion' ),
		'description' => __( 'Format: YYYY-MM-DD HH:MM. Shows a live countdown on the product when on sale.', 'aura-fashion' ),
		'desc_tip'    => true,
		'placeholder' => '2026-12-31 23:59',
		'type'        => 'text',
	) );
}
add_action( 'woocommerce_product_options_general_product_data', 'aura_sale_end_date_field' );

function aura_save_sale_end_date( $post_id ) {
	$val = isset( $_POST['_aura_sale_end'] ) ? sanitize_text_field( wp_unslash( $_POST['_aura_sale_end'] ) ) : '';
	update_post_meta( $post_id, '_aura_sale_end', $val );
}
add_action( 'woocommerce_process_product_meta', 'aura_save_sale_end_date' );

function aura_product_sale_countdown() {
	global $product;
	if ( ! $product || ! $product->is_on_sale() ) {
		return;
	}

	$end = get_post_meta( $product->get_id(), '_aura_sale_end', true );
	if ( empty( $end ) ) {
		// Fallback: WooCommerce scheduled sale end
		$date_on_sale_to = $product->get_date_on_sale_to();
		if ( $date_on_sale_to ) {
			$end = $date_on_sale_to->date( 'Y-m-d H:i:s' );
		}
	}
	if ( empty( $end ) ) {
		return;
	}

	$end_ts = strtotime( $end );
	if ( ! $end_ts || $end_ts < time() ) {
		return;
	}
	?>
	<div class="aura-product-countdown" data-end="<?php echo esc_attr( date( 'c', $end_ts ) ); ?>" role="timer" aria-label="<?php esc_attr_e( 'Sale ends in', 'aura-fashion' ); ?>">
		<span class="countdown-label"><?php esc_html_e( 'Sale ends in:', 'aura-fashion' ); ?></span>
		<div class="countdown-units">
			<span><strong data-d>00</strong>d</span>
			<span><strong data-h>00</strong>h</span>
			<span><strong data-m>00</strong>m</span>
			<span><strong data-s>00</strong>s</span>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'aura_product_sale_countdown', 12 );
add_action( 'woocommerce_after_shop_loop_item_title', 'aura_product_sale_countdown', 12 );

/* ==========================================================================
   5. ABANDONED CART – hooks + recommended plugins notice
   ========================================================================== */

/**
 * Store cart email when guest enters it (for recovery plugins / custom use).
 */
function aura_capture_checkout_email() {
	if ( ! empty( $_POST['billing_email'] ) && is_email( $_POST['billing_email'] ) ) {
		$email = sanitize_email( wp_unslash( $_POST['billing_email'] ) );
		WC()->session->set( 'aura_checkout_email', $email );
		// Also push to ESP if configured (optional double-opt for recovery)
		if ( get_theme_mod( 'aura_esp_capture_checkout', false ) ) {
			aura_subscribe_to_esp( $email, 'checkout' );
		}
	}
}
add_action( 'woocommerce_checkout_process', 'aura_capture_checkout_email' );

/* ==========================================================================
   6. REFERRAL / LOYALTY – basic points skeleton + plugin recommendation
   ========================================================================== */

function aura_loyalty_points_on_order( $order_id ) {
	if ( ! get_theme_mod( 'aura_loyalty_enable', false ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_user_id() <= 0 ) {
		return;
	}
	$user_id = $order->get_user_id();
	$points  = intval( $order->get_total() ); // 1 point per currency unit
	$current = intval( get_user_meta( $user_id, 'aura_loyalty_points', true ) );
	update_user_meta( $user_id, 'aura_loyalty_points', $current + $points );
}
add_action( 'woocommerce_order_status_completed', 'aura_loyalty_points_on_order' );

function aura_show_loyalty_points() {
	if ( ! is_user_logged_in() || ! get_theme_mod( 'aura_loyalty_enable', false ) ) {
		return;
	}
	$points = intval( get_user_meta( get_current_user_id(), 'aura_loyalty_points', true ) );
	printf(
		'<p class="aura-loyalty-points">%s <strong>%d</strong></p>',
		esc_html__( 'Your loyalty points:', 'aura-fashion' ),
		$points
	);
}
add_action( 'woocommerce_account_dashboard', 'aura_show_loyalty_points', 5 );

/* ==========================================================================
   7. INSTAGRAM FEED
   ========================================================================== */

function aura_instagram_feed_section() {
	$enable = get_theme_mod( 'aura_instagram_enable', false );
	$token  = get_theme_mod( 'aura_instagram_token', '' );
	$embed  = get_theme_mod( 'aura_instagram_embed', '' );

	if ( ! $enable ) {
		return;
	}
	?>
	<section class="section aura-instagram">
		<div class="aura-container">
			<div class="section-header">
				<h2><?php echo esc_html( get_theme_mod( 'aura_instagram_title', __( 'Follow Us @AuraFashion', 'aura-fashion' ) ) ); ?></h2>
				<p><?php esc_html_e( 'Shop the look · Tag us for a chance to be featured', 'aura-fashion' ); ?></p>
			</div>
			<?php if ( $embed ) : ?>
				<div class="instagram-embed-wrap">
					<?php echo $embed; // phpcs:ignore – admin-controlled embed code ?>
				</div>
			<?php elseif ( $token ) : ?>
				<div id="aura-ig-feed" class="instagram-grid" data-token="<?php echo esc_attr( $token ); ?>">
					<div class="products-loading active"><div class="spinner"></div></div>
				</div>
			<?php else : ?>
				<div class="instagram-placeholder-grid">
					<?php for ( $i = 0; $i < 6; $i++ ) : ?>
						<div class="ig-placeholder">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="width:30%;opacity:0.3;"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="18" cy="6" r="1.5" fill="currentColor"/></svg>
						</div>
					<?php endfor; ?>
				</div>
				<p class="text-center" style="color:var(--aura-gray);font-size:0.85rem;margin-top:16px;">
					<?php esc_html_e( 'Add an Instagram Access Token or Embed code in Appearance → Customize → Instagram.', 'aura-fashion' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/* ==========================================================================
   8. CUSTOMER REVIEWS WITH PHOTOS
   ========================================================================== */

/**
 * Allow image uploads on product reviews.
 */
function aura_review_photo_field() {
	if ( ! is_product() ) {
		return;
	}
	?>
	<p class="comment-form-photo">
		<label for="aura_review_photo"><?php esc_html_e( 'Add a photo (optional)', 'aura-fashion' ); ?></label>
		<input type="file" id="aura_review_photo" name="aura_review_photo" accept="image/jpeg,image/png,image/webp" />
	</p>
	<?php
}
add_action( 'comment_form_logged_in_after', 'aura_review_photo_field' );
add_action( 'comment_form_after_fields', 'aura_review_photo_field' );

function aura_save_review_photo( $comment_id ) {
	if ( ! isset( $_FILES['aura_review_photo'] ) || empty( $_FILES['aura_review_photo']['tmp_name'] ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attachment_id = media_handle_upload( 'aura_review_photo', 0 );
	if ( ! is_wp_error( $attachment_id ) ) {
		add_comment_meta( $comment_id, 'aura_review_photo', $attachment_id );
	}
}
add_action( 'comment_post', 'aura_save_review_photo' );

function aura_display_review_photo( $comment_text, $comment ) {
	if ( ! is_product() ) {
		return $comment_text;
	}
	$photo_id = get_comment_meta( $comment->comment_ID, 'aura_review_photo', true );
	if ( $photo_id ) {
		$img = wp_get_attachment_image( $photo_id, 'medium', false, array( 'class' => 'aura-review-photo', 'loading' => 'lazy' ) );
		$comment_text .= '<div class="aura-review-photo-wrap">' . $img . '</div>';
	}
	return $comment_text;
}
add_filter( 'comment_text', 'aura_display_review_photo', 10, 2 );

// Make review form support enctype
function aura_review_form_enctype( $args ) {
	$args['class_form'] = ( isset( $args['class_form'] ) ? $args['class_form'] . ' ' : '' ) . 'aura-review-form';
	return $args;
}
add_filter( 'woocommerce_product_review_comment_form_args', 'aura_review_form_enctype' );

function aura_review_form_multipart() {
	if ( is_product() ) {
		echo ' enctype="multipart/form-data"';
	}
}
// JS will set enctype on the form as a reliable fallback
