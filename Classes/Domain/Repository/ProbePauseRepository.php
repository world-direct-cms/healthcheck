<?php

namespace WorldDirect\Healthcheck\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

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
 * This class is not a repository in the Extbase way. It only is used
 * to encapsulate the functionality of reading the database for a
 * pause entry with a specific class name.
 * 
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Domain\Repository
 */
class ProbePauseRepository
{
    const PROBE_PAUSE_TABLE = "tx_healthcheck_domain_model_probe_pause";

    /**
     * This method queries the database for a probe pause entry with the given 
     * class name. If there is at least one entry found, it returns true. Other-
     * wise false is returned.
     * 
     * @param string $className The name of the class to check for pause
     * 
     * @return bool Whether the probe is paused or not.
     */
    public static function isPaused(string $className): bool
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::PROBE_PAUSE_TABLE);

        $result = $queryBuilder
            ->select('*')
            ->from(self::PROBE_PAUSE_TABLE)
            ->where(
                $queryBuilder->expr()->eq('class_name', $queryBuilder->createNamedParameter($className, \PDO::PARAM_STR))
            )
            ->executeQuery()
            ->fetchAssociative();

        // Check if result is an array. If so there is a entry in the database.
        // Return "true" for paused.
        if (is_array($result)) {
            return true;
        }

        // Any other case: Not paused
        return false;
    }
}