<?php
defined('TYPO3_MODE') || die('Access denied.');

if (!defined ('DIV2007_EXT')) {
    define('DIV2007_EXT', 'div2007');
}

$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

if (class_exists($emClass)) {
    call_user_func($emClass . '::addStaticFile', DIV2007_EXT, 'Configuration/TypoScript/', 'Div2007 language setup');
}

