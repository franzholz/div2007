<?php
defined('TYPO3_MODE') || die('Access denied.');

if (!defined ('DIV2007_EXT')) {
    define('DIV2007_EXT', 'div2007');
}

call_user_func(function () {
    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get(DIV2007_EXT);

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT])
    ) {
        $storeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT];
    } else {
        unset($storeArray);
    }

    if (isset($extensionConfiguration) && is_array($extensionConfiguration)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT] = $extensionConfiguration;
        if (isset($storeArray) && is_array($storeArray)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT] = array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT], $storeArray);
        }
    }

    $emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
    $extensionPath = call_user_func($emClass . '::extPath', DIV2007_EXT);

    if (!defined ('PATH_BE_DIV2007')) {
        define('PATH_BE_DIV2007', $extensionPath);
    }

    if (!defined ('PATH_FE_DIV2007_REL')) {
        $relativeExtensionPath = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(
            $extensionPath
        );

        define('PATH_FE_DIV2007_REL', $relativeExtensionPath);
    }

    if (!defined ('STATIC_INFO_TABLES_EXT')) {
        define('STATIC_INFO_TABLES_EXT', 'static_info_tables');
    }

    // constants for the TCA fields

    if (!defined('DIV2007_LANGUAGE_LGL')) {
        define('DIV2007_LANGUAGE_LGL', 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.');
    }
    if (!defined('DIV2007_LANGUAGE_PATH')) {
        define('DIV2007_LANGUAGE_PATH', 'LLL:EXT:core/Resources/Private/Language/');
    }
    if (!defined('DIV2007_LANGUAGE_SUBPATH')) {
        define('DIV2007_LANGUAGE_SUBPATH', '/Resources/Private/Language/');
    }
    if (!defined('DIV2007_LANGUAGE_SUBPATH')) {
        define('DIV2007_ICONS_SUBPATH', 'Resources/Public/Images/Icons/');
    }
});

