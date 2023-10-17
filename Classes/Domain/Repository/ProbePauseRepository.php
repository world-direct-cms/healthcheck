<?php

namespace WorldDirect\Healthcheck\Domain\Repository;

use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Result;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use UnexpectedValueException;

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
    /**
     * Constant for the table name for the ProbePause entries.
     */
    const PROBE_PAUSE_TABLE = 'tx_healthcheck_domain_model_probe_pause';

    /**
     * Connection Pool
     *
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * A response factory to create response.
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * Constructor for new ProbePause Repository objects.
     *
     * @param ConnectionPool $connectionPool The connectionPool to get the QueryBuilder
     * @param ResponseFactory $responseFactory The factory to create responses
     *
     * @return void
     */
    public function __construct(ConnectionPool $connectionPool, ResponseFactory $responseFactory)
    {
        $this->connectionPool = $connectionPool;
        $this->responseFactory = $responseFactory;
    }

    /**
     * This method queries the database for a probe pause entry with the given
     * class name. If there is at least one entry found, it returns true. Other-
     * wise false is returned.
     *
     * @param string $className The name of the class to check for pause
     *
     * @return bool Whether the probe is paused or not.
     */
    public function isPaused(string $className): bool
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::PROBE_PAUSE_TABLE);

        $result = $queryBuilder
            ->select('*')
            ->from(self::PROBE_PAUSE_TABLE)
            ->where(
                $queryBuilder->expr()->eq('class_name', $queryBuilder->createNamedParameter($className, \PDO::PARAM_STR))
            )
            ->execute();

        if ($result  instanceof Result) {
            $result = $result->fetchAssociative();
        } else {
            return false;
        }

        // Check if result is an array. If so there is a entry in the database.
        // Return "true" for paused.
        if (is_array($result)) {
            return true;
        }

        // Any other case: Not paused
        return false;
    }

    /**
     * Function pauses the probe given in the className. It does that
     * by creating a ProbePause entriy with the fqcn of the probe to pause.
     *
     * @param string $className The fqcn for the probe to pause.
     *
     * @return ResponseInterface JSON response if the call was succesfull or not.
     */
    public function pauseProbe(string $className): ResponseInterface
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::PROBE_PAUSE_TABLE);

        // Create a new row with the given className, to pause the probe
        $insertedRows = $queryBuilder
            ->insert(self::PROBE_PAUSE_TABLE)
            ->values([
                'pid' => '0',
                'class_name' => $className
            ])
            ->execute();

        // If there was 1 inserted element, return "success", if not return "error".
        if ($insertedRows == 1) {
            return $this->createJsonResponse(['success']);
        }
        return $this->createJsonResponse(['error']);
    }

    /**
     * Function removes the pause for the given probe with the fqcn.
     * It does this by removing all ProbePause entries with the given className.
     *
     * @param string $className The fqcn for the probe to play again.
     *
     * @return ResponseInterface JSON response whether the play was successfull or not.
     */
    public function playProbe(string $className): ResponseInterface
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::PROBE_PAUSE_TABLE);

        // Delete all entries
        $deletedElements = $queryBuilder
            ->delete(self::PROBE_PAUSE_TABLE)
            ->where(
                $queryBuilder->expr()->eq('class_name', $queryBuilder->createNamedParameter($className, \PDO::PARAM_STR))
            )
            ->execute();

        // If there was at least 1 deleted element, return "success", if not return "error".
        if ($deletedElements > 0) {
            return $this->createJsonResponse(['success']);
        }
        return $this->createJsonResponse(['error']);
    }

    /**
     * return a JSON Response object containing $data, which is encoded
     * in json format.
     *
     * @param array<mixed> $data The data array
     *
     * @return ResponseInterface The returned JSON response
     */
    private function createJsonResponse(array $data): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json');
        $json = json_encode($data);
        if (is_string($json)) {
            $response->getBody()->write($json);
        } else {
            $response->getBody()->write("{'error' => 'json_encode threw an error'}");
        }
        return $response;
    }
}
