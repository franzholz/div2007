<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2015 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Collection of static functions contributed by different people
 *
 * This class contains diverse staticfunctions in "alpha" status.
 * it uses calls which require a minimum of PHP 5.3.
 * This is needed if your PHP code must remain callable also under PHP 5.2.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author     Franz Holzinger <franz@ttproducts.de>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */


class tx_div2007_core_php53 {

	/**
	 * Merges two arrays recursively and "binary safe" (integer keys are
	 * overridden as well), overruling similar values in the original array
	 * with the values of the overrule array.
	 * In case of identical keys, ie. keeping the values of the overrule array.
	 *
	 * This method takes the original array by reference for speed optimization with large arrays
	 *
	 * The differences to the existing PHP function array_merge_recursive() are:
	 *  * Keys of the original array can be unset via the overrule array. ($enableUnsetFeature)
	 *  * Much more control over what is actually merged. ($addKeys, $includeEmptyValues)
	 *  * Elements or the original array get overwritten if the same key is present in the overrule array.
	 *
	 * @param array $original Original array. It will be *modified* by this method and contains the result afterwards!
	 * @param array $overrule Overrule array, overruling the original array
	 * @param boolean $addKeys If set to FALSE, keys that are NOT found in $original will not be set. Thus only existing value can/will be overruled from overrule array.
	 * @param boolean $includeEmptyValues If set, values from $overrule will overrule if they are empty or zero.
	 * @param boolean $enableUnsetFeature If set, special values "__UNSET" can be used in the overrule array in order to unset array keys in the original array.
	 * @return boolean TRUE if the TYPO3 call to mergeRecursiveWithOverrule has been executed
	 */
	static public function mergeRecursiveWithOverrule (array &$original, array $overrule, $addKeys = TRUE, $includeEmptyValues = TRUE, $enableUnsetFeature = TRUE) {
		$result = FALSE;

		$className = '\\TYPO3\\CMS\\Core\\Utility\\ArrayUtility';
		if (
			class_exists($className) &&
			method_exists($className, 'mergeRecursiveWithOverrule')
		) {
			\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($original, $overrule, $addKeys, $includeEmptyValues, $enableUnsetFeature);
			$result = TRUE;
		}
		return $result;
	}
}

