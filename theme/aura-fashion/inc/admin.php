<?php
/**
 * Admin & Store Management – v1.7.0
 * – Theme options panel (Appearance → Aura Options)
 * – Custom product badges (New, Bestseller, Limited, Eco)
 * – Bulk import guidance page
 * – Analytics integration helpers + plugin recommendations
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   1. THEME OPTIONS PANEL
   ========================================================================== */

function aura_admin_menu() {
	add_menu_page(
		__( 'Aura Options', 'aura-fashion' ),
		__( 'Aura Options', 'aura-fashion' ),
		'manage_options',
		'aura-options',
		'aura_options_page',
		'dashicons-art',
		59
	);
	add_submenu_page(
		'aura-options',
		__( 'General', 'aura-fashion' ),
		__( 'General', 'aura-fashion' ),
		'manage_options',
		'aura-options',
		'aura_options_page'
	);
	add_submenu_page(
		'aura-options',
		__( 'Badges', 'aura-fashion' ),
		__( 'Product Badges', 'aura-fashion' ),
		'manage_options',
		'aura-badges',
		'aura_badges_settings_page'
	);
	add_submenu_page(
		'aura-options',
		__( 'Import Guide', 'aura-fashion' ),
		__( 'Bulk Import Guide', 'aura-fashion' ),
		'manage_options',
		'aura-import-guide',
		'aura_import_guide_page'
	);
	add_submenu_page(
		'aura-options',
		__( 'Analytics', 'aura-fashion' ),
		__( 'Analytics', 'aura-fashion' ),
		'manage_options',
		'aura-analytics',
		'aura_analytics_page'
	);
}
add_action( 'admin_menu', 'aura_admin_menu' );

function aura_register_settings() {
	register_setting( 'aura_options_group', 'aura_options', array(
		'type'              => 'array',
		'sanitize_callback' => 'aura_sanitize_options',
		'default'           => aura_default_options(),
	) );
}
add_action( 'admin_init', 'aura_register_settings' );

function aura_default_options() {
	return array(
		'free_shipping_threshold' => 100,
		'exit_popup_enable'       => 1,
		'exit_popup_code'         => 'WELCOME10',
		'mega_menu'               => 1,
		'loyalty_enable'          => 0,
		'badge_new_days'          => 30,
		'badge_bestseller_sales'  => 10,
		'ga4_id'                  => '',
		'fb_pixel_id'             => '',
		'store_notice'            => '',
		'store_notice_enable'     => 0,
	);
}

function aura_sanitize_options( $input ) {
	$out = aura_default_options();
	if ( ! is_array( $input ) ) {
		return $out;
	}
	$out['free_shipping_threshold'] = absint( $input['free_shipping_threshold'] ?? 100 );
	$out['exit_popup_enable']       = ! empty( $input['exit_popup_enable'] ) ? 1 : 0;
	$out['exit_popup_code']         = sanitize_text_field( $input['exit_popup_code'] ?? '' );
	$out['mega_menu']               = ! empty( $input['mega_menu'] ) ? 1 : 0;
	$out['loyalty_enable']          = ! empty( $input['loyalty_enable'] ) ? 1 : 0;
	$out['badge_new_days']          = max( 1, absint( $input['badge_new_days'] ?? 30 ) );
	$out['badge_bestseller_sales']  = max( 1, absint( $input['badge_bestseller_sales'] ?? 10 ) );
	$out['ga4_id']                  = sanitize_text_field( $input['ga4_id'] ?? '' );
	$out['fb_pixel_id']             = sanitize_text_field( $input['fb_pixel_id'] ?? '' );
	$out['store_notice']            = sanitize_text_field( $input['store_notice'] ?? '' );
	$out['store_notice_enable']     = ! empty( $input['store_notice_enable'] ) ? 1 : 0;
	return $out;
}

function aura_get_option( $key, $default = null ) {
	$opts = get_option( 'aura_options', aura_default_options() );
	if ( ! is_array( $opts ) ) {
		$opts = aura_default_options();
	}
	if ( array_key_exists( $key, $opts ) ) {
		return $opts[ $key ];
	}
	$defs = aura_default_options();
	return $default !== null ? $default : ( $defs[ $key ] ?? null );
}

function aura_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = wp_parse_args( get_option( 'aura_options', array() ), aura_default_options() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aura Options', 'aura-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Store-wide settings. Colors, hero text, and logo remain in Appearance → Customize.', 'aura-fashion' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'aura_options_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Store notice bar', 'aura-fashion' ); ?></th>
					<td>
						<label><input type="checkbox" name="aura_options[store_notice_enable]" value="1" <?php checked( $o['store_notice_enable'], 1 ); ?>> <?php esc_html_e( 'Show top notice bar', 'aura-fashion' ); ?></label><br>
						<input type="text" class="large-text" name="aura_options[store_notice]" value="<?php echo esc_attr( $o['store_notice'] ); ?>" placeholder="<?php esc_attr_e( 'Free shipping over $100 · New season now live', 'aura-fashion' ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Free shipping threshold', 'aura-fashion' ); ?></th>
					<td>
						<input type="number" name="aura_options[free_shipping_threshold]" value="<?php echo esc_attr( $o['free_shipping_threshold'] ); ?>" min="0" step="1">
						<p class="description"><?php esc_html_e( 'Also syncs with the cart progress bar (overrides Customizer if set here).', 'aura-fashion' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Exit-intent popup', 'aura-fashion' ); ?></th>
					<td>
						<label><input type="checkbox" name="aura_options[exit_popup_enable]" value="1" <?php checked( $o['exit_popup_enable'], 1 ); ?>> <?php esc_html_e( 'Enable', 'aura-fashion' ); ?></label><br>
						<label><?php esc_html_e( 'Discount code', 'aura-fashion' ); ?>
							<input type="text" name="aura_options[exit_popup_code]" value="<?php echo esc_attr( $o['exit_popup_code'] ); ?>">
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Mega menu', 'aura-fashion' ); ?></th>
					<td>
						<label><input type="checkbox" name="aura_options[mega_menu]" value="1" <?php checked( $o['mega_menu'], 1 ); ?>> <?php esc_html_e( 'Enable category mega menu', 'aura-fashion' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Loyalty points', 'aura-fashion' ); ?></th>
					<td>
						<label><input type="checkbox" name="aura_options[loyalty_enable]" value="1" <?php checked( $o['loyalty_enable'], 1 ); ?>> <?php esc_html_e( 'Enable basic points (1 per currency unit)', 'aura-fashion' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Google Analytics 4 ID', 'aura-fashion' ); ?></th>
					<td>
						<input type="text" name="aura_options[ga4_id]" value="<?php echo esc_attr( $o['ga4_id'] ); ?>" placeholder="G-XXXXXXXXXX" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Meta (Facebook) Pixel ID', 'aura-fashion' ); ?></th>
					<td>
						<input type="text" name="aura_options[fb_pixel_id]" value="<?php echo esc_attr( $o['fb_pixel_id'] ); ?>" placeholder="1234567890" class="regular-text">
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save options', 'aura-fashion' ) ); ?>
		</form>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'themes.php?page=aura-demo-import' ) ); ?>"><?php esc_html_e( 'Demo Import', 'aura-fashion' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Theme Customizer', 'aura-fashion' ); ?></a>
		</p>
	</div>
	<?php
}

/* Sync key options to theme_mod so existing frontend code keeps working */
function aura_sync_options_to_mods( $option ) {
	if ( ! is_array( $option ) ) {
		return;
	}
	if ( isset( $option['free_shipping_threshold'] ) ) {
		set_theme_mod( 'aura_free_shipping_amount', absint( $option['free_shipping_threshold'] ) );
	}
	if ( isset( $option['exit_popup_enable'] ) ) {
		set_theme_mod( 'aura_exit_popup_enable', (bool) $option['exit_popup_enable'] );
	}
	if ( isset( $option['exit_popup_code'] ) ) {
		set_theme_mod( 'aura_exit_popup_code', sanitize_text_field( $option['exit_popup_code'] ) );
	}
	if ( isset( $option['mega_menu'] ) ) {
		set_theme_mod( 'aura_mega_menu', (bool) $option['mega_menu'] );
	}
	if ( isset( $option['loyalty_enable'] ) ) {
		set_theme_mod( 'aura_loyalty_enable', (bool) $option['loyalty_enable'] );
	}
}
add_action( 'update_option_aura_options', 'aura_sync_options_to_mods' );

/* Store notice bar on front */
function aura_store_notice_bar() {
	if ( ! aura_get_option( 'store_notice_enable' ) ) {
		return;
	}
	$text = aura_get_option( 'store_notice' );
	if ( ! $text ) {
		return;
	}
	echo '<div class="aura-store-notice" role="status"><div class="aura-container">' . esc_html( $text ) . '</div></div>';
}
add_action( 'wp_body_open', 'aura_store_notice_bar', 5 );

/* ==========================================================================
   2. CUSTOM PRODUCT BADGES – New, Bestseller, Limited, Eco
   ========================================================================== */

function aura_badge_terms() {
	return array(
		'new'        => __( 'New', 'aura-fashion' ),
		'bestseller' => __( 'Bestseller', 'aura-fashion' ),
		'limited'    => __( 'Limited', 'aura-fashion' ),
		'eco'        => __( 'Eco', 'aura-fashion' ),
	);
}

/** Product edit metabox */
function aura_badges_meta_box() {
	add_meta_box(
		'aura_product_badges',
		__( 'Aura Badges', 'aura-fashion' ),
		'aura_badges_meta_box_html',
		'product',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'aura_badges_meta_box' );

function aura_badges_meta_box_html( $post ) {
	wp_nonce_field( 'aura_badges_save', 'aura_badges_nonce' );
	$saved = get_post_meta( $post->ID, '_aura_badges', true );
	$saved = is_array( $saved ) ? $saved : array();
	foreach ( aura_badge_terms() as $key => $label ) {
		printf(
			'<label style="display:block;margin-bottom:8px;"><input type="checkbox" name="aura_badges[]" value="%s" %s> %s</label>',
			esc_attr( $key ),
			checked( in_array( $key, $saved, true ), true, false ),
			esc_html( $label )
		);
	}
	echo '<p class="description">' . esc_html__( '“New” can also auto-apply from publish date (see Product Badges settings). “Bestseller” can auto-apply from total sales.', 'aura-fashion' ) . '</p>';
}

function aura_badges_save( $post_id ) {
	if ( ! isset( $_POST['aura_badges_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aura_badges_nonce'] ) ), 'aura_badges_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$badges = isset( $_POST['aura_badges'] ) ? array_map( 'sanitize_key', (array) $_POST['aura_badges'] ) : array();
	$allowed = array_keys( aura_badge_terms() );
	$badges = array_values( array_intersect( $badges, $allowed ) );
	update_post_meta( $post_id, '_aura_badges', $badges );
}
add_action( 'save_post_product', 'aura_badges_save' );

function aura_badges_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['aura_badges_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aura_badges_settings_nonce'] ) ), 'aura_badges_settings' ) ) {
		$opts = get_option( 'aura_options', aura_default_options() );
		$opts['badge_new_days'] = max( 1, absint( $_POST['badge_new_days'] ?? 30 ) );
		$opts['badge_bestseller_sales'] = max( 1, absint( $_POST['badge_bestseller_sales'] ?? 10 ) );
		update_option( 'aura_options', $opts );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Badge settings saved.', 'aura-fashion' ) . '</p></div>';
	}
	$days = aura_get_option( 'badge_new_days', 30 );
	$sales = aura_get_option( 'badge_bestseller_sales', 10 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Product Badges', 'aura-fashion' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'aura_badges_settings', 'aura_badges_settings_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Auto “New” badge', 'aura-fashion' ); ?></th>
					<td>
						<?php esc_html_e( 'Products published within', 'aura-fashion' ); ?>
						<input type="number" name="badge_new_days" value="<?php echo esc_attr( $days ); ?>" min="1" style="width:80px">
						<?php esc_html_e( 'days show the New badge automatically (unless overridden).', 'aura-fashion' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Auto “Bestseller” badge', 'aura-fashion' ); ?></th>
					<td>
						<?php esc_html_e( 'Products with at least', 'aura-fashion' ); ?>
						<input type="number" name="badge_bestseller_sales" value="<?php echo esc_attr( $sales ); ?>" min="1" style="width:80px">
						<?php esc_html_e( 'total sales show Bestseller automatically.', 'aura-fashion' ); ?>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<p><?php esc_html_e( 'Manual badges (Limited, Eco, or any) are set on each product under “Aura Badges”.', 'aura-fashion' ); ?></p>
	</div>
	<?php
}

/**
 * Resolve badges for a product (manual + auto).
 */
function aura_get_product_badges( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}
	$id = $product->get_id();
	$manual = get_post_meta( $id, '_aura_badges', true );
	$manual = is_array( $manual ) ? $manual : array();
	$labels = aura_badge_terms();
	$out = array();

	foreach ( $manual as $key ) {
		if ( isset( $labels[ $key ] ) ) {
			$out[ $key ] = $labels[ $key ];
		}
	}

	// Auto New
	$days = intval( aura_get_option( 'badge_new_days', 30 ) );
	$created = get_post_time( 'U', true, $id );
	if ( $created && ( time() - $created ) < ( $days * DAY_IN_SECONDS ) && ! isset( $out['new'] ) ) {
		$out['new'] = $labels['new'];
	}

	// Auto Bestseller
	$threshold = intval( aura_get_option( 'badge_bestseller_sales', 10 ) );
	$sales = intval( get_post_meta( $id, 'total_sales', true ) );
	if ( $sales >= $threshold && ! isset( $out['bestseller'] ) ) {
		$out['bestseller'] = $labels['bestseller'];
	}

	return $out;
}

function aura_render_product_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$badges = aura_get_product_badges( $product );
	if ( empty( $badges ) ) {
		return;
	}
	echo '<div class="aura-badges">';
	foreach ( $badges as $key => $label ) {
		printf( '<span class="aura-badge aura-badge--%s">%s</span>', esc_attr( $key ), esc_html( $label ) );
	}
	echo '</div>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'aura_render_product_badges', 9 );
add_action( 'woocommerce_single_product_summary', 'aura_render_product_badges', 4 );

/* ==========================================================================
   3. BULK PRODUCT IMPORT GUIDE
   ========================================================================== */

function aura_import_guide_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Bulk Product Import Guide', 'aura-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Fastest ways to build a large catalog for Aura Fashion.', 'aura-fashion' ); ?></p>

		<h2>1. <?php esc_html_e( 'WooCommerce built-in CSV import (recommended)', 'aura-fashion' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Go to Products → Import', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Download the sample CSV from WooCommerce', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Fill columns: Type, SKU, Name, Published, Is featured?, Short description, Description, Regular price, Sale price, Categories, Images, Attribute 1 name, Attribute 1 value(s), etc.', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'For variable products: one parent row (type=variable) + variation rows (type=variation) with Parent SKU', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Upload CSV → map columns → Run importer', 'aura-fashion' ); ?></li>
		</ol>
		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_importer' ) ); ?>"><?php esc_html_e( 'Open Product Importer', 'aura-fashion' ); ?></a></p>

		<h2>2. <?php esc_html_e( 'CSV tips for Aura badges & fashion attributes', 'aura-fashion' ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'Categories: use Women, Men, Accessories, Sale (or create during import)', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Attributes: Color, Size (names containing “color”/“size” get swatches automatically)', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Images: full URLs in the Images column (comma-separated for gallery)', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'After import: open products in bulk and set Aura Badges (Limited / Eco), or rely on auto New / Bestseller', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Meta _aura_product_video and _aura_sale_end can be set with a CSV plugin that supports custom meta', 'aura-fashion' ); ?></li>
		</ul>

		<h2>3. <?php esc_html_e( 'Useful free plugins', 'aura-fashion' ); ?></h2>
		<ul>
			<li><strong>Product Import Export for WooCommerce</strong> – <?php esc_html_e( 'advanced CSV with custom fields', 'aura-fashion' ); ?></li>
			<li><strong>WP All Import</strong> – <?php esc_html_e( 'XML/CSV from suppliers', 'aura-fashion' ); ?></li>
		</ul>

		<h2>4. <?php esc_html_e( 'Demo content', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'For a quick sample catalog, use Appearance → Aura Demo Import (creates 8 products + categories + pages).', 'aura-fashion' ); ?></p>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'themes.php?page=aura-demo-import' ) ); ?>"><?php esc_html_e( 'Demo Import', 'aura-fashion' ); ?></a></p>
	</div>
	<?php
}

/* ==========================================================================
   4. ANALYTICS – GA4 + Meta Pixel inject + plugin guidance
   ========================================================================== */

function aura_analytics_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Analytics', 'aura-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Paste IDs under Aura Options → General, or use a dedicated plugin for advanced e‑commerce tracking.', 'aura-fashion' ); ?></p>

		<h2><?php esc_html_e( 'Built-in: GA4 & Meta Pixel', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'Set Google Analytics 4 Measurement ID (G-…) and/or Meta Pixel ID on the Aura Options page. Scripts load in the site footer when IDs are present.', 'aura-fashion' ); ?></p>
		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=aura-options' ) ); ?>"><?php esc_html_e( 'Open Aura Options', 'aura-fashion' ); ?></a></p>

		<h2><?php esc_html_e( 'Recommended plugins', 'aura-fashion' ); ?></h2>
		<ul>
			<li><strong>Google Analytics for WooCommerce</strong> (official) – <?php esc_html_e( 'full purchase funnel', 'aura-fashion' ); ?></li>
			<li><strong>Facebook for WooCommerce</strong> – <?php esc_html_e( 'Pixel + Catalog', 'aura-fashion' ); ?></li>
			<li><strong>MonsterInsights</strong> or <strong>Site Kit by Google</strong> – <?php esc_html_e( 'dashboard inside WP', 'aura-fashion' ); ?></li>
		</ul>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=google+analytics+woocommerce&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Search GA plugins', 'aura-fashion' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=facebook+for+woocommerce&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Search Facebook for WooCommerce', 'aura-fashion' ); ?></a>
		</p>

		<h2><?php esc_html_e( 'Abandoned cart recovery', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'Aura captures the billing email during checkout for recovery tools. Install a free recovery plugin to send reminder emails automatically:', 'aura-fashion' ); ?></p>
		<ul>
			<li><strong>WooCommerce Cart Abandonment Recovery</strong> (CartFlows)</li>
			<li><strong>Retainful</strong></li>
			<li><strong>Abandoned Cart Lite for WooCommerce</strong></li>
		</ul>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=cart+abandonment+recovery&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Search recovery plugins', 'aura-fashion' ); ?></a></p>
	</div>
	<?php
}

function aura_output_analytics_scripts() {
	$ga4 = aura_get_option( 'ga4_id' );
	$fb  = aura_get_option( 'fb_pixel_id' );

	if ( $ga4 ) {
		$ga4 = preg_replace( '/[^A-Z0-9\-]/i', '', $ga4 );
		?>
		<!-- Google Analytics 4 -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo esc_js( $ga4 ); ?>');
		</script>
		<?php
	}

	if ( $fb ) {
		$fb = preg_replace( '/[^0-9]/', '', $fb );
		?>
		<!-- Meta Pixel -->
		<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '<?php echo esc_js( $fb ); ?>');
		fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $fb ); ?>&ev=PageView&noscript=1"/></noscript>
		<?php
	}
}
add_action( 'wp_head', 'aura_output_analytics_scripts', 99 );

/** Purchase event for GA4 when order-received */
function aura_ga4_purchase_event() {
	$ga4 = aura_get_option( 'ga4_id' );
	if ( ! $ga4 || ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
		return;
	}
	global $wp;
	$order_id = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : 0;
	$order = $order_id ? wc_get_order( $order_id ) : false;
	if ( ! $order ) {
		return;
	}
	$items = array();
	foreach ( $order->get_items() as $item ) {
		$items[] = array(
			'item_id'   => (string) $item->get_product_id(),
			'item_name' => $item->get_name(),
			'quantity'  => $item->get_quantity(),
			'price'     => floatval( $order->get_item_total( $item, false ) ),
		);
	}
	?>
	<script>
	if (typeof gtag === 'function') {
		gtag('event', 'purchase', {
			transaction_id: '<?php echo esc_js( $order->get_order_number() ); ?>',
			value: <?php echo floatval( $order->get_total() ); ?>,
			currency: '<?php echo esc_js( $order->get_currency() ); ?>',
			items: <?php echo wp_json_encode( $items ); ?>
		});
	}
	if (typeof fbq === 'function') {
		fbq('track', 'Purchase', {
			value: <?php echo floatval( $order->get_total() ); ?>,
			currency: '<?php echo esc_js( $order->get_currency() ); ?>'
		});
	}
	</script>
	<?php
}
add_action( 'wp_footer', 'aura_ga4_purchase_event', 20 );
