<?php
/**
 * Single blog post – Style Journal
 *
 * @package Aura_Fashion
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'journal-single' ); ?>>
		<section class="page-hero">
			<div class="aura-container" style="max-width:720px;">
				<span class="journal-date"><?php echo esc_html( get_the_date() ); ?></span>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="journal-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="aura-container journal-featured">
				<?php the_post_thumbnail( 'large' ); ?>
			</div>
		<?php endif; ?>

		<div class="aura-container journal-content entry-content">
			<?php the_content(); ?>
		</div>

		<footer class="aura-container journal-footer">
			<div class="journal-meta">
				<span><?php esc_html_e( 'Posted in', 'aura-fashion' ); ?> <?php the_category( ', ' ); ?></span>
				<?php the_tags( '<span class="journal-tags">', ', ', '</span>' ); ?>
			</div>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>" class="btn btn-outline btn-sm">
				<?php esc_html_e( '← Back to Journal', 'aura-fashion' ); ?>
			</a>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
