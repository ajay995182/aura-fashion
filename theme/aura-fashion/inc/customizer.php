<?php
/**
 * Theme Customizer
 *
 * @package Aura_Fashion
 */

defined( 'ABSPATH' ) || exit;

function aura_customize_register( $wp_customize ) {

	/* ---- Colors ---- */
	$wp_customize->add_section( 'aura_colors', array(
		'title'    => __( 'Aura Colors', 'aura-fashion' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'aura_accent_color', array(
		'default'           => '#c9a227',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'aura_accent_color', array(
		'label'   => __( 'Accent (Gold) Color', 'aura-fashion' ),
		'section' => 'aura_colors',
	) ) );

	$wp_customize->add_setting( 'aura_primary_color', array(
		'default'           => '#0d0d0d',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'aura_primary_color', array(
		'label'   => __( 'Primary (Black) Color', 'aura-fashion' ),
		'section' => 'aura_colors',
	) ) );

	/* ---- Hero ---- */
	$wp_customize->add_section( 'aura_hero', array(
		'title'    => __( 'Hero Section', 'aura-fashion' ),
		'priority' => 35,
	) );

	$wp_customize->add_setting( 'aura_hero_badge', array(
		'default'           => __( 'New Season 2026', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_hero_badge', array(
		'label'   => __( 'Hero Badge Text', 'aura-fashion' ),
		'section' => 'aura_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'aura_hero_title', array(
		'default'           => __( 'Elevate Your Style', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_hero_title', array(
		'label'   => __( 'Hero Title', 'aura-fashion' ),
		'section' => 'aura_hero',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'aura_hero_desc', array(
		'default'           => __( 'Discover timeless pieces crafted for the modern wardrobe. Premium quality, effortless elegance.', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'aura_hero_desc', array(
		'label'   => __( 'Hero Description', 'aura-fashion' ),
		'section' => 'aura_hero',
		'type'    => 'textarea',
	) );

	/* ---- Store Name ---- */
	$wp_customize->add_setting( 'aura_store_name', array(
		'default'           => get_bloginfo( 'name' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_store_name', array(
		'label'   => __( 'Store Name (when no logo)', 'aura-fashion' ),
		'section' => 'title_tagline',
		'type'    => 'text',
		'priority'=> 9,
	) );

	/* ---- Size Guide ---- */
	$wp_customize->add_section( 'aura_size_guide', array(
		'title'    => __( 'Size Guide', 'aura-fashion' ),
		'priority' => 40,
	) );

	$wp_customize->add_setting( 'aura_size_guide_content', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'aura_size_guide_content', array(
		'label'       => __( 'Size Guide HTML / Table', 'aura-fashion' ),
		'description' => __( 'Paste HTML table or leave blank for default fashion size chart.', 'aura-fashion' ),
		'section'     => 'aura_size_guide',
		'type'        => 'textarea',
	) );

	/* ---- Email Marketing (ESP) ---- */
	$wp_customize->add_section( 'aura_esp', array(
		'title'       => __( 'Email Marketing (ESP)', 'aura-fashion' ),
		'description' => __( 'Connect Mailchimp, Klaviyo or Brevo. Leave API key empty to store emails locally only.', 'aura-fashion' ),
		'priority'    => 42,
	) );

	$wp_customize->add_setting( 'aura_esp_provider', array(
		'default'           => 'local',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_esp_provider', array(
		'label'   => __( 'Provider', 'aura-fashion' ),
		'section' => 'aura_esp',
		'type'    => 'select',
		'choices' => array(
			'local'     => __( 'Local storage only', 'aura-fashion' ),
			'mailchimp' => 'Mailchimp',
			'klaviyo'   => 'Klaviyo',
			'brevo'     => 'Brevo (Sendinblue)',
		),
	) );

	$wp_customize->add_setting( 'aura_esp_api_key', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_esp_api_key', array(
		'label'       => __( 'API Key', 'aura-fashion' ),
		'description' => __( 'Mailchimp: key ends with -usXX. Klaviyo: private API key. Brevo: v3 API key.', 'aura-fashion' ),
		'section'     => 'aura_esp',
		'type'        => 'password',
	) );

	$wp_customize->add_setting( 'aura_esp_list_id', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_esp_list_id', array(
		'label'       => __( 'List / Audience ID', 'aura-fashion' ),
		'description' => __( 'Mailchimp Audience ID, Klaviyo List ID, or Brevo List ID (number).', 'aura-fashion' ),
		'section'     => 'aura_esp',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'aura_esp_capture_checkout', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'aura_esp_capture_checkout', array(
		'label'   => __( 'Also subscribe emails entered at checkout', 'aura-fashion' ),
		'section' => 'aura_esp',
		'type'    => 'checkbox',
	) );

	/* ---- Exit Intent Popup ---- */
	$wp_customize->add_section( 'aura_exit_popup', array(
		'title'    => __( 'Exit-Intent / Discount Popup', 'aura-fashion' ),
		'priority' => 43,
	) );

	$wp_customize->add_setting( 'aura_exit_popup_enable', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'aura_exit_popup_enable', array(
		'label'   => __( 'Enable Exit-Intent Popup', 'aura-fashion' ),
		'section' => 'aura_exit_popup',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'aura_exit_popup_title', array(
		'default'           => __( 'Wait! Don\'t leave empty-handed', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_exit_popup_title', array(
		'label'   => __( 'Popup Title', 'aura-fashion' ),
		'section' => 'aura_exit_popup',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'aura_exit_popup_desc', array(
		'default'           => __( 'Get 10% off your first order. Enter your email below.', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'aura_exit_popup_desc', array(
		'label'   => __( 'Popup Description', 'aura-fashion' ),
		'section' => 'aura_exit_popup',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'aura_exit_popup_code', array(
		'default'           => 'WELCOME10',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_exit_popup_code', array(
		'label'       => __( 'Discount Code to Show', 'aura-fashion' ),
		'description' => __( 'Create this coupon in WooCommerce → Coupons.', 'aura-fashion' ),
		'section'     => 'aura_exit_popup',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'aura_exit_popup_btn', array(
		'default'           => __( 'Get My Discount', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_exit_popup_btn', array(
		'label'   => __( 'Button Text', 'aura-fashion' ),
		'section' => 'aura_exit_popup',
		'type'    => 'text',
	) );

	/* ---- Free Shipping Bar ---- */
	$wp_customize->add_section( 'aura_shipping_bar', array(
		'title'    => __( 'Free Shipping Progress Bar', 'aura-fashion' ),
		'priority' => 44,
	) );

	$wp_customize->add_setting( 'aura_free_shipping_amount', array(
		'default'           => 100,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'aura_free_shipping_amount', array(
		'label'       => __( 'Free Shipping Threshold', 'aura-fashion' ),
		'description' => __( 'Cart subtotal needed to unlock free shipping. Set 0 to disable the bar.', 'aura-fashion' ),
		'section'     => 'aura_shipping_bar',
		'type'        => 'number',
	) );

	/* ---- Instagram ---- */
	$wp_customize->add_section( 'aura_instagram', array(
		'title'    => __( 'Instagram Feed', 'aura-fashion' ),
		'priority' => 45,
	) );

	$wp_customize->add_setting( 'aura_instagram_enable', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'aura_instagram_enable', array(
		'label'   => __( 'Show Instagram section on homepage', 'aura-fashion' ),
		'section' => 'aura_instagram',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'aura_instagram_title', array(
		'default'           => __( 'Follow Us @AuraFashion', 'aura-fashion' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_instagram_title', array(
		'label'   => __( 'Section Title', 'aura-fashion' ),
		'section' => 'aura_instagram',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'aura_instagram_token', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'aura_instagram_token', array(
		'label'       => __( 'Instagram Access Token (optional)', 'aura-fashion' ),
		'description' => __( 'Long-lived token from Facebook Developers. Or use Embed code below.', 'aura-fashion' ),
		'section'     => 'aura_instagram',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'aura_instagram_embed', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'aura_instagram_embed', array(
		'label'       => __( 'Instagram Embed / Widget HTML', 'aura-fashion' ),
		'description' => __( 'Paste SnapWidget, Elfsight, or official Instagram embed code.', 'aura-fashion' ),
		'section'     => 'aura_instagram',
		'type'        => 'textarea',
	) );

	/* ---- Loyalty ---- */
	$wp_customize->add_section( 'aura_loyalty', array(
		'title'       => __( 'Loyalty Points (Basic)', 'aura-fashion' ),
		'description' => __( 'Simple points system. For full referral programs use YITH Points or LoyaltyLion.', 'aura-fashion' ),
		'priority'    => 46,
	) );

	$wp_customize->add_setting( 'aura_loyalty_enable', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'aura_loyalty_enable', array(
		'label'   => __( 'Enable basic loyalty points (1 point per $ spent)', 'aura-fashion' ),
		'section' => 'aura_loyalty',
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'aura_customize_register' );

/**
 * Output custom CSS from Customizer
 */
function aura_customizer_css() {
	$accent  = get_theme_mod( 'aura_accent_color', '#c9a227' );
	$primary = get_theme_mod( 'aura_primary_color', '#0d0d0d' );
	?>
	<style id="aura-customizer-css">
		:root {
			--aura-gold: <?php echo esc_attr( $accent ); ?>;
			--aura-black: <?php echo esc_attr( $primary ); ?>;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'aura_customizer_css' );
