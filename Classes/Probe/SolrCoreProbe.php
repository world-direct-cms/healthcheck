<?php

namespace WorldDirect\Healthcheck\Probe;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;

/*
 * This file is part of the TYPO3 extension "worlddirect/healthcheck".
 *
 * (c) Klaus Hörmann-Engl <klaus.hoermann-engl@world-direct.at>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/**
 * SolrCoreProbe checks if configured Solr cores are reachable.
 * 
 * This probe reads site configurations and attempts to connect to each
 * configured Solr core to verify connectivity.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class SolrCoreProbe extends ProbeBase implements ProbeInterface
{
    /**
     * Check if the solr extension is loaded.
     *
     * @return bool True if solr extension is loaded
     */
    public function useProbe(): bool
    {
        return ExtensionManagementUtility::isLoaded('solr');
    }

    /**
     * Get the title of the probe.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Solr Core Connectivity';
    }

    /**
     * Run the Solr core connectivity probe.
     * Iterates through all sites and their Solr configurations to test connections.
     *
     * @return void
     */
    public function run(): void
    {
        // Start the probe
        parent::start();

        try {
            // Get all sites
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $sites = $siteFinder->getAllSites();

            if (empty($sites)) {
                $this->result->addErrorMessage(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.error.noSites')
                );
                parent::stop();
                return;
            }

            $coresChecked = 0;
            $coresFailed = 0;

            // Iterate through each site
            foreach ($sites as $site) {
                $siteIdentifier = $site->getIdentifier();

                // Extract Solr connection parameters
                $solrConfig = $this->extractSolrConfig($site->getConfiguration());

                if (empty($solrConfig)) {
                    continue;
                }

                foreach ($solrConfig as $language => $config) {
                    $coresChecked++;

                    // Try to connect to the Solr core
                    $connectionResult = $this->testSolrCoreConnection($config, $siteIdentifier, $language);

                    if ($connectionResult['success']) {
                        $this->result->addSuccessMessage($connectionResult['message']);
                    } else {
                        $coresFailed++;
                        $this->result->addErrorMessage($connectionResult['message']);
                    }
                }
            }

            // Add summary message if no cores were found
            if ($coresChecked === 0) {
                $this->result->addErrorMessage(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.error.noCoresConfigured')
                );
            }
        } catch (\Throwable $throwable) {
            // Handle any unexpected errors
            $this->result->addErrorMessage(
                sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.error.exception'),
                    $throwable->getMessage()
                )
            );
        }

        // End the probe
        parent::stop();
    }

    /**
     * Extract Solr configuration from site configuration.
     * 
     * TODO: Not sure if this works the same in all typo3 version 12, 13 and 14. Did not test, as solr is not ready for typo3 v14.
     *
     * @param array $siteConfig The site configuration array
     * @return array Array of Solr configurations per language
     */
    protected function extractSolrConfig(array $siteConfig): array
    {
        $solrConfigs = [];

        // Get global Solr configuration (without language suffix)
        $globalHost = $siteConfig['solr_host_read'] ?? $siteConfig['solr_host'] ?? null;
        $globalPort = $siteConfig['solr_port_read'] ?? $siteConfig['solr_port'] ?? null;
        $globalPath = $siteConfig['solr_path_read'] ?? $siteConfig['solr_path'] ?? null;
        $globalCore = $siteConfig['solr_core_read'] ?? $siteConfig['solr_core'] ?? null;
        $globalScheme = $siteConfig['solr_scheme_read'] ?? $siteConfig['solr_scheme'] ?? null;

        // Check for language-specific configurations within languages array
        if (isset($siteConfig['languages']) && is_array($siteConfig['languages'])) {
            foreach ($siteConfig['languages'] as $languageConfig) {
                if (!is_array($languageConfig) || !isset($languageConfig['languageId'])) {
                    continue;
                }

                $langId = $languageConfig['languageId'];

                // Get core from language config, connection settings from global config
                $core = $languageConfig['solr_core_read'] ?? $languageConfig['solr_core'] ?? $globalCore ?? '';
                $host = $globalHost ?? 'localhost';
                $port = $globalPort ?? 8983;
                $path = $globalPath ?? '/';
                $scheme = $globalScheme ?? 'http';

                if (!empty($core)) {
                    $solrConfigs[$langId] = [
                        'host' => $host,
                        'port' => (int)$port,
                        'path' => $path,
                        'core' => $core,
                        'scheme' => $scheme,
                    ];
                }
            }
        }

        return $solrConfigs;
    }

    /**
     * Test connection to a Solr core.
     *
     * @param array $config Solr connection configuration
     * @param string $siteIdentifier Site identifier
     * @param int|string $language Language ID
     * @return array Result array with 'success' and 'message' keys
     */
    protected function testSolrCoreConnection(array $config, string $siteIdentifier, $language): array
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 8983;
        $path = rtrim($config['path'] ?? '/solr/', '/');
        $core = $config['core'] ?? '';
        $scheme = $config['scheme'] ?? 'http';

        // Build the Solr ping URL
        $solrUrl = sprintf(
            '%s://%s:%d%s/%s/admin/ping',
            $scheme,
            $host,
            $port,
            $path . '/solr',
            $core
        );

        try {
            // Use TYPO3's HTTP request library or fall back to file_get_contents
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true,
                ],
            ]);

            $response = file_get_contents($solrUrl . '?wt=json', false, $context);

            if ($response === false) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.error.connectionFailed'),
                        $siteIdentifier,
                        $language,
                        $core,
                        $host,
                        $port
                    ),
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] === 'OK') {
                return [
                    'success' => true,
                    'message' => sprintf(
                        $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.success'),
                        $siteIdentifier,
                        $language,
                        $core,
                        $host,
                        $port
                    ),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => sprintf(
                        $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.error.invalidResponse'),
                        $siteIdentifier,
                        $language,
                        $core
                    ),
                ];
            }
        } catch (\Throwable $throwable) {
            return [
                'success' => false,
                'message' => sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solrcore.error.exception'),
                    $siteIdentifier,
                    $language,
                    $throwable->getMessage()
                ),
            ];
        }
    }
}
