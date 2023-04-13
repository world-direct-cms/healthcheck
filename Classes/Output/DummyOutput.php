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
 * This class is a dummy output to get you started quickly.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Output
 */
class DummyOutput extends OutputBase implements OutputInterface
{
    /**
     * Returns the correct content type for this output, which
     * is text/html.
     *
     * @return string The content type of the returned content
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * Function returns the content to be rendered.
     *
     * @param HealthcheckResult $result The total HealthcheckResult
     *
     * @return string The content of this output
     */
    public function getContent(HealthcheckResult $result): string
    {
        // Do whatever you want with the information in the
        // Healthcheckresult object $result and return the
        // desired output.

        return '[]';
    }
}
