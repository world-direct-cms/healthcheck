<?php

defined('TYPO3') || die('Access denied.');

// Set probes
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\DatabaseProbe::class;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\SchedulerProbe::class;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\CacheProbe::class;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\ExternalImportProbe::class;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'][] = \WorldDirect\Healthcheck\Probe\SolrProbe::class;

// Set outputs
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['output'][] = \WorldDirect\Healthcheck\Output\HtmlOutput::class;
$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['output'][] = \WorldDirect\Healthcheck\Output\JsonOutput::class;
