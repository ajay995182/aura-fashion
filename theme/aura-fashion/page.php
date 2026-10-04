<?php
/**
 * Default Page Template
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php the_title(); ?></h1>
	</div>
</section>

<div class="aura-container page-content">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</div>

<?php get_footer(); ?>
