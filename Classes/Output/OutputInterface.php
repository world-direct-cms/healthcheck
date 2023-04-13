<?php

namespace WorldDirect\Healthcheck\Output;

use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;

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
 * The OutputInterface must be implemented by all Output classes, in
 * order to get the returned content as well as the returned content
 * type.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Output
 */
interface OutputInterface
{
    /**
     * Function returns the content for the given result.
     *
     * @param HealthcheckResult $result The healthcheck result to build the output from.
     *
     * @return string The string output
     */
    public function getContent(HealthcheckResult $result): string;

    /**
     * Return the contentType to use for a response
     *
     * @return string The content type to use. E.g. "application/json".
     */
    public function getContentType(): string;
}
