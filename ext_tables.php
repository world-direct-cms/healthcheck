<?php

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        //---------------------------------------------------------------------
        // Load static TS template
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('healthcheck', 'Configuration/TypoScript', 'healthcheck');
    }
);
