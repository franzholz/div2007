<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.HTMLContent.
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
 * HTML functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;


class HtmlUtility {
    static private   $initialized = false;
    static protected $xhtmlFix = false;

    static public function setInitialized ($initialized)
    {
        static::$initialized = $initialized;
    }

    static public function getInitialized ()
    {
        return static::$initialized;
    }

    static public function useXHTML ()
    {
        $result = false;
        if (is_object($GLOBALS['TSFE'])) {
            $config = $GLOBALS['TSFE']->config['config'];
            if (
                (
                    $config['xhtmlDoctype'] != '' &&
                    stripos($config['xhtmlDoctype'], 'xthml') !== false
                )
                    ||
                (
                    $config['doctype'] != '' &&
                    stripos($config['doctype'], 'xthml') !== false
                )
            ) {
                $result = true;
            }
        }
        return $result;
    }

    static public function generateXhtmlFix ()
    {
        static::$xhtmlFix = (static::useXHTML() ? '/' : '');
        static::setInitialized(true);
        return static::$xhtmlFix;
    }

    static public function getXhtmlFix ()
    {
        return static::$xhtmlFix;
    }

    static public function determineXhtmlFix ()
    {
        if (static::getInitialized()) {
            $result = static::getXhtmlFix();
        } else {
            $result = static::generateXhtmlFix();
        }
        return $result;
    }

    /**
     * Attention. Because this method might not work as intended. I recommend to use 
     * a linux command line tool "tidy" to convert your files from HTML to XTHML.
     *
     * Tries to convert the content to be XHTML compliant and other stuff like that.
     * STILL EXPERIMENTAL. See comments below.
     *
     * What it does NOT do (yet) according to XHTML specs.:
     * - Wellformedness: Nesting is NOT checked
     * - name/id attribute issue is not observed at this point.
     * - Certain nesting of elements not allowed. Most interesting, <PRE> cannot contain img, big,small,sub,sup ...
     * - Wrapping scripts and style element contents in CDATA - or alternatively they should have entitites converted.
     * - Setting charsets may put some special requirements on both XML declaration/ meta-http-equiv. (C.9)
     * - UTF-8 encoding is in fact expected by XML!!
     * - stylesheet element and attribute names are NOT converted to lowercase
     * - ampersands (and entities in general I think) MUST be converted to an entity reference! (&amps;). This may mean further conversion of non-tag content before output to page. May be related to the charset issue as a whole.
     * - Minimized values not allowed: Must do this: selected="selected"
     *
     * What it does at this point:
     * - All tags (frame,base,meta,link + img,br,hr,area,input) is ended with "/>" - others?
     * - Lowercase for elements and attributes
     * - All attributes in quotes
     * - Add "alt" attribute to img-tags if it's not there already.
     *
     * @param string $content Content to clean up
     * @param boolean $onlyForXhtml The conversion is only done when XHTML is activated for a page in the TYPO3 config.doctype setup.
     * @return string Cleaned up content returned.
     * @access private
     */
    static public function XHTML_clean ($content, $onlyForXhtml = true)
    {
        if (
            !$onlyForXhtml ||
            static::useXHTML()
        ) {
            $htmlParser = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Html\HtmlParser::class);
            $result = $htmlParser->HTMLcleaner($content, [], 1, 0, ['xhtml' => 1]);
        } else {
            $result =  $content;
        }
        return $result;
    }
}

