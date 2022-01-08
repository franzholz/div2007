<?php

namespace JambageCom\Div2007\Hooks\Evaluation;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Evaluation function for weights in Gramm
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Franz Holzinger <franz@ttproducts.de>
 */

class Double6 {
    /**
    * Evaluation of 'input'-type values based on 'eval' list
    *
    * @param	string		Value to evaluate
    * @param	string		Is-in string
    * @param	boolean		if true the value is set
    * @return	string		Modified $value
    */
    public function evaluateFieldValue ($value, $is_in, $set)
    {
        if ($set) {
            $theDec = 0;

            for ($a = strlen($value); $a > 0; $a--) {
                $commaCheck = substr($value, $a - 1, 1);
                if (
                    $commaCheck == '.' ||
                    $commaCheck == ','
                ) {
                    $theDec = substr($value, $a);
                    $value = substr($value, 0, $a - 1);
                    break;
                }
            }

            $theDec = preg_replace('/[^0-9]/', '', $theDec) . '000000';
            $value = intval(str_replace(' ', '', $value)) . '.' . substr($theDec, 0, 6);
        }

        return $value;
    }
}

