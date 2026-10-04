<?php
/**
 * Single Lookbook – Shop the Look
 *
 * @package Aura_Fashion
 */

get_header();

while ( have_posts() ) :
	the_post();
	$product_ids_raw = get_post_meta( get_the_ID(), '_aura_lookbook_products', true );
	$product_ids = array_filter( array_map( 'absint', explode( ',', (string) $product_ids_raw ) ) );
	?>

	<article <?php post_class( 'lookbook-single' ); ?>>
		<section class="page-hero lookbook-hero">
			<div class="aura-container">
				<p class="lookbook-label"><?php esc_html_e( 'Lookbook', 'aura-fashion' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="lookbook-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<div class="aura-container lookbook-content-wrap">
			<div class="lookbook-featured-image">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large' ); ?>
				<?php endif; ?>
			</div>
			<div class="lookbook-content entry-content">
				<?php the_content(); ?>
			</div>
		</div>

		<?php if ( ! empty( $product_ids ) && function_exists( 'wc_get_product' ) ) : ?>
			<section class="section lookbook-shop">
				<div class="aura-container">
					<div class="section-header">
						<h2><?php esc_html_e( 'Shop the Look', 'aura-fashion' ); ?></h2>
						<p><?php esc_html_e( 'Pieces featured in this story.', 'aura-fashion' ); ?></p>
					</div>
					<div class="products-grid">
						<?php
						$q = new WP_Query( array(
							'post_type'      => 'product',
							'post__in'       => $product_ids,
							'orderby'        => 'post__in',
							'posts_per_page' => count( $product_ids ),
						) );
						while ( $q->have_posts() ) {
							$q->the_post();
							wc_get_template_part( 'content', 'product' );
						}
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>

<?php
endwhile;
get_footer();
