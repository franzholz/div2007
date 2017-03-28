<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!defined ('DIV2007_EXT')) {
	define('DIV2007_EXT', 'div2007');
}

$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

if (
	class_exists($callingClassName) &&
	method_exists($callingClassName, 'extPath')
) {
	$bePath = call_user_func($callingClassName . '::extPath', DIV2007_EXT);
} else {
	$bePath = t3lib_extMgm::extPath(DIV2007_EXT);
}

if (!defined ('PATH_BE_DIV2007')) {
	define('PATH_BE_DIV2007', $bePath);
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

