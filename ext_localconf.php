<?php
if (!defined ('TYPO3_MODE'))	die ('Access denied.');

if (!defined ('DIV2007_EXT')) {
	define('DIV2007_EXT', 'div2007');
}


if (!defined ('DIV2007_EXTkey')) { // deprecated
	define('DIV2007_EXTkey', 'div2007');
}

if (!defined ('PATH_BE_div2007')) {
	define('PATH_BE_div2007', t3lib_extMgm::extPath(DIV2007_EXT));
}


if (!defined ('STATIC_INFO_TABLES_EXT')) {
	define('STATIC_INFO_TABLES_EXT', 'static_info_tables');
}



?>