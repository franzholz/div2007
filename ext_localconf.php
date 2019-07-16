<?php
defined('TYPO3_MODE') || die('Access denied.');

if (!defined ('DIV2007_EXT')) {
    define('DIV2007_EXT', 'div2007');
}

call_user_func(function () {
    if (
        defined('TYPO3_version') &&
        version_compare(TYPO3_version, '9.0.0', '>=')
    ) {
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get(DIV2007_EXT);
    } else if (isset($extensionConfiguration)) {
        $extensionConfiguration = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:
    }

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

    if (version_compare(TYPO3_version, '8.0.0', '>=')) {
        // 'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',

        define('DIV2007_LANGUAGE_LGL', 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.');
    } else {
        // 'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
        define('DIV2007_LANGUAGE_LGL', 'LLL:EXT:lang/locallang_general.php:LGL.');
    }

    define('DIV2007_LANGUAGE_SUBPATH', '/Resources/Private/Language/');
    define('DIV2007_ICONS_SUBPATH', 'Resources/Public/Images/Icons/');
});

