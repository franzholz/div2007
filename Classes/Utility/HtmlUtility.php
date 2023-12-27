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
 * HTML functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class HtmlUtility
{
    private static $initialized = false;
    protected static $xhtmlFix = false;

    public static function setInitialized($initialized)
    {
        static::$initialized = $initialized;
    }

    public static function getInitialized()
    {
        return static::$initialized;
    }

    public static function useXHTML()
    {
        $result = false;
        if (
            is_object($GLOBALS['TSFE']) &&
            isset($GLOBALS['TSFE']->config['config'])
        ) {
            $config = $GLOBALS['TSFE']->config['config'];
            if (
                (
                    isset($config['xhtmlDoctype']) &&
                    stripos($config['xhtmlDoctype'], 'xthml') !== false
                ) ||
                    (
                        isset($config['doctype']) &&
                        stripos($config['doctype'], 'xthml') !== false
                    )
            ) {
                $result = true;
            }
        }

        return $result;
    }

    public static function generateXhtmlFix()
    {
        static::$xhtmlFix = (static::useXHTML() ? '/' : '');
        static::setInitialized(true);

        return static::$xhtmlFix;
    }

    public static function getXhtmlFix()
    {
        return static::$xhtmlFix;
    }

    public static function determineXhtmlFix()
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
     * @param bool $onlyForXhtml The conversion is only done when XHTML is activated for a page in the TYPO3 config.doctype setup.
     *
     * @return string cleaned up content returned
     *
     * @access private
     */
    public static function XHTML_clean($content, $onlyForXhtml = true)
    {
        if (
            !$onlyForXhtml ||
            static::useXHTML()
        ) {
            $htmlParser = GeneralUtility::makeInstance(HtmlParser::class);
            $result = $htmlParser->HTMLcleaner($content, [], 1, 0, ['xhtml' => 1]);
        } else {
            $result = $content;
        }

        return $result;
    }
}
