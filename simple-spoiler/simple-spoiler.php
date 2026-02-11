<?php
/*
Plugin Name: Simple Spoiler
Plugin URI: https://webliberty.ru/simple-spoiler/
Description: Allows creating simple spoiler via shortcode.
Version: 1.5
Author: Webliberty
Author URI: https://webliberty.ru/
Text Domain: simple-spoiler
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'simple_spoiler_add_admin_menu' );
function simple_spoiler_add_admin_menu() {
	add_menu_page(
		__( 'Simple Spoiler Settings', 'simple-spoiler' ),
		'Simple Spoiler',
		'manage_options',
		'simple-spoiler',
		'simple_spoiler_settings_page',
		'dashicons-editor-code',
		80
	);
}

function simple_spoiler_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'simple_spoiler_options' );
			do_settings_sections( 'simple_spoiler_page' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action( 'admin_notices', 'simple_spoiler_admin_notice' );
function simple_spoiler_admin_notice() {
	if ( isset( $_GET['settings-updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings updated', 'simple-spoiler' ) . '</p></div>';
	}
}

add_action( 'admin_init', 'simple_spoiler_register_settings' );
function simple_spoiler_register_settings() {
	register_setting( 'simple_spoiler_options', 'simple_spoiler_bg_wrap', 'simple_spoiler_sanitize_color' );
	register_setting( 'simple_spoiler_options', 'simple_spoiler_bg_body', 'simple_spoiler_sanitize_color' );
	register_setting( 'simple_spoiler_options', 'simple_spoiler_br_color', 'simple_spoiler_sanitize_color' );

	add_settings_section(
		'simple_spoiler_colors',
		__( 'Spoiler Color Settings', 'simple-spoiler' ),
		'__return_null',
		'simple_spoiler_page'
	);

	add_settings_field( 'spoiler_bg_wrap', __( 'Header Background', 'simple-spoiler' ), 'simple_spoiler_field_bg_wrap', 'simple_spoiler_page', 'simple_spoiler_colors' );
	add_settings_field( 'spoiler_bg_body', __( 'Body Background', 'simple-spoiler' ), 'simple_spoiler_field_bg_body', 'simple_spoiler_page', 'simple_spoiler_colors' );
	add_settings_field( 'spoiler_border_color', __( 'Border Color', 'simple-spoiler' ), 'simple_spoiler_field_br_color', 'simple_spoiler_page', 'simple_spoiler_colors' );
}

function simple_spoiler_field_bg_wrap() {
	$value = get_option( 'simple_spoiler_bg_wrap', array( 'input' => '#f1f1f1' ) );
	$color = isset( $value['input'] ) ? sanitize_hex_color( $value['input'] ) : '#f1f1f1';
	echo '<input type="color" name="simple_spoiler_bg_wrap[input]" value="' . esc_attr( $color ) . '" />';
}

function simple_spoiler_field_bg_body() {
	$value = get_option( 'simple_spoiler_bg_body', array( 'input' => '#fbfbfb' ) );
	$color = isset( $value['input'] ) ? sanitize_hex_color( $value['input'] ) : '#fbfbfb';
	echo '<input type="color" name="simple_spoiler_bg_body[input]" value="' . esc_attr( $color ) . '" />';
}

function simple_spoiler_field_br_color() {
	$value = get_option( 'simple_spoiler_br_color', array( 'input' => '#dddddd' ) );
	$color = isset( $value['input'] ) ? sanitize_hex_color( $value['input'] ) : '#dddddd';
	echo '<input type="color" name="simple_spoiler_br_color[input]" value="' . esc_attr( $color ) . '" />';
}

function simple_spoiler_sanitize_color( $option ) {
	$sanitized = array();

	if ( is_array( $option ) && isset( $option['input'] ) ) {
		$sanitized['input'] = sanitize_hex_color( $option['input'] );
	}

	return $sanitized;
}

add_shortcode( 'spoiler', 'simple_spoiler_shortcode_render' );
function simple_spoiler_shortcode_render( $atts, $content = '' ) {
	$atts = shortcode_atts(
		array(
			'title' => __( 'Spoiler', 'simple-spoiler' ),
		),
		$atts,
		'spoiler'
	);

	$title = esc_html( $atts['title'] );
	$body  = wp_kses_post( $content );

	return '<div class="spoiler-wrap">
				<div class="spoiler-head folded">' . $title . '</div>
				<div class="spoiler-body">' . $body . '</div>
			</div>';
}

add_filter( 'comment_text', 'simple_spoiler_enable_in_comments', 11, 2 );
function simple_spoiler_enable_in_comments( $content, $comment ) {
	if ( isset( $comment->comment_type ) && 'comment' === $comment->comment_type ) {
		$original_tags = $GLOBALS['shortcode_tags'];
		$GLOBALS['shortcode_tags'] = array( 'spoiler' => $original_tags['spoiler'] );
		$content = do_shortcode( $content );
		$GLOBALS['shortcode_tags'] = $original_tags;
	}
	return $content;
}

add_action( 'wp_enqueue_scripts', 'simple_spoiler_enqueue_assets' );
function simple_spoiler_enqueue_assets() {
	wp_enqueue_style(
		'simple-spoiler-style',
		plugins_url( 'css/simple-spoiler.min.css', __FILE__ ),
		array(),
		'1.5'
	);

	wp_enqueue_script(
		'simple-spoiler-script',
		plugins_url( 'js/simple-spoiler.min.js', __FILE__ ),
		array( 'jquery' ),
		'1.5',
		true
	);
}

add_action( 'wp_head', 'simple_spoiler_inline_css' );
function simple_spoiler_inline_css() {
	$wrap_color = sanitize_hex_color( get_option( 'simple_spoiler_bg_wrap' )['input'] ?? '#f1f1f1' );
	$body_color = sanitize_hex_color( get_option( 'simple_spoiler_bg_body' )['input'] ?? '#fbfbfb' );
	$border     = sanitize_hex_color( get_option( 'simple_spoiler_br_color' )['input'] ?? '#dddddd' );

	echo '<style type="text/css">
		.spoiler-head {
			background: ' . esc_attr( $wrap_color ) . ';
			border: 1px solid ' . esc_attr( $border ) . ';
		}
		.spoiler-body {
			background: ' . esc_attr( $body_color ) . ';
			border-width: 0 1px 1px 1px;
			border-style: solid;
			border-color: ' . esc_attr( $border ) . ';
		}
	</style>';
}
