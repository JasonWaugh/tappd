<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor','hello-elementor-theme-style' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

function dependencies_files()
{
    wp_enqueue_style('open_sans', '//fonts.googleapis.com/css?family=Open+Sans:300');
    wp_enqueue_script('site_jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js');
	wp_enqueue_script('font_awesome', '//kit.fontawesome.com/04893f3499.js');
}
add_action('wp_enqueue_scripts', 'dependencies_files');

// Add new tab to My Account menu

add_filter ( 'woocommerce_account_menu_items', 'wpsh_custom_endpoint', 40 );
function wpsh_custom_endpoint( $menu_links ){
 
	$menu_links = array_slice( $menu_links, 0, 5, true ) 
		// Add your own slug (support, for example) and tab title here below
	+ array( 'tappd-profile' => 'Tappd Profile' ) 
	+ array_slice( $menu_links, 5, NULL, true );
 
	return $menu_links;
 
}
// Let’s register this new endpoint permalink

add_action( 'init', 'wpsh_new_endpoint' );
function wpsh_new_endpoint() {
	add_rewrite_endpoint( 'tappd-profile', EP_PAGES ); // Don’t forget to change the slug here
}

// Now let’s add some content inside your endpoint

add_action( 'woocommerce_account_tappd-profile_endpoint', 'wpsh_endpoint_content' );
function wpsh_endpoint_content() {
 
	// At the moment I will add Learndash profile with the shordcode
	
	echo do_shortcode('[elementor-template id="3280"]');
}

// NB! In order to make it work you need to go to Settings > Permalinks and just push "Save Changes" button.

/*Validation Sign up*/
function elementor_form_email_field_validation( $field, $record, $ajax_handler ) {
	// Validate email format
	if ( ! is_email( $field['value'] ) ) {
		$ajax_handler->add_error( $field['id'], esc_html__( 'Invalid email address, it must be in xx@xx.xx format.', 'textdomain' ) );
		return;
	}
	
	if ( strlen( $field['value'] ) > 40 ) {
		$ajax_handler->add_error( $field['id'], esc_html__( 'Max 40 characters', 'textdomain' ) );
		return;
	}

	// Do your validation here.
}
add_action( 'elementor_pro/forms/validation/email', 'elementor_form_email_field_validation', 10, 3 );

function elementor_form_textfullname_field_validation( $field, $record, $ajax_handler ) {
	// Validate text field
	if (strlen($field['value']) > 20) {
		$ajax_handler->add_error( $field['id'], esc_html__( 'Max 20 Characters.', 'textdomain' ) );
		return;
	}
}
add_action( 'elementor_pro/forms/validation/text', 'elementor_form_textfullname_field_validation', 10, 3 );

function elementor_form_textarea_field_validation( $field, $record, $ajax_handler ) {
	// Validate text field
	if (strlen($field['value']) > 200) {
		$ajax_handler->add_error( $field['id'], esc_html__( 'Max 200 Characters.', 'textdomain' ) );
		return;
	}
}
add_action( 'elementor_pro/forms/validation/textarea', 'elementor_form_textarea_field_validation', 10, 3 );

function elementor_form_tel2_field_validation( $field, $record, $ajax_handler ) {
	// Validate text field
	if (strlen($field['value']) > 10) {
		$ajax_handler->add_error( $field['id'], esc_html__( 'Max 10 Characters.', 'textdomain' ) );
		return;
	}
}
add_action( 'elementor_pro/forms/validation/tel', 'elementor_form_tel2_field_validation', 10, 3 );

function elementor_form_tel_field_validation( $field, $record, $ajax_handler ) {
	// Remove native validation
	$forms_module = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' );
	remove_action( 'elementor_pro/forms/validation/tel', [ $forms_module->field_types['tel'], 'validation' ] );

	// Run your own validation, ex:
	if ( empty( $field['value'] ) ) {
		return;
	}

	// Match this format XXX-XXX-XXXX, e.g. 123-456-7890
	if ( preg_match( '/[0-9]{10}/', $field['value'] ) !== 1 ) {
		$ajax_handler->add_error( $field['id'], esc_html__( 'Please make sure the phone number is in XXXXXXXXXX format', 'textdomain' ) );
	}
}
add_action( 'elementor_pro/forms/validation/tel', 'elementor_form_tel_field_validation', 10, 3 );


/* Custom script with no dependencies, enqueued in the header */
add_action('login_enqueue_scripts', 'tutsplus_enqueue_custom_js');
function tutsplus_enqueue_custom_js() {
    wp_enqueue_script('custom', get_stylesheet_directory_uri().'/scripts/script5.js');
}





