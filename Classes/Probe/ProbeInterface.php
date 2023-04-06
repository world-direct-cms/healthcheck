<?php

namespace WorldDirect\Healthcheck\Probe;

/**
 * This is the interface for probes to implement. You can build your own probes.
 * 
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Check
 */
interface ProbeInterface
{
    /**
     * Execute the probe and return the results.
     * 
     * @return void
     */
    public function run(): array;
}