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
 * Frontend functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */

use TYPO3\CMS\Core\SingletonInterface;

class PhpHelper implements SingletonInterface
{
    /* Json_decode with special chars */
    public function json_decode_special(
        string $json,
        ?bool $associative = null,
        int $depth = 512,
        int $flags = 0,
        bool $throwException = true
    ): mixed
    {
        $json = str_replace("\n", "\\n", $json);
        $json = str_replace("\r", "", $json);
        $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);
        $json = preg_replace('/(,)\s*}$/', '}', $json);

        if (json_validate($json, $depth, JSON_INVALID_UTF8_IGNORE)) {
            $result = json_decode($json, $associative, $depth, $flags);
        } else {
            $result = 'Error no. ' . json_last_error() . ': ' . json_last_error_msg();
            if ($throwException) {
                throw new \RuntimeException('Error in div2007 json_decode_special. ' . $result, 1745997340);
            }
        }
        return $result;
    }
}
