<?php

namespace WorldDirect\Healthcheck\Probe;

use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
 * This probe checks if the latest ExternalLog Import entry field "status"
 * has only the value "0" (OK). Any other values breaks the healthcheck.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class ExternalImportProbe extends ProbeBase implements ProbeInterface
{
    /**
     * The external import table to check
     */
    const EXTERNAL_IMPORT_TABLE = 'tx_externalimport_domain_model_log';

    /**
     * The name of the external_import extension
     */
    const EXT_NAME = 'external_import';

    /**
     * Use the probe only when the EXT:external_import is installed.
     *
     * @return bool True when "external_immport" is installed.
     */
    public function useProbe(): bool
    {
        return ExtensionManagementUtility::isLoaded(self::EXT_NAME);
    }

    /**
     * Run the probe and check for externalImport logs.
     *
     * @return void
     */
    public function run(): void
    {
        // Start the probe
        parent::start();

        // Check the external import logs
        try {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionPool->getQueryBuilderForTable(self::EXTERNAL_IMPORT_TABLE);

            // Get latest import log entry
            $log = $queryBuilder
                ->select('*')
                ->from(self::EXTERNAL_IMPORT_TABLE)
                ->setMaxResults(1)
                ->orderBy('crdate', 'desc')
                ->executeQuery()
                ->fetchAssociative();

            if (is_array($log)) {
                if (strval($log['status']) != '0') {
                    // Error message
                    $this->result->addErrorMessage(
                        sprintf(
                            $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.externalimport.error'),
                            strval($log['configuration']),
                            strval($log['message'])
                        )
                    );
                } else {
                    // Success message
                    $this->result->addSuccessMessage(
                        sprintf(
                            $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.externalimport.success'),
                            strval($log['configuration']),
                            strval($log['message'])
                        )
                    );
                }
            } else {
                // Handle error
                $this->result->addErrorMessage($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.externalimport.error.database'));
            }
        } catch (\Throwable $throwable) {
            // Handle error
            $this->result->addErrorMessage($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.externalimport.error.database'));
        }

        // Stop the probe
        parent::stop();
    }
}
