<?php
/**
 * One-click Demo Import – rich sample pages, menu, categories, products,
 * attributes, variable products, coupons, lookbooks, journal posts, badges.
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin page under Appearance
 */
function aura_demo_import_menu() {
	add_theme_page(
		__( 'Aura Demo Import', 'aura-fashion' ),
		__( 'Aura Demo Import', 'aura-fashion' ),
		'manage_options',
		'aura-demo-import',
		'aura_demo_import_page'
	);
}
add_action( 'admin_menu', 'aura_demo_import_menu' );

function aura_demo_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$done = get_option( 'aura_demo_imported' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aura Fashion – Demo Import', 'aura-fashion' ); ?></h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Demo content was already imported. You can run it again to refresh sample pages (products are only created if missing).', 'aura-fashion' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Creates: sample pages (Wishlist, About, Contact, FAQ, Order Tracking), product categories & attributes, 16+ demo products (simple + variable with size/color), coupons, lookbook entries, Style Journal posts, Primary menu, and basic theme options. WooCommerce must be active.', 'aura-fashion' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'aura_demo_import', 'aura_demo_nonce' ); ?>
			<p>
				<button type="submit" name="aura_run_demo_import" class="button button-primary button-hero">
					<?php esc_html_e( 'Import Full Demo Content', 'aura-fashion' ); ?>
				</button>
			</p>
		</form>
		<?php
		if ( isset( $_POST['aura_run_demo_import'] ) && check_admin_referer( 'aura_demo_import', 'aura_demo_nonce' ) ) {
			$result = aura_run_demo_import();
			echo '<div class="notice notice-success"><p>' . esc_html( $result ) . '</p></div>';
		}
		?>
		<hr>
		<h2><?php esc_html_e( 'After import', 'aura-fashion' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Settings → Permalinks → Save (required for shop & custom post types)', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Appearance → Menus → confirm Primary Menu is assigned', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'WooCommerce → Settings → confirm Shop / Cart / Checkout / My Account pages', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Appearance → Customize → set logo, hero text, colors', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Aura Options → set free-shipping threshold, exit-popup code, analytics IDs', 'aura-fashion' ); ?></li>
			<li><?php esc_html_e( 'Create a coupon code matching the exit-popup discount (e.g. AURA10)', 'aura-fashion' ); ?></li>
		</ol>
		<p><strong><?php esc_html_e( 'Testing:', 'aura-fashion' ); ?></strong> <?php esc_html_e( 'See TESTING.md in the theme folder for a full feature-by-feature checklist.', 'aura-fashion' ); ?></p>
	</div>
	<?php
}

/**
 * Ensure product attribute taxonomy exists and return term IDs.
 */
function aura_demo_ensure_attribute( $name, $slug, $terms ) {
	global $wpdb;
	$attribute_id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $attribute_id ) {
		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_attribute_taxonomies',
			array(
				'attribute_name'    => $slug,
				'attribute_label'   => $name,
				'attribute_type'    => 'select',
				'attribute_orderby' => 'menu_order',
				'attribute_public'  => 0,
			)
		);
		delete_transient( 'wc_attribute_taxonomies' );
		wc_get_attribute_taxonomies(); // refresh
	}
	$taxonomy = 'pa_' . $slug;
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy( $taxonomy, 'product' );
	}
	$term_ids = array();
	foreach ( $terms as $term_name ) {
		$existing = term_exists( $term_name, $taxonomy );
		if ( ! $existing ) {
			$existing = wp_insert_term( $term_name, $taxonomy );
		}
		if ( ! is_wp_error( $existing ) ) {
			$term_ids[] = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
		}
	}
	return array( 'taxonomy' => $taxonomy, 'term_ids' => $term_ids );
}

function aura_run_demo_import() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return __( 'WooCommerce is not active. Activate it first.', 'aura-fashion' );
	}

	$created = array();

	// --- Pages ---
	$pages = array(
		'wishlist' => array(
			'title'    => 'Wishlist',
			'content'  => '[aura_wishlist]',
			'template' => '',
		),
		'about' => array(
			'title'    => 'About',
			'content'  => '',
			'template' => 'page-templates/template-about.php',
		),
		'contact' => array(
			'title'    => 'Contact',
			'content'  => '',
			'template' => 'page-templates/template-contact.php',
		),
		'faq' => array(
			'title'    => 'FAQ',
			'content'  => '',
			'template' => 'page-templates/template-faq.php',
		),
		'order-tracking' => array(
			'title'    => 'Order Tracking',
			'content'  => '',
			'template' => 'page-templates/template-order-tracking.php',
		),
	);

	$page_ids = array();
	foreach ( $pages as $key => $data ) {
		$existing = get_page_by_title( $data['title'] );
		if ( $existing ) {
			$page_ids[ $key ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post( array(
			'post_title'   => $data['title'],
			'post_content' => $data['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			if ( $data['template'] ) {
				update_post_meta( $id, '_wp_page_template', $data['template'] );
			}
			$page_ids[ $key ] = $id;
			$created[] = $data['title'] . ' page';
		}
	}

	// --- Categories ---
	$cat_slugs = array(
		'women'       => 'Women',
		'men'         => 'Men',
		'accessories' => 'Accessories',
		'sale'        => 'Sale',
		'new-in'      => 'New In',
	);
	$cat_ids = array();
	foreach ( $cat_slugs as $slug => $name ) {
		$term = term_exists( $slug, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			$created[] = "Category: $name";
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $slug ] = is_array( $term ) ? intval( $term['term_id'] ) : intval( $term );
		}
	}

	// --- Attributes: Size + Color ---
	$size_attr  = aura_demo_ensure_attribute( 'Size', 'size', array( 'XS', 'S', 'M', 'L', 'XL' ) );
	$color_attr = aura_demo_ensure_attribute( 'Color', 'color', array( 'Black', 'White', 'Ivory', 'Navy', 'Gold', 'Blush' ) );
	$created[]  = 'Attributes: Size, Color';

	// --- Sample products (simple + sale + badges) ---
	$samples = array(
		array(
			'title'    => 'Aura Silk Blouse',
			'price'    => '89',
			'sale'     => '69',
			'cat'      => array( 'women', 'sale', 'new-in' ),
			'featured' => true,
			'stock'    => 8,
			'badge'    => array( 'new', 'limited' ),
			'desc'     => 'Fluid silk blouse with a soft drape and mother-of-pearl buttons. Perfect for day-to-evening.',
		),
		array(
			'title'    => 'Gold Chain Necklace',
			'price'    => '45',
			'sale'     => '',
			'cat'      => array( 'accessories' ),
			'featured' => true,
			'stock'    => 25,
			'badge'    => array( 'bestseller' ),
			'desc'     => 'Delicate 18k gold-plated chain. Hypoallergenic and water-resistant.',
		),
		array(
			'title'    => 'Classic Linen Shirt',
			'price'    => '75',
			'sale'     => '',
			'cat'      => array( 'men', 'new-in' ),
			'featured' => true,
			'stock'    => 20,
			'badge'    => array( 'new', 'eco' ),
			'desc'     => 'Breathable European linen. Garment-washed for softness from first wear.',
		),
		array(
			'title'    => 'Wide-Leg Trousers',
			'price'    => '110',
			'sale'     => '88',
			'cat'      => array( 'women', 'sale' ),
			'featured' => false,
			'stock'    => 12,
			'badge'    => array(),
			'desc'     => 'High-rise wide-leg trousers in a fluid crepe. Elastic back waist for comfort.',
		),
		array(
			'title'    => 'Leather Crossbody',
			'price'    => '150',
			'sale'     => '',
			'cat'      => array( 'accessories' ),
			'featured' => true,
			'stock'    => 10,
			'badge'    => array( 'bestseller' ),
			'desc'     => 'Full-grain leather crossbody with adjustable strap and magnetic closure.',
		),
		array(
			'title'    => 'Merino Crew Knit',
			'price'    => '95',
			'sale'     => '75',
			'cat'      => array( 'men', 'sale' ),
			'featured' => false,
			'stock'    => 18,
			'badge'    => array( 'eco' ),
			'desc'     => 'Fine-gauge merino wool crew. Naturally temperature-regulating.',
		),
		array(
			'title'    => 'Satin Slip Dress',
			'price'    => '120',
			'sale'     => '96',
			'cat'      => array( 'women', 'sale' ),
			'featured' => true,
			'stock'    => 6,
			'badge'    => array( 'limited' ),
			'desc'     => 'Bias-cut satin slip with adjustable straps. Wear alone or layered.',
		),
		array(
			'title'    => 'Structured Blazer',
			'price'    => '180',
			'sale'     => '',
			'cat'      => array( 'women' ),
			'featured' => false,
			'stock'    => 14,
			'badge'    => array(),
			'desc'     => 'Tailored blazer with soft shoulder and single-button closure. Fully lined.',
		),
		array(
			'title'    => 'Cashmere Scarf',
			'price'    => '85',
			'sale'     => '',
			'cat'      => array( 'accessories', 'new-in' ),
			'featured' => true,
			'stock'    => 30,
			'badge'    => array( 'new', 'eco' ),
			'desc'     => '100% cashmere scarf in a generous size. Soft hand-feel and natural drape.',
		),
		array(
			'title'    => 'Organic Cotton Tee',
			'price'    => '38',
			'sale'     => '28',
			'cat'      => array( 'men', 'sale' ),
			'featured' => false,
			'stock'    => 40,
			'badge'    => array( 'eco', 'bestseller' ),
			'desc'     => 'GOTS-certified organic cotton. Relaxed fit, reinforced seams.',
		),
		array(
			'title'    => 'Pleated Midi Skirt',
			'price'    => '98',
			'sale'     => '',
			'cat'      => array( 'women', 'new-in' ),
			'featured' => true,
			'stock'    => 11,
			'badge'    => array( 'new' ),
			'desc'     => 'Knife-pleat midi with elastic waist. Moves beautifully with every step.',
		),
		array(
			'title'    => 'Minimalist Watch',
			'price'    => '165',
			'sale'     => '129',
			'cat'      => array( 'accessories', 'sale' ),
			'featured' => false,
			'stock'    => 7,
			'badge'    => array( 'limited' ),
			'desc'     => 'Swiss movement, sapphire crystal, stainless case. 40mm dial.',
		),
	);

	$product_ids = array();

	foreach ( $samples as $s ) {
		$exists = get_page_by_title( $s['title'], OBJECT, 'product' );
		if ( $exists ) {
			$product_ids[] = $exists->ID;
			continue;
		}
		$pid = wp_insert_post( array(
			'post_title'   => $s['title'],
			'post_content' => $s['desc'] . "\n\n" . __( 'A refined piece from the Aura collection. Premium materials, timeless cut.', 'aura-fashion' ),
			'post_excerpt' => __( 'Effortless elegance for every day.', 'aura-fashion' ),
			'post_status'  => 'publish',
			'post_type'    => 'product',
		) );
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}
		wp_set_object_terms( $pid, 'simple', 'product_type' );
		update_post_meta( $pid, '_regular_price', $s['price'] );
		update_post_meta( $pid, '_price', $s['sale'] !== '' ? $s['sale'] : $s['price'] );
		if ( $s['sale'] !== '' ) {
			update_post_meta( $pid, '_sale_price', $s['sale'] );
			// Sale end 14 days from now for countdown testing
			update_post_meta( $pid, '_sale_price_dates_to', strtotime( '+14 days' ) );
			update_post_meta( $pid, '_aura_sale_end', date( 'Y-m-d H:i:s', strtotime( '+14 days' ) ) );
		}
		update_post_meta( $pid, '_manage_stock', 'yes' );
		update_post_meta( $pid, '_stock', (string) $s['stock'] );
		update_post_meta( $pid, '_stock_status', 'instock' );
		update_post_meta( $pid, '_sku', 'AURA-' . strtoupper( substr( sanitize_title( $s['title'] ), 0, 8 ) ) );

		$term_ids = array();
		foreach ( $s['cat'] as $cslug ) {
			if ( ! empty( $cat_ids[ $cslug ] ) ) {
				$term_ids[] = $cat_ids[ $cslug ];
			}
		}
		if ( $term_ids ) {
			wp_set_object_terms( $pid, $term_ids, 'product_cat' );
		}
		if ( ! empty( $s['featured'] ) ) {
			wp_set_object_terms( $pid, array( 'featured' ), 'product_visibility', true );
		}
		// Badges
		if ( ! empty( $s['badge'] ) ) {
			update_post_meta( $pid, '_aura_badges', $s['badge'] );
		}
		// Featured image from placeholder service (requires allow_url_fopen / server internet)
		if ( ! has_post_thumbnail( $pid ) && function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$img_url = 'https://picsum.photos/seed/aura' . $pid . '/800/1066.jpg';
			$att_id  = media_sideload_image( $img_url, $pid, $s['title'], 'id' );
			if ( $att_id && ! is_wp_error( $att_id ) ) {
				set_post_thumbnail( $pid, $att_id );
			}
		}
		$product_ids[] = $pid;
		$created[]     = 'Product: ' . $s['title'];
	}

	// --- Variable product with Size + Color (for swatches testing) ---
	$var_title = 'Aura Essential Tee – Size & Color';
	$var_exists = get_page_by_title( $var_title, OBJECT, 'product' );
	if ( ! $var_exists ) {
		$vid = wp_insert_post( array(
			'post_title'   => $var_title,
			'post_content' => __( 'Our bestselling essential tee in multiple sizes and colors. Soft cotton jersey, modern fit. Choose your size and color below.', 'aura-fashion' ),
			'post_excerpt' => __( 'Soft cotton jersey essential. Multiple sizes & colors.', 'aura-fashion' ),
			'post_status'  => 'publish',
			'post_type'    => 'product',
		) );
		if ( $vid && ! is_wp_error( $vid ) ) {
			wp_set_object_terms( $vid, 'variable', 'product_type' );
			if ( ! empty( $cat_ids['women'] ) && ! empty( $cat_ids['new-in'] ) ) {
				wp_set_object_terms( $vid, array( $cat_ids['women'], $cat_ids['new-in'] ), 'product_cat' );
			}
			wp_set_object_terms( $vid, array( 'featured' ), 'product_visibility', true );
			update_post_meta( $vid, '_aura_badges', array( 'new', 'bestseller' ) );
			update_post_meta( $vid, '_sku', 'AURA-TEE-VAR' );

			// Link attributes
			$attributes = array();
			if ( ! empty( $size_attr['taxonomy'] ) ) {
				wp_set_object_terms( $vid, array( 'S', 'M', 'L', 'XL' ), $size_attr['taxonomy'] );
				$attributes[ $size_attr['taxonomy'] ] = array(
					'name'         => $size_attr['taxonomy'],
					'value'        => '',
					'position'     => 0,
					'is_visible'   => 1,
					'is_variation' => 1,
					'is_taxonomy'  => 1,
				);
			}
			if ( ! empty( $color_attr['taxonomy'] ) ) {
				wp_set_object_terms( $vid, array( 'Black', 'White', 'Navy', 'Ivory' ), $color_attr['taxonomy'] );
				$attributes[ $color_attr['taxonomy'] ] = array(
					'name'         => $color_attr['taxonomy'],
					'value'        => '',
					'position'     => 1,
					'is_visible'   => 1,
					'is_variation' => 1,
					'is_taxonomy'  => 1,
				);
			}
			update_post_meta( $vid, '_product_attributes', $attributes );

			// Create a few variations
			$variations = array(
				array( 'size' => 'S', 'color' => 'Black', 'price' => '42' ),
				array( 'size' => 'M', 'color' => 'Black', 'price' => '42' ),
				array( 'size' => 'L', 'color' => 'Black', 'price' => '42' ),
				array( 'size' => 'S', 'color' => 'White', 'price' => '42' ),
				array( 'size' => 'M', 'color' => 'White', 'price' => '42' ),
				array( 'size' => 'M', 'color' => 'Navy', 'price' => '45' ),
				array( 'size' => 'L', 'color' => 'Navy', 'price' => '45' ),
				array( 'size' => 'M', 'color' => 'Ivory', 'price' => '44' ),
			);
			foreach ( $variations as $v ) {
				$variation_id = wp_insert_post( array(
					'post_title'  => $var_title . ' - ' . $v['size'] . ' / ' . $v['color'],
					'post_status' => 'publish',
					'post_parent' => $vid,
					'post_type'   => 'product_variation',
				) );
				if ( $variation_id && ! is_wp_error( $variation_id ) ) {
					update_post_meta( $variation_id, 'attribute_pa_size', strtolower( $v['size'] ) );
					update_post_meta( $variation_id, 'attribute_pa_color', strtolower( $v['color'] ) );
					update_post_meta( $variation_id, '_regular_price', $v['price'] );
					update_post_meta( $variation_id, '_price', $v['price'] );
					update_post_meta( $variation_id, '_manage_stock', 'yes' );
					update_post_meta( $variation_id, '_stock', '15' );
					update_post_meta( $variation_id, '_stock_status', 'instock' );
				}
			}
			WC_Product_Variable::sync( $vid );
			$product_ids[] = $vid;
			$created[]     = 'Variable product: ' . $var_title;
		}
	} else {
		$product_ids[] = $var_exists->ID;
	}

	// --- Coupons ---
	$coupons = array(
		array( 'code' => 'AURA10', 'amount' => '10', 'type' => 'percent', 'desc' => 'Exit popup / welcome 10% off' ),
		array( 'code' => 'FREESHIP', 'amount' => '0', 'type' => 'percent', 'desc' => 'Free shipping (use with free-shipping threshold)', 'free_ship' => true ),
		array( 'code' => 'WELCOME15', 'amount' => '15', 'type' => 'percent', 'desc' => 'Welcome series 15% off' ),
	);
	foreach ( $coupons as $c ) {
		$existing_coupon = get_page_by_title( $c['code'], OBJECT, 'shop_coupon' );
		if ( $existing_coupon ) {
			continue;
		}
		$cid = wp_insert_post( array(
			'post_title'   => $c['code'],
			'post_content' => $c['desc'],
			'post_status'  => 'publish',
			'post_type'    => 'shop_coupon',
		) );
		if ( $cid && ! is_wp_error( $cid ) ) {
			update_post_meta( $cid, 'discount_type', $c['type'] );
			update_post_meta( $cid, 'coupon_amount', $c['amount'] );
			update_post_meta( $cid, 'individual_use', 'yes' );
			update_post_meta( $cid, 'usage_limit', '100' );
			if ( ! empty( $c['free_ship'] ) ) {
				update_post_meta( $cid, 'free_shipping', 'yes' );
			}
			$created[] = 'Coupon: ' . $c['code'];
		}
	}

	// --- Lookbook CPT entries ---
	if ( post_type_exists( 'aura_lookbook' ) ) {
		$looks = array(
			array(
				'title'   => 'Office Edit – Soft Tailoring',
				'content' => 'Pair the Structured Blazer with Wide-Leg Trousers and the Gold Chain Necklace for an elevated work look.',
				'products'=> array_slice( $product_ids, 0, 3 ),
			),
			array(
				'title'   => 'Weekend Ease – Linen & Layers',
				'content' => 'Classic Linen Shirt, Organic Cotton Tee, and Cashmere Scarf for effortless weekend style.',
				'products'=> array_slice( $product_ids, 2, 3 ),
			),
			array(
				'title'   => 'Evening Glow – Satin & Gold',
				'content' => 'Satin Slip Dress with Minimalist Watch and Leather Crossbody for dinner or events.',
				'products'=> array_slice( $product_ids, 5, 3 ),
			),
		);
		foreach ( $looks as $look ) {
			$exists = get_page_by_title( $look['title'], OBJECT, 'aura_lookbook' );
			if ( $exists ) {
				continue;
			}
			$lid = wp_insert_post( array(
				'post_title'   => $look['title'],
				'post_content' => $look['content'],
				'post_status'  => 'publish',
				'post_type'    => 'aura_lookbook',
			) );
			if ( $lid && ! is_wp_error( $lid ) ) {
				update_post_meta( $lid, '_aura_lookbook_products', $look['products'] );
				$created[] = 'Lookbook: ' . $look['title'];
			}
		}
	}

	// --- Style Journal (blog) posts ---
	$posts = array(
		array(
			'title'   => 'How to Build a Capsule Wardrobe',
			'content' => "A capsule wardrobe is a curated collection of versatile pieces that work together. Start with neutrals—black, ivory, navy—then add one or two accent colors.\n\nFocus on quality over quantity: a great blazer, a silk blouse, well-cut trousers, and a classic tee will take you further than a closet full of trends.\n\nOur Aura Essential Tee and Structured Blazer are designed exactly for this approach.",
		),
		array(
			'title'   => 'The Case for Sustainable Fibers',
			'content' => "Organic cotton, linen, and merino wool are not only better for the planet—they often feel better on the skin and last longer.\n\nLook for GOTS or similar certifications, and prefer garments that can be repaired or recycled. Our Eco-badged pieces highlight these choices.",
		),
		array(
			'title'   => 'Layering for Transitional Weather',
			'content' => "When temperatures swing, layering is everything. A fine merino knit under a blazer, or a cashmere scarf over a slip dress, keeps you comfortable without bulk.\n\nShop our New In and Accessories for pieces that layer beautifully year-round.",
		),
	);
	foreach ( $posts as $p ) {
		$exists = get_page_by_title( $p['title'], OBJECT, 'post' );
		if ( $exists ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_title'   => $p['title'],
			'post_content' => $p['content'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$created[] = 'Journal: ' . $p['title'];
		}
	}

	// --- Theme options defaults for testing ---
	$opts = get_option( 'aura_options', array() );
	if ( empty( $opts['free_shipping_threshold'] ) ) {
		$opts['free_shipping_threshold'] = '100';
	}
	if ( empty( $opts['exit_popup_code'] ) ) {
		$opts['exit_popup_code'] = 'AURA10';
	}
	if ( empty( $opts['store_notice'] ) ) {
		$opts['store_notice'] = __( 'Free shipping on orders over $100 · Use code AURA10 for 10% off', 'aura-fashion' );
	}
	update_option( 'aura_options', $opts );
	$created[] = 'Theme options defaults';

	// --- Menu ---
	$menu_name = 'Primary Menu';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id   = wp_create_nav_menu( $menu_name );
		$created[] = 'Primary Menu';
	} else {
		$menu_id = $menu->term_id;
	}

	if ( ! is_wp_error( $menu_id ) ) {
		$items = wp_get_nav_menu_items( $menu_id );
		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}
		$links = array(
			array( 'title' => 'Home', 'url' => home_url( '/' ) ),
			array( 'title' => 'Shop', 'url' => get_permalink( wc_get_page_id( 'shop' ) ) ),
		);
		foreach ( $cat_slugs as $slug => $name ) {
			$term_link = get_term_link( $slug, 'product_cat' );
			if ( ! is_wp_error( $term_link ) ) {
				$links[] = array( 'title' => $name, 'url' => $term_link );
			}
		}
		foreach ( array( 'wishlist', 'about', 'contact', 'faq' ) as $pk ) {
			if ( ! empty( $page_ids[ $pk ] ) ) {
				$links[] = array( 'title' => $pages[ $pk ]['title'], 'url' => get_permalink( $page_ids[ $pk ] ) );
			}
		}
		// Lookbook archive if CPT exists
		if ( post_type_exists( 'aura_lookbook' ) ) {
			$links[] = array( 'title' => 'Lookbook', 'url' => get_post_type_archive_link( 'aura_lookbook' ) );
		}
		$links[] = array( 'title' => 'Journal', 'url' => get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) );

		foreach ( $links as $i => $link ) {
			if ( is_wp_error( $link['url'] ) || empty( $link['url'] ) ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'    => $link['title'],
				'menu-item-url'      => $link['url'],
				'menu-item-status'   => 'publish',
				'menu-item-position' => $i + 1,
			) );
		}
		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	// Front page / blog settings hint
	$front = get_option( 'show_on_front' );
	if ( $front !== 'page' ) {
		// Leave as is; user can set Homepage to a static page in Reading settings
	}

	update_option( 'aura_demo_imported', 1 );
	flush_rewrite_rules();

	$count = count( $created );
	return sprintf(
		/* translators: %d = number of items */
		__( 'Demo import finished. Created/updated %d items. Save Permalinks if links 404. See TESTING.md for full feature tests.', 'aura-fashion' ),
		$count
	);
}
