<?php
if (!defined ('TYPO3_MODE'))	die ('Access denied.');

if (!defined ('DIV2007_EXTkey')) {
	define('DIV2007_EXTkey', 'div2007');
}

if (!defined ('PATH_BE_div2007')) {
	define('PATH_BE_div2007', t3lib_extMgm::extPath(DIV2007_EXTkey));
}

?>