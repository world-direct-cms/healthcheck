<?php

namespace WorldDirect\Healthcheck\Probe;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Connection;
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
 * Mailjet Delivery probe checks the email delivery success rate from the mailjet extension.
 * It analyzes emails sent in the last 24 hours and calculates the failure rate.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class MailjetDeliveryProbe extends ProbeBase implements ProbeInterface
{
    /**
     * The email log table from worlddirect/mailjet extension
     */
    const EMAIL_LOG_TABLE = 'tx_mailjet_domain_model_emaillog';

    /**
     * Time window to check (24 hours in seconds)
     */
    const TIME_WINDOW_SECONDS = 86400;

    /**
     * Minimum number of emails to apply percentage thresholds
     */
    const MIN_SAMPLE_SIZE = 10;

    /**
     * Warning threshold: failure rate percentage
     */
    const WARNING_THRESHOLD_PERCENT = 5;

    /**
     * Critical threshold: failure rate percentage
     */
    const CRITICAL_THRESHOLD_PERCENT = 20;

    /**
     * Use the probe only when the extension "worlddirect/mailjet" is installed.
     *
     * @return bool True when "mailjet" is installed.
     */
    public function useProbe(): bool
    {
        return ExtensionManagementUtility::isLoaded('mailjet');
    }

    /**
     * Get the title of the probe.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Mailjet Delivery Success Rate';
    }

    /**
     * Run the mail delivery probe. Check the email delivery success rate
     * from the last 24 hours.
     *
     * @return void
     */
    public function run(): void
    {
        parent::start();

        try {
            $timestampThreshold = time() - self::TIME_WINDOW_SECONDS;
            $totalCount = $this->getTotalEmailCount($timestampThreshold);

            if ($totalCount == 0) {
                $this->result->addSuccessMessage(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.success.noEmails')
                );
                parent::stop();
                return;
            }

            $failedCount = $this->getFailedEmailCount($timestampThreshold);
            $successCount = $totalCount - $failedCount;

            if ($totalCount < self::MIN_SAMPLE_SIZE) {
                $this->handleLowVolumeResult($totalCount, $failedCount, $successCount);
            } else {
                $this->handleNormalVolumeResult($totalCount, $failedCount, $successCount);
            }
        } catch (\Throwable $throwable) {
            $this->result->addErrorMessage(
                $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.error.noDatabase')
            );
        }

        parent::stop();
    }

    /**
     * Get the total count of emails sent in the specified time window.
     *
     * @param int $timestampThreshold The timestamp threshold
     * @return int The total email count
     */
    private function getTotalEmailCount(int $timestampThreshold): int
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::EMAIL_LOG_TABLE);

        return (int)$queryBuilder
            ->count('uid')
            ->from(self::EMAIL_LOG_TABLE)
            ->where(
                $queryBuilder->expr()->gte('sent_at', $queryBuilder->createNamedParameter($timestampThreshold, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * Get the count of failed emails in the specified time window.
     *
     * @param int $timestampThreshold The timestamp threshold
     * @return int The failed email count
     */
    private function getFailedEmailCount(int $timestampThreshold): int
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::EMAIL_LOG_TABLE);

        return (int)$queryBuilder
            ->count('uid')
            ->from(self::EMAIL_LOG_TABLE)
            ->where(
                $queryBuilder->expr()->gte('sent_at', $queryBuilder->createNamedParameter($timestampThreshold, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('delivery_status', $queryBuilder->createNamedParameter('failed', Connection::PARAM_STR)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * Handle the probe result for low email volume (< MIN_SAMPLE_SIZE).
     *
     * @param int $totalCount Total email count
     * @param int $failedCount Failed email count
     * @param int $successCount Successful email count
     * @return void
     */
    private function handleLowVolumeResult(int $totalCount, int $failedCount, int $successCount): void
    {
        if ($failedCount == $totalCount) {
            $this->result->addErrorMessage(
                sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.error.allFailed'),
                    $totalCount
                )
            );
        } else {
            $this->result->addSuccessMessage(
                sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.success.lowVolume'),
                    $successCount,
                    $totalCount
                )
            );
        }
    }

    /**
     * Handle the probe result for normal email volume (>= MIN_SAMPLE_SIZE).
     *
     * @param int $totalCount Total email count
     * @param int $failedCount Failed email count
     * @param int $successCount Successful email count
     * @return void
     */
    private function handleNormalVolumeResult(int $totalCount, int $failedCount, int $successCount): void
    {
        $failureRate = ($failedCount / $totalCount) * 100;

        if ($failureRate >= self::CRITICAL_THRESHOLD_PERCENT) {
            $this->result->addErrorMessage(
                sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.error.critical'),
                    number_format($failureRate, 1),
                    $failedCount,
                    $totalCount,
                    self::CRITICAL_THRESHOLD_PERCENT
                )
            );
        } elseif ($failureRate >= self::WARNING_THRESHOLD_PERCENT) {
            $this->result->addErrorMessage(
                sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.error.warning'),
                    number_format($failureRate, 1),
                    $failedCount,
                    $totalCount,
                    self::WARNING_THRESHOLD_PERCENT
                )
            );
        } else {
            $this->result->addSuccessMessage(
                sprintf(
                    $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.mailjetdelivery.success.normal'),
                    number_format($failureRate, 1),
                    $successCount,
                    $totalCount
                )
            );
        }
    }
}
