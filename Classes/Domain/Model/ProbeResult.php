<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Domain\Model\Status;
use WorldDirect\Healthcheck\Domain\Model\ProbeResultMessage;

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
 * The ProbeResult holds a overall status, informations
 * about the duration of the probe and all the individual
 * messages, which again are responsible for the overall
 * status.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Domain\Model
 */
class ProbeResult
{
    /**
     * The overall status of the probe.
     * Default set to success, as long as an error occurs.
     *
     * @var Status
     */
    protected $status = Status::SUCCESS;

    /**
     * The probe starttime.
     *
     * @var float
     */
    protected $starttime;

    /**
     * The probe endtime.
     *
     * @var float
     */
    protected $endtime;

    /**
     * The duration of the probe depeding on the start- and endtime.
     *
     * @var float
     */
    protected $duration;

    /**
     * Array holding ProbeResultMessages.
     *
     * @var array<ProbeResultMessage>
     */
    protected $messages;

    /**
     * Get the ProbeResult status.
     *
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * Set the probe starttime.
     *
     * @param float $starttime The probe starttime.
     *
     * @return void
     */
    public function setStarttime(float $starttime): void
    {
        $this->starttime = $starttime;
    }

    /**
     * Set the probe endtime.
     *
     * @param float $endtime The probe endtime.
     *
     * @return void
     */
    public function setEndtime(float $endtime): void
    {
        $this->endtime = $endtime;
        $this->duration = $this->endtime - $this->starttime;
    }

    /**
     * Gets the duration of the probe.
     *
     * @return float
     */
    public function getDuration(): float
    {
        return round($this->duration, 4);
    }

    /**
     * Returns the messages
     *
     * @return array<ProbeResultMessage> The messages for this probe
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Add a success probe result message.
     *
     * @param string $message The message text
     *
     * @return void
     */
    public function addSuccessMessage(string $message): void
    {
        /** @var ProbeResultMessage $probeResultMessage */
        $probeResultMessage = GeneralUtility::makeInstance(ProbeResultMessage::class);
        $probeResultMessage->setStatus(Status::SUCCESS);
        $probeResultMessage->setMessage($message);

        $this->messages[] = $probeResultMessage;
    }

    /**
     * Add a error probe result message.
     *
     * @param string $message The message text
     *
     * @return void
     */
    public function addErrorMessage(string $message): void
    {
        // Create a new ProbeResultMessage to set the error and message to
        /** @var ProbeResultMessage $probeResultMessage */
        $probeResultMessage = GeneralUtility::makeInstance(ProbeResultMessage::class);
        $probeResultMessage->setStatus(Status::ERROR);
        $probeResultMessage->setMessage($message);

        // Add error message to messages
        $this->messages[] = $probeResultMessage;

        // Set the ProbeResult status
        // As soon as a single error message gets set, the overall probe status is set to error.
        $this->status = Status::ERROR;
    }
}
