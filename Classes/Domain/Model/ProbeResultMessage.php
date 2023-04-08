<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use WorldDirect\Healthcheck\Domain\Model\Status;

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
 * A single probe result message. Holds a status and a describing message.
 *
 * @author klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Domain\Model
 */
class ProbeResultMessage
{
    /**
     * The status of the error message.
     *
     * @var Status
     */
    protected $status;

    /**
     * The message holding additional information about the message.
     *
     * @var string
     */
    protected $message;

    /**
     * Get the status of the error message.
     *
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * Set the status of the error message.
     *
     * @param Status $status The status of the error message.
     *
     * @return void
     */
    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the message holding additional information about the message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set the message holding additional information about the message.
     *
     * @param string $message The message holding additional information about the message.
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
