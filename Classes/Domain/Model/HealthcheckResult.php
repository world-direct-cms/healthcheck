<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use TYPO3\CMS\T3editor\T3editor;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Domain\Model\Status;
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
  * The HealthCheckResult holds an array with all the ProbeResults.
  * It can render a overallStatus in order to know if the Healthcheck
  * returns a success.
  *
  * @author Klaus Hörmann-Engl
  * @package WorldDirect\Healthcheck\Domain\Model
  */
class HealthcheckResult
{
    /**
     * The overall status of the HealthcheckResult.
     *
     * @var Status
     */
    protected $status = Status::SUCCESS;
    /**
     * Array holding all Probes.
     *
     * @var array<ProbeInterface>
     */
    protected $probes;

    /**
     * Returns the current status.
     *
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * Adds a probe to the HealthcheckResult.
     *
     * @param ProbeInterface $probe The probe to be checked.
     *
     * @return void
     */
    public function addProbe(ProbeInterface $probe)
    {
        $this->probes[] = $probe;
    }

    /**
     * Returns the probes array
     *
     * @return array<ProbeInterface>
     */
    public function getProbes(): array
    {
        return $this->probes;
    }

    /**
     * Update the overall status of the Healthcheck.
     * Depending on the Status of each probe and if the
     * probe is paused or not.
     * If the probe is paused, the status of it does not 
     * affect the overall HealthcheckResult status.
     *
     * @return void
     */
    public function updateStatus(): void
    {
        foreach ($this->probes as $probeItem) {
            /** @var ProbeBase $probe */
            $probe = $probeItem;

            // Check if the probe is not paused.
            if (!$probe->isPaused()) {
                if ($probe->getResult()->getStatus() == Status::ERROR) {
                    $this->status = Status::ERROR;
                    break;
                }
            }
        }
    }
}
