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
 * language specific functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
use TYPO3\CMS\Core\SingletonInterface;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Base\TranslationBase;


class Language implements SingletonInterface
{
    /**
     * translation for a code value which corresponds to a TCA value array
     * E.g. The salutation field in sr_feuser_register stores an integer. However the text string
     * is needed for the views. This function takes the code and returns the text string in the
     * appropriate language.
     *
     * @param   object      language object of type \JambageCom\Div2007\Base\TranslationBase
     * @param   string      value
     * @param   array       value array in the format used in TCA
     *
     * @return  string      text for the value if found
     */
    public static function decodeArrayValue(
        TranslationBase $languageObj,
        $value,
        $valueArray
    ) {
        $result = false;

        foreach ($valueArray as $key => $parts) {
            if (is_array($parts)) {
                $selectKey = $parts['1'];
                $selectValue = $parts['0'];
            } else {
                $selectKey = $key;
                $selectValue = $parts;
            }

            if ($value == $selectKey) {
                $tmp = $languageObj->splitLabel($selectValue);
                $text = $languageObj->getLabel($tmp);
                $result = $text;
                break;
            }
        }

        return $result;
    }

    /**
     * Find a site language by id. This will return the first occurrence of a
     * language found. The same language id might be used for other languages
     * in other site configurations.
     *
     * @param int $languageId
     * @return SiteLanguage|null
     */
    public function getLanguageById(int $languageId): ?SiteLanguage
    {
        foreach (GeneralUtility::makeInstance(SiteFinder::class)->getAllSites() as $site) {
            foreach ($site->getAllLanguages() as $language) {
                if ($languageId === $language->getLanguageId()) {
                    return $language;
                }
            }
        }

        return null;
    }
}
