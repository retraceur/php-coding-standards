<?php
/**
 * Retraceur Coding Standard.
 *
 * @package Retraceur\PHPCodingStandards
 * @link    https://github.com/retraceur/php-coding-standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace PHPCodingStandards\Retraceur\Sniffs\DateTime;

use PHPCodingStandards\Retraceur\AbstractFunctionRestrictionsSniff;

/**
 * Forbids the use of various native DateTime related PHP/WP functions and suggests alternatives.
 *
 * @since 2.2.0
 */
final class RestrictedFunctionsSniff extends AbstractFunctionRestrictionsSniff {

	/**
	 * Groups of functions to restrict.
	 *
	 * @return array
	 */
	public function getGroups() {
		return array(

			/*
			 * Disallow the changing the timezone.
			 *
			 * @link https://docs.wpvip.com/php_codesniffer/errors/#h-manipulating-the-timezone-server-side
			 */
			'timezone_change' => array(
				'type'      => 'error',
				'message'   => 'Using %s() and similar isn\'t allowed, instead use WP internal timezone support.',
				'functions' => array(
					'date_default_timezone_set',
				),
			),

			/*
			 * Use gmdate(), not date().
			 * Don't rely on the current PHP time zone as it might have been changed by third party code.
			 */
			'date' => array(
				'type'      => 'error',
				'message'   => '%s() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead.',
				'functions' => array(
					'date',
				),
			),
		);
	}
}
