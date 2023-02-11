<?php
defined('TYPO3') || defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'div2007',
        'Configuration/TypoScript/',
        'Div2007 language setup'
    );
});

