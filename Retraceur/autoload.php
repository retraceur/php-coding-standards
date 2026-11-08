<?php
/**
 * Retraceur Coding Standard.
 *
 * Charge l'autoloader de PHPCSUtils quand il n'est pas déjà disponible (installation sans Composer).
 *
 * PHPCSUtils est recherché dans les dossiers déclarés via `installed_paths`
 * (ex. `--runtime-set installed_paths .phpcs/PHPCSUtils,.phpcs/RetraceurCS`),
 * puis via la variable d'environnement PHPCSUTILS_PATH.
 *
 * @package Retraceur\PHPCodingStandards
 * @link    https://github.com/retraceur/php-coding-standards
 * @license https://opensource.org/licenses/MIT MIT
 */

use PHP_CodeSniffer\Config;

if ( ! class_exists( 'PHPCSUtils\Utils\Arrays' ) ) {
	$retraceur_candidates = array();

	$retraceur_installed = Config::getConfigData( 'installed_paths' );
	if ( is_string( $retraceur_installed ) && '' !== $retraceur_installed ) {
		$retraceur_candidates = array_map( 'trim', explode( ',', $retraceur_installed ) );
	}

	$retraceur_env = getenv( 'PHPCSUTILS_PATH' );
	if ( false !== $retraceur_env && '' !== $retraceur_env ) {
		$retraceur_candidates[] = $retraceur_env;
	}

	foreach ( $retraceur_candidates as $retraceur_path ) {
		$retraceur_file = rtrim( $retraceur_path, '/\\' ) . DIRECTORY_SEPARATOR . 'phpcsutils-autoload.php';
		if ( is_file( $retraceur_file ) ) {
			require_once $retraceur_file;
			break;
		}
	}

	if ( ! class_exists( 'PHPCSUtils\Utils\Arrays' ) ) {
		throw new RuntimeException(
			'Retraceur Coding Standard : PHPCSUtils est introuvable. Ajoutez le dossier de PHPCSUtils à "installed_paths" ou définissez PHPCSUTILS_PATH.'
		);
	}
}

unset( $retraceur_candidates, $retraceur_installed, $retraceur_env, $retraceur_path, $retraceur_file );
