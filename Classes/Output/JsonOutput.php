<?php

namespace WorldDirect\Healthcheck\Output;

use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;
use WorldDirect\Healthcheck\Output\OutputInterface;

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

class JsonOutput implements OutputInterface
{
    // TODO: Comment function
    public function getContent(HealthcheckResult $result): string
    {
        // TODO: Build JSON from healthcheck result object

        // Dummy content
        $array = ['test' => 'Hello World'];

        $json = json_encode($array);

        return strval($json);
    }

    // TODO: comment function
    public function getContentType(): string
    {
        return 'application/json';
    }
}
