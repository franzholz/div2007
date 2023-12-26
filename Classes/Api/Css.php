<?php

namespace JambageCom\Div2007\Api;

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
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * HTML functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
class Css implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Returns a class-name.
     *
     * @param   string      The class name
     * @param   string      type (HTML tag or combination) You can use '-' as the inside separator sign
     *
     * @return  string      The combined class name
     *
     * @see pi_getClassName()
     */
    public function getClassName($class, $type = '')
    {
        $result = '';
        if ($type != '') {
            $result = $type;
        }
        $separators = ' -_';
        $result .= ucwords($class, $separators);

        $result = preg_replace('/[^A-Za-z0-9]/', '', $result);

        return $result;
    }
}
