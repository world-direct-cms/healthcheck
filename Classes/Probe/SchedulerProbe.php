<?php

namespace WorldDirect\Healthcheck\Probe;

use WorldDirect\Healthcheck\Utility\BasicUtility;

class SchedulerProbe extends ProbeBase implements ProbeInterface
{
    public function run(): array
    {
        $result = [];

        // Start the probe
        parent::start();

        // Get the language service for the messages
        $langService = BasicUtility::getLanguageService();

        // TODO: Try to make a database query, throw exception when it does not work
        // Read all scheduled tasks from the database, especially their last status
        // If there is a status of "error" the probe fails --> Return error message
        // If everything is ok, return ok

        // Get identifier
        $probeId = strtolower($this->getShortClassName($this));

        // Stop the probe
        parent::stop();

        // Add meta info to the result
        $result = parent::addMetaInformation($result, $probeId);

        return $result;
    }
}