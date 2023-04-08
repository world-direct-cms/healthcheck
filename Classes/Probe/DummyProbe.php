<?php

namespace WorldDirect\Healthcheck\Probe;

use WorldDirect\Healthcheck\Domain\Model\ProbeResult;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;

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
 * This class is a dummy probe for you to get started easily.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class DummyProbe extends ProbeBase implements ProbeInterface
{
    /**
     * Probe run function
     *
     * @return void
     */
    public function run(): void
    {
        // TODO: Implement dummy probe
    }
}
