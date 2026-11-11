<?php
/**
 * Mises à jour depuis les Releases GitHub.
 *
 * WordPress (5.8+) lit l'en-tête « Update URI » du plugin et appelle le filtre
 * update_plugins_github.com : on y renvoie la dernière Release publiée sur GitHub,
 * avec son fichier wp-then-now-slider.zip (ajouté par le workflow « Release zip »).
 *
 * Optionnel, dans wp-config.php (dépôt privé ou limite d'appels à l'API GitHub) :
 *   define( 'WPTN_GITHUB_TOKEN', 'ghp_xxx' );
 *
 * @package WP_Then_Now_Slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPTN_REPO', 'cedlelensois-lang/WP-Then-Now-slider' );
define( 'WPTN_SLUG', 'wp-then-now-slider' );
define( 'WPTN_ASSET', 'wp-then-now-slider.zip' );

/**
 * Dernière Release GitHub (mise en cache 6 h).
 *
 * @param bool $force Ignorer le cache.
 * @return array|null version, package, url, body, date — ou null.
 */
function wptn_github_release( $force = false ) {
	$key = 'wptn_github_release';

	// « Tableau de bord > Mises à jour > Vérifier à nouveau » vide aussi notre cache.
	if ( is_admin() && isset( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$force = true;
	}

	if ( ! $force ) {
		$cached = get_site_transient( $key );
		if ( false !== $cached ) {
			return $cached ? $cached : null;
		}
	}

	$args = array(
		'timeout' => 10,
		'headers' => array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'WP-Then-Now-Slider/' . WPTN_VERSION . '; ' . home_url( '/' ),
		),
	);
	if ( defined( 'WPTN_GITHUB_TOKEN' ) && WPTN_GITHUB_TOKEN ) {
		$args['headers']['Authorization'] = 'Bearer ' . WPTN_GITHUB_TOKEN;
	}

	$response = wp_remote_get( 'https://api.github.com/repos/' . WPTN_REPO . '/releases/latest', $args );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		set_site_transient( $key, 0, HOUR_IN_SECONDS ); // Nouvel essai dans 1 h.
		return null;
	}

	$data    = json_decode( wp_remote_retrieve_body( $response ), true );
	$package = '';
	if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
		foreach ( $data['assets'] as $asset ) {
			if ( isset( $asset['name'], $asset['browser_download_url'] ) && WPTN_ASSET === $asset['name'] ) {
				$package = $asset['browser_download_url'];
				break;
			}
		}
	}

	// Release sans zip (workflow pas encore terminé) : on réessaiera dans 1 h.
	if ( empty( $data['tag_name'] ) || ! $package ) {
		set_site_transient( $key, 0, HOUR_IN_SECONDS );
		return null;
	}

	$release = array(
		'version' => ltrim( $data['tag_name'], 'vV' ),
		'package' => $package,
		'url'     => isset( $data['html_url'] ) ? $data['html_url'] : 'https://github.com/' . WPTN_REPO,
		'body'    => isset( $data['body'] ) ? (string) $data['body'] : '',
		'date'    => isset( $data['published_at'] ) ? $data['published_at'] : '',
	);
	set_site_transient( $key, $release, 6 * HOUR_IN_SECONDS );
	return $release;
}

/**
 * Indique à WordPress la version disponible sur GitHub.
 *
 * @param array|false $update      Mise à jour trouvée par un autre filtre.
 * @param array       $plugin_data En-têtes du plugin.
 * @param string      $plugin_file Fichier du plugin.
 * @return array|false
 */
function wptn_update_check( $update, $plugin_data, $plugin_file ) {
	if ( plugin_basename( WPTN_FILE ) !== $plugin_file ) {
		return $update;
	}
	$release = wptn_github_release();
	if ( ! $release ) {
		return $update;
	}
	return array(
		'slug'         => WPTN_SLUG,
		'version'      => $release['version'],
		'url'          => $release['url'],
		'package'      => $release['package'],
		'requires'     => '5.8',
		'requires_php' => '7.2',
	);
}
add_filter( 'update_plugins_github.com', 'wptn_update_check', 10, 3 );

/**
 * Fenêtre « Voir les détails » de la page Extensions.
 *
 * @param false|object|array $result Résultat.
 * @param string             $action Action demandée.
 * @param object             $args   Arguments.
 * @return false|object|array
 */
function wptn_plugin_info( $result, $action, $args ) {
	if ( 'plugin_information' !== $action || empty( $args->slug ) || WPTN_SLUG !== $args->slug ) {
		return $result;
	}
	$release = wptn_github_release();
	if ( ! $release ) {
		return $result;
	}
	$repo_url = 'https://github.com/' . WPTN_REPO;
	$shot     = 'https://raw.githubusercontent.com/' . WPTN_REPO . '/main/screenshots/apercu.png';

	return (object) array(
		'name'          => 'WP Then Now Slider',
		'slug'          => WPTN_SLUG,
		'version'       => $release['version'],
		'author'        => '<a href="https://github.com/cedlelensois-lang">Cédric</a>',
		'homepage'      => $repo_url,
		'requires'      => '5.8',
		'requires_php'  => '7.2',
		'last_updated'  => $release['date'],
		'download_link' => $release['package'],
		'sections'      => array(
			'description' => '<p>' . esc_html__( 'Create and manage multiple before / after photo comparisons with a draggable slider. Includes the [then_now id="X"] shortcode and a Gutenberg block.', 'wp-then-now-slider' ) . '</p>'
				. '<p><img src="' . esc_url( $shot ) . '" alt="" style="max-width:100%;height:auto" /></p>'
				. '<p><a href="' . esc_url( $repo_url ) . '" target="_blank" rel="noopener">GitHub</a></p>',
			'changelog'   => wptn_markdown_to_html( $release['body'] ),
		),
	);
}
add_filter( 'plugins_api', 'wptn_plugin_info', 10, 3 );

/**
 * Conversion Markdown très simple (notes de version GitHub).
 *
 * @param string $md Texte Markdown.
 * @return string HTML.
 */
function wptn_markdown_to_html( $md ) {
	$html   = '';
	$in_ul  = false;
	$inline = function ( $t ) {
		$t = esc_html( $t );
		$t = preg_replace( '/!\[([^\]]*)\]\((https?:[^)\s]+)\)/', '<img src="$2" alt="$1" style="max-width:100%;height:auto" />', $t );
		$t = preg_replace( '/\[([^\]]+)\]\((https?:[^)\s]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $t );
		$t = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $t );
		$t = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $t );
		return $t;
	};
	foreach ( preg_split( '/\r?\n/', (string) $md ) as $line ) {
		$line = rtrim( $line );
		if ( preg_match( '/^\s*[-*]\s+(.*)$/', $line, $m ) ) {
			if ( ! $in_ul ) {
				$html .= '<ul>';
				$in_ul = true;
			}
			$html .= '<li>' . $inline( $m[1] ) . '</li>';
			continue;
		}
		if ( $in_ul ) {
			$html .= '</ul>';
			$in_ul = false;
		}
		if ( preg_match( '/^(#{1,6})\s+(.*)$/', $line, $m ) ) {
			$level = min( 4, strlen( $m[1] ) + 1 );
			$html .= '<h' . $level . '>' . $inline( $m[2] ) . '</h' . $level . '>';
		} elseif ( '' !== trim( $line ) && '---' !== trim( $line ) ) {
			$html .= '<p>' . $inline( $line ) . '</p>';
		}
	}
	if ( $in_ul ) {
		$html .= '</ul>';
	}
	return wp_kses_post( $html );
}

/**
 * Lien « Vérifier les mises à jour » sous le nom du plugin.
 *
 * @param array  $links Liens.
 * @param string $file  Fichier du plugin.
 * @return array
 */
function wptn_row_meta( $links, $file ) {
	if ( plugin_basename( WPTN_FILE ) === $file && current_user_can( 'update_plugins' ) ) {
		$url     = wp_nonce_url( admin_url( 'plugins.php?wptn-check-update=1' ), 'wptn_check_update' );
		$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Check for updates', 'wp-then-now-slider' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'wptn_row_meta', 10, 2 );

/**
 * Vérification immédiate demandée depuis la page Extensions.
 */
function wptn_handle_check_update() {
	if ( empty( $_GET['wptn-check-update'] ) || ! current_user_can( 'update_plugins' ) ) {
		return;
	}
	check_admin_referer( 'wptn_check_update' );

	$release = wptn_github_release( true );
	delete_site_transient( 'update_plugins' );
	wp_update_plugins();

	if ( ! $release ) {
		$status = 'error';
	} elseif ( version_compare( $release['version'], WPTN_VERSION, '>' ) ) {
		$status = 'available';
	} else {
		$status = 'latest';
	}
	wp_safe_redirect( admin_url( 'plugins.php?wptn-update-status=' . $status ) );
	exit;
}
add_action( 'admin_init', 'wptn_handle_check_update' );

/**
 * Message affiché après la vérification.
 */
function wptn_update_notice() {
	if ( empty( $_GET['wptn-update-status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$status  = sanitize_key( $_GET['wptn-update-status'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$release = wptn_github_release();
	if ( 'available' === $status && $release ) {
		/* translators: %s: numéro de version */
		$msg  = sprintf( __( 'WP Then Now Slider: version %s is available. Use the “Update now” link below.', 'wp-then-now-slider' ), $release['version'] );
		$type = 'warning';
	} elseif ( 'latest' === $status ) {
		/* translators: %s: numéro de version */
		$msg  = sprintf( __( 'WP Then Now Slider: you are using the latest version (%s).', 'wp-then-now-slider' ), WPTN_VERSION );
		$type = 'success';
	} else {
		$msg  = __( 'WP Then Now Slider: unable to reach GitHub right now. Please try again later.', 'wp-then-now-slider' );
		$type = 'error';
	}
	printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $type ), esc_html( $msg ) );
}
add_action( 'admin_notices', 'wptn_update_notice' );

/**
 * Vide le cache après une mise à jour.
 */
add_action(
	'upgrader_process_complete',
	function () {
		delete_site_transient( 'wptn_github_release' );
	}
);
