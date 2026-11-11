<?php
/**
 * Plugin Name:       WP Then Now Slider
 * Plugin URI:        https://github.com/cedlelensois-lang/WP-Then-Now-slider
 * Description:       Create and manage multiple before / after photo comparisons with a draggable slider. Includes the [then_now id="X"] shortcode and a Gutenberg block.
 * Version:           2.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Cédric
 * License:           GPL-2.0-or-later
 * Text Domain:       wp-then-now-slider
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPTN_VERSION', '2.1.0' );
define( 'WPTN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPTN_CPT', 'wptn_comparison' );

/* -------------------------------------------------------------------------
 * Schéma des réglages
 * Une seule définition sert pour : valeurs par défaut, nettoyage, écran
 * d'édition, attributs du shortcode et du bloc, panneaux du bloc.
 * ---------------------------------------------------------------------- */

/**
 * Sections du schéma.
 *
 * @return array
 */
function wptn_sections() {
	return array(
		'images'    => __( 'Images', 'wp-then-now-slider' ),
		'layout'    => __( 'Layout and display', 'wp-then-now-slider' ),
		'colors'    => __( 'Colors', 'wp-then-now-slider' ),
		'filters'   => __( 'Filters', 'wp-then-now-slider' ),
		'watermark' => __( 'Watermark', 'wp-then-now-slider' ),
		'badge'     => __( 'Badge', 'wp-then-now-slider' ),
		'caption'   => __( 'Caption', 'wp-then-now-slider' ),
	);
}

/**
 * Définition de tous les réglages d'une comparaison.
 *
 * @return array
 */
function wptn_schema() {
	$positions = array(
		'top-left'      => __( 'Top left', 'wp-then-now-slider' ),
		'top-center'    => __( 'Top center', 'wp-then-now-slider' ),
		'top-right'     => __( 'Top right', 'wp-then-now-slider' ),
		'bottom-left'   => __( 'Bottom left', 'wp-then-now-slider' ),
		'bottom-center' => __( 'Bottom center', 'wp-then-now-slider' ),
		'bottom-right'  => __( 'Bottom right', 'wp-then-now-slider' ),
	);
	$filters   = array(
		'none'      => __( 'None', 'wp-then-now-slider' ),
		'grayscale' => __( 'Black & white', 'wp-then-now-slider' ),
		'sepia'     => __( 'Sepia', 'wp-then-now-slider' ),
		'vintage'   => __( 'Vintage', 'wp-then-now-slider' ),
		'warm'      => __( 'Warm', 'wp-then-now-slider' ),
		'cool'      => __( 'Cool', 'wp-then-now-slider' ),
		'contrast'  => __( 'High contrast', 'wp-then-now-slider' ),
		'saturate'  => __( 'Vivid colors', 'wp-then-now-slider' ),
		'blur'      => __( 'Blur', 'wp-then-now-slider' ),
	);
	$slider_modes = array( 'show_if' => array( 'mode' => array( 'slider', 'hover' ) ) );

	return array(
		/* Images */
		'before'             => array( 'type' => 'media', 'default' => 0, 'section' => 'images', 'label' => __( 'Before image', 'wp-then-now-slider' ) ),
		'after'              => array( 'type' => 'media', 'default' => 0, 'section' => 'images', 'label' => __( 'After image', 'wp-then-now-slider' ) ),
		'labels'             => array( 'type' => 'bool', 'default' => true, 'section' => 'images', 'label' => __( 'Show labels', 'wp-then-now-slider' ) ),
		'before_label'       => array( 'type' => 'text', 'default' => __( 'Before', 'wp-then-now-slider' ), 'section' => 'images', 'label' => __( 'Before label', 'wp-then-now-slider' ), 'show_if' => array( 'labels' => array( true ) ) ),
		'after_label'        => array( 'type' => 'text', 'default' => __( 'After', 'wp-then-now-slider' ), 'section' => 'images', 'label' => __( 'After label', 'wp-then-now-slider' ), 'show_if' => array( 'labels' => array( true ) ) ),

		/* Disposition et affichage */
		'mode'               => array(
			'type'    => 'select',
			'default' => 'slider',
			'section' => 'layout',
			'label'   => __( 'Display mode', 'wp-then-now-slider' ),
			'options' => array(
				'slider' => __( 'Drag the handle', 'wp-then-now-slider' ),
				'hover'  => __( 'Follow the mouse on hover', 'wp-then-now-slider' ),
				'click'  => __( 'Click to switch images', 'wp-then-now-slider' ),
				'side'   => __( 'Side by side', 'wp-then-now-slider' ),
			),
		),
		'orientation'        => array(
			'type'    => 'select',
			'default' => 'horizontal',
			'section' => 'layout',
			'label'   => __( 'Orientation', 'wp-then-now-slider' ),
			'options' => array(
				'horizontal' => __( 'Horizontal (left / right)', 'wp-then-now-slider' ),
				'vertical'   => __( 'Vertical (top / bottom)', 'wp-then-now-slider' ),
			),
			'show_if' => array( 'mode' => array( 'slider', 'hover', 'side' ) ),
		),
		'reverse'            => array( 'type' => 'bool', 'default' => false, 'section' => 'layout', 'label' => __( 'Reverse order', 'wp-then-now-slider' ), 'help' => __( 'Show the after image on the left (or at the top).', 'wp-then-now-slider' ) ),
		'start'              => array( 'type' => 'range', 'default' => 50, 'min' => 0, 'max' => 100, 'section' => 'layout', 'label' => __( 'Starting position (%)', 'wp-then-now-slider' ) ) + $slider_modes,
		'handle_style'       => array(
			'type'    => 'select',
			'default' => 'circle',
			'section' => 'layout',
			'label'   => __( 'Handle style', 'wp-then-now-slider' ),
			'options' => array(
				'circle' => __( 'Circle', 'wp-then-now-slider' ),
				'square' => __( 'Square', 'wp-then-now-slider' ),
				'arrows' => __( 'Arrows only', 'wp-then-now-slider' ),
				'line'   => __( 'Line only', 'wp-then-now-slider' ),
			),
		) + $slider_modes,
		'handle_visibility'  => array(
			'type'    => 'select',
			'default' => 'always',
			'section' => 'layout',
			'label'   => __( 'Handle visibility', 'wp-then-now-slider' ),
			'options' => array(
				'always' => __( 'Always visible', 'wp-then-now-slider' ),
				'hover'  => __( 'On mouse over', 'wp-then-now-slider' ),
				'hidden' => __( 'Hidden', 'wp-then-now-slider' ),
			),
			'help'    => __( 'When hidden, visitors can still drag anywhere on the image.', 'wp-then-now-slider' ),
		) + $slider_modes,
		'fullscreen'         => array( 'type' => 'bool', 'default' => false, 'section' => 'layout', 'label' => __( 'Fullscreen button', 'wp-then-now-slider' ) ),

		/* Couleurs */
		'color_mode'         => array(
			'type'    => 'radio',
			'default' => 'global',
			'section' => 'colors',
			'label'   => __( 'Colors', 'wp-then-now-slider' ),
			'options' => array(
				'global' => __( 'Global', 'wp-then-now-slider' ),
				'custom' => __( 'Individual (specific to this comparison)', 'wp-then-now-slider' ),
			),
		),
		'color'              => array( 'type' => 'color', 'default' => '', 'section' => 'colors', 'label' => __( 'Handle color', 'wp-then-now-slider' ), 'help' => __( 'Empty = global color.', 'wp-then-now-slider' ), 'show_if' => array( 'color_mode' => array( 'custom' ) ) ),
		'focus_color'        => array( 'type' => 'color', 'default' => '', 'section' => 'colors', 'label' => __( 'Selection color', 'wp-then-now-slider' ), 'help' => __( 'Handle color when selected or being dragged. Empty = global color.', 'wp-then-now-slider' ), 'show_if' => array( 'color_mode' => array( 'custom' ) ) ),

		/* Filtres */
		'before_filter'      => array( 'type' => 'select', 'default' => 'none', 'section' => 'filters', 'label' => __( 'Before image filter', 'wp-then-now-slider' ), 'options' => $filters ),
		'after_filter'       => array( 'type' => 'select', 'default' => 'none', 'section' => 'filters', 'label' => __( 'After image filter', 'wp-then-now-slider' ), 'options' => $filters ),

		/* Filigrane */
		'watermark_image'    => array( 'type' => 'media', 'default' => 0, 'section' => 'watermark', 'label' => __( 'Watermark image', 'wp-then-now-slider' ), 'help' => __( 'A logo with a transparent background (PNG or SVG) works best.', 'wp-then-now-slider' ) ),
		'watermark_text'     => array( 'type' => 'text', 'default' => '', 'section' => 'watermark', 'label' => __( 'Watermark text', 'wp-then-now-slider' ), 'help' => __( 'Used when no watermark image is set, e.g. © My studio.', 'wp-then-now-slider' ) ),
		'watermark_position' => array( 'type' => 'select', 'default' => 'bottom-left', 'section' => 'watermark', 'label' => __( 'Watermark position', 'wp-then-now-slider' ), 'options' => $positions + array( 'center' => __( 'Center', 'wp-then-now-slider' ) ) ),
		'watermark_opacity'  => array( 'type' => 'range', 'default' => 60, 'min' => 5, 'max' => 100, 'section' => 'watermark', 'label' => __( 'Watermark opacity (%)', 'wp-then-now-slider' ) ),

		/* Badge */
		'badge_text'         => array( 'type' => 'text', 'default' => '', 'section' => 'badge', 'label' => __( 'Badge text', 'wp-then-now-slider' ), 'help' => __( 'E.g. New, -50%, 2024. Leave empty for no badge.', 'wp-then-now-slider' ) ),
		'badge_position'     => array( 'type' => 'select', 'default' => 'top-center', 'section' => 'badge', 'label' => __( 'Badge position', 'wp-then-now-slider' ), 'options' => $positions ),
		'badge_color'        => array( 'type' => 'color', 'default' => '', 'section' => 'badge', 'label' => __( 'Badge color', 'wp-then-now-slider' ), 'help' => __( 'Empty = selection color.', 'wp-then-now-slider' ) ),

		/* Légende */
		'caption'            => array( 'type' => 'textarea', 'default' => '', 'section' => 'caption', 'label' => __( 'Caption', 'wp-then-now-slider' ), 'help' => __( 'Basic HTML allowed (bold, italic, links).', 'wp-then-now-slider' ) ),
		'caption_position'   => array(
			'type'    => 'select',
			'default' => 'below',
			'section' => 'caption',
			'label'   => __( 'Caption position', 'wp-then-now-slider' ),
			'options' => array(
				'below'   => __( 'Below the image', 'wp-then-now-slider' ),
				'overlay' => __( 'Over the image', 'wp-then-now-slider' ),
			),
		),
	);
}

/**
 * Valeurs par défaut (schéma + options réservées au shortcode).
 *
 * @return array
 */
function wptn_defaults() {
	$d = array();
	foreach ( wptn_schema() as $key => $f ) {
		$d[ $key ] = $f['default'];
	}
	return $d + array(
		'before_alt' => '',
		'after_alt'  => '',
		'class'      => '',
	);
}

/**
 * Nettoie une valeur selon le type du champ.
 *
 * @param array $f         Champ du schéma.
 * @param mixed $value     Valeur brute.
 * @param bool  $allow_url Accepter une URL pour les images (shortcode).
 * @return mixed
 */
function wptn_sanitize_value( $f, $value, $allow_url = false ) {
	switch ( $f['type'] ) {
		case 'media':
			if ( is_numeric( $value ) ) {
				return absint( $value );
			}
			return ( $allow_url && is_string( $value ) ) ? esc_url_raw( $value ) : 0;
		case 'text':
			return sanitize_text_field( (string) $value );
		case 'textarea':
			return wp_kses_post( (string) $value );
		case 'bool':
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		case 'select':
		case 'radio':
			return isset( $f['options'][ $value ] ) ? (string) $value : $f['default'];
		case 'range':
			return max( $f['min'], min( $f['max'], (int) $value ) );
		case 'color':
			$c = sanitize_hex_color( (string) $value );
			return $c ? $c : '';
	}
	return $f['default'];
}

/**
 * Nettoie un tableau de réglages.
 *
 * @param array $in         Valeurs brutes.
 * @param bool  $allow_url  Accepter les URL d'images.
 * @param bool  $only_given Ne garder que les clés présentes.
 * @return array
 */
function wptn_sanitize_settings( $in, $allow_url = false, $only_given = false ) {
	$in  = (array) $in;
	$out = array();
	foreach ( wptn_schema() as $key => $f ) {
		if ( ! array_key_exists( $key, $in ) ) {
			if ( $only_given ) {
				continue;
			}
			// Case non cochée = faux ; sinon valeur par défaut.
			$out[ $key ] = ( 'bool' === $f['type'] ) ? false : $f['default'];
			continue;
		}
		$out[ $key ] = wptn_sanitize_value( $f, $in[ $key ], $allow_url );
	}
	foreach ( array( 'before_alt', 'after_alt' ) as $key ) {
		if ( isset( $in[ $key ] ) ) {
			$out[ $key ] = sanitize_text_field( $in[ $key ] );
		}
	}
	if ( isset( $in['class'] ) ) {
		$out['class'] = implode( ' ', array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) $in['class'] ) ) );
	}
	return $out;
}

/**
 * Réglages enregistrés d'une comparaison (avec reprise des anciens réglages).
 *
 * @param int $post_id ID.
 * @return array
 */
function wptn_get_settings( $post_id ) {
	$saved = get_post_meta( $post_id, '_wptn_settings', true );
	$saved = is_array( $saved ) ? $saved : array();
	if ( ! isset( $saved['mode'] ) && ! empty( $saved['hover'] ) ) {
		$saved['mode'] = 'hover'; // Réglage « hover » de la v2.0.
	}
	return wp_parse_args( $saved, wptn_defaults() );
}

/**
 * Couleurs globales (Avant / Après > Réglages).
 *
 * @return array
 */
function wptn_global_options() {
	$o = get_option( 'wptn_options', array() );
	return wp_parse_args(
		is_array( $o ) ? $o : array(),
		array(
			'color'       => '#ffffff',
			'focus_color' => '',
		)
	);
}

/* -------------------------------------------------------------------------
 * Enregistrement : type de contenu, ressources, bloc, shortcode
 * ---------------------------------------------------------------------- */

/**
 * Charge les traductions.
 */
function wptn_load_textdomain() {
	load_plugin_textdomain( 'wp-then-now-slider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wptn_load_textdomain', 0 );

function wptn_register() {
	register_post_type(
		WPTN_CPT,
		array(
			'labels'          => array(
				'name'               => __( 'Before / after comparisons', 'wp-then-now-slider' ),
				'singular_name'      => __( 'Comparison', 'wp-then-now-slider' ),
				'menu_name'          => __( 'Before / After', 'wp-then-now-slider' ),
				'all_items'          => __( 'All comparisons', 'wp-then-now-slider' ),
				'add_new'            => __( 'Add New', 'wp-then-now-slider' ),
				'add_new_item'       => __( 'New comparison', 'wp-then-now-slider' ),
				'edit_item'          => __( 'Edit comparison', 'wp-then-now-slider' ),
				'new_item'           => __( 'New comparison', 'wp-then-now-slider' ),
				'search_items'       => __( 'Search comparisons', 'wp-then-now-slider' ),
				'not_found'          => __( 'No comparisons found.', 'wp-then-now-slider' ),
				'not_found_in_trash' => __( 'No comparisons found in Trash.', 'wp-then-now-slider' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => true, // Nécessaire pour la liste dans le bloc.
			'menu_icon'       => 'dashicons-image-flip-horizontal',
			'menu_position'   => 25,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
		)
	);

	wp_register_style( 'wptn-style', WPTN_URL . 'assets/css/style.css', array(), WPTN_VERSION );
	wp_register_script( 'wptn-script', WPTN_URL . 'assets/js/slider.js', array(), WPTN_VERSION, true );
	wp_register_script(
		'wptn-block',
		WPTN_URL . 'assets/js/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-server-side-render' ),
		WPTN_VERSION,
		true
	);
	wp_set_script_translations( 'wptn-block', 'wp-then-now-slider', plugin_dir_path( __FILE__ ) . 'languages' );

	// Attributs du bloc générés depuis le schéma.
	$types      = array(
		'media'    => 'number',
		'range'    => 'number',
		'bool'     => 'boolean',
		'text'     => 'string',
		'textarea' => 'string',
		'select'   => 'string',
		'radio'    => 'string',
		'color'    => 'string',
	);
	$attributes = array(
		'comparisonId' => array( 'type' => 'number', 'default' => 0 ),
		'align'        => array( 'type' => 'string', 'default' => '' ),
		'className'    => array( 'type' => 'string', 'default' => '' ),
	);
	foreach ( wptn_schema() as $key => $f ) {
		$attributes[ $key ] = array(
			'type'    => $types[ $f['type'] ],
			'default' => $f['default'],
		);
	}
	// Anciens attributs (v1.x / v2.0), convertis à l'affichage.
	foreach ( array( 'beforeId' => 'number', 'afterId' => 'number', 'beforeUrl' => 'string', 'afterUrl' => 'string', 'beforeLabel' => 'string', 'afterLabel' => 'string', 'showLabels' => 'boolean', 'hover' => 'boolean', 'useGlobal' => 'boolean', 'focusColor' => 'string' ) as $key => $type ) {
		$attributes[ $key ] = array( 'type' => $type );
	}

	register_block_type(
		'wptn/slider',
		array(
			'editor_script'   => 'wptn-block',
			'editor_style'    => 'wptn-style',
			'render_callback' => 'wptn_render_block',
			'attributes'      => $attributes,
			'supports'        => array( 'align' => array( 'wide', 'full' ) ),
		)
	);

	// Shortcode principal + anciens noms (compatibilité), seulement s'ils sont libres.
	add_shortcode( 'then_now', 'wptn_shortcode' );
	foreach ( wptn_legacy_shortcodes() as $tag ) {
		if ( ! shortcode_exists( $tag ) ) {
			add_shortcode( $tag, 'wptn_shortcode' );
		}
	}
}
add_action( 'init', 'wptn_register' );

/**
 * Anciens noms du shortcode, toujours acceptés.
 *
 * @return string[]
 */
function wptn_legacy_shortcodes() {
	return array( 'avant_apres', 'before_after' );
}

/**
 * Charge CSS/JS dans le <head> si la page contient un slider.
 */
function wptn_maybe_enqueue_early() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( ! $post ) {
		return;
	}
	$found = has_block( 'wptn/slider', $post ) || has_shortcode( $post->post_content, 'then_now' );
	foreach ( wptn_legacy_shortcodes() as $tag ) {
		$found = $found || has_shortcode( $post->post_content, $tag );
	}
	if ( $found ) {
		wp_enqueue_style( 'wptn-style' );
		wp_enqueue_script( 'wptn-script' );
	}
}
add_action( 'wp_enqueue_scripts', 'wptn_maybe_enqueue_early' );

/* -------------------------------------------------------------------------
 * Rendu
 * ---------------------------------------------------------------------- */

/**
 * Infos d'une image à partir d'un ID de média ou d'une URL.
 *
 * @param int|string $value ID ou URL.
 * @param string     $alt   Texte alternatif de secours.
 * @return array|null
 */
function wptn_get_image( $value, $alt = '' ) {
	if ( is_numeric( $value ) && (int) $value > 0 ) {
		$src = wp_get_attachment_image_src( (int) $value, 'full' );
		if ( ! $src ) {
			return null;
		}
		$meta_alt = get_post_meta( (int) $value, '_wp_attachment_image_alt', true );
		return array(
			'url'    => $src[0],
			'width'  => (int) $src[1],
			'height' => (int) $src[2],
			'alt'    => $alt ? $alt : $meta_alt,
		);
	}
	if ( is_string( $value ) && '' !== trim( $value ) ) {
		return array(
			'url'    => $value,
			'width'  => 0,
			'height' => 0,
			'alt'    => $alt,
		);
	}
	return null;
}

/**
 * Balise <img>.
 *
 * @param array  $image Infos de l'image.
 * @param string $class Classes.
 * @return string
 */
function wptn_img_tag( $image, $class ) {
	$dims = '';
	if ( $image['width'] && $image['height'] ) {
		$dims = sprintf( ' width="%d" height="%d"', $image['width'], $image['height'] );
	}
	return sprintf(
		'<img class="%s" src="%s" alt="%s"%s loading="lazy" decoding="async" draggable="false" />',
		esc_attr( $class ),
		esc_url( $image['url'] ),
		esc_attr( $image['alt'] ),
		$dims
	);
}

/**
 * HTML du slider.
 *
 * @param array $args Réglages (non nettoyés).
 * @return string
 */
function wptn_render( $args ) {
	$s = wp_parse_args( wptn_sanitize_settings( $args, true, true ), wptn_defaults() );

	$before = wptn_get_image( $s['before'], $s['before_alt'] );
	$after  = wptn_get_image( $s['after'], $s['after_alt'] );

	if ( ! $before || ! $after ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<p class="wptn-error">' . esc_html__( 'WP Then Now Slider: please set two images (before and after).', 'wp-then-now-slider' ) . '</p>';
		}
		return '';
	}

	wp_enqueue_style( 'wptn-style' );
	wp_enqueue_script( 'wptn-script' );

	$mode        = $s['mode'];
	$orientation = ( 'click' === $mode ) ? 'horizontal' : $s['orientation'];
	$is_slider   = in_array( $mode, array( 'slider', 'hover' ), true );

	// Couleurs : individuelles si « custom », sinon globales.
	$global = wptn_global_options();
	$custom = ( 'custom' === $s['color_mode'] );
	$color  = ( $custom && $s['color'] ) ? $s['color'] : sanitize_hex_color( $global['color'] );
	$color  = $color ? $color : '#ffffff';
	$focus  = ( $custom && $s['focus_color'] ) ? $s['focus_color'] : sanitize_hex_color( $global['focus_color'] );

	$style = sprintf( '--wptn-pos: %s%%; --wptn-color: %s; --wptn-wm-opacity: %s;', $s['start'], $color, $s['watermark_opacity'] / 100 );
	if ( $focus ) {
		$style .= ' --wptn-focus: ' . $focus . ';';
	}
	if ( $s['badge_color'] ) {
		$style .= ' --wptn-badge: ' . $s['badge_color'] . ';';
	}

	// « first » = image à gauche (ou en haut), « second » = à droite (ou en bas).
	$items = array(
		array( 'img' => $before, 'label' => $s['before_label'], 'filter' => $s['before_filter'] ),
		array( 'img' => $after, 'label' => $s['after_label'], 'filter' => $s['after_filter'] ),
	);
	if ( $s['reverse'] ) {
		$items = array_reverse( $items );
	}
	list( $first, $second ) = $items;

	$classes = array(
		'wptn',
		'wptn-' . $orientation,
		'wptn-mode-' . $mode,
		'wptn-handle-' . $s['handle_style'],
		'wptn-handle-vis-' . $s['handle_visibility'],
		'' !== $s['caption'] ? 'wptn-has-caption wptn-caption-' . $s['caption_position'] : '',
		$s['class'],
	);

	$slider_attrs = '';
	if ( 'click' === $mode ) {
		$slider_attrs = sprintf(
			' role="button" tabindex="0" aria-pressed="false" aria-label="%s"',
			esc_attr__( 'Click to switch between the two images', 'wp-then-now-slider' )
		);
	}

	ob_start();
	?>
	<figure class="<?php echo esc_attr( trim( implode( ' ', array_filter( $classes ) ) ) ); ?>"
		data-mode="<?php echo esc_attr( $mode ); ?>"
		data-orientation="<?php echo esc_attr( $orientation ); ?>"
		style="<?php echo esc_attr( $style ); ?>">
		<div class="wptn-slider"<?php echo $slider_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<?php
			// L'image « second » est dans le flux (elle donne la hauteur), « first » se superpose.
			echo wptn_img_tag( $second['img'], 'wptn-img wptn-second wptn-filter-' . $second['filter'] ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo wptn_img_tag( $first['img'], 'wptn-img wptn-first wptn-filter-' . $first['filter'] ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>

			<?php if ( $s['labels'] ) : ?>
				<?php if ( '' !== $first['label'] ) : ?>
					<span class="wptn-label wptn-label-first"><?php echo esc_html( $first['label'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $second['label'] ) : ?>
					<span class="wptn-label wptn-label-second"><?php echo esc_html( $second['label'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			$wm = $s['watermark_image'] ? wptn_get_image( $s['watermark_image'] ) : null;
			if ( $wm ) :
				?>
				<img class="wptn-watermark wptn-pos-<?php echo esc_attr( $s['watermark_position'] ); ?>" src="<?php echo esc_url( $wm['url'] ); ?>" alt="" aria-hidden="true" draggable="false" />
			<?php elseif ( '' !== $s['watermark_text'] ) : ?>
				<span class="wptn-watermark wptn-watermark-text wptn-pos-<?php echo esc_attr( $s['watermark_position'] ); ?>" aria-hidden="true"><?php echo esc_html( $s['watermark_text'] ); ?></span>
			<?php endif; ?>

			<?php if ( '' !== $s['badge_text'] ) : ?>
				<span class="wptn-badge wptn-pos-<?php echo esc_attr( $s['badge_position'] ); ?>"><?php echo esc_html( $s['badge_text'] ); ?></span>
			<?php endif; ?>

			<?php if ( $is_slider ) : ?>
				<div class="wptn-handle">
					<span class="wptn-line"></span>
					<span class="wptn-knob" role="slider" tabindex="0"
						aria-label="<?php esc_attr_e( 'Before / after comparison slider', 'wp-then-now-slider' ); ?>"
						aria-orientation="<?php echo esc_attr( $orientation ); ?>"
						aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $s['start'] ); ?>">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
							<path d="M9 6l-6 6 6 6M15 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $s['caption'] && 'overlay' === $s['caption_position'] ) : ?>
				<div class="wptn-caption wptn-caption-over"><?php echo wp_kses_post( wpautop( $s['caption'] ) ); ?></div>
			<?php endif; ?>

			<?php if ( $s['fullscreen'] ) : ?>
				<button type="button" class="wptn-fs-btn" aria-pressed="false"
					aria-label="<?php esc_attr_e( 'Fullscreen', 'wp-then-now-slider' ); ?>"
					data-enter="<?php esc_attr_e( 'Fullscreen', 'wp-then-now-slider' ); ?>"
					data-exit="<?php esc_attr_e( 'Exit fullscreen', 'wp-then-now-slider' ); ?>">
					<svg class="wptn-fs-open" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<svg class="wptn-fs-close" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $s['caption'] && 'below' === $s['caption_position'] ) : ?>
			<figcaption class="wptn-caption"><?php echo wp_kses_post( wpautop( $s['caption'] ) ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
	return ob_get_clean();
}

/**
 * Rendu d'une comparaison enregistrée.
 *
 * @param int   $id        ID de la comparaison.
 * @param array $overrides Réglages qui remplacent ceux enregistrés.
 * @return string
 */
function wptn_render_comparison( $id, $overrides = array() ) {
	$post = get_post( $id );
	if ( ! $post || WPTN_CPT !== $post->post_type || 'trash' === $post->post_status ) {
		if ( current_user_can( 'edit_posts' ) ) {
			/* translators: %d: ID */
			return '<p class="wptn-error">' . esc_html( sprintf( __( 'WP Then Now Slider: comparison #%d not found.', 'wp-then-now-slider' ), $id ) ) . '</p>';
		}
		return '';
	}
	$settings = wptn_get_settings( $id );
	if ( 'custom' !== $settings['color_mode'] ) {
		$settings['color']       = '';
		$settings['focus_color'] = '';
	}
	return wptn_render( array_merge( $settings, $overrides ) );
}

/**
 * Shortcode [then_now] (alias : [avant_apres], [before_after]).
 *
 * - Comparaison enregistrée : [then_now id="12"]
 * - Images directes        : [then_now before="123" after="456"]
 * Avec id, les autres attributs remplacent les réglages enregistrés.
 *
 * @param array $atts Attributs.
 * @return string
 */
function wptn_shortcode( $atts ) {
	$atts = (array) $atts;

	// Ancien attribut hover="yes" (v2.0).
	if ( ! isset( $atts['mode'] ) && isset( $atts['hover'] ) && filter_var( $atts['hover'], FILTER_VALIDATE_BOOLEAN ) ) {
		$atts['mode'] = 'hover';
	}

	$given = array_intersect_key( $atts, wptn_defaults() );

	// Une couleur passée dans le shortcode est forcément individuelle.
	if ( isset( $given['color'] ) || isset( $given['focus_color'] ) ) {
		$given['color_mode'] = 'custom';
	}

	if ( ! empty( $atts['id'] ) ) {
		return wptn_render_comparison( (int) $atts['id'], $given );
	}
	return wptn_render( $given );
}

/**
 * Rendu du bloc Gutenberg.
 *
 * @param array $a Attributs du bloc.
 * @return string
 */
function wptn_render_block( $a ) {
	$class = trim( ( ! empty( $a['align'] ) ? 'align' . sanitize_html_class( $a['align'] ) : '' ) . ' ' . ( isset( $a['className'] ) ? $a['className'] : '' ) );

	if ( ! empty( $a['comparisonId'] ) ) {
		return wptn_render_comparison( (int) $a['comparisonId'], array( 'class' => $class ) );
	}

	// Conversion des anciens attributs.
	if ( empty( $a['before'] ) ) {
		$a['before'] = ! empty( $a['beforeId'] ) ? $a['beforeId'] : ( isset( $a['beforeUrl'] ) ? $a['beforeUrl'] : 0 );
	}
	if ( empty( $a['after'] ) ) {
		$a['after'] = ! empty( $a['afterId'] ) ? $a['afterId'] : ( isset( $a['afterUrl'] ) ? $a['afterUrl'] : 0 );
	}
	$legacy = array(
		'beforeLabel' => 'before_label',
		'afterLabel'  => 'after_label',
		'showLabels'  => 'labels',
		'focusColor'  => 'focus_color',
	);
	foreach ( $legacy as $old => $new ) {
		if ( isset( $a[ $old ] ) ) {
			$a[ $new ] = $a[ $old ];
		}
	}
	if ( ! empty( $a['hover'] ) && 'slider' === $a['mode'] ) {
		$a['mode'] = 'hover';
	}
	if ( isset( $a['useGlobal'] ) && ! $a['useGlobal'] ) {
		$a['color_mode'] = 'custom';
	}

	$args          = array_intersect_key( $a, wptn_schema() );
	$args['class'] = $class;
	return wptn_render( $args );
}

/* -------------------------------------------------------------------------
 * Administration : écran d'édition d'une comparaison
 * ---------------------------------------------------------------------- */

function wptn_add_meta_boxes() {
	add_meta_box( 'wptn_settings', __( 'Images and settings', 'wp-then-now-slider' ), 'wptn_meta_box_settings', WPTN_CPT, 'normal', 'high' );
	add_meta_box( 'wptn_shortcode', __( 'Shortcode', 'wp-then-now-slider' ), 'wptn_meta_box_shortcode', WPTN_CPT, 'side', 'high' );
	add_meta_box( 'wptn_preview', __( 'Preview', 'wp-then-now-slider' ), 'wptn_meta_box_preview', WPTN_CPT, 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'wptn_add_meta_boxes' );

/**
 * Champ de formulaire généré depuis le schéma.
 *
 * @param string $key   Clé du réglage.
 * @param array  $f     Champ du schéma.
 * @param mixed  $value Valeur actuelle.
 */
function wptn_field_row( $key, $f, $value ) {
	$id   = 'wptn-' . str_replace( '_', '-', $key );
	$name = 'wptn[' . $key . ']';
	$cond = isset( $f['show_if'] ) ? ' data-show-if="' . esc_attr( wp_json_encode( $f['show_if'] ) ) . '"' : '';
	?>
	<tr<?php echo $cond; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f['label'] ); ?></label></th>
		<td>
			<?php
			switch ( $f['type'] ) {
				case 'media':
					$url = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
					?>
					<div class="wptn-media-field">
						<div class="wptn-media-preview">
							<?php if ( $url ) : ?>
								<img src="<?php echo esc_url( $url ); ?>" alt="" />
							<?php else : ?>
								<span class="wptn-media-empty"><?php esc_html_e( 'No image', 'wp-then-now-slider' ); ?></span>
							<?php endif; ?>
						</div>
						<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						<button type="button" class="button wptn-media-select"><?php esc_html_e( 'Choose an image', 'wp-then-now-slider' ); ?></button>
						<button type="button" class="button-link wptn-media-remove" <?php echo $value ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove', 'wp-then-now-slider' ); ?></button>
					</div>
					<?php
					break;
				case 'text':
					printf( '<input type="text" id="%s" class="regular-text" name="%s" value="%s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
					break;
				case 'textarea':
					printf( '<textarea id="%s" class="large-text" rows="3" name="%s">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ) );
					break;
				case 'bool':
					printf( '<label><input type="checkbox" id="%s" name="%s" value="1" %s /> %s</label>', esc_attr( $id ), esc_attr( $name ), checked( $value, true, false ), esc_html__( 'Yes', 'wp-then-now-slider' ) );
					break;
				case 'select':
					printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
					foreach ( $f['options'] as $opt => $label ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
					}
					echo '</select>';
					break;
				case 'radio':
					echo '<fieldset>';
					foreach ( $f['options'] as $opt => $label ) {
						printf( '<label><input type="radio" name="%s" value="%s" %s /> %s</label><br />', esc_attr( $name ), esc_attr( $opt ), checked( $value, $opt, false ), esc_html( $label ) );
					}
					echo '</fieldset>';
					break;
				case 'range':
					printf(
						'<input type="range" id="%1$s" name="%2$s" min="%3$d" max="%4$d" value="%5$d" oninput="this.nextElementSibling.textContent=this.value" /> <output>%5$d</output>',
						esc_attr( $id ),
						esc_attr( $name ),
						(int) $f['min'],
						(int) $f['max'],
						(int) $value
					);
					break;
				case 'color':
					printf( '<input type="text" id="%s" class="wptn-color-field" name="%s" value="%s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
					break;
			}
			if ( ! empty( $f['help'] ) ) {
				echo '<p class="description">' . esc_html( $f['help'] ) . '</p>';
			}
			if ( 'color_mode' === $key ) {
				printf(
					'<p class="description">%s</p>',
					sprintf(
						/* translators: %s: lien vers les réglages */
						esc_html__( 'Global colors can be changed in %s.', 'wp-then-now-slider' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . WPTN_CPT . '&page=wptn-settings' ) ) . '">' . esc_html__( 'Before / After > Settings', 'wp-then-now-slider' ) . '</a>'
					)
				);
			}
			?>
		</td>
	</tr>
	<?php
}

/**
 * Meta box principale : sections du schéma.
 *
 * @param WP_Post $post Comparaison.
 */
function wptn_meta_box_settings( $post ) {
	$s      = wptn_get_settings( $post->ID );
	$schema = wptn_schema();
	wp_nonce_field( 'wptn_save', 'wptn_nonce' );

	echo '<p class="description">' . esc_html__( 'Tip: use two images with the same dimensions for perfect alignment.', 'wp-then-now-slider' ) . '</p>';

	foreach ( wptn_sections() as $section => $title ) {
		echo '<details class="wptn-section"' . ( in_array( $section, array( 'images', 'layout' ), true ) ? ' open' : '' ) . '>';
		echo '<summary>' . esc_html( $title ) . '</summary>';
		echo '<table class="form-table" role="presentation">';
		foreach ( $schema as $key => $f ) {
			if ( $f['section'] === $section ) {
				wptn_field_row( $key, $f, $s[ $key ] );
			}
		}
		echo '</table></details>';
	}
}

/**
 * Meta box shortcode.
 *
 * @param WP_Post $post Comparaison.
 */
function wptn_meta_box_shortcode( $post ) {
	if ( 'auto-draft' === $post->post_status ) {
		echo '<p>' . esc_html__( 'Save the comparison to get its shortcode.', 'wp-then-now-slider' ) . '</p>';
		return;
	}
	$code = '[then_now id="' . $post->ID . '"]';
	?>
	<input type="text" class="widefat wptn-shortcode-input" readonly value="<?php echo esc_attr( $code ); ?>" onclick="this.select()" />
	<p><button type="button" class="button wptn-copy" data-code="<?php echo esc_attr( $code ); ?>"><?php esc_html_e( 'Copy', 'wp-then-now-slider' ); ?></button></p>
	<p class="description"><?php esc_html_e( 'Paste this shortcode into a page or post, or use the “Before / After” block.', 'wp-then-now-slider' ); ?></p>
	<?php
}

/**
 * Meta box aperçu.
 *
 * @param WP_Post $post Comparaison.
 */
function wptn_meta_box_preview( $post ) {
	$s = wptn_get_settings( $post->ID );
	if ( ! $s['before'] || ! $s['after'] ) {
		echo '<p>' . esc_html__( 'Choose both images, then save to see the preview.', 'wp-then-now-slider' ) . '</p>';
		return;
	}
	echo '<div style="max-width:800px">' . wptn_render_comparison( $post->ID ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Enregistrement des réglages.
 *
 * @param int $post_id ID.
 */
function wptn_save( $post_id ) {
	if ( ! isset( $_POST['wptn_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wptn_nonce'] ) ), 'wptn_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = isset( $_POST['wptn'] ) ? (array) wp_unslash( $_POST['wptn'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	update_post_meta( $post_id, '_wptn_settings', wptn_sanitize_settings( $in ) );
}
add_action( 'save_post_' . WPTN_CPT, 'wptn_save' );

/**
 * Scripts de l'admin (sélecteur de médias, couleurs, aperçu).
 *
 * @param string $hook Page courante.
 */
function wptn_admin_assets( $hook ) {
	$screen      = get_current_screen();
	$is_settings = ( WPTN_CPT . '_page_wptn-settings' === $hook );
	if ( ! $screen || ( WPTN_CPT !== $screen->post_type && ! $is_settings ) ) {
		return;
	}
	wp_enqueue_style( 'wptn-style' );
	wp_enqueue_style( 'wptn-admin', WPTN_URL . 'assets/css/admin.css', array(), WPTN_VERSION );

	if ( $is_settings || in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		if ( ! $is_settings ) {
			wp_enqueue_media();
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wptn-script' );
		wp_enqueue_script( 'wptn-admin', WPTN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), WPTN_VERSION, true );
		wp_localize_script(
			'wptn-admin',
			'wptnAdmin',
			array(
				'title'  => __( 'Choose an image', 'wp-then-now-slider' ),
				'button' => __( 'Use this image', 'wp-then-now-slider' ),
				'empty'  => __( 'No image', 'wp-then-now-slider' ),
				'copied' => __( 'Copied!', 'wp-then-now-slider' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'wptn_admin_assets' );

/* -------------------------------------------------------------------------
 * Administration : réglages globaux
 * ---------------------------------------------------------------------- */

function wptn_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=' . WPTN_CPT,
		__( 'Before / After settings', 'wp-then-now-slider' ),
		__( 'Settings', 'wp-then-now-slider' ),
		'manage_options',
		'wptn-settings',
		'wptn_settings_page'
	);
}
add_action( 'admin_menu', 'wptn_settings_menu' );

function wptn_settings_init() {
	register_setting(
		'wptn_settings',
		'wptn_options',
		array(
			'type'              => 'array',
			'sanitize_callback' => function ( $in ) {
				$in    = is_array( $in ) ? $in : array();
				$color = isset( $in['color'] ) ? sanitize_hex_color( $in['color'] ) : '';
				return array(
					'color'       => $color ? $color : '#ffffff',
					'focus_color' => isset( $in['focus_color'] ) ? (string) sanitize_hex_color( $in['focus_color'] ) : '',
				);
			},
			'default'           => array(
				'color'       => '#ffffff',
				'focus_color' => '',
			),
		)
	);
}
add_action( 'admin_init', 'wptn_settings_init' );

function wptn_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = wptn_global_options();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Before / After settings', 'wp-then-now-slider' ); ?></h1>
		<p><?php esc_html_e( 'These colors apply to every comparison set to “Global colors”.', 'wp-then-now-slider' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'wptn_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="wptn-g-color"><?php esc_html_e( 'Handle color', 'wp-then-now-slider' ); ?></label></th>
					<td><input type="text" id="wptn-g-color" class="wptn-color-field" name="wptn_options[color]" value="<?php echo esc_attr( $o['color'] ); ?>" data-default-color="#ffffff" /></td>
				</tr>
				<tr>
					<th><label for="wptn-g-focus"><?php esc_html_e( 'Selection color', 'wp-then-now-slider' ); ?></label></th>
					<td>
						<input type="text" id="wptn-g-focus" class="wptn-color-field" name="wptn_options[focus_color]" value="<?php echo esc_attr( $o['focus_color'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Handle color when selected or being dragged. Leave empty to use the theme color.', 'wp-then-now-slider' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Données transmises au bloc : schéma, sections, lien des réglages.
 */
function wptn_block_editor_data() {
	$schema = array();
	foreach ( wptn_schema() as $key => $f ) {
		$schema[ $key ] = array_intersect_key( $f, array_flip( array( 'type', 'default', 'section', 'label', 'help', 'options', 'min', 'max', 'show_if' ) ) );
	}
	wp_localize_script(
		'wptn-block',
		'wptnBlock',
		array(
			'schema'      => $schema,
			'sections'    => wptn_sections(),
			'settingsUrl' => current_user_can( 'manage_options' ) ? admin_url( 'edit.php?post_type=' . WPTN_CPT . '&page=wptn-settings' ) : '',
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'wptn_block_editor_data' );

/* -------------------------------------------------------------------------
 * Administration : liste des comparaisons
 * ---------------------------------------------------------------------- */

function wptn_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['wptn_thumb'] = __( 'Images', 'wp-then-now-slider' );
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['wptn_shortcode'] = __( 'Shortcode', 'wp-then-now-slider' );
		}
	}
	return $new;
}
add_filter( 'manage_' . WPTN_CPT . '_posts_columns', 'wptn_columns' );

function wptn_column_content( $column, $post_id ) {
	if ( 'wptn_shortcode' === $column ) {
		printf(
			'<input type="text" readonly class="wptn-shortcode-input" value="%s" onclick="this.select()" />',
			esc_attr( '[then_now id="' . $post_id . '"]' )
		);
	}
	if ( 'wptn_thumb' === $column ) {
		$s = wptn_get_settings( $post_id );
		echo '<div class="wptn-thumbs">';
		foreach ( array( 'before', 'after' ) as $k ) {
			$url = $s[ $k ] ? wp_get_attachment_image_url( $s[ $k ], 'thumbnail' ) : '';
			echo $url ? '<img src="' . esc_url( $url ) . '" alt="" />' : '<span class="wptn-thumb-empty"></span>';
		}
		echo '</div>';
	}
}
add_action( 'manage_' . WPTN_CPT . '_posts_custom_column', 'wptn_column_content', 10, 2 );

/**
 * Liens sur la page des extensions.
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . WPTN_CPT ) ) . '">' . esc_html__( 'Comparisons', 'wp-then-now-slider' ) . '</a>' );
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . WPTN_CPT . '&page=wptn-settings' ) ) . '">' . esc_html__( 'Settings', 'wp-then-now-slider' ) . '</a>' );
		return $links;
	}
);

/* -------------------------------------------------------------------------
 * Migration depuis « Avant Après Slider » / « Before After Slider » (v1.x)
 * ---------------------------------------------------------------------- */

/**
 * Reprend les comparaisons, réglages et blocs de l'ancienne version.
 */
function wptn_migrate_legacy() {
	if ( get_option( 'wptn_migrated' ) ) {
		return;
	}
	global $wpdb;

	// Comparaisons et leurs réglages.
	$wpdb->update( $wpdb->posts, array( 'post_type' => WPTN_CPT ), array( 'post_type' => 'aas_comparison' ) );
	$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wptn_settings' ), array( 'meta_key' => '_aas_settings' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery

	// Couleurs globales.
	$old = get_option( 'aas_options' );
	if ( false !== $old && false === get_option( 'wptn_options' ) ) {
		update_option( 'wptn_options', $old );
	}

	// Blocs déjà insérés dans les pages.
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"UPDATE {$wpdb->posts} SET post_content = REPLACE( post_content, '<!-- wp:aas/slider', '<!-- wp:wptn/slider' ) WHERE post_content LIKE '%wp:aas/slider%'"
	);

	wp_cache_flush();
	update_option( 'wptn_migrated', WPTN_VERSION );
}
register_activation_hook( __FILE__, 'wptn_migrate_legacy' );
add_action( 'admin_init', 'wptn_migrate_legacy' );
