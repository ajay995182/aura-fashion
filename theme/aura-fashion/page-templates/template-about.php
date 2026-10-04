<?php
/**
 * Template Name: About Us
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Our story, our passion, our craft.', 'aura-fashion' ); ?></p>
	</div>
</section>

<div class="aura-container page-content">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

	<?php if ( ! get_the_content() ) : ?>
		<h2><?php esc_html_e( 'Who We Are', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'Aura Fashion was born from a simple belief: clothing should feel as good as it looks. We design timeless pieces that blend modern silhouettes with thoughtful craftsmanship, using materials that respect both people and planet.', 'aura-fashion' ); ?></p>

		<h2><?php esc_html_e( 'Our Promise', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'Every garment is made to last. We work with small, responsible workshops and choose fabrics that age beautifully. Quality over quantity — always.', 'aura-fashion' ); ?></p>

		<h2><?php esc_html_e( 'Join the Journey', 'aura-fashion' ); ?></h2>
		<p><?php esc_html_e( 'Whether you are discovering us for the first time or returning for another season, thank you for being part of the Aura community.', 'aura-fashion' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
