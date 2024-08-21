<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Kasper Skårhøj (kasperYYYY@typo3.com)
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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Resource\FilePathSanitizer;

use JambageCom\Div2007\Api\Frontend;
use JambageCom\Div2007\Api\FrontendApi;
use JambageCom\Div2007\Base\BrowserBase;
use JambageCom\Div2007\Base\TranslationBase;

/**
 * front end functions.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
class FrontendUtility
{
    /**
     * @var TypoScriptFrontendController
     */
    protected static $typoScriptFrontendController;

    public static function test(): bool
    {
        return true;
    }

    /**
     * This is the MAIN DOCUMENT of the TypoScript driven standard front-end (from
     * the "cms" extension).
     *
     * Basically call this php script which all requests for TYPO3
     * delivered pages goes to in the frontend (the website) The script configures
     * constants, includes libraries and does a little logic here and there in order
     * to instantiate the right classes to create the webpage.
     *
     * All the real data processing goes on in the library classes which this script
     * will use as needed.
     *
     * @author Kasper Skårhøj <kasperYYYY@typo3.com>
     */

    /**
     * This method is needed only for Ajax calls.
     * You can use $GLOBALS['TSFE']->id or $GLOBALS['TSFE']->determineId instead of this method.
     *
     * @return int
     */
    public static function getPageId(...$params)
    {
        $api = GeneralUtility::makeInstance(FrontendApi::class);
        $result = $api->getPageId($params);

        return $result;
    }

    /**
     * Get the logged in front end user.
     *
     * @param	string		field of the user if set
     *
     * @return object the current frontend user or string value of the field or boolean false
     *
     * @see tx_div2007::user();
     */
    public static function getFrontEndUser($field = '')
    {
        $result = false;
        $context = GeneralUtility::makeInstance(Context::class);
        $tsfe = static::getTypoScriptFrontendController();

        if (
            isset($tsfe->fe_user) &&
            is_object($tsfe->fe_user) &&
            $context->getPropertyFromAspect('frontend.user', 'isLoggedIn') &&
            isset($tsfe->fe_user->user) &&
            is_array($tsfe->fe_user->user) &&
            isset($tsfe->fe_user->user['username']) &&
            $tsfe->fe_user->user['username'] != ''
        ) {
            $result = $tsfe->fe_user;

            if (
                $field != '' &&
                isset($tsfe->fe_user->user[$field])
            ) {
                $result = $tsfe->fe_user->user[$field];
            }
        }

        return $result;
    }

    /**
     * Returns a JavaScript <script> section with some function calls to JavaScript functions from "typo3/js/jsfunc.updateform.js" (which is also included by setting a reference in $GLOBALS['TSFE']->additionalHeaderData['JSincludeFormupdate'])
     * The JavaScript codes simply transfers content into form fields of a form which is probably used for editing information by frontend users. Used by fe_adminLib.inc.
     *
     * @param array $dataArray Data array which values to load into the form fields from $formName (only field names found in $fieldList)
     * @param string $formName The form name
     * @param string´ $arrPrefix A prefix for the data array
     * @param string $fieldList The list of fields which are loaded
     * @param string $javascriptFilename relative path to the filename of the Javascript which can execute the update form
     *
     * @return string containing the update Javascript
     *
     * @access public
     *
     * @see tx_agency_display::createScreen()
     */
    public static function getUpdateJS(
        $dataArray,
        $formName,
        $arrPrefix,
        $fieldList,
        $javascriptFilename = ''
    ) {
        $result = false;
        $JSPart = '';
        $updateValues = GeneralUtility::trimExplode(',', $fieldList);
        foreach ($updateValues as $fKey) {
            $value = $dataArray[$fKey] ?? '';
            if (is_array($value)) {
                foreach ($value as $Nvalue) {
                    $JSPart .= '
    updateForm(\'' . $formName . '\',\'' . $arrPrefix . '[' . $fKey . '][]\',' . GeneralUtility::quoteJSvalue($Nvalue) . ');';
                }
            } else {
                $JSPart .= '
    updateForm(\'' . $formName . '\',\'' . $arrPrefix . '[' . $fKey . ']\',' . GeneralUtility::quoteJSvalue($value) . ');';
            }
        }
        $JSPart = '<script type="text/javascript">
    /*<![CDATA[*/ ' . $JSPart . '
    /*]]>*/
</script>
';

        if (
            static::determineJavascriptFilename(
                $javascriptFilename,
                'jsfunc.updateform.js'
            )
        ) {
            static::addJavascriptFile($javascriptFilename, 'JSincludeFormupdate');
            $result = $JSPart;
        }

        return $result;
    }

    public static function addJavascriptFile($filename, $key): void
    {
        $script =
            '<script type="text/javascript" src="' .
                $GLOBALS['TSFE']->absRefPrefix .
                GeneralUtility::createVersionNumberedFilename($filename) .
            '"></script>';
        $GLOBALS['TSFE']->additionalHeaderData[$key] = $script;
    }

    public static function addCssFile($filename, $key): void
    {
        $GLOBALS['TSFE']->additionalHeaderData[$key] =
            '<link rel="stylesheet" href="' .
            $GLOBALS['TSFE']->absRefPrefix .
            GeneralUtility::createVersionNumberedFilename($filename) . '" type="text/css" />';
    }

    public static function determineJavascriptFilename(
        &$javascriptFilename,
        $defaultBasename
    ) {
        $result = static::determineFilename(
            $javascriptFilename,
            $defaultBasename,
            'Resources/Public/JavaScript/'
        );

        return $result;
    }

    public static function determineCssFilename(
        &$javascriptFilename,
        $defaultBasename
    ) {
        $result = static::determineFilename(
            $javascriptFilename,
            $defaultBasename,
            'Resources/Public/Css/'
        );

        return $result;
    }

    public static function determineFilename(
        &$filename,
        $defaultBasename,
        $defaultPath
    ) {
        $result = false;
        $path = '';

        $extensionPath = ExtensionManagementUtility::extPath(DIV2007_EXT);
        $relativeExtensionPath = PathUtility::stripPathSitePrefix(
            $extensionPath
        );

        if (empty($filename)) {
            $filename = $relativeExtensionPath .
                $defaultPath . $defaultBasename;
        }

        $lookupFile = explode('?', $filename);
        $scriptPath = Environment::getCurrentScript();

        $path =
            GeneralUtility::resolveBackPath(
                GeneralUtility::dirname(
                    $scriptPath
                ) .
                '/' .
                $lookupFile[0]
            );

        if (file_exists($path)) {
            $result = true;
        }

        return $result;
    }

    public static function addTab(
        $templateCode,
        &$markerArray,
        &$subpartArray,
        &$wrappedSubpartArray,
        $keyPrefix = '',
        $javascriptFilename = '',
        $cssFilename = ''
    ) {
        $result = false;
        preg_match_all('/###(TAB_.*)###/', $templateCode, $treffer);
        $internalMarkerArray = [];
        if (
            isset($treffer) &&
            is_array($treffer) &&
            isset($treffer['0'])
        ) {
            $internalMarkerArray = array_unique($treffer['0']);
        }

        $headerCounter = 0;
        $boxCounter = 0;
        foreach ($internalMarkerArray as $marker) {
            if (str_starts_with($marker, '###TAB_HEADER_' . ($headerCounter + 1))) {
                $headerCounter++;
            }
            if (str_starts_with($marker, '###TAB_BOX_' . ($boxCounter + 1))) {
                $boxCounter++;
            }
        }

        if (
            $headerCounter == $boxCounter &&
            static::determineJavascriptFilename(
                $javascriptFilename,
                'jsfunc.tab.js'
            )
        ) {
            static::addJavascriptFile($javascriptFilename, $keyPrefix . 'JSincludeTab');
            $markerArray['###TAB_OPEN_JS###'] =
'<script type="text/javascript">
        openTab(1); // open Tab 1
</script>';

            if (
                static::determineCssFilename(
                    $cssFilename,
                    'tab.css'
                )
            ) {
                static::addCssFile($cssFilename, $keyPrefix . 'CSSincludeTab');
                $result = true;
                $wrappedSubpartArray['###TAB_MENU###'] =
                    [
                        '<div id="tabmenu" class="tabmenu">',
                        '</div>',
                    ];

                for ($i = 1; $i <= $headerCounter; $i++) {
                    $wrappedSubpartArray['###TAB_HEADER_' . $i . '###'] =
                        [
                            '<div id="tab_top_' . $i . '" class="tab_top_active" onclick="javascript:openTab(' . $i . ');">',
                            '</div>',
                        ];
                    $wrappedSubpartArray['###TAB_BOX_' . $i . '###'] =
                        [
                            '<div id="tab_box_' . $i . '" class="tab_box">',
                            '</div>',
                        ];
                }
            }
        } else {
            $markerArray['###TAB_OPEN_JS###'] = '';
            $subpartArray['###TAB_MENU###'] = '';
            for ($i = 1; $i <= $headerCounter; $i++) {
                $subpartArray['###TAB_HEADER_' . $i . '###'] = '';
            }
            for ($i = 1; $i <= $boxCounter; $i++) {
                $subpartArray['###TAB_BOX_' . $i . '###'] = '';
            }
        }

        return $result;
    }

    public static function getContentObjectRendererClassname()
    {
        $useClassName = false;
        $callingClassName = ContentObjectRenderer::class;

        return $callingClassName;
    }

    /**
     * Class constructor.
     * Well, it has to be called manually since it is not a real constructor function.
     * Call this function which is making an instance of the class, and pass to it a database record and the tablename from where the record is from. That will then become the "current" record loaded into memory and accessed by the .fields property found in eg. stdWrap.
     *
     * @param array $data the record data that is rendered
     * @param string $table the table that the data record is from
     */
    public static function getContentObjectRenderer($data = [], $table = '')
    {
        $className = static::getContentObjectRendererClassname();
        $cObj = GeneralUtility::makeInstance($className);	// local cObj
        $cObj->start($data, $table);

        return $cObj;
    }

    /**
     * deprecated:
     * use BrowserUtility::render instead
     *
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
     * @param   bool     if CSS styled content with div tags shall be used
     * @param   int     determines how the results of the pagerowser will be shown. See description below
     * @param   string      Attributes for the table tag which is wrapped around the table cells containing the browse links
     *                      (only used if no CSS style is set)
     * @param   array       array with elements to overwrite the default $wrapper-array
     * @param   string      varname for the pointer
     * @param   bool     enable htmlspecialchars() for the getLabel function (set this to false if you want e.g. use images instead of text for links like 'previous' and 'next').
     * @param   array       Additional query string to be passed as parameters to the links
     *
     * @return  string      Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
     */
    public static function listBrowser(
        BrowserBase $pObject,
        TranslationBase $languageObj,
        $cObj,
        $prefixId,
        $bCSSStyled = true,
        $showResultCount = 1,
        $browseParams = '',
        $wrapArr = [],
        $pointerName = 'pointer',
        $hscText = true,
        $addQueryString = []
    ) {
        $usedLang = '';
        $parser = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
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
        $totalPages = ceil($count / $limit);
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
                static::classParam(
                    'browsebox-SCell', '', $prefixId
                ) . ' nowrap="nowrap"><p>|</p></td>';
            $wrapper['browseLinksWrap'] = trim('<table ' . $browseParams) . '><tr>|</tr></table>';
        }

        if (
            isset($pObject->internal['image']) &&
            is_array($pObject->internal['image']) &&
            $pObject->internal['image']['path']
        ) {
            $onMouseOver = ($pObject->internal['image']['onmouseover'] ? 'onmouseover="' . $pObject->internal['image']['onmouseover'] . '" ' : '');
            $onMouseOut = ($pObject->internal['image']['onmouseout'] ? 'onmouseout="' . $pObject->internal['image']['onmouseout'] . '" ' : '');
            $onMouseOverActive = ($pObject->internal['imageactive']['onmouseover'] ? 'onmouseover="' . $pObject->internal['imageactive']['onmouseover'] . '" ' : '');
            $onMouseOutActive = (
                $pObject->internal['imageactive']['onmouseout'] ?
                    'onmouseout="' . $pObject->internal['imageactive']['onmouseout'] . '" ' :
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
            <div ' . static::classParam('browsebox', '', $prefixId) . '>
                |
            </div>';
        }

        // now overwrite all entries in $wrapper which are also in $wrapArr
        $wrapper = array_merge($wrapper, $wrapArr);

        if ($showResultCount != 2) { // show pagebrowser
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
            $links = [];

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
                    $linkArray[$pointerName] = ($pointer - 1 ?: '');
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

            for ($a = $firstPage; $a < $lastPage; $a++) { // Links to pages
                $pageText = '';
                if ($pObject->internal['showRange']) {
                    $pageText = (($a * $limit) + 1) . '-' .
                        min(
                            $count,
                            ($a + 1) * $limit
                        );
                } elseif ($totalPages > 1) {
                    if ($wrapper['browseTextWrap']) {
                        if ($pointer == $a) { // current page
                            $pageText = $cObj->wrap($a + 1, $wrapper['activeBrowseTextWrap']);
                        } else {
                            $pageText =
                                $cObj->wrap(
                                    $a + 1,
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
                    } elseif ($pageText != '') {
                        $linkArray[$pointerName] = ($a ?: '');
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
                } elseif ($pageText != '') {
                    $linkArray[$pointerName] = ($a ?: '');
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

                $markerArray['###FROM###'] = $cObj->wrap($count > 0 ? $pR1 : 0, $wrapper['showResultsNumbersWrap']);
                $markerArray['###TO###'] = $cObj->wrap(min($count, $pR2), $wrapper['showResultsNumbersWrap']);
                $markerArray['###OUT_OF###'] = $cObj->wrap($count, $wrapper['showResultsNumbersWrap']);
                $markerArray['###FROM_TO###'] = $cObj->wrap(($count > 0 ? $pR1 : 0) . ' ' . $languageObj->getLabel('list_browseresults_to', $usedLang, 'to') . ' ' . min($count, $pR2), $wrapper['showResultsNumbersWrap']);
                $markerArray['###CURRENT_PAGE###'] = $cObj->wrap($pointer + 1, $wrapper['showResultsNumbersWrap']);
                $markerArray['###TOTAL_PAGES###'] = $cObj->wrap($totalPages, $wrapper['showResultsNumbersWrap']);
                $list_browseresults_displays = $languageObj->getLabel('list_browseresults_displays_marker', $usedLang, 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
                // substitute markers
                $resultCountMsg = $parser->substituteMarkerArray($list_browseresults_displays, $markerArray);
            } else {
                // render the resultcount in the "traditional" way using sprintf
                $resultCountMsg = sprintf(
                    str_replace(
                        '###SPAN_BEGIN###',
                        '<span' . static::classParam('browsebox-strong', '', $prefixId) . '>',
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
     * @param   object      parent object of type tx_div2007_alpha_browse_base
     *
     * @return  bool     returns true (1) if conditions are met
     *
     * @see linkTPKeepCtrlVars()
     */
    public static function autoCache($pObject, $inArray)
    {
        $bUseCache = true;

        if (is_array($inArray)) {
            foreach ($inArray as $fN => $fV) {
                $bIsCachable = false;
                if (!strcmp($fV, '')) {
                    $bIsCachable = true;
                } elseif (
                    isset($pObject->autoCacheFields[$fN]) &&
                    is_array($pObject->autoCacheFields[$fN])
                ) {
                    if (
                        isset($pObject->autoCacheFields[$fN]['range']) &&
                        is_array($pObject->autoCacheFields[$fN]['range']) &&
                        intval($fV) >= intval($pObject->autoCacheFields[$fN]['range'][0]) &&
                        intval($fV) <= intval($pObject->autoCacheFields[$fN]['range'][1])) {
                        $bIsCachable = true;
                    }

                    if (
                        isset($pObject->autoCacheFields[$fN]['list']) &&
                        is_array($pObject->autoCacheFields[$fN]['list']) &&
                        in_array($fV, $pObject->autoCacheFields[$fN]['list'])
                    ) {
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
     * Returns the class-attribute with the correctly prefixed classname
     * Using getClassName().
     *
     * @param   string      The class name(s) (suffix) - separate multiple classes with commas
     * @param   string      Additional class names which should not be prefixed - separate multiple classes with commas
     * @param   string      $prefixId
     * @param   bool     if set, then the prefix 'tx_' is added to the extension name
     *
     * @return  string      a "class" attribute with value and a single space char before it
     *
     * @see pi_classParam()
     */
    public static function classParam($class, $addClasses = '', $prefixId = '', $bAddPrefixTx = false)
    {
        $output = '';
        foreach (GeneralUtility::trimExplode(',', $class) as $v) {
            $output .= ' ' . static::getClassName($v, $prefixId, $bAddPrefixTx);
        }
        foreach (GeneralUtility::trimExplode(',', $addClasses) as $v) {
            $output .= ' ' . $v;
        }

        return ' class="' . trim($output) . '"';
    }

    /**
     * Returns a class-name prefixed with $prefixId and with all underscores substituted to dashes (-). Copied from pi_getClassName.
     *
     * @param   string      The class name (or the END of it since it will be prefixed by $prefixId . '-')
     * @param   string      $prefixId
     * @param   bool     if set, then the prefix 'tx_' is added to the extension name
     *
     * @return  string      The combined class name (with the correct prefix)
     *
     * @see pi_getClassName()
     */
    public static function getClassName($class, $prefixId = '', $bAddPrefixTx = false)
    {
        if ($bAddPrefixTx && $prefixId != '' && !str_starts_with($prefixId, 'tx_')) {
            $prefixId = 'tx-' . $prefixId;
        }

        return str_replace('_', '-', $prefixId) . ($prefixId != '' ? '-' : '') . $class;
    }

    /**
     * deprecated:
     * use BrowserUtility::linkTPKeepCtrlVars instead
     *
     * Link a string to the current page while keeping currently set values in piVars.
     * Like static::linkTP, but $urlParameters is by default set to $this->piVars with $overruleCtrlVars overlaid.
     * This means any current entries from this->piVars are passed on (except the key "DATA" which will be unset before!) and entries in $overruleCtrlVars will OVERRULE the current in the link.
     *
     * @param   object      parent object of type tx_div2007_alpha_browse_base
     * @param   object      cObject
     * @param   string      prefix id
     * @param   string      The content string to wrap in <a> tags
     * @param   array       Array of values to override in the current piVars. Contrary to static::linkTP the keys in this array must correspond to the real piVars array and therefore NOT be prefixed with the $this->prefixId string. Further, if a value is a blank string it means the piVar key will not be a part of the link (unset)
     * @param   bool     If $cache is set, the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
     * @param   bool     If set, then the current values of piVars will NOT be preserved anyways... Practical if you want an easy way to set piVars without having to worry about the prefix, "tx_xxxxx[]"
     * @param   int     Alternative page ID for the link. (By default this function links to the SAME page!)
     *
     * @return  string      The input string wrapped in <a> tags
     *
     * @see static::linkTP()
     */
    public static function linkTPKeepCtrlVars(
        BrowserBase $pObject,
        $cObj,
        $prefixId,
        $str,
        $overruleCtrlVars = [],
        $cache = 0,
        $clearAnyway = 0,
        $altPageId = 0
    ) {
        $overruledCtrlVars = '';

        if (
            is_array($overruleCtrlVars) &&
            !$clearAnyway
        ) {
            $overruledCtrlVars = [];
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
                [
                    $prefixId => $overruledCtrlVars,
                ],
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
     * @param   object      parent object of type tx_div2007_alpha_browse_base
     * @param   object      cObject
     * @param   string      The content string to wrap in <a> tags
     * @param   array       Array with URL parameters as key/value pairs. They will be "imploded" and added to the list of parameters defined in the plugins TypoScript property "parent.addParams" plus $this->pi_moreParams.
     * @param   bool     If $cache is set (0/1), the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
     * @param   int     Alternative page ID for the link. (By default this function links to the SAME page!)
     *
     * @return  string      The input string wrapped in <a> tags
     *
     * @see pi_linkTP_keepPIvars(), TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::typoLink()
     */
    public static function linkTP(
        BrowserBase $pObject,
        $cObj,
        $str,
        $urlParameters = [],
        $cache = 0,
        $altPageId = 0
    ) {
        $conf = [];
        $conf['no_cache'] = $pObject->getIsUserIntObject() ? 0 : !$cache;
        $conf['parameter'] = $altPageId ?: ($pObject->tmpPageId ?: $GLOBALS['TSFE']->id);
        $conf['additionalParams'] = $pObject->conf['parent.']['addParams'] . GeneralUtility::implodeArrayForUrl('', $urlParameters, '', true) . $pObject->moreParams;
        $result = $cObj->typoLink($str, $conf);

        return $result;
    }

    /**
     * Returns a linked string made from typoLink parameters.
     *
     * This function takes $label as a string, wraps it in a link-tag based on the $params string, which should contain data like that you would normally pass to the popular <LINK>-tag in the TSFE.
     * Optionally you can supply $urlParameters which is an array with key/value pairs that are rawurlencoded and appended to the resulting url.
     *
     * @param   object      cObject
     * @param   string      text string being wrapped by the link
     * @param   string      Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
     * @param   array       An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
     * @param   string      Specific target set, if any. (Default is using the current)
     * @param   array       Configuration
     *
     * @return  string      The wrapped $label-text string
     *
     * @see getTypoLink_URL()
     */
    public static function getTypoLink(
        $cObj,
        $label,
        $params,
        $urlParameters = [],
        $target = '',
        $conf = []
    ) {
        $result = false;

        if (is_object($cObj)) {
            $conf['parameter'] = $params;

            if ($target) {
                if (!isset($conf['target'])) {
                    $conf['target'] = $target;
                }
                if (!isset($conf['extTarget'])) {
                    $conf['extTarget'] = $target;
                }
            }

            $paramsOld = '';
            $paramsNew = '';
            if (isset($conf['additionalParams'])) {
                $paramsOld = $conf['additionalParams'];
            }

            if (!isset($conf['language'])) {
                $api =
                    GeneralUtility::makeInstance(Frontend::class);
                $sys_language_uid = $api->getLanguageId();
                if ($sys_language_uid) {
                    $conf['language'] = $sys_language_uid;
                }
            }

            if (is_array($urlParameters)) {
                if (count($urlParameters)) {
                    $paramsNew = GeneralUtility::implodeArrayForUrl('', $urlParameters);
                }
            } else {
                $paramsNew = $urlParameters;
            }
            $conf['additionalParams'] = $paramsOld . $paramsNew;
            $result = $cObj->typoLink($label, $conf);
        } else {
            $result = 'error in call of \JambageCom\Div2007\Utility\FrontendUtility::getTypoLink: parameter $cObj is not an object';
        }

        return $result;
    }

    /**
     * Returns the URL of a "typolink" create from the input parameter string, url-parameters and target.
     *
     * @param   object      cObject
     * @param   string      Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
     * @param   array       An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
     * @param   string      Specific target set, if any. (Default is using the current)
     * @param   array       Configuration
     *
     * @return  string      The URL
     *
     * @see getTypoLink()
     */
    public static function getTypoLink_URL(
        $cObj,
        $params,
        $urlParameters = [],
        $target = '',
        $conf = []
    ) {
        $result = false;

        if (is_object($cObj)) {
            $result = static::getTypoLink(
                $cObj,
                '',
                $params,
                $urlParameters,
                $target,
                $conf
            );

            if ($result !== false) {
                $result = $cObj->lastTypoLinkResult;
            }
        } else {
            $out = 'error in call of \JambageCom\Div2007\Utility\FrontendUtility::getTypoLink_URL: parameter $cObj is not an object';
            debug($out, '$out'); // keep this
        }

        return $result;
    }

    public static function hasRTEparser()
    {
        $result = isset($GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.']);

        return $result;
    }

    /**
     * This is the original pi_RTEcssText from the former tslib_pibase
     * Will process the input string with the parseFunc function from TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer based on configuration set in "lib.parseFunc_RTE" in the current TypoScript template.
     * This is useful for rendering of content in RTE fields where the transformation mode is set to "ts_css" or so.
     * Notice that this requires the use of "css_styled_content" to work right.
     *
     * @param	object     cOject of class TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     * @param	string     The input text string to process
     *
     * @return	string     The processed string
     *
     * @see TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::parseFunc()
     */
    public static function RTEcssText($cObj, $str)
    {
        $result = '';
        $parseFunc = '';
        if (static::hasRTEparser()) {
            $parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
        }
        if (is_array($parseFunc)) {
            $result = $cObj->parseFunc($str, $parseFunc);
        }

        return $result;
    }

    public static function translate($extensionKey, $filename, $key)
    {
        $result = $GLOBALS['TSFE']->sL('LLL:EXT:' . $extensionKey . $filename . ':' . $key);

        return $result;
    }

    /**
     * Wrap content with the plugin code
     * wraps the content of the plugin before the final output.
     *
     * @param	string		content
     * @param	string		CODE of plugin
     * @param	string		prefix id of the plugin
     * @param	string		content uid
     *
     * @return	string		The resulting content
     *
     * @see pi_linkToPage()
     */
    public static function wrapContentCode(
        $content,
        $theCode,
        $prefixId,
        $uid
    ) {
        $idNumber = str_replace('_', '-', $prefixId . '-' . strtolower($theCode));
        $classname = $idNumber;
        if ($uid != '') {
            $idNumber .= '-' . $uid;
        }

        $result = '<!-- START: ' . $idNumber . ' --><div id="' . $idNumber . '" class="' . $classname . '">' .
            ($content != '' ? $content : '') . '</div><!-- END: ' . $idNumber . ' -->';

        return $result;
    }

    /**
     * Wraps the input string in a <div> tag with the class attribute set to the prefixId.
     * All content returned from your plugins should be returned through this function so all content from your plugin is encapsulated in a <div>-tag nicely identifying the content of your plugin.
     *
     * @param	string		HTML content to wrap in the div-tags with the "main class" of the plugin
     *
     * @return	string		HTML content wrapped, ready to return to the parent object
     *
     * @see pi_wrapInBaseClass()
     */
    public static function wrapInBaseClass(
        $str,
        $prefixId,
        $extKey
    ) {
        $content = '<div class="' . str_replace('_', '-', $prefixId) . '">
        ' . $str . '
    </div>
    ';

        if (empty($GLOBALS['TSFE']->config['config']['disablePrefixComment'])) {
            $content = '

    <!--

        BEGIN: Content of extension "' . $extKey . '", plugin "' . $prefixId . '"

    -->
    ' . $content . '
    <!-- END: Content of extension "' . $extKey . '", plugin "' . $prefixId . '" -->

    ';
        }

        return $content;
    }

    public static function fixImageCodeAbsRefPrefix(
        &$imageCode,
        $domain = ''
    ): void {
        $absRefPrefix = '';
        $absRefPrefixDomain = '';
        $bSetAbsRefPrefix = false;
        if (
            $GLOBALS['TSFE']->absRefPrefix != '' &&
            $GLOBALS['TSFE']->absRefPrefix != '/'
        ) {
            $absRefPrefix = $GLOBALS['TSFE']->absRefPrefix;
        } else {
            $bSetAbsRefPrefix = true;
        }

        $normalizedParams = null;
        if (is_object($GLOBALS['TYPO3_REQUEST'])) {
            $normalizedParams = $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams');
        }
        if (is_object($normalizedParams)) {
            $absRefPrefixDomain = $normalizedParams->getSiteUrl();
        } else {
            $absRefPrefixDomain = GeneralUtility::getIndpEnv('REQUEST_URI');
        }

        if (
            $domain != '' &&
            !$bSetAbsRefPrefix
        ) {
            $parse = [];
            $parse = parse_url($absRefPrefix);
            $absRefPrefixDomain = $absRefPrefix;
            $absRefPrefixDomain = str_replace($parse['host'], $domain, $absRefPrefixDomain);
        }

        if ($bSetAbsRefPrefix) {
            $absRefPrefix = '/';
        }
        $fixImgCode = str_replace($absRefPrefix . 'index.php', $absRefPrefixDomain . 'index.php', $imageCode);
        $fixImgCode = str_replace('src="' . $absRefPrefix, 'src="' . $absRefPrefixDomain, $fixImgCode);
        $fixImgCode = str_replace('"' . $absRefPrefix . 'fileadmin/', '"' . $absRefPrefixDomain . 'fileadmin/', $fixImgCode);
        $fixImgCode = str_replace('"' . $absRefPrefix . 'uploads/', '"' . $absRefPrefixDomain . 'uploads/', $fixImgCode);
        $fixImgCode = str_replace('"' . $absRefPrefix . 'typo3conf/', '"' . $absRefPrefixDomain . 'typo3conf/', $fixImgCode);
        $imageCode = $fixImgCode;
    }

    public static function slashName($name, $apostrophe = '"')
    {
        $name = str_replace(',', ' ', $name);
        $rc = $apostrophe . addcslashes($name, '<>()@;:\\".[]' . chr('\n')) . $apostrophe;

        return $rc;
    }

    /**
     * Returns content of a file. If it's an image the content of the file is not returned but rather an image tag is.
     *
     * @param string $fName The filename, being a TypoScript resource data type
     * @param string $addParams Additional parameters (attributes). Default is empty alt and title tags.
     * @param bool $sanitize if true then the front end sanitizer is used to check if the file location is inside the path Resources/Public
     *
     * @return string If jpg,gif,jpeg,png: returns image_tag with picture in. If html,txt: returns content string
     *
     * @see FILE(), \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer fileResource
     */
    public static function fileResource($fName, $addParams = 'alt="" title=""', $sanitize = true)
    {
        $result = '';
        $incFile = '';
        if (
            $fName != ''
        ) {
            if ($sanitize) {
                $sanitizer = GeneralUtility::makeInstance(FilePathSanitizer::class);
                $incFile = $sanitizer->sanitize($fName);
            } else {
                $incFile = GeneralUtility::getFileAbsFileName($fName);
            }
        }

        if ($incFile && file_exists($incFile)) {
            $fileInfo = GeneralUtility::split_fileref($incFile);
            $extension = $fileInfo['fileext'];
            if (
                $extension === 'jpg' ||
                $extension === 'jpeg' ||
                $extension === 'gif' ||
                $extension === 'png'
            ) {
                $tsfe = static::getTypoScriptFrontendController();
                $xhtmlFix = HtmlUtility::generateXhtmlFix();
                $imgFile = $incFile;
                $imgInfo = @getimagesize($imgFile);
                $result = '<img src="' . htmlspecialchars($tsfe->absRefPrefix . $imgFile) . '" width="' . (int)$imgInfo[0] . '" height="' . (int)$imgInfo[1] . '"' . static::getBorderAttribute(' border="0"') . ' ' . $addParams . ' ' . $xhtmlFix . '>';
            } elseif (filesize($incFile) < 1024 * 1024) {
                $result = file_get_contents($incFile);
            }
        }

        return $result;
    }

    /**
     * Returns the 'border' attribute for an <img> tag only if the doctype is not xhtml_strict, xhtml_11 or html5
     * or if the config parameter 'disableImgBorderAttr' is not set.
     *
     * @param string $borderAttr The border attribute
     *
     * @return string The border attribute
     */
    public static function getBorderAttribute($borderAttr)
    {
        $tsfe = static::getTypoScriptFrontendController();
        $docType = $tsfe->xhtmlDoctype;
        if (
            $docType !== 'xhtml_strict' &&
            $docType !== 'xhtml_11' &&
            (
                !isset($tsfe->config['config']['doctype']) ||
                $tsfe->config['config']['doctype'] !== 'html5'
            ) &&
            (
                empty($tsfe->config['config']['disableImgBorderAttr'])
            )
        ) {
            return $borderAttr;
        }

        return '';
    }

    /***********************************************
     *
     * Database functions, making of queries
     *
     ***********************************************/
    /**
     * Generates a list of Page-uid's from $id. List does not include $id itself
     * (unless the id specified is negative in which case it does!)
     * The only pages WHICH PREVENTS DECENDING in a branch are
     * - deleted pages,
     * - pages in a recycler (doktype = 255) or of the Backend User Section (doktpe = 6) type
     * - pages that has the extendToSubpages set, WHERE start/endtime, hidden
     * and fe_users would hide the records.
     * Apart from that, pages with enable-fields excluding them, will also be
     * removed. HOWEVER $dontCheckEnableFields set will allow
     * enableFields-excluded pages to be included anyway - including
     * extendToSubpages sections!
     * Mount Pages are also descended but notice that these ID numbers are not
     * useful for links unless the correct MPvar is set.
     *
     * @param int $id The id of the start page from which point in the page tree to descend. IF NEGATIVE the id itself is included in the end of the list (only if $begin is 0) AND the output does NOT contain a last comma. Recommended since it will resolve the input ID for mount pages correctly and also check if the start ID actually exists!
     * @param int $depth The number of levels to descend. If you want to descend infinitely, just set this to 100 or so. Should be at least "1" since zero will just make the function return (no descend...)
     * @param int $begin Is an optional integer that determines at which level in the tree to start collecting uid's. Zero means 'start right away', 1 = 'next level and out'
     * @param bool $dontCheckEnableFields See function description
     * @param string $addSelectFields Additional fields to select. Syntax: ",[fieldname],[fieldname],...
     * @param string $moreWhereClauses Additional where clauses. Syntax: " AND [fieldname]=[value] AND ...
     * @param array $prevId_array array of IDs from previous recursions. In order to prevent infinite loops with mount pages.
     * @param int $recursionLevel Internal: Zero for the first recursion, incremented for each recursive call.
     * @return string Returns the list of ids as a comma separated string
     */
    public static function getTreeList($id, $depth, $begin = 0, $dontCheckEnableFields = false, $addSelectFields = '', $moreWhereClauses = '', array $prevId_array = [], $recursionLevel = 0)
    {
        $addCurrentPageId = false;
        $id = (int)$id;
        if ($id < 0) {
            $id = abs($id);
            $addCurrentPageId = true;
        }
        $cObj = static::getContentObjectRenderer();

        $pageRepository = static::getTypoScriptFrontendController()->sys_page;
        if ($dontCheckEnableFields) {
            $backupEnableFields = $pageRepository->where_hid_del;
            $pageRepository->where_hid_del = '';
        }
        $result = $pageRepository->getDescendantPageIdsRecursive($id, (int)$depth, (int)$begin, [], (bool)$dontCheckEnableFields);
        if ($dontCheckEnableFields) {
            $pageRepository->where_hid_del = $backupEnableFields;
        }
        if ($addCurrentPageId) {
            $result = array_merge([$id], $result);
        }
        return implode(',', $result);
    }

    public static function setTypoScriptFrontendController(TypoScriptFrontendController $typoScriptFrontendController): void
    {
        static::$typoScriptFrontendController = $typoScriptFrontendController;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected static function getTypoScriptFrontendController()
    {
        return static::$typoScriptFrontendController ?: $GLOBALS['TSFE'];
    }
}
