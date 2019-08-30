<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2018 Kasper Skårhøj (kasperYYYY@typo3.com)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

use JambageCom\Div2007\Utility\FrontendUtility;

/**
* front end functions.
*
* @author	Franz Holzinger <franz@ttproducts.de>
* @maintainer Franz Holzinger <franz@ttproducts.de>
* @package TYPO3
* @subpackage div2007
*/


class BrowserUtility {
    /**
     * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the ctrlVars "pointer" will be pointing to the "result page" to show.
     * Using $this->ctrlVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
     * Using $this->internal['resCount'], $this->internal['limit'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
     * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
     * Using $this->internal['bShowFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
     * Using $this->internal['pagefloat']: this defines were the current page is shown in the list of pages in the Pagebrowser. If this var is an integer it will be interpreted as position in the list of pages. If its value is the keyword "center" the current page will be shown in the middle of the pagelist.
     * Using $this->internal['showRange']: this var switches the display of the pagelinks from pagenumbers to ranges f.e.: 1-5 6-10 11-15... instead of 1 2 3...
     * Using $this->pi_isOnlyFields: this holds a comma-separated list of fieldnames which - if they are among the GETvars - will not disable caching for the page with pagebrowser.
     *
     * The third parameter is an array with several wraps for the parts of the pagebrowser. The following elements will be recognized:
     * disabledLinkWrap, inactiveLinkWrap, activeLinkWrap, browseLinksWrap, showResultsWrap, showResultsNumbersWrap, browseBoxWrap.
     *
     * If $wrapArr['showResultsNumbersWrap'] is set, the formatting string is expected to hold template markers (###FROM###, ###TO###, ###OUT_OF###, ###FROM_TO###, ###CURRENT_PAGE###, ###TOTAL_PAGES###)
     * otherwise the formatting string is expected to hold sprintf-markers (%s) for from, to, outof (in that sequence)
     *
     * @param   object      parent object of type \JambageCom\Div2007\Base\BrowserBase
     * @param   object      language object of type \JambageCom\Div2007\Base\TranslationBase
     * @param   object      cObject
     * @param   string      prefix id
     * @param   boolean     if CSS styled content with div tags shall be used
     * @param   integer     determines how the results of the pagerowser will be shown. See description below
     * @param   string      Attributes for the table tag which is wrapped around the table cells containing the browse links
     *                      (only used if no CSS style is set)
     * @param   array       Array with elements to overwrite the default $wrapper-array.
     * @param   string      varname for the pointer.
     * @param   boolean     enable htmlspecialchars() for the getLabel function (set this to false if you want e.g. use images instead of text for links like 'previous' and 'next').
     * @param   array       Additional query string to be passed as parameters to the links
     * @return  string      Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
     */
    static public function render (
        \JambageCom\Div2007\Base\BrowserBase $pObject,
        \JambageCom\Div2007\Base\TranslationBase $languageObj,
        $cObj,
        $prefixId,
        $bCSSStyled = true,
        $showResultCount = 1,
        $browseParams = '',
        $wrapArr = array(),
        $pointerName = 'pointer',
        $hscText = true,
        $addQueryString = array()
    )
    {
        $usedLang = '';
        $linkArray = $addQueryString;
            // Initializing variables:
        $pointer = intval($pObject->ctrlVars[$pointerName]);
        $count = intval($pObject->internal['resCount']);
        $limit =
            MathUtility::forceIntegerInRange(
                $pObject->internal['limit'],
                1,
                1000
            );
        $totalPages = ceil($count/$limit);
        $maxPages =
            MathUtility::forceIntegerInRange(
                $pObject->internal['maxPages'],
                1,
                100
            );
        $bUseCache =
            static::autoCache(
                $pObject,
                $pObject->ctrlVars
            );

            // $showResultCount determines how the results of the pagerowser will be shown.
            // If set to 0: only the result-browser will be shown
            //           1: (default) the text "Displaying results..." and the result-browser will be shown.
            //           2: only the text "Displaying results..." will be shown
        $showResultCount = intval($showResultCount);

            // if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
        $bShowFirstLast = $pObject->internal['bShowFirstLast'];

            // if this has a value the "previous" button is always visible (will be forced if "bShowFirstLast" is set)
        $alwaysPrev =
            (
                $bShowFirstLast ?
                    true :
                    $pObject->internal['bAlwaysPrev']
            );

        if (isset($pObject->internal['pagefloat'])) {
            if (strtoupper($pObject->internal['pagefloat']) == 'CENTER') {
                $pagefloat = ceil(($maxPages - 1) / 2);
            } else {
                // pagefloat set as integer. 0 = left, value >= $pObject->internal['maxPages'] = right
                $pagefloat =
                    MathUtility::forceIntegerInRange(
                        $pObject->internal['pagefloat'],
                        -1,
                        $maxPages - 1
                    );
            }
        } else {
            $pagefloat = -1; // pagefloat disabled
        }

                // default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
        if ($bCSSStyled) {
            $wrapper['disabledLinkWrap'] = '<span class="disabledLinkWrap">|</span>';
            $wrapper['inactiveLinkWrap'] = '<span class="inactiveLinkWrap">|</span>';
            $wrapper['activeLinkWrap'] = '<span class="activeLinkWrap">|</span>';
            $wrapper['browseLinksWrap'] = '<div class="browseLinksWrap">|</div>';
            $wrapper['disabledNextLinkWrap'] = '<span class="pagination-next">|</span>';
            $wrapper['inactiveNextLinkWrap'] = '<span class="pagination-next">|</span>';
            $wrapper['disabledPreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
            $wrapper['inactivePreviousLinkWrap'] = '<span class="pagination-previous">|</span>';
        } else {
            $wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
            $wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
            $wrapper['activeLinkWrap'] = '<td' .
                FrontendUtility::classParam(
                    'browsebox-SCell', '', $prefixId
                ) . ' nowrap="nowrap"><p>|</p></td>';
            $wrapper['browseLinksWrap'] = trim('<table ' . $browseParams) . '><tr>|</tr></table>';
        }

        if (
            is_array($pObject->internal['image']) &&
            $pObject->internal['image']['path']
        ) {
            $onMouseOver = ($pObject->internal['image']['onmouseover'] ? 'onmouseover="'.$pObject->internal['image']['onmouseover'] . '" ': '');
            $onMouseOut = ($pObject->internal['image']['onmouseout'] ? 'onmouseout="' . $pObject->internal['image']['onmouseout'] . '" ': '');
            $onMouseOverActive = ($pObject->internal['imageactive']['onmouseover'] ? 'onmouseover="' . $pObject->internal['imageactive']['onmouseover'] . '" ': '');
            $onMouseOutActive = (
                $pObject->internal['imageactive']['onmouseout'] ?
                    'onmouseout="' . $pObject->internal['imageactive']['onmouseout'] . '" ':
                    ''
            );
            $wrapper['browseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['image']['filemask'] . '" ' . $onMouseOver . $onMouseOut . '>';
            $wrapper['activeBrowseTextWrap'] = '<img src="' . $pObject->internal['image']['path'] . $pObject->internal['imageactive']['filemask'] . '" ' . $onMouseOverActive . $onMouseOutActive . '>';
        }

        if ($bCSSStyled) {
            $wrapper['showResultsWrap'] = '<div class="showResultsWrap">|</div>';
            $wrapper['browseBoxWrap'] = '<div class="browseBoxWrap">|</div>';
        } else {
            $wrapper['showResultsWrap'] = '<p>|</p>';
            $wrapper['browseBoxWrap'] = '
            <!--
                List browsing box:
            -->
            <div ' . FrontendUtility::classParam('browsebox', '', $prefixId) . '>
                |
            </div>';
        }

            // now overwrite all entries in $wrapper which are also in $wrapArr
        $wrapper = array_merge($wrapper, $wrapArr);

        if ($showResultCount != 2) { //show pagebrowser
            if ($pagefloat > -1) {
                $lastPage =
                    min(
                        $totalPages,
                        max(
                            $pointer + 1 + $pagefloat,
                            $maxPages
                        )
                    );
                $firstPage = max(0, $lastPage - $maxPages);
            } else {
                $firstPage = 0;
                $lastPage =
                    MathUtility::forceIntegerInRange(
                        $totalPages,
                        1,
                        $maxPages
                    );
            }
            $links = array();

                // Make browse-table/links:
            if ($bShowFirstLast) { // Link to first page
                if ($pointer > 0) {
                    $linkArray[$pointerName] = null;
                    $links[] =
                        $cObj->wrap(
                            static::linkTPKeepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $languageObj->getLabel(
                                    'list_browseresults_first',
                                    $usedLang,
                                    '<< First',
                                    $hscText
                                ),
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                } else {
                    $links[] =
                        $cObj->wrap(
                            $languageObj->getLabel(
                                'list_browseresults_first',
                                $usedLang,
                                '<< First',
                                $hscText
                            ),
                            $wrapper['disabledLinkWrap']
                        );
                }
            }

            if ($alwaysPrev >= 0) { // Link to previous page
                $previousText =
                    $languageObj->getLabel(
                        'list_browseresults_prev',
                        $usedLang,
                        '< Previous',
                        $hscText
                    );
                if ($pointer > 0) {
                    $linkArray[$pointerName] = ($pointer - 1 ? $pointer - 1 : '');
                    $links[] =
                        $cObj->wrap(
                            static::linkTPKeepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $previousText,
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                } elseif ($alwaysPrev) {
                    $links[] =
                        $cObj->wrap(
                            $previousText,
                            $wrapper['disabledLinkWrap']
                        );
                }
            }

            for($a = $firstPage; $a < $lastPage; $a++) { // Links to pages
                $pageText = '';
                if ($pObject->internal['showRange']) {
                    $pageText = (($a * $limit) + 1) . '-' .
                        min(
                            $count,
                            (($a + 1) * $limit)
                        );
                } else if ($totalPages > 1) {
                    if ($wrapper['browseTextWrap']) {
                        if ($pointer == $a) { // current page
                            $pageText = $cObj->wrap(($a + 1), $wrapper['activeBrowseTextWrap']);
                        } else {
                            $pageText =
                                $cObj->wrap(
                                    ($a + 1),
                                    $wrapper['browseTextWrap']
                                );
                        }
                    } else {
                        $pageText =
                            trim(
                                $languageObj->getLabel(
                                    'list_browseresults_page',
                                    $usedLang,
                                    'Page',
                                    $hscText
                                )
                            ) . ' ' . ($a + 1);
                    }
                }

                $link = null;
                if ($pointer == $a) { // current page
                    if ($pObject->internal['dontLinkActivePage']) {
                        $link =
                            $cObj->wrap(
                                $pageText,
                                $wrapper['activeLinkWrap']
                            );
                    } else if ($pageText != '') {
                        $linkArray[$pointerName] = ($a ? $a : '');
                        $link =
                            static::linkTPKeepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $pageText,
                                $linkArray,
                                $bUseCache
                            );
                    }
                } else if ($pageText != '') {
                    $linkArray[$pointerName] = ($a ? $a : '');
                    $link =
                        $cObj->wrap(
                            static::linkTPKeepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $pageText,
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                }
                if (!empty($link)) {
                    $links[] = $link;
                }
            }

            if ($pointer < $totalPages - 1 || $bShowFirstLast) {
                $nextText =
                    $languageObj->getLabel(
                        'list_browseresults_next',
                        $usedLang,
                        'Next >',
                        $hscText
                    );
                if ($pointer == $totalPages - 1) { // Link to next page
                    $links[] = $cObj->wrap($nextText, $wrapper['disabledLinkWrap']);
                } else {
                    $linkArray[$pointerName] = $pointer + 1;
                    $links[] =
                        $cObj->wrap(
                            static::linkTPKeepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $nextText,
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                }
            }

            if ($bShowFirstLast) { // Link to last page
                if ($pointer < $totalPages - 1) {
                    $linkArray[$pointerName] = $totalPages - 1;
                    $links[] =
                        $cObj->wrap(
                            static::linkTPKeepCtrlVars(
                                $pObject,
                                $cObj,
                                $prefixId,
                                $languageObj->getLabel(
                                    'list_browseresults_last',
                                    $usedLang,
                                    'Last >>',
                                    $hscText
                                ),
                                $linkArray,
                                $bUseCache
                            ),
                            $wrapper['inactiveLinkWrap']
                        );
                } else {
                    $links[] =
                        $cObj->wrap(
                            $languageObj->getLabel(
                                'list_browseresults_last',
                                $usedLang,
                                'Last >>',
                                $hscText
                            ),
                            $wrapper['disabledLinkWrap']
                        );
                }
            }
            $theLinks = $cObj->wrap(implode(chr(10), $links), $wrapper['browseLinksWrap']);
        } else {
            $theLinks = '';
        }

        $pR1 = $pointer * $limit + 1;
        $pR2 = $pointer * $limit + $limit;

        if ($showResultCount) {
            if (isset($wrapper['showResultsNumbersWrap'])) {
                // this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
                // the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

                $markerArray['###FROM###'] = $cObj->wrap($count > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
                $markerArray['###TO###'] = $cObj->wrap(min($count, $pR2), $wrapper['showResultsNumbersWrap']);
                $markerArray['###OUT_OF###'] = $cObj->wrap($count, $wrapper['showResultsNumbersWrap']);
                $markerArray['###FROM_TO###'] = $cObj->wrap(($count > 0 ? $pR1 : 0) . ' ' . $languageObj->getLabel('list_browseresults_to', $usedLang, 'to') . ' ' . min($count,$pR2),$wrapper['showResultsNumbersWrap']);
                $markerArray['###CURRENT_PAGE###'] = $cObj->wrap($pointer + 1, $wrapper['showResultsNumbersWrap']);
                $markerArray['###TOTAL_PAGES###'] = $cObj->wrap($totalPages, $wrapper['showResultsNumbersWrap']);
                $list_browseresults_displays = $languageObj->getLabel('list_browseresults_displays_marker', $usedLang, 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
                // substitute markers
                $resultCountMsg = $cObj->substituteMarkerArray($list_browseresults_displays, $markerArray);
            } else {
                // render the resultcount in the "traditional" way using sprintf
                $resultCountMsg = sprintf(
                    str_replace(
                        '###SPAN_BEGIN###',
                        '<span' . FrontendUtility::classParam('browsebox-strong', '', $prefixId) . '>',
                        $languageObj->getLabel('list_browseresults_displays', $usedLang, 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')
                    ),
                    $count > 0 ? $pR1 : 0,
                    min($count, $pR2),
                    $count
                );
            }
            $resultCountMsg = $cObj->wrap($resultCountMsg, $wrapper['showResultsWrap']);
        } else {
            $resultCountMsg = '';
        }
        $rc = $cObj->wrap($resultCountMsg . $theLinks, $wrapper['browseBoxWrap']);
        return $rc;
    }

    /**
     * deprecated:
     * use BrowserUtility::autoCache instead
     *
     * Returns true if the array $inArray contains only values allowed to be cached based on the configuration in $this->pi_autoCacheFields
     * Used by static::linkTPKeepCtrlVars
     * This is an advanced form of evaluation of whether a URL should be cached or not.
     *
     * @param   object      parent object of type \JambageCom\Div2007\Base\BrowserBase
     * @return  boolean     Returns true (1) if conditions are met.
     * @see linkTPKeepCtrlVars()
     */
    static public function autoCache ($pObject, $inArray)
    {
        $bUseCache = true;

        if (is_array($inArray)) {
            foreach($inArray as $fN => $fV) {
                $bIsCachable = false;
                if (!strcmp($inArray[$fN],'')) {
                    $bIsCachable = true;
                } elseif (is_array($pObject->autoCacheFields[$fN])) {
                    if (
                        is_array($pObject->autoCacheFields[$fN]['range']) &&
                        intval($inArray[$fN]) >= intval($pObject->autoCacheFields[$fN]['range'][0]) &&
                        intval($inArray[$fN]) <= intval($pObject->autoCacheFields[$fN]['range'][1])) {
                        $bIsCachable = true;
                    }

                    if (    
                        is_array($this->autoCacheFields[$fN]['list']) &&
                        in_array($inArray[$fN], $pObject->autoCacheFields[$fN]['list'])
                    ){
                        $bIsCachable = true;
                    }
                }

                if (!$bIsCachable) {
                    $bUseCache = false;
                    break;
                }
            }
        }
        return $bUseCache;
    }

    /**
     * Link a string to the current page while keeping currently set values in piVars.
     * Like static::linkTP, but $urlParameters is by default set to $this->piVars with $overruleCtrlVars overlaid.
     * This means any current entries from this->piVars are passed on (except the key "DATA" which will be unset before!) and entries in $overruleCtrlVars will OVERRULE the current in the link.
     *
     * @param   object      parent object of type \JambageCom\Div2007\Base\BrowserBase
     * @param   object      cObject
     * @param   string      prefix id
     * @param   string      The content string to wrap in <a> tags
     * @param   array       Array of values to override in the current piVars. Contrary to static::linkTP the keys in this array must correspond to the real piVars array and therefore NOT be prefixed with the $this->prefixId string. Further, if a value is a blank string it means the piVar key will not be a part of the link (unset)
     * @param   boolean     If $cache is set, the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
     * @param   boolean     If set, then the current values of piVars will NOT be preserved anyways... Practical if you want an easy way to set piVars without having to worry about the prefix, "tx_xxxxx[]"
     * @param   integer     Alternative page ID for the link. (By default this function links to the SAME page!)
     * @return  string      The input string wrapped in <a> tags
     * @see static::linkTP()
     */
    static public function linkTPKeepCtrlVars (
        \JambageCom\Div2007\Base\BrowserBase $pObject,
        $cObj,
        $prefixId,
        $str,
        $overruleCtrlVars = array(),
        $cache = 0,
        $clearAnyway = 0,
        $altPageId = 0
    )
    {
        $overruledCtrlVars = '';

        if (
            is_array($overruleCtrlVars) &&
            !$clearAnyway
        ) {
            $overruledCtrlVars = array();
            if (
                isset($pObject->ctrlVars) &&
                is_array($pObject->ctrlVars)
            ) {
                $ctrlVars = $pObject->ctrlVars;
                unset($ctrlVars['DATA']);
                $overruledCtrlVars = $ctrlVars;
            }
            $merged =
                ArrayUtility::mergeRecursiveWithOverrule(
                    $overruledCtrlVars,
                    $overruleCtrlVars
                );
            if ($pObject->getAutoCacheEnable()) {
                $cache = static::autoCache($pObject, $overruledCtrlVars);
            }
        }

        $result =
            static::linkTP(
                $pObject,
                $cObj,
                $str,
                array(
                    $prefixId => $overruledCtrlVars
                ),
                $cache,
                $altPageId
            );
        return $result;
    }

    /**
     * deprecated:
     * use BrowserUtility::linkTPKeepCtrlVars instead
     *
     * Link string to the current page.
     * Returns the $str wrapped in <a>-tags with a link to the CURRENT page, but with $urlParameters set as extra parameters for the page.
     *
     * @param   object      parent object of type \JambageCom\Div2007\Base\BrowserBase
     * @param   object      cObject
     * @param   string      The content string to wrap in <a> tags
     * @param   array       Array with URL parameters as key/value pairs. They will be "imploded" and added to the list of parameters defined in the plugins TypoScript property "parent.addParams" plus $this->pi_moreParams.
     * @param   boolean     If $cache is set (0/1), the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
     * @param   integer     Alternative page ID for the link. (By default this function links to the SAME page!)
     * @return  string      The input string wrapped in <a> tags
     * @see pi_linkTP_keepPIvars(), TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::typoLink()
     */
    static public function linkTP (
        \JambageCom\Div2007\Base\BrowserBase $pObject,
        $cObj,
        $str,
        $urlParameters = array(),
        $cache = 0,
        $altPageId = 0
    )
    {
        $conf = array();
        $conf['useCacheHash'] = $pObject->getIsUserIntObject() ? 0 : $cache;
        $conf['no_cache'] = $pObject->getIsUserIntObject() ? 0 : !$cache;
        $conf['parameter'] = $altPageId ? $altPageId : ($pObject->tmpPageId ? $pObject->tmpPageId : $GLOBALS['TSFE']->id);
        $conf['additionalParams'] = $pObject->conf['parent.']['addParams'] . GeneralUtility::implodeArrayForUrl('', $urlParameters, '', true) . $pObject->moreParams;
        $result = $cObj->typoLink($str, $conf);
        return $result;
    }
}

