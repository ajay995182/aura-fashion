<?php
/**
 * Blog home – Style Journal
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php esc_html_e( 'Style Journal', 'aura-fashion' ); ?></h1>
		<p><?php esc_html_e( 'Stories, trends, and inspiration from the Aura world.', 'aura-fashion' ); ?></p>
	</div>
</section>

<div class="aura-container" style="padding:60px 0 80px;">
	<?php if ( have_posts() ) : ?>
		<div class="journal-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'journal-card' ); ?>>
					<a href="<?php the_permalink(); ?>" class="journal-card-link">
						<div class="journal-card-image">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large' ); ?>
							<?php else : ?>
								<div class="aura-placeholder" style="aspect-ratio:16/10;"></div>
							<?php endif; ?>
						</div>
						<div class="journal-card-body">
							<span class="journal-date"><?php echo esc_html( get_the_date() ); ?></span>
							<h2><?php the_title(); ?></h2>
							<p><?php echo esc_html( get_the_excerpt() ); ?></p>
							<span class="journal-read"><?php esc_html_e( 'Read more →', 'aura-fashion' ); ?></span>
						</div>
					</a>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="text-center" style="margin-top:40px;">
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		</div>
	<?php else : ?>
		<p class="text-center"><?php esc_html_e( 'No posts yet. Publish your first Style Journal entry.', 'aura-fashion' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
