<?php
/**
 * Désinstallation : supprime les réglages et le cache du plugin.
 *
 * Les comparaisons elles-mêmes (type de contenu wptn_comparison) sont conservées,
 * pour ne pas perdre de contenu en cas de réinstallation. Pour les supprimer aussi,
 * mettez-les à la corbeille dans « Avant / Après » avant de désinstaller.
 *
 * @package WP_Then_Now_Slider
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wptn_options' );
delete_option( 'wptn_migrated' );
delete_site_transient( 'wptn_github_release' );
