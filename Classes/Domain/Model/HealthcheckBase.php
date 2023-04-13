<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Domain\Model\TypoScriptConfiguration;

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
 * The HealthCheckBase class is a base object for all instances, which need
 * access to the TypoScript settings/configuration. E.g. "plugin.tx_healthcheck.settings".
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Domain\Model
 */
class HealthcheckBase
{
    /**
     * The typoscript configuration
     *
     * @var TypoScriptConfiguration
     */
    protected $tsConfig;

    /**
     * Get the Settings object.
     *
     * @return TypoScriptConfiguration The TypoScript configuration object
     */
    public function getTypoScriptConfiguration(): TypoScriptConfiguration
    {
        return $this->tsConfig;
    }

    /**
     * Constructor for new HealthcheckBase objects
     *
     * @return void
     */
    public function __construct()
    {
        $this->tsConfig = GeneralUtility::makeInstance(TypoScriptConfiguration::class);
    }
}
