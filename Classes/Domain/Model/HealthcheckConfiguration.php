<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use WorldDirect\Healthcheck\Utility\BasicUtility;

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
 * This class holds the configuration of the healthcheck as configured
 * in the extension configuration. Function uses default values for each
 * property. Only the secret is set to empty in order to force using one.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Domain\Model
 */
class HealthcheckConfiguration
{
    /**
     * Constant holding the extension key.
     */
    const EXT_KEY = 'healthcheck';

    /**
     * The secret is default empty, therefore we enforce the user to set it.
     *
     * @var string
     */
    protected $secret = '';

    /**
     * The pathSegment to check in the Middleware.
     *
     * @var string
     */
    protected $pathSegment = 'healthcheck';

    /**
     * The allowedIps setting is default empty, therefore we enforce the user to set it.
     *
     * @var string
     */
    protected $allowedIps = '';

    /**
     * Whether to enable debug output or not. Default not.
     *
     * @var int
     */
    protected $enableDebug = 0;

    /**
     * Whether to show the EXT:buildinfo informations in the healthcheck.
     *
     * @var int
     */
    protected $enableBuildinfo = 0;

    /**
     * Wheter to enable additional information like current IP address, current datetime, ...
     *
     * @var int
     */
    protected $enableAdditionalInfo = 0;

    /**
     * The probes to check.
     *
     * @var array<string>
     */
    protected $probes = [];

    /**
     * The possible output formats.
     *
     * @var array<string, string>
     */
    protected $outputs = [];

    /**
     * Construct a new HealthcheckConfiguration using the extension configuration.
     *
     * @return void
     */
    public function __construct()
    {
        try {
            /** @var ExtensionConfiguration $extensionConfiguration */
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $extConf = $extensionConfiguration->get(self::EXT_KEY);
            if (is_array($extConf)) {
                if (isset($extConf['secret'])) {
                    $this->secret = $extConf['secret'];
                }
                if (isset($extConf['pathSegment'])) {
                    $this->pathSegment = $extConf['pathSegment'];
                }
                if (isset($extConf['allowedIps'])) {
                    $this->allowedIps = $extConf['allowedIps'];
                }
                if (isset($extConf['enableDebug'])) {
                    $this->enableDebug = intval($extConf['enableDebug']);
                }
                if (isset($extConf['enableBuildinfo'])) {
                    $this->enableBuildinfo = intval($extConf['enableBuildinfo']);
                }
                if (isset($extConf['enableAdditionalInfo'])) {
                    $this->enableAdditionalInfo = intval($extConf['enableAdditionalInfo']);
                }
            }

            // Get all configured probes
            $this->probes = (array)$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'];

            // Get all configured output formats
            $outputs = (array)$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['output'];
            foreach ($outputs as $output) {
                $key = strtolower(str_replace('Output', '', BasicUtility::getShortClassName($output)));
                $this->outputs[$key] = $output;
            }
        } catch (\Exception $exception) {
            // Do nothing, use the default set property values
        }
    }

    /**
     * Return the secret value.
     *
     * @return string The secret
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Return the pathSegment from the configuration.
     *
     * @return string The path segment
     */
    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    /**
     * Returns the allowed IPs setting.
     *
     * @return string List of allowed IPs
     */
    public function getAllowedIps(): string
    {
        return $this->allowedIps;
    }

    /**
     * Returns whether debugging is enabled or not.
     *
     * @return int Enabled debug?
     */
    public function getEnableDebug(): int
    {
        return $this->enableDebug;
    }

    /**
     * Return whether the buildinfo extension information is enabled or not.
     *
     * @return int Buildinfo extension info enabled?
     */
    public function getEnableBuildinfo(): int
    {
        return $this->enableBuildinfo;
    }

    /**
     * Use the additional information?
     *
     * @return int Show additional info?
     */
    public function getEnableAdditionalInfo(): int
    {
        return $this->enableAdditionalInfo;
    }

    /**
     * Returns the probes array.
     *
     * @return array<string> Array with probe classes
     */
    public function getProbes(): array
    {
        return $this->probes;
    }

    /**
     * Returns the configured output formats.
     *
     * @return array<string, string> Array with output formats classes
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }
}
