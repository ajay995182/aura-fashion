<?php
/**
 * Template Name: Contact
 *
 * @package Aura_Fashion
 */

get_header();
?>

<section class="page-hero">
	<div class="aura-container">
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'We would love to hear from you.', 'aura-fashion' ); ?></p>
	</div>
</section>

<div class="aura-container" style="padding:60px 0;">
	<div class="contact-grid">
		<div>
			<h2 style="margin-bottom:24px;"><?php esc_html_e( 'Send a Message', 'aura-fashion' ); ?></h2>
			<form class="contact-form" action="#" method="post" onsubmit="return false;">
				<input type="text" name="name" placeholder="<?php esc_attr_e( 'Your Name', 'aura-fashion' ); ?>" required>
				<input type="email" name="email" placeholder="<?php esc_attr_e( 'Your Email', 'aura-fashion' ); ?>" required>
				<input type="text" name="subject" placeholder="<?php esc_attr_e( 'Subject', 'aura-fashion' ); ?>">
				<textarea name="message" rows="6" placeholder="<?php esc_attr_e( 'Your Message', 'aura-fashion' ); ?>" required></textarea>
				<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Send Message', 'aura-fashion' ); ?></button>
			</form>
			<p style="margin-top:12px;font-size:0.85rem;color:var(--aura-gray);"><?php esc_html_e( 'Note: Connect this form to your preferred form plugin (Contact Form 7, WPForms, etc.) for production use.', 'aura-fashion' ); ?></p>
		</div>

		<div>
			<h2 style="margin-bottom:24px;"><?php esc_html_e( 'Store Info', 'aura-fashion' ); ?></h2>
			<div class="contact-info-item">
				<div class="icon">
					<svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
				</div>
				<div>
					<strong><?php esc_html_e( 'Address', 'aura-fashion' ); ?></strong>
					<p style="color:var(--aura-gray);margin:4px 0 0;"><?php esc_html_e( '123 Fashion Avenue, Style City, 10001', 'aura-fashion' ); ?></p>
				</div>
			</div>
			<div class="contact-info-item">
				<div class="icon">
					<svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328z"/></svg>
				</div>
				<div>
					<strong><?php esc_html_e( 'Phone', 'aura-fashion' ); ?></strong>
					<p style="color:var(--aura-gray);margin:4px 0 0;">+1 (555) 123-4567</p>
				</div>
			</div>
			<div class="contact-info-item">
				<div class="icon">
					<svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/></svg>
				</div>
				<div>
					<strong><?php esc_html_e( 'Email', 'aura-fashion' ); ?></strong>
					<p style="color:var(--aura-gray);margin:4px 0 0;">hello@aurafashion.com</p>
				</div>
			</div>
			<div class="contact-info-item">
				<div class="icon">
					<svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>
				</div>
				<div>
					<strong><?php esc_html_e( 'Hours', 'aura-fashion' ); ?></strong>
					<p style="color:var(--aura-gray);margin:4px 0 0;"><?php esc_html_e( 'Mon–Fri 9:00–18:00 · Sat 10:00–16:00', 'aura-fashion' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
