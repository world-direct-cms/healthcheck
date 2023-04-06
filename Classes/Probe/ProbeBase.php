<?php

namespace WorldDirect\Healthcheck\Probe;

use ReflectionClass;

class ProbeBase
{
    /**
     * starttime
     * 
     * @param string The time the probe started
     */
    public $starttime;

    /**
     * endtime
     * 
     * @return string The time the probe is done
     */
    public $endtime;

    public function start()
    {
        $this->starttime = microtime(true);
    }

    public function stop()
    {
        $this->endtime = microtime(true);
    }

    public function addMetaInformation(array $result) {
        $result['starttime'] = $this->starttime;
        $result['endtime'] = $this->endtime;
        return $result;
    }

    public function getShortClassName($object): string
    {
        return (new ReflectionClass($object))->getShortName();
    }

}