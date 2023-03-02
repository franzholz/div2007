<?php

namespace JambageCom\Div2007\Base;

/***************************************************************
*  Copyright notice
*
*  (c) 2008-2018 Franz Holzinger <franz@ttproducts.de>
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
 * Part of the div2007 (Static Methods for Extensions End of 2007) extension.
 *
 * hook functions for the TYPO3 cms
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 */

use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

use JambageCom\Div2007\Utility\FlexformUtility;


class PageContentPreviewRenderingListenerBase implements \TYPO3\CMS\Core\SingletonInterface {
    public $extensionKey = '';	// extension key must be overridden

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        $content = $event->getPreviewContent();
        $record = $event->getRecord();
        $pageContext = $event->getPageLayoutContext();
        $pageRecord = $pageContext->getPageRecord();
        $codes = $this->pmDrawItem($record, $pageRecord);
        $event->setPreviewContent($content . $codes);
    }


    /**
    * Draw the item in the page module
    *
    * @param	array		record
    * @param	object		the parent object
    * @return	  string
    */

    public function pmDrawItem (array $record, array $pageRecord): string
    {
        $codes = '';
        $extensionKey = '';
        if (
            $this->extensionKey != ''
        ) {
            $extensionKey = $this->extensionKey;
        }

        if (
            $extensionKey != '' &&
            ExtensionManagementUtility::isLoaded($extensionKey) &&
            in_array(
                intval($pageRecord['doktype']),
                [1, 2, 5]
            ) &&
            $record['pi_flexform'] != ''
        ) {
            FlexformUtility::load(
                $record['pi_flexform'],
                $extensionKey
            );
            $codes =
                'CODE: ' . FlexformUtility::get(
                    $extensionKey,
                    'display_mode'
                );
        }
        return $codes;
    }
}

