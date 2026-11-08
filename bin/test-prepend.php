<?php
/**
 * Préchargé par bin/test.php (auto_prepend_file) pour exécuter PHPCS en « mode tests » :
 * seuls les sniffs demandés sont chargés, avec leurs réglages par défaut, et les annotations
 * `phpcs:set` situées dans du HTML inline sont lues, comme dans le framework de tests de PHPCS.
 */
define( 'PHP_CODESNIFFER_IN_TESTS', true );

$retraceur_utils = getenv( 'PHPCSUTILS_PATH' );
if ( false !== $retraceur_utils && is_file( $retraceur_utils . '/phpcsutils-autoload.php' ) ) {
	require_once $retraceur_utils . '/phpcsutils-autoload.php';
}
unset( $retraceur_utils );
