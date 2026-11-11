<?php
/**
 * Plugin Name:       WP Then Now Slider
 * Plugin URI:        https://github.com/cedlelensois-lang/WP-Then-Now-slider
 * Description:       Create and manage multiple before / after photo comparisons with a draggable slider. Includes the [then_now id="X"] shortcode and a Gutenberg block.
 * Version:           2.0.0
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

define( 'WPTN_VERSION', '2.0.0' );
define( 'WPTN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPTN_CPT', 'wptn_comparison' );

/**
 * Réglages par défaut d'une comparaison.
 *
 * @return array
 */
function wptn_defaults() {
	return array(
		'before'       => 0,
		'after'        => 0,
		'before_label' => __( 'Before', 'wp-then-now-slider' ),
		'after_label'  => __( 'After', 'wp-then-now-slider' ),
		'orientation'  => 'horizontal',
		'start'        => 50,
		'hover'        => false,
		'labels'       => true,
		'color_mode'   => 'global', // global = réglages globaux, custom = couleurs propres.
		'color'        => '',
		'focus_color'  => '',
	);
}

/**
 * Couleurs globales (Avant / Après > Réglages).
 *
 * @return array color, focus_color (vide = couleur du thème).
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

/**
 * Réglages enregistrés d'une comparaison.
 *
 * @param int $post_id ID de la comparaison.
 * @return array
 */
function wptn_get_settings( $post_id ) {
	$saved = get_post_meta( $post_id, '_wptn_settings', true );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), wptn_defaults() );
}

/* -------------------------------------------------------------------------
 * Enregistrement : type de contenu, ressources, bloc, shortcode
 * ---------------------------------------------------------------------- */

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

	register_block_type(
		'wptn/slider',
		array(
			'editor_script'   => 'wptn-block',
			'editor_style'    => 'wptn-style',
			'render_callback' => 'wptn_render_block',
			'attributes'      => array(
				'comparisonId' => array( 'type' => 'number', 'default' => 0 ),
				'beforeId'     => array( 'type' => 'number', 'default' => 0 ),
				'beforeUrl'    => array( 'type' => 'string', 'default' => '' ),
				'afterId'      => array( 'type' => 'number', 'default' => 0 ),
				'afterUrl'     => array( 'type' => 'string', 'default' => '' ),
				'beforeLabel'  => array( 'type' => 'string', 'default' => __( 'Before', 'wp-then-now-slider' ) ),
				'afterLabel'   => array( 'type' => 'string', 'default' => __( 'After', 'wp-then-now-slider' ) ),
				'orientation'  => array( 'type' => 'string', 'default' => 'horizontal' ),
				'start'        => array( 'type' => 'number', 'default' => 50 ),
				'hover'        => array( 'type' => 'boolean', 'default' => false ),
				'showLabels'   => array( 'type' => 'boolean', 'default' => true ),
				'useGlobal'    => array( 'type' => 'boolean', 'default' => true ),
				'color'        => array( 'type' => 'string', 'default' => '' ),
				'focusColor'   => array( 'type' => 'string', 'default' => '' ),
				'align'        => array( 'type' => 'string', 'default' => '' ),
				'className'    => array( 'type' => 'string', 'default' => '' ),
			),
			'supports'        => array( 'align' => array( 'wide', 'full' ) ),
		)
	);

	wp_set_script_translations( 'wptn-block', 'wp-then-now-slider', plugin_dir_path( __FILE__ ) . 'languages' );

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
 * Nom du shortcode affiché dans l'admin, selon la langue de l'utilisateur.
 *
 * @return string
 */
function wptn_shortcode_tag() {
	return 'then_now';
}

/**
 * Anciens noms du shortcode, toujours acceptés.
 *
 * @return string[]
 */
function wptn_legacy_shortcodes() {
	return array( 'avant_apres', 'before_after' );
}

/**
 * Charge les traductions (français inclus ; anglais par défaut).
 */
function wptn_load_textdomain() {
	load_plugin_textdomain( 'wp-then-now-slider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wptn_load_textdomain', 0 );

/**
 * Charge CSS/JS dans le <head> si la page contient un slider.
 */
function wptn_maybe_enqueue_early() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( $post && ( has_shortcode( $post->post_content, 'then_now' ) || has_shortcode( $post->post_content, 'avant_apres' ) || has_shortcode( $post->post_content, 'before_after' ) || has_block( 'wptn/slider', $post ) ) ) {
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
 * HTML du slider.
 *
 * @param array $args Options.
 * @return string
 */
function wptn_render( $args ) {
	$args = wp_parse_args(
		$args,
		array_merge(
			wptn_defaults(),
			array(
				'before_alt' => '',
				'after_alt'  => '',
				'class'      => '',
			)
		)
	);

	$before = wptn_get_image( $args['before'], $args['before_alt'] );
	$after  = wptn_get_image( $args['after'], $args['after_alt'] );

	if ( ! $before || ! $after ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<p class="wptn-error">' . esc_html__( 'WP Then Now Slider: please set two images (before and after).', 'wp-then-now-slider' ) . '</p>';
		}
		return '';
	}

	wp_enqueue_style( 'wptn-style' );
	wp_enqueue_script( 'wptn-script' );

	$orientation = ( 'vertical' === $args['orientation'] ) ? 'vertical' : 'horizontal';
	$start       = max( 0, min( 100, (float) $args['start'] ) );
	$hover       = filter_var( $args['hover'], FILTER_VALIDATE_BOOLEAN );
	$labels      = filter_var( $args['labels'], FILTER_VALIDATE_BOOLEAN );
	// Couleurs : individuelles si « custom », sinon globales. Une couleur individuelle vide reprend la globale.
	$global      = wptn_global_options();
	$custom      = ( 'custom' === $args['color_mode'] );
	$color       = $custom ? sanitize_hex_color( $args['color'] ) : '';
	$color       = $color ? $color : sanitize_hex_color( $global['color'] );
	$color       = $color ? $color : '#ffffff';
	$focus_color = $custom ? sanitize_hex_color( $args['focus_color'] ) : '';
	$focus_color = $focus_color ? $focus_color : sanitize_hex_color( $global['focus_color'] );

	$img = function ( $image, $class ) {
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
	};

	$classes = trim( 'wptn-slider wptn-' . $orientation . ' ' . $args['class'] );

	ob_start();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>"
		data-orientation="<?php echo esc_attr( $orientation ); ?>"
		data-hover="<?php echo $hover ? '1' : '0'; ?>"
		style="--wptn-pos: <?php echo esc_attr( $start ); ?>%; --wptn-color: <?php echo esc_attr( $color ); ?>;<?php echo $focus_color ? ' --wptn-focus: ' . esc_attr( $focus_color ) . ';' : ''; ?>">
		<?php echo $img( $after, 'wptn-img wptn-after' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo $img( $before, 'wptn-img wptn-before' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $labels ) : ?>
			<?php if ( '' !== $args['before_label'] ) : ?>
				<span class="wptn-label wptn-label-before"><?php echo esc_html( $args['before_label'] ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $args['after_label'] ) : ?>
				<span class="wptn-label wptn-label-after"><?php echo esc_html( $args['after_label'] ); ?></span>
			<?php endif; ?>
		<?php endif; ?>
		<div class="wptn-handle">
			<span class="wptn-line"></span>
			<span class="wptn-knob" role="slider" tabindex="0"
				aria-label="<?php esc_attr_e( 'Before / after comparison slider', 'wp-then-now-slider' ); ?>"
				aria-orientation="<?php echo esc_attr( $orientation ); ?>"
				aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( round( $start ) ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
					<path d="M9 6l-6 6 6 6M15 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
		</div>
	</div>
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
 * Tous les autres attributs (orientation, start, hover…) restent utilisables pour remplacer un réglage.
 *
 * @param array $atts Attributs.
 * @return string
 */
function wptn_shortcode( $atts ) {
	$atts = (array) $atts;
	$keys = array( 'before', 'after', 'before_label', 'after_label', 'before_alt', 'after_alt', 'orientation', 'start', 'hover', 'labels', 'color', 'focus_color', 'class' );

	// Seuls les attributs réellement saisis remplacent les réglages enregistrés.
	$given = array_intersect_key( $atts, array_flip( $keys ) );

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

	return wptn_render(
		array(
			'before'       => ! empty( $a['beforeId'] ) ? $a['beforeId'] : $a['beforeUrl'],
			'after'        => ! empty( $a['afterId'] ) ? $a['afterId'] : $a['afterUrl'],
			'before_label' => $a['beforeLabel'],
			'after_label'  => $a['afterLabel'],
			'orientation'  => $a['orientation'],
			'start'        => $a['start'],
			'hover'        => $a['hover'],
			'labels'       => $a['showLabels'],
			'color_mode'   => empty( $a['useGlobal'] ) ? 'custom' : 'global',
			'color'        => $a['color'],
			'focus_color'  => isset( $a['focusColor'] ) ? $a['focusColor'] : '',
			'class'        => $class,
		)
	);
}

/* -------------------------------------------------------------------------
 * Administration : écran d'édition d'une comparaison
 * ---------------------------------------------------------------------- */

function wptn_add_meta_boxes() {
	add_meta_box( 'wptn_images', __( 'Images and settings', 'wp-then-now-slider' ), 'wptn_meta_box_settings', WPTN_CPT, 'normal', 'high' );
	add_meta_box( 'wptn_shortcode', __( 'Shortcode', 'wp-then-now-slider' ), 'wptn_meta_box_shortcode', WPTN_CPT, 'side', 'high' );
	add_meta_box( 'wptn_preview', __( 'Preview', 'wp-then-now-slider' ), 'wptn_meta_box_preview', WPTN_CPT, 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'wptn_add_meta_boxes' );

/**
 * Champ de sélection d'image.
 *
 * @param string $key   before|after.
 * @param int    $id    ID du média.
 * @param string $title Libellé.
 */
function wptn_image_field( $key, $id, $title ) {
	$url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<div class="wptn-media-field" data-key="<?php echo esc_attr( $key ); ?>">
		<strong><?php echo esc_html( $title ); ?></strong>
		<div class="wptn-media-preview">
			<?php if ( $url ) : ?>
				<img src="<?php echo esc_url( $url ); ?>" alt="" />
			<?php else : ?>
				<span class="wptn-media-empty"><?php esc_html_e( 'No image', 'wp-then-now-slider' ); ?></span>
			<?php endif; ?>
		</div>
		<input type="hidden" name="wptn[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $id ); ?>" />
		<button type="button" class="button wptn-media-select"><?php esc_html_e( 'Choose an image', 'wp-then-now-slider' ); ?></button>
		<button type="button" class="button-link wptn-media-remove" <?php echo $id ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove', 'wp-then-now-slider' ); ?></button>
	</div>
	<?php
}

/**
 * Meta box principale.
 *
 * @param WP_Post $post Comparaison.
 */
function wptn_meta_box_settings( $post ) {
	$s = wptn_get_settings( $post->ID );
	wp_nonce_field( 'wptn_save', 'wptn_nonce' );
	?>
	<div class="wptn-media-row">
		<?php wptn_image_field( 'before', (int) $s['before'], __( 'Before image', 'wp-then-now-slider' ) ); ?>
		<?php wptn_image_field( 'after', (int) $s['after'], __( 'After image', 'wp-then-now-slider' ) ); ?>
	</div>
	<p class="description"><?php esc_html_e( 'Tip: use two images with the same dimensions for perfect alignment.', 'wp-then-now-slider' ); ?></p>

	<table class="form-table" role="presentation">
		<tr>
			<th><label for="wptn-before-label"><?php esc_html_e( 'Before label', 'wp-then-now-slider' ); ?></label></th>
			<td><input type="text" id="wptn-before-label" class="regular-text" name="wptn[before_label]" value="<?php echo esc_attr( $s['before_label'] ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="wptn-after-label"><?php esc_html_e( 'After label', 'wp-then-now-slider' ); ?></label></th>
			<td><input type="text" id="wptn-after-label" class="regular-text" name="wptn[after_label]" value="<?php echo esc_attr( $s['after_label'] ); ?>" /></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Show labels', 'wp-then-now-slider' ); ?></th>
			<td><label><input type="checkbox" name="wptn[labels]" value="1" <?php checked( $s['labels'] ); ?> /> <?php esc_html_e( 'Yes', 'wp-then-now-slider' ); ?></label></td>
		</tr>
		<tr>
			<th><label for="wptn-orientation"><?php esc_html_e( 'Orientation', 'wp-then-now-slider' ); ?></label></th>
			<td>
				<select id="wptn-orientation" name="wptn[orientation]">
					<option value="horizontal" <?php selected( $s['orientation'], 'horizontal' ); ?>><?php esc_html_e( 'Horizontal (left / right)', 'wp-then-now-slider' ); ?></option>
					<option value="vertical" <?php selected( $s['orientation'], 'vertical' ); ?>><?php esc_html_e( 'Vertical (top / bottom)', 'wp-then-now-slider' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="wptn-start"><?php esc_html_e( 'Starting position', 'wp-then-now-slider' ); ?></label></th>
			<td>
				<input type="range" id="wptn-start" name="wptn[start]" min="0" max="100" value="<?php echo esc_attr( $s['start'] ); ?>" oninput="this.nextElementSibling.textContent=this.value+' %'" />
				<span><?php echo esc_html( $s['start'] ); ?> %</span>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Follow the mouse on hover', 'wp-then-now-slider' ); ?></th>
			<td><label><input type="checkbox" name="wptn[hover]" value="1" <?php checked( $s['hover'] ); ?> /> <?php esc_html_e( 'Yes', 'wp-then-now-slider' ); ?></label></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Colors', 'wp-then-now-slider' ); ?></th>
			<td>
				<fieldset class="wptn-color-mode">
					<label><input type="radio" name="wptn[color_mode]" value="global" <?php checked( 'custom' !== $s['color_mode'] ); ?> /> <?php esc_html_e( 'Global', 'wp-then-now-slider' ); ?></label><br />
					<label><input type="radio" name="wptn[color_mode]" value="custom" <?php checked( 'custom' === $s['color_mode'] ); ?> /> <?php esc_html_e( 'Individual (specific to this comparison)', 'wp-then-now-slider' ); ?></label>
				</fieldset>
				<p class="description">
					<?php
					printf(
						/* translators: %s: lien vers les réglages */
						esc_html__( 'Global colors can be changed in %s.', 'wp-then-now-slider' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . WPTN_CPT . '&page=wptn-settings' ) ) . '">' . esc_html__( 'Before / After > Settings', 'wp-then-now-slider' ) . '</a>'
					);
					?>
				</p>
			</td>
		</tr>
		<tr class="wptn-custom-color">
			<th><label for="wptn-color"><?php esc_html_e( 'Handle color', 'wp-then-now-slider' ); ?></label></th>
			<td>
				<input type="text" id="wptn-color" class="wptn-color-field" name="wptn[color]" value="<?php echo esc_attr( $s['color'] ); ?>" />
				<p class="description"><?php esc_html_e( 'Empty = global color.', 'wp-then-now-slider' ); ?></p>
			</td>
		</tr>
		<tr class="wptn-custom-color">
			<th><label for="wptn-focus-color"><?php esc_html_e( 'Selection color', 'wp-then-now-slider' ); ?></label></th>
			<td>
				<input type="text" id="wptn-focus-color" class="wptn-color-field" name="wptn[focus_color]" value="<?php echo esc_attr( $s['focus_color'] ); ?>" />
				<p class="description"><?php esc_html_e( 'Handle color when selected or being dragged. Empty = global color.', 'wp-then-now-slider' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
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
	$code = '[' . wptn_shortcode_tag() . ' id="' . $post->ID . '"]';
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
	echo '<div style="max-width:800px">' . wptn_render( $s ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
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

	$in    = isset( $_POST['wptn'] ) ? (array) wp_unslash( $_POST['wptn'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$color = isset( $in['color'] ) ? sanitize_hex_color( $in['color'] ) : '';
	$focus = isset( $in['focus_color'] ) ? sanitize_hex_color( $in['focus_color'] ) : '';

	$settings = array(
		'before'       => isset( $in['before'] ) ? absint( $in['before'] ) : 0,
		'after'        => isset( $in['after'] ) ? absint( $in['after'] ) : 0,
		'before_label' => isset( $in['before_label'] ) ? sanitize_text_field( $in['before_label'] ) : '',
		'after_label'  => isset( $in['after_label'] ) ? sanitize_text_field( $in['after_label'] ) : '',
		'orientation'  => ( isset( $in['orientation'] ) && 'vertical' === $in['orientation'] ) ? 'vertical' : 'horizontal',
		'start'        => isset( $in['start'] ) ? max( 0, min( 100, (int) $in['start'] ) ) : 50,
		'hover'        => ! empty( $in['hover'] ),
		'labels'       => ! empty( $in['labels'] ),
		'color_mode'   => ( isset( $in['color_mode'] ) && 'custom' === $in['color_mode'] ) ? 'custom' : 'global',
		'color'        => $color ? $color : '',
		'focus_color'  => $focus ? $focus : '',
	);

	update_post_meta( $post_id, '_wptn_settings', $settings );
}
add_action( 'save_post_' . WPTN_CPT, 'wptn_save' );

/**
 * Scripts de l'admin (sélecteur de médias, couleur, aperçu).
 *
 * @param string $hook Page courante.
 */
function wptn_admin_assets( $hook ) {
	$screen      = get_current_screen();
	$is_settings = ( WPTN_CPT . '_page_aas-settings' === $hook );
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
 * Couleurs globales transmises au bloc (aperçu dans l'éditeur).
 */
function wptn_block_editor_data() {
	$o = wptn_global_options();
	wp_localize_script(
		'wptn-block',
		'wptnBlock',
		array(
			'color'       => $o['color'],
			'focusColor'  => $o['focus_color'],
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
			esc_attr( '[' . wptn_shortcode_tag() . ' id="' . $post_id . '"]' )
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
 * Lien « Comparaisons » sur la page des extensions.
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
