<?php
/**
 * Lookbook Archive
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php esc_html_e( 'Lookbook', 'aura-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Shop the season’s defining looks.', 'aura-fashion' ); ?></p>
	</div>
</section>

<div class="aura-container" style="padding:60px 0 80px;">
	<?php if ( have_posts() ) : ?>
		<div class="lookbook-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'lookbook-card' ); ?>>
					<a href="<?php the_permalink(); ?>" class="lookbook-card-link">
						<div class="lookbook-card-image">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'aura-category' ); ?>
							<?php else : ?>
								<div class="aura-placeholder" style="aspect-ratio:3/4;">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="width:30%;opacity:0.25;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
								</div>
							<?php endif; ?>
							<div class="lookbook-card-overlay">
								<span class="btn btn-outline btn-sm"><?php esc_html_e( 'Shop the Look', 'aura-fashion' ); ?></span>
							</div>
						</div>
						<div class="lookbook-card-body">
							<h2><?php the_title(); ?></h2>
							<?php if ( has_excerpt() ) : ?>
								<p><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
						</div>
					</a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
	<?php else : ?>
		<p class="text-center"><?php esc_html_e( 'No lookbooks yet. Add some from the admin under Lookbooks.', 'aura-fashion' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
