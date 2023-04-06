<?php

defined('TYPO3') || die('Access denied.');

// Default probes
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\DatabaseProbe::class;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\SchedulerProbe::class;