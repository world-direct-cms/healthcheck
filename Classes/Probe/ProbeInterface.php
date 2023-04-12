<?php

namespace WorldDirect\Healthcheck\Probe;

use WorldDirect\Healthcheck\Domain\Model\ProbeResult;

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
 * This is the interface for probes to implement. You can build your own probes.
 * They only must implement the "run" function and return an array.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Check
 */
interface ProbeInterface
{
    /**
     * Whether to use the probe or not. E.g. depending on if an extension is installed or not.
     *
     * @return bool If the probe should be used or not.
     */
    public function useProbe(): bool;

    /**
     * Execute the probe and return the probeResult.
     *
     * @return void
     */
    public function run(): void;

}
