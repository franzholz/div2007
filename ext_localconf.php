<?php
defined('TYPO3') || defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function ($extensionKey): void {
    if (!defined ('DIV2007_EXT')) {
        define('DIV2007_EXT', $extensionKey);
    }

    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get($extensionKey);

    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey]) &&
        is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey])
    ) {
        $storeArray = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey];
    } else {
        unset($storeArray);
    }

    if (isset($extensionConfiguration) && is_array($extensionConfiguration)) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = $extensionConfiguration;
        if (isset($storeArray) && is_array($storeArray)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey] = array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extensionKey], $storeArray);
        }
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
    if (!defined('DIV2007_ICONS_SUBPATH')) {
        define('DIV2007_ICONS_SUBPATH', 'Resources/Public/Images/Icons/');
    }
}, 'div2007');

