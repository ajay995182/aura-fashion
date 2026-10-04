<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 *
 * Aura Fashion – Single Product (gallery, sticky ATC, tabs)
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();
	global $product;
	?>

	<div class="aura-container">
		<nav class="woocommerce-breadcrumb aura-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'aura-fashion' ); ?>">
			<?php
			if ( function_exists( 'woocommerce_breadcrumb' ) ) {
				woocommerce_breadcrumb( array(
					'delimiter'   => ' <span class="sep">/</span> ',
					'wrap_before' => '<ol class="breadcrumb-list">',
					'wrap_after'  => '</ol>',
					'before'      => '<li>',
					'after'       => '</li>',
				) );
			}
			?>
		</nav>

		<div class="single-product-layout aura-product-enhanced">
			<div class="product-gallery aura-gallery">
				<?php
				$video = get_post_meta( $product->get_id(), '_aura_product_video', true );
				if ( $video && function_exists( 'aura_display_product_video' ) ) {
					aura_display_product_video();
				}
				?>
				<div class="product-main-image" id="product-main-image">
					<?php
					if ( has_post_thumbnail() ) {
						echo get_the_post_thumbnail( $product->get_id(), 'aura-product-large', array( 'class' => 'aura-zoom-img' ) );
					} elseif ( function_exists( 'aura_placeholder_img' ) ) {
						echo aura_placeholder_img( 'aura-product-large' ); // phpcs:ignore
					}
					?>
				</div>
				<?php
				$attachment_ids = $product->get_gallery_image_ids();
				if ( $attachment_ids || has_post_thumbnail() ) :
					?>
					<div class="product-thumbnails" role="list">
						<?php if ( has_post_thumbnail() ) : ?>
							<button type="button" class="product-thumb active" data-full="<?php echo esc_url( get_the_post_thumbnail_url( $product->get_id(), 'aura-product-large' ) ); ?>" role="listitem" aria-label="<?php esc_attr_e( 'Main image', 'aura-fashion' ); ?>">
								<?php echo get_the_post_thumbnail( $product->get_id(), 'thumbnail' ); ?>
							</button>
						<?php endif; ?>
						<?php foreach ( $attachment_ids as $aid ) : ?>
							<button type="button" class="product-thumb" data-full="<?php echo esc_url( wp_get_attachment_image_url( $aid, 'aura-product-large' ) ); ?>" role="listitem">
								<?php echo wp_get_attachment_image( $aid, 'thumbnail' ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="product-summary aura-summary-sticky">
				<?php if ( $product->is_on_sale() ) : ?>
					<span class="product-card-badge sale" style="position:static;display:inline-block;margin-bottom:12px;"><?php esc_html_e( 'Sale', 'aura-fashion' ); ?></span>
				<?php endif; ?>

				<h1 class="product_title entry-title"><?php the_title(); ?></h1>
				<div class="product-price"><?php echo $product->get_price_html(); // phpcs:ignore ?></div>

				<?php
				if ( function_exists( 'aura_stock_countdown' ) ) {
					aura_stock_countdown();
				}
				if ( function_exists( 'aura_product_sale_countdown' ) ) {
					aura_product_sale_countdown();
				}
				?>

				<div class="product-short-desc"><?php echo wp_kses_post( $product->get_short_description() ); ?></div>

				<?php
				if ( function_exists( 'aura_size_match_hint' ) ) {
					aura_size_match_hint();
				}
				woocommerce_template_single_add_to_cart();
				?>

				<div class="product-extra-actions">
					<button type="button" class="wishlist-btn-single wishlist-toggle" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-pressed="false" aria-label="<?php esc_attr_e( 'Add to Wishlist', 'aura-fashion' ); ?>">
						<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
					</button>
				</div>

				<div class="product-meta-block">
					<?php
					$sku = $product->get_sku();
					if ( $sku ) {
						echo '<p><span class="meta-label">SKU</span> ' . esc_html( $sku ) . '</p>';
					}
					$cats = wc_get_product_category_list( $product->get_id(), ', ' );
					if ( $cats ) {
						echo '<p><span class="meta-label">' . esc_html__( 'Category', 'aura-fashion' ) . '</span> ' . $cats . '</p>'; // phpcs:ignore
					}
					?>
				</div>
			</div>
		</div>

		<div class="aura-product-tabs">
			<div class="aura-tabs-nav" role="tablist">
				<button type="button" class="aura-tab active" role="tab" aria-selected="true" data-tab="description"><?php esc_html_e( 'Description', 'aura-fashion' ); ?></button>
				<button type="button" class="aura-tab" role="tab" aria-selected="false" data-tab="additional"><?php esc_html_e( 'Details', 'aura-fashion' ); ?></button>
				<button type="button" class="aura-tab" role="tab" aria-selected="false" data-tab="reviews"><?php esc_html_e( 'Reviews', 'aura-fashion' ); ?> (<?php echo esc_html( $product->get_review_count() ); ?>)</button>
			</div>
			<div class="aura-tabs-panels">
				<div class="aura-tab-panel active" id="tab-description" role="tabpanel">
					<?php the_content(); ?>
				</div>
				<div class="aura-tab-panel" id="tab-additional" role="tabpanel" hidden>
					<?php
					ob_start();
					if ( function_exists( 'woocommerce_product_additional_information_tab' ) ) {
						woocommerce_product_additional_information_tab();
					}
					$add = ob_get_clean();
					echo $add ? $add : '<p>' . esc_html__( 'No additional information.', 'aura-fashion' ) . '</p>'; // phpcs:ignore
					?>
				</div>
				<div class="aura-tab-panel" id="tab-reviews" role="tabpanel" hidden>
					<?php comments_template(); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="aura-sticky-atc" id="aura-sticky-atc" hidden>
		<div class="aura-sticky-atc-inner">
			<div class="sticky-atc-info">
				<span class="sticky-atc-title"><?php the_title(); ?></span>
				<span class="sticky-atc-price"><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
			</div>
			<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
				<a href="#product-main-image" class="btn btn-primary btn-sm sticky-atc-btn" id="sticky-atc-scroll">
					<?php echo esc_html( $product->add_to_cart_text() ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php
	woocommerce_upsell_display();
	if ( function_exists( 'woocommerce_cross_sell_display' ) ) {
		// Cross-sells are primarily cart; related covers single
	}
	woocommerce_output_related_products();
endwhile;

get_footer( 'shop' );
?>
<script>
jQuery(function($){
	$('.product-thumb').on('click', function(){
		$('.product-thumb').removeClass('active');
		$(this).addClass('active');
		var src = $(this).data('full');
		if (src) $('#product-main-image img').attr('src', src).attr('srcset','');
	});
	$('.aura-tab').on('click', function(){
		var tab = $(this).data('tab');
		$('.aura-tab').removeClass('active').attr('aria-selected','false');
		$(this).addClass('active').attr('aria-selected','true');
		$('.aura-tab-panel').removeClass('active').attr('hidden', true);
		$('#tab-' + tab).addClass('active').removeAttr('hidden');
	});
	var $sticky = $('#aura-sticky-atc');
	var $summary = $('.aura-summary-sticky');
	if ($sticky.length && $summary.length) {
		$(window).on('scroll', function(){
			var rect = $summary[0].getBoundingClientRect();
			if (rect.bottom < 0) {
				$sticky.removeAttr('hidden').addClass('show');
			} else {
				$sticky.attr('hidden', true).removeClass('show');
			}
		});
		$('#sticky-atc-scroll').on('click', function(e){
			e.preventDefault();
			$('html, body').animate({ scrollTop: $summary.offset().top - 80 }, 400);
		});
	}
});
</script>
<?php
