<?php
/**
 * Template Name: Order Tracking
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Check the status of your order anytime.', 'aura-fashion' ); ?></p>
	</div>
</section>

<div class="aura-container" style="padding-bottom:80px;">
	<?php echo do_shortcode( '[aura_order_tracking]' ); ?>
</div>

<?php get_footer(); ?>
