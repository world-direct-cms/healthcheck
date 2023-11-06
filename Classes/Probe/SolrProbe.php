<?php

namespace WorldDirect\Healthcheck\Probe;

use PDO;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckConfiguration;

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
 * Solr probe checks for solr indexing errors.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class SolrProbe extends ProbeBase implements ProbeInterface
{
    /**
     * The solr index queue table
     */
    const SOLR_INDEX_QUEUE_TABLE = 'tx_solr_indexqueue_item';

    public function useProbe(): bool
    {
        return ExtensionManagementUtility::isLoaded('solr');
    }

    /**
     *
     * @return void The probes result
     */
    public function run(): void
    {
        // Start the probe
        parent::start();

        try {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionPool->getQueryBuilderForTable(self::SOLR_INDEX_QUEUE_TABLE);

            // Get all index queue items, with error set
            $errorItemsCount = $queryBuilder
                ->count('uid')
                ->from(self::SOLR_INDEX_QUEUE_TABLE)
                ->where(
                    $queryBuilder->expr()->neq('errors', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
                )
                ->executeQuery()
                ->fetchOne();

            // Check if the errorItemsCount is larger then the allowed threshhold
            $healthcheckConfiguration = GeneralUtility::makeInstance(HealthcheckConfiguration::class);
            if ($errorItemsCount > $healthcheckConfiguration->getSolrMaxErrorCount()) {
                // Error
                $this->result->addErrorMessage(
                    sprintf(
                        $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solr.error.maxErrors'),
                        intval($errorItemsCount),
                        $healthcheckConfiguration->getSolrMaxErrorCount()
                    )
                );
            } else {
                // Success message
                $this->result->addSuccessMessage(
                    sprintf(
                        $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solr.success'),
                        intval($errorItemsCount),
                        $healthcheckConfiguration->getSolrMaxErrorCount()
                    )
                );
            }
        } catch(\Throwable $throwable) {
            // Handle no connection error
            $this->result->addErrorMessage($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.solr.error.noDatabase'));
        }

        // End the probe
        parent::stop();
    }
}
