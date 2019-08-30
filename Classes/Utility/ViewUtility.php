<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Franz Holzinger <franz@ttproducts.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * view functions
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use \JambageCom\Div2007\Utility\HtmlUtility;

class ViewUtility {
    /**
    * Returns the help page with a mini guide how to setup the extension
    *
    * example:
    * 	$content .= ViewUtility::displayHelpPage($this->cObj->fileResource('EXT:myextension/template/help.tmpl'));
    * 	unset($this->errorMessage);
    *
    * @param   object      language object of type \JambageCom\Div2007\Base\TranslationBase
    * @param   object	   cObj
    * @param   string	   HTML template content
    * @param   string	   extension key
    * @param   string	   error message for the marker ###ERROR_MESSAGE###
    * @param   string	   CODE of plugin
    *
    * @return	string		HTML to display the help page
    * @access	public
    *
    */
    static public function displayHelpPage (
        \JambageCom\Div2007\Base\TranslationBase $languageObjj,
        \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj,
        $helpTemplate,
        $extensionKey,
        $errorMessage = '',
        $theCode = ''
    ) {
        $parser = $cObj;
        if (
            defined('TYPO3_version') &&
            version_compare(TYPO3_version, '8.0.0', '>=')
        ) {
            $parser = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class);
        }

            // Get language version
        $helpTemplate_lang='';
        if ($languageObjj->getLocalLangKey()) {
            $helpTemplate_lang =
                $parser->getSubpart(
                    $helpTemplate,
                    '###TEMPLATE_' . $languageObjj->getLocalLangKey() . '###'
                );
        }

        $helpTemplate = (
            $helpTemplate_lang ?
                $helpTemplate_lang :
                $parser->getSubpart($helpTemplate, '###TEMPLATE_DEFAULT###')
        );
            // Markers and substitution:

        $markerArray['###PATH###'] = ExtensionManagementUtility::siteRelPath($extensionKey);
        $markerArray['###ERROR_MESSAGE###'] = ($errorMessage ? '<strong>' . $errorMessage . '</strong><br' . HtmlUtility::generateXhtmlFix() . '>' : '');
        $markerArray['###CODE###'] = $theCode;
        $result = $parser->substituteMarkerArray($helpTemplate, $markerArray);
        return $result;
    }
}


