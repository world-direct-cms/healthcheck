<?php

namespace WorldDirect\Healthcheck\Probe;

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
     * Should this probe run always, or only if certain conditions are met.
     *
     * @return bool Whether to run the probe or not.
     */
    public function useProbe(): bool
    {
        // TODO: Probe: Check if this probe is to be run
        return true;
    }

    /**
     * Probe run function
     *
     * @return void
     */
    public function run(): void
    {
        // Start probe
        parent::start();

        // TODO: Probe: Implement dummy probe

        // End probe
        parent::stop();
    }
}
