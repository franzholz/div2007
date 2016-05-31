<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
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
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * error functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage tt_products
 *
 *
 */


class tx_div2007_error {

	static public function getMessage ($langObj, $error_code) {
		$result = '';
		$messageArray = array();
		$i = 0;

		foreach ($error_code as $key => $indice) {
			if ($key == 0) {
				$messageArray = explode('|', $message = tx_div2007_alpha5::getLL_fh002($langObj, $indice));
				$result .= tx_div2007_alpha5::getLL_fh002($langObj, 'plugin') . ': ' . $messageArray[0];
			} else if (isset($messageArray[$i])) {
				$result .= $indice . $messageArray[$i];
			}
			$i++;
		}

		return $result;
	}
}



?>