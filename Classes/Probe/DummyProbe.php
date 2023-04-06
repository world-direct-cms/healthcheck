<?php

namespace WorldDirect\Healthcheck\Probe;

use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;

class DummyProbe extends ProbeBase implements ProbeInterface
{
    public function run(): array
    {
        return $result;
    }
}