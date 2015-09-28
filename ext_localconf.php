<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!defined ('DIV2007_EXT')) {
	define('DIV2007_EXT', 'div2007');
}

if (!defined ('DIV2007_EXTkey')) { // deprecated
	define('DIV2007_EXTkey', 'div2007');
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

if (!defined ('PATH_BE_div2007')) { // deprecated
	define('PATH_BE_div2007', $bePath);
}

if (!defined ('PATH_BE_DIV2007')) {
	define('PATH_BE_DIV2007', $bePath);
}

if (!defined ('STATIC_INFO_TABLES_EXT')) {
	define('STATIC_INFO_TABLES_EXT', 'static_info_tables');
}

