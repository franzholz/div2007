<?php

namespace JambageCom\Div2007\Utility;

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
 * Control functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;


class CompatibilityUtility {

    static public function getPageRepository()
    {
        $classname = '';
        if (
            defined('TYPO3_version') &&
            version_compare(TYPO3_version, '10.0.0', '>=')
        ) {
            $classname = \TYPO3\CMS\Core\Domain\Repository\PageRepository::class;
        } else {
            $classname = \TYPO3\CMS\Frontend\Page\PageRepository::class;
        }
        $pageRepository = GeneralUtility::makeInstance($classname);
        return $pageRepository;
    }
}

