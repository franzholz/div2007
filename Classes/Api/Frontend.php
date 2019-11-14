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
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;



class Frontend implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController = null;

    /**
    * An "fe_user" object instance. Required for session access.
    *
    * @var FrontendUserAuthentication
    */
    protected $frontendUser = null;

    /**
    * Constructor for session handling class
    *
    * @return void
    */
    public function __construct ($typoScriptFrontendController = '') {
        if (!empty($typoScriptFrontendController)) {
            $this->typoScriptFrontendController = $typoScriptFrontendController;
        } else {
            $this->typoScriptFrontendController = $GLOBALS['TSFE'];
        }

        if (
            !empty($this->typoScriptFrontendController) &&
            $this->typoScriptFrontendController instanceof TypoScriptFrontendController
        ) {
            $this->frontendUser = $this->typoScriptFrontendController->fe_user;
        }
    }

    /**
     * Registration of records/"shopping basket" in session data
     * This will take the input array, $recs, and merge into the current "recs" array found in the session data.
     * If a change in the recs storage happens (which it probably does) the function setKey() is called in order to store the array again.
     *
     * @param array $recs The data array to merge into/override the current recs values. The $recs array is constructed as [table]][uid] = scalar-value (eg. string/integer).
     * @param int $maxSizeOfSessionData The maximum size of stored session data. If zero, no limit is applied and even confirmation of cookie session is discarded.
     * @param boolean $checkCookie The cookie check for write allowance is enabled by default.
     */
    public function record_registration ($recs, $maxSizeOfSessionData = 0, $checkCookie = true)
    {
        // Storing value ONLY if there is a confirmed cookie set,
        // otherwise a shellscript could easily be spamming the fe_sessions table
        // with bogus content and thus bloat the database
        if (
            is_array($recs) &&
            !empty($recs) &&
            (
                !$maxSizeOfSessionData ||
                !$checkCookie ||
                $this->frontendUser->isCookieSet()
            )
        ) {
            if ($recs['clear_all']) {
                $this->frontendUser->setKey('ses', 'recs', []);
            }
            $change = 0;
            $recs_array = $this->frontendUser->getKey('ses', 'recs');
            foreach ($recs as $table => $data) {
                if (is_array($data)) {
                    foreach ($data as $rec_id => $value) {
                        if ($value != $recs_array[$table][$rec_id]) {
                            $recs_array[$table][$rec_id] = $value;
                            $change = 1;
                        }
                    }
                }
            }

            if (
                $change &&
                (
                    !$maxSizeOfSessionData ||
                    strlen(serialize($recs_array)) < $maxSizeOfSessionData
                )
            ) {
                $this->frontendUser->setKey('ses', 'recs', $recs_array);
            }
        }
    }
    

    /**
     * @return TypoScriptFrontendController
     */
    public function getTypoScriptFrontendController ()
    {
        if ($this->typoScriptFrontendController instanceof TypoScriptFrontendController) {
            return $this->typoScriptFrontendController;
        }
        
        // This usually happens when typolink is created by the TYPO3 Backend, where no TSFE object
        // is there. This functionality is currently completely internal, as these links cannot be
        // created properly from the Backend.
        // However, this is added to avoid any exceptions when trying to create a link
        $this->typoScriptFrontendController = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            null,
            GeneralUtility::_GP('id'),
            (int)GeneralUtility::_GP('type')
        );
        $this->typoScriptFrontendController->sys_page = GeneralUtility::makeInstance(PageRepository::class);
        $this->typoScriptFrontendController->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        return $this->typoScriptFrontendController;
    }


    public function getLanguageId () {
        $result = false;

        if (
            version_compare(TYPO3_version, '9.4.0', '>=')
        ) {
            $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
            // (previously known as TSFE->sys_language_uid)
            $result = $languageAspect->getId();
        } else {
            $tsfe = $this->getTypoScriptFrontendController();
            $result = $tsfe->config['config']['sys_language_uid'];
        }

        return $result;
    }
}

