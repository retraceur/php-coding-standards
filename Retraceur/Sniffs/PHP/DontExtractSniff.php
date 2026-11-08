<?php
/**
 * Retraceur Coding Standard.
 *
 * @package Retraceur\PHPCodingStandards
 * @link    https://github.com/retraceur/php-coding-standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace PHPCodingStandards\Retraceur\Sniffs\PHP;

use PHPCodingStandards\Retraceur\AbstractFunctionRestrictionsSniff;

/**
 * Restricts the usage of extract().
 *
 * @since 0.10.0 Previously this check was contained within the
 *               `Retraceur.VIP.RestrictedFunctions` sniff.
 * @since 0.13.0 Class name changed: this class is now namespaced.
 * @since 1.0.0  This sniff has been moved from the `Functions` category to the `PHP` category.
 */
final class DontExtractSniff extends AbstractFunctionRestrictionsSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * Example: groups => array(
	 *  'lambda' => array(
	 *      'type'      => 'error' | 'warning',
	 *      'message'   => 'Use anonymous functions instead please!',
	 *      'functions' => array( 'file_get_contents', 'create_function' ),
	 *  )
	 * )
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(

			'extract' => array(
				'type'      => 'error',
				'message'   => '%s() usage is highly discouraged, due to the complexity and unintended issues it might cause.',
				'functions' => array(
					'extract',
				),
			),

		);
	}
}
