<?php
/**
 * Retraceur Coding Standard.
 *
 * @package Retraceur\PHPCodingStandards
 * @link    https://github.com/retraceur/php-coding-standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace PHPCodingStandards\Retraceur\Helpers;

/**
 * Helper utilities for working with user provided property values.
 *
 * ---------------------------------------------------------------------------------------------
 * This class is only intended for internal use by RetraceurCS and is not part of the public API.
 * This also means that it has no promise of backward compatibility. Use at your own risk.
 * ---------------------------------------------------------------------------------------------
 *
 * @internal
 *
 * @since 3.0.0 The method in this class was previously contained in the
 *              `PHPCodingStandards\Retraceur\Sniff` class and has been moved here.
 */
final class RulesetPropertyHelper {

	/**
	 * Merge a pre-set array with a ruleset provided array.
	 *
	 * - By default flips custom lists to allow for using `isset()` instead
	 *   of `in_array()`.
	 * - When `$flip` is true:
	 *   * Presumes the base array is in a `'value' => true` format.
	 *   * Any custom items will be given the value `false` to be able to
	 *     distinguish them from pre-set (base array) values.
	 *   * Will filter previously added custom items out from the base array
	 *     before merging/returning to allow for resetting to the base array.
	 *
	 * {@internal Function is static as it doesn't use any of the properties or others
	 * methods anyway.}
	 *
	 * @since 0.11.0
	 * @since 2.0.0  No longer supports custom array properties which were incorrectly
	 *               passed as a string.
	 * @since 3.0.0  Moved from the Sniff class to this class.
	 *
	 * @param array $custom Custom list as provided via a ruleset.
	 * @param array $base   Optional. Base list. Defaults to an empty array.
	 *                      Expects `value => true` format when `$flip` is true.
	 * @param bool  $flip   Optional. Whether or not to flip the custom list.
	 *                      Defaults to true.
	 * @return array
	 */
	public static function merge_custom_array( $custom, array $base = array(), $flip = true ) {
		if ( true === $flip ) {
			$base = array_filter( $base );
		}

		if ( empty( $custom ) || ! \is_array( $custom ) ) {
			return $base;
		}

		if ( true === $flip ) {
			$custom = array_fill_keys( $custom, false );
		}

		if ( empty( $base ) ) {
			return $custom;
		}

		return array_merge( $base, $custom );
	}
}
