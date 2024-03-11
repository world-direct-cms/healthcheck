<?php

namespace WorldDirect\Healthcheck\Probe;

use PDO;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use Doctrine\DBAL\ForwardCompatibility\Result;
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
 * Scheduler probe checks for failed ScheduledTasks.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class SchedulerProbe extends ProbeBase implements ProbeInterface
{
    /**
     * The scheduler table
     */
    const SCHEDULER_TABLE = 'tx_scheduler_task';

    /**
     * Use the probe only when the extension "scheduler" is installed.
     *
     * @return bool True when "scheduler" is installed.
     */
    public function useProbe(): bool
    {
        return ExtensionManagementUtility::isLoaded('scheduler');
    }

    /**
     * Run the scheduler probe. Check if there are any scheduled tasks
     * which are in a error state.
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
            $queryBuilder = $connectionPool->getQueryBuilderForTable(self::SCHEDULER_TABLE);

            // Get all tasks
            $tasks = $queryBuilder
                ->select('*')
                ->from(self::SCHEDULER_TABLE)
                ->where(
                    $queryBuilder->expr()->eq('disable', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
                )
                ->execute();

            if ($tasks instanceof Result) {
                $tasks = $tasks->fetchAllAssociative();
            } else {
                $tasks = [];
            }

            /** @var HealthcheckConfiguration */
            $healthcheckConfiguration = GeneralUtility::makeInstance(HealthcheckConfiguration::class);

            // Step through all tasks and check if they have "lastexecution_failure" set
            // also check if the next execution is 10 minutes late
            foreach ($tasks as $task) {
                if (isset($task['lastexecution_failure']) && $task['lastexecution_failure'] != '') {
                    // Error message
                    $this->result->addErrorMessage(
                        sprintf(
                            $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.scheduler.error.executionFailure'),
                            strval($task['uid']),
                            strval($task['description'])
                        )
                    );
                } else if (isset($task['nextexecution']) && is_int($task['nextexecution']) && (time() - $task['nextexecution']) > $healthcheckConfiguration->getSchedulerMaxMinutesLate() * 60) {
                    // Error message
                    $this->result->addErrorMessage(
                        sprintf(
                            $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.scheduler.error.executionLate'),
                            strval($task['uid']),
                            strval($healthcheckConfiguration->getSchedulerMaxMinutesLate()),
                            strval($task['description'])
                        )
                    );
                } else {
                    // Success message
                    $this->result->addSuccessMessage(
                        sprintf(
                            $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.scheduler.success'),
                            strval($task['uid']),
                            strval($task['description'])
                        )
                    );
                }
            }

            // Add success message if there are no tasks configured at all
            if (sizeof($tasks) == 0) {
                $this->result->addSuccessMessage(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.scheduler.notasks')
                );
            }
        } catch(\Throwable $throwable) {
            // Handle no connection error
            $this->result->addErrorMessage($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.scheduler.error.noDatabase'));
            ;
        }

        // Stop the probe
        parent::stop();
    }
}
