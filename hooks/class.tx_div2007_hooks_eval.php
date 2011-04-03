<?php
/**
 * Collection of static functions
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2008-2010 Franz Holzinger
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Franz Holzinger <franz@ttproducts.de>
 * @copyright  2008-2010 Franz Holzinger
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Id:$
 * @since      0.1
 */

/**
 * Collection of evaluation functions
 *
 *
 * This is a library that results of the work of the Extension Coordination Team (ECT).
 *
 * In this class we collect diverse static functions that are usefull for extension development,
 * but that didn't made their way into t3lib_div
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Franz Holzinger <contact@fholzinger.com>
 */

class tx_double6 {
	/**
	 * Evaluation of 'input'-type values based on 'eval' list
	 *
	 * @param	string		Value to evaluate
	 * @param	string		Is-in string
	 * @param	boolean		if TRUE the value is set
	 * @return	string		Modified $value
	 */
	function evaluateFieldValue ($value,$is_in,$set)	{
		if ($set)	{
			$theDec = 0;
			for ($a=strlen($value); $a>0; $a--)	{
				if (substr($value,$a-1,1)=='.' || substr($value,$a-1,1)==',')	{
					$theDec = substr($value,$a);
					$value = substr($value,0,$a-1);
					break;
				}
			}
			$theDec = preg_replace('/[^0-9]/', '', $theDec) . '000000';
			$value = intval(str_replace(' ','',$value)).'.'.substr($theDec,0,6);
		}
		return $value;
	}
}


?>
