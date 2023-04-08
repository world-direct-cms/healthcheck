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

interface OutputInterface
{
    /**
     * Function renders the given results in the appropriate format.
     *
     * @param HealthcheckResult The healthcheck result to output.
     *
     * @return string The formatted string output
     */
    public function render(HealthcheckResult $result): string;
}
