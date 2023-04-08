<?php

namespace WorldDirect\Healthcheck\Probe;

use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use WorldDirect\Healthcheck\Utility\BasicUtility;
use WorldDirect\Healthcheck\Domain\Model\ProbeResult;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;
use WorldDirect\Healthcheck\Domain\Model\Status;

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
 * Database probe checks if the TYPO3 application can connect to the database.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class DatabaseProbe extends ProbeBase implements ProbeInterface
{
    /**
     * The query to check if the connections work
     */
    const PROBE_QUERY = 'SHOW TABLES';

    /**
     * Run the database probe. Check if all connection can make queries to the
     * database. If not an error is returned.
     *
     * @return void
     */
    public function run(): void
    {
        // Start the probe
        parent::start();

        // Go through all possible connection an try to execute a simple query
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        foreach ($pool->getConnectionNames() as $name) {
            try {
                $connection = $pool->getConnectionByName($name);

                // Try to execute a query on the database, if this does not work an exception is thrown
                $statement = $connection->executeQuery(self::PROBE_QUERY);

                // TODO: Add success message
            } catch(\Throwable $throwable) {
                // Handle error
                $this->result->addErrorMessage($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.database.error.notConnected'));
            }
            // TODO: Write success message with the connection name, which was sucessfull
        }

        // Write ok message if the probe result is still SUCCESS.
        // TODO: Remove this and rebuild the language file
        if ($this->result->getStatus() == Status::SUCCESS) {
            $this->result->addSuccessMessage($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.database.success'));
        }

        // Stop the probe
        parent::stop();
    }
}
