<?php

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        //---------------------------------------------------------------------
        // Load static TS template
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('healthcheck', 'Configuration/TypoScript', 'healthcheck');

        //---------------------------------------------------------------------
        // Allow Probe Pause data on standard pages
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_healthcheck_domain_model_probe_pause');
    }
);
