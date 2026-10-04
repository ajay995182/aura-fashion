<?php
/**
 * Main fallback template
 *
 * @package Aura_Fashion
 */

get_header();
?>

<div class="aura-container" style="padding:60px 0;">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?> style="margin-bottom:40px;">
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No content found.', 'aura-fashion' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
