<?php

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}


$emClass = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

if (class_exists($emClass)) {
    call_user_func($emClass . '::addStaticFile', $_EXTKEY, 'Configuration/TypoScript/', 'Div2007 language setup');
}

