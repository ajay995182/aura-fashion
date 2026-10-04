<?php
/**
 * Front Page – full scrolling homepage with photos + About + FAQ + more
 *
 * @package Aura_Fashion
 */

get_header();

$cat_images = array(
	'women'       => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&h=800&fit=crop',
	'men'         => 'https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?w=600&h=800&fit=crop',
	'accessories' => 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?w=600&h=800&fit=crop',
	'sale'        => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&h=800&fit=crop',
);
$hero_bg = get_theme_mod(
	'aura_hero_image',
	'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1600&h=900&fit=crop'
);
?>

<!-- 1. HERO with real photo (img layer so it always displays) -->
<section class="hero">
	<div class="hero-media" aria-hidden="true">
		<img
			src="<?php echo esc_url( $hero_bg ); ?>"
			alt=""
			class="hero-photo"
			width="1600"
			height="900"
			fetchpriority="high"
			decoding="async"
		/>
		<div class="hero-overlay"></div>
	</div>
	<div class="aura-container hero-inner">
		<div class="hero-content">
			<span class="hero-badge"><?php echo esc_html( get_theme_mod( 'aura_hero_badge', __( 'New Season 2026', 'aura-fashion' ) ) ); ?></span>
			<h1><?php echo esc_html( get_theme_mod( 'aura_hero_title', __( 'Elevate Your Style', 'aura-fashion' ) ) ); ?></h1>
			<p><?php echo esc_html( get_theme_mod( 'aura_hero_desc', __( 'Discover timeless pieces crafted for the modern wardrobe. Premium quality, effortless elegance.', 'aura-fashion' ) ) ); ?></p>
			<div class="hero-actions">
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Shop Now', 'aura-fashion' ); ?></a>
				<a href="#about" class="btn btn-outline"><?php esc_html_e( 'Our Story', 'aura-fashion' ); ?></a>
			</div>
		</div>
	</div>
</section>

<!-- 2. SHOP BY CATEGORY -->
<section class="section" id="categories">
	<div class="aura-container">
		<div class="section-header">
			<h2><?php esc_html_e( 'Shop by Category', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Explore our curated collections.', 'aura-fashion' ); ?></p>
		</div>
		<div class="categories-grid">
			<?php
			$cats = array(
				'women'       => __( 'Women', 'aura-fashion' ),
				'men'         => __( 'Men', 'aura-fashion' ),
				'accessories' => __( 'Accessories', 'aura-fashion' ),
				'sale'        => __( 'Sale', 'aura-fashion' ),
			);
			foreach ( $cats as $slug => $label ) :
				$term = get_term_by( 'slug', $slug, 'product_cat' );
				$url  = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/product-category/' . $slug . '/' );
				if ( is_wp_error( $url ) ) {
					$url = home_url( '/product-category/' . $slug . '/' );
				}
				$img = $cat_images[ $slug ];
				if ( $term && ! is_wp_error( $term ) ) {
					$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
					if ( $thumb_id ) {
						$src = wp_get_attachment_image_url( $thumb_id, 'large' );
						if ( $src ) {
							$img = $src;
						}
					}
				}
				?>
				<a href="<?php echo esc_url( $url ); ?>" class="category-card">
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $label ); ?>" width="600" height="800" loading="lazy" decoding="async" />
					<div class="category-card-overlay">
						<div>
							<h3><?php echo esc_html( $label ); ?></h3>
							<span><?php esc_html_e( 'Explore →', 'aura-fashion' ); ?></span>
						</div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 3. FEATURED PRODUCTS -->
<section class="section" id="featured" style="background:var(--aura-black-soft);">
	<div class="aura-container">
		<div class="section-header">
			<h2><?php esc_html_e( 'Featured Products', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Hand-picked favourites this season.', 'aura-fashion' ); ?></p>
		</div>
		<div class="products-grid">
			<?php
			$featured = new WP_Query( array(
				'post_type'      => 'product',
				'posts_per_page' => 8,
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
					),
				),
			) );
			if ( ! $featured->have_posts() ) {
				$featured = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => 8 ) );
			}
			if ( $featured->have_posts() ) {
				while ( $featured->have_posts() ) {
					$featured->the_post();
					wc_get_template_part( 'content', 'product' );
				}
				wp_reset_postdata();
			} else {
				echo '<p class="text-center" style="grid-column:1/-1;color:var(--aura-gray);">' . esc_html__( 'No products yet. Run Aura Demo Import or add products in WooCommerce.', 'aura-fashion' ) . '</p>';
			}
			?>
		</div>
		<div class="text-center mt-3">
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'View All Products', 'aura-fashion' ); ?></a>
		</div>
	</div>
</section>

<!-- 4. ABOUT US (scroll section) -->
<section class="section home-about" id="about">
	<div class="aura-container">
		<div class="home-about-grid">
			<div class="home-about-image">
				<img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop" alt="<?php esc_attr_e( 'About Aura Fashion', 'aura-fashion' ); ?>" width="800" height="1000" loading="lazy" decoding="async" />
			</div>
			<div class="home-about-content">
				<span class="section-label"><?php esc_html_e( 'Our Story', 'aura-fashion' ); ?></span>
				<h2><?php esc_html_e( 'About Aura', 'aura-fashion' ); ?></h2>
				<p><?php esc_html_e( 'Aura Fashion was born from a simple belief: clothing should feel as good as it looks. We design timeless pieces that blend modern silhouettes with thoughtful craftsmanship.', 'aura-fashion' ); ?></p>
				<p><?php esc_html_e( 'Every garment is made to last. We work with responsible workshops and choose fabrics that age beautifully — quality over quantity, always.', 'aura-fashion' ); ?></p>
				<ul class="home-about-points">
					<li><?php esc_html_e( 'Premium, lasting materials', 'aura-fashion' ); ?></li>
					<li><?php esc_html_e( 'Timeless, versatile design', 'aura-fashion' ); ?></li>
					<li><?php esc_html_e( 'Responsible production', 'aura-fashion' ); ?></li>
				</ul>
				<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Learn More', 'aura-fashion' ); ?></a>
			</div>
		</div>
	</div>
</section>

<!-- 5. PROMO / LOOK BAND -->
<section class="section home-promo" id="look">
	<div class="home-promo-bg" style="background-image: linear-gradient(rgba(10,10,10,0.55), rgba(10,10,10,0.55)), url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1600&h=700&fit=crop');">
		<div class="aura-container text-center">
			<span class="hero-badge"><?php esc_html_e( 'Limited', 'aura-fashion' ); ?></span>
			<h2><?php esc_html_e( 'The Season Edit', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Curated looks for day to night. Soft tailoring, elevated essentials, and pieces that move with you.', 'aura-fashion' ); ?></p>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Shop the Edit', 'aura-fashion' ); ?></a>
		</div>
	</div>
</section>

<!-- 6. SALE COUNTDOWN -->
<section class="section" id="sale">
	<div class="aura-container text-center">
		<div class="section-header">
			<h2><?php esc_html_e( 'Season Sale', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Limited time offers – don’t miss out.', 'aura-fashion' ); ?></p>
		</div>
		<div class="sale-countdown" id="sale-countdown" data-end="2026-12-31T23:59:59">
			<div class="countdown-item"><div class="number" data-days>00</div><div class="label"><?php esc_html_e( 'Days', 'aura-fashion' ); ?></div></div>
			<div class="countdown-item"><div class="number" data-hours>00</div><div class="label"><?php esc_html_e( 'Hours', 'aura-fashion' ); ?></div></div>
			<div class="countdown-item"><div class="number" data-mins>00</div><div class="label"><?php esc_html_e( 'Mins', 'aura-fashion' ); ?></div></div>
			<div class="countdown-item"><div class="number" data-secs>00</div><div class="label"><?php esc_html_e( 'Secs', 'aura-fashion' ); ?></div></div>
		</div>
		<a href="<?php echo esc_url( home_url( '/product-category/sale/' ) ); ?>" class="btn btn-primary mt-3"><?php esc_html_e( 'Shop Sale', 'aura-fashion' ); ?></a>
	</div>
</section>

<!-- 7. WHY SHOP WITH US -->
<section class="section home-features" id="why-us" style="background:var(--aura-black-soft);">
	<div class="aura-container">
		<div class="section-header">
			<h2><?php esc_html_e( 'Why Shop With Us', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Everything you need for a smooth experience.', 'aura-fashion' ); ?></p>
		</div>
		<div class="home-features-grid">
			<div class="home-feature-card">
				<div class="home-feature-icon">🚚</div>
				<h3><?php esc_html_e( 'Free Shipping', 'aura-fashion' ); ?></h3>
				<p><?php esc_html_e( 'On orders over $100. Fast, tracked delivery worldwide.', 'aura-fashion' ); ?></p>
			</div>
			<div class="home-feature-card">
				<div class="home-feature-icon">↩️</div>
				<h3><?php esc_html_e( 'Easy Returns', 'aura-fashion' ); ?></h3>
				<p><?php esc_html_e( '30-day returns. Unworn items with tags — no hassle.', 'aura-fashion' ); ?></p>
			</div>
			<div class="home-feature-card">
				<div class="home-feature-icon">🔒</div>
				<h3><?php esc_html_e( 'Secure Checkout', 'aura-fashion' ); ?></h3>
				<p><?php esc_html_e( 'Account-protected orders and encrypted payments.', 'aura-fashion' ); ?></p>
			</div>
			<div class="home-feature-card">
				<div class="home-feature-icon">✨</div>
				<h3><?php esc_html_e( 'Quality First', 'aura-fashion' ); ?></h3>
				<p><?php esc_html_e( 'Premium materials and careful construction in every piece.', 'aura-fashion' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- 8. FAQ (scroll section) -->
<section class="section" id="faq">
	<div class="aura-container home-faq-wrap">
		<div class="section-header">
			<h2><?php esc_html_e( 'Frequently Asked Questions', 'aura-fashion' ); ?></h2>
			<p><?php esc_html_e( 'Quick answers to common questions.', 'aura-fashion' ); ?></p>
		</div>
		<div class="home-faq-list">
			<?php
			$faqs = array(
				array(
					'q' => __( 'What is your return policy?', 'aura-fashion' ),
					'a' => __( 'We accept returns within 30 days of delivery. Items must be unworn, unwashed, and in original packaging with tags attached.', 'aura-fashion' ),
				),
				array(
					'q' => __( 'How long does shipping take?', 'aura-fashion' ),
					'a' => __( 'Standard shipping takes 5–7 business days. Express (2–3 days) is available at checkout. International: typically 10–14 business days.', 'aura-fashion' ),
				),
				array(
					'q' => __( 'Do you offer free shipping?', 'aura-fashion' ),
					'a' => __( 'Yes — free standard shipping on all orders over $100.', 'aura-fashion' ),
				),
				array(
					'q' => __( 'How do I find my size?', 'aura-fashion' ),
					'a' => __( 'Each product page has a Size Guide. Between sizes? We recommend sizing up, or contact us for help.', 'aura-fashion' ),
				),
				array(
					'q' => __( 'Do I need an account to order?', 'aura-fashion' ),
					'a' => __( 'Yes. Please log in or create a free account at checkout so we can protect your order and make tracking easy.', 'aura-fashion' ),
				),
				array(
					'q' => __( 'Are your materials sustainable?', 'aura-fashion' ),
					'a' => __( 'We prioritise organic cotton, recycled fibres, and responsibly sourced materials. Look for the Eco badge on products.', 'aura-fashion' ),
				),
			);
			foreach ( $faqs as $i => $faq ) :
				?>
				<div class="faq-item<?php echo 0 === $i ? ' active' : ''; ?>">
					<button type="button" class="faq-question" aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<span class="faq-icon">+</span>
					</button>
					<div class="faq-answer">
						<p><?php echo esc_html( $faq['a'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="text-center mt-3">
			<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'View All FAQ', 'aura-fashion' ); ?></a>
		</div>
	</div>
</section>

<!-- 9. NEWSLETTER / CTA -->
<section class="section home-cta" id="newsletter">
	<div class="aura-container text-center">
		<h2><?php esc_html_e( 'Join the Aura List', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'New drops, exclusive offers, and style notes — straight to your inbox.', 'aura-fashion' ); ?></p>
		<form class="home-newsletter-form aura-newsletter-form" method="post" action="#">
			<input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'aura-fashion' ); ?>" required aria-label="<?php esc_attr_e( 'Email', 'aura-fashion' ); ?>" />
			<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Subscribe', 'aura-fashion' ); ?></button>
		</form>
		<p class="home-cta-note"><?php esc_html_e( 'By subscribing you agree to receive marketing emails. Unsubscribe anytime.', 'aura-fashion' ); ?></p>
	</div>
</section>

<script>
document.querySelectorAll('.home-faq-list .faq-question').forEach(function(btn) {
	btn.addEventListener('click', function() {
		var item = btn.closest('.faq-item');
		var isOpen = item.classList.contains('active');
		document.querySelectorAll('.home-faq-list .faq-item').forEach(function(i) {
			i.classList.remove('active');
			i.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
		});
		if (!isOpen) {
			item.classList.add('active');
			btn.setAttribute('aria-expanded', 'true');
		}
	});
});
</script>

<?php
do_action( 'aura_homepage_after_sale' );
get_footer();
