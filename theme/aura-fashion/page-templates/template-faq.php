<?php
/**
 * Template Name: FAQ
 *
 * @package Aura_Fashion
 */

get_header();

$faqs = array(
	array(
		'q' => __( 'What is your return policy?', 'aura-fashion' ),
		'a' => __( 'We accept returns within 30 days of delivery. Items must be unworn, unwashed, and in original packaging with tags attached. Simply start a return from your account or contact our support team.', 'aura-fashion' ),
	),
	array(
		'q' => __( 'How long does shipping take?', 'aura-fashion' ),
		'a' => __( 'Standard shipping takes 5–7 business days. Express shipping (2–3 business days) is available at checkout. International orders typically arrive within 10–14 business days.', 'aura-fashion' ),
	),
	array(
		'q' => __( 'Do you offer free shipping?', 'aura-fashion' ),
		'a' => __( 'Yes! Free standard shipping on all orders over $100. Free express shipping on orders over $200.', 'aura-fashion' ),
	),
	array(
		'q' => __( 'How do I find my size?', 'aura-fashion' ),
		'a' => __( 'Each product page has a Size Guide button with detailed measurements. If you are between sizes, we recommend sizing up. Still unsure? Contact us — we are happy to help.', 'aura-fashion' ),
	),
	array(
		'q' => __( 'Can I modify or cancel my order?', 'aura-fashion' ),
		'a' => __( 'Orders can be modified or cancelled within 1 hour of placement. After that, the order enters processing and cannot be changed. Please contact support as soon as possible.', 'aura-fashion' ),
	),
	array(
		'q' => __( 'Are your materials sustainable?', 'aura-fashion' ),
		'a' => __( 'We prioritise certified organic cotton, recycled polyester, and responsibly sourced wool. Look for the “Eco” badge on product pages for more details on each garment’s materials.', 'aura-fashion' ),
	),
);
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Answers to common questions.', 'aura-fashion' ); ?></p>
	</div>
</section>

<div class="aura-container" style="padding:60px 0;max-width:720px;">
	<?php foreach ( $faqs as $i => $faq ) : ?>
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

<script>
document.querySelectorAll('.faq-question').forEach(btn => {
	btn.addEventListener('click', () => {
		const item = btn.closest('.faq-item');
		const isOpen = item.classList.contains('active');
		document.querySelectorAll('.faq-item').forEach(i => {
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

<?php get_footer(); ?>
