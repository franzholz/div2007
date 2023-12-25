<?php
defined('TYPO3') || defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function ($extensionKey, $table): void {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey^,
        'Configuration/TypoScript/',
        'Div2007 language setup'
    );
}, 'div2007', basename(__FILE__, '.php'));
