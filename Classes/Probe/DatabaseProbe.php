<?php

namespace WorldDirect\Healthcheck\Probe;

use InvalidArgumentException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use WorldDirect\Healthcheck\Utility\BasicUtility;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;

class DatabaseProbe extends ProbeBase implements ProbeInterface
{
    /**
     * The query to check if the connections work
     */
    CONST PROBE_QUERY = "SHOW TABLES";

    /**
     * Run the database probe. Check if all connection can make queries to the
     * database. If not an error is returned.
     * 
     * @return array
     */
    public function run(): array
    {
        // Start the probe
        parent::start();

        // Get the language service for the messages
        $langService = BasicUtility::getLanguageService();

        // Get identifier
        $probeId = strtolower($this->getShortClassName($this));

        // Go through all possible connection an try to execute a simple query
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        foreach ($pool->getConnectionNames() as $name) {
            try {
                $connection = $pool->getConnectionByName($name);

                // Try to execute a query on the database, if this does not work an exception is thrown
                $statement = $connection->executeQuery(self::PROBE_QUERY);
            } catch(\Throwable $throwable) {
                // Handle error
                $result['status'] = 'error';
                $result['message'] = $langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.database.error.notConnected');
            }
        }

        // Write ok message if no error isset
        if (!isset($result['status'])) {
            $result['status'] = 'success';
            $result['message'] = $langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.database.success');
        }

        // Stop the probe
        parent::stop();

        // Add default stuff to probe result
        $result = parent::addMetaInformation($result, $probeId);

        return $result;
    }
}