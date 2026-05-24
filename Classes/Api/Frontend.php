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
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

use JambageCom\Div2007\Api\FrontendApi;

class Frontend implements SingletonInterface
{
    /**
     * An "fe_user" object instance. Required for session access.
     *
     * @var FrontendUserAuthentication
     */
    protected $frontendUser;

    /**
     * Constructor for session handling class.
     */
    public function __construct()
    {
        $request = FrontendApi::getGlobalRequestObject();
        $this->frontendUser = $request->getAttribute('frontend.user');
    }

    /**
     * Registration of records/"shopping basket" in session data
     * This will take the input array, $recs, and merge into the current "recs" array found in the session data.
     * If a change in the recs storage happens (which it probably does) the function setKey() is called in order to store the array again.
     *
     * @param array $recs The data array to merge into/override the current recs values. The $recs array is constructed as [table]][uid] = scalar-value (eg. string/integer).
     * @param int $maxSizeOfSessionData The maximum size of stored session data. If zero, no limit is applied and even confirmation of cookie session is discarded.
     * @param bool $checkCookie the cookie check for write allowance is enabled by default
     */
    public function record_registration($recs, $maxSizeOfSessionData = 0, $checkCookie = true): void
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
            if (!empty($recs['clear_all'])) {
                $this->frontendUser->setKey('ses', 'recs', []);
            }
            $change = 0;
            $recs_array = $this->frontendUser->getKey('ses', 'recs');
            foreach ($recs as $table => $data) {
                if (is_array($data)) {
                    foreach ($data as $rec_id => $value) {
                        if (
                            !isset($recs_array[$table][$rec_id]) ||
                            $value != $recs_array[$table][$rec_id]
                        ) {
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
}
