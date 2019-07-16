<?php

namespace JambageCom\Div2007\Api;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger (franz@ttproducts.de)
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
 * Frontend functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;


class Frontend {

    /**
    * An "fe_user" object instance. Required for session access.
    *
    * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
    */
    protected $frontendUser = null;

    /**
    * Constructor for session handling class
    *
    * @return void
    */
    public function __construct () {
        if (
            isset($GLOBALS['TSFE']) &&
            is_object($GLOBALS['TSFE'])
        ) {
            $this->frontendUser = $GLOBALS['TSFE']->fe_user;
        }
    }

    /**
     * Registration of records/"shopping basket" in session data
     * This will take the input array, $recs, and merge into the current "recs" array found in the session data.
     * If a change in the recs storage happens (which it probably does) the function setKey() is called in order to store the array again.
     *
     * @param array $recs The data array to merge into/override the current recs values. The $recs array is constructed as [table]][uid] = scalar-value (eg. string/integer).
     * @param int $maxSizeOfSessionData The maximum size of stored session data. If zero, no limit is applied and even confirmation of cookie session is discarded.
     * @deprecated since TYPO3 v8, will be removed in TYPO3 v9. Automatically feeding a "basket" by magic GET/POST keyword "recs" has been deprecated.
     */
    public function record_registration ($recs, $maxSizeOfSessionData = 0)
    {
        // Storing value ONLY if there is a confirmed cookie set,
        // otherwise a shellscript could easily be spamming the fe_sessions table
        // with bogus content and thus bloat the database
        if (!$maxSizeOfSessionData || $this->frontendUser->isCookieSet()) {
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
}

