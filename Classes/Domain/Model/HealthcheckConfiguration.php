<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

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
     * Constant holding the extension key
     */
    CONST EXT_KEY = "healthcheck";

    /**
     * secret is default empty, therefore we enforce the user to set it
     * 
     * @param string
     */
    protected $secret = '';

    /**
     * pathSegment
     * 
     * @param string
     */
    protected $pathSegment = 'healthcheck';

    /**
     * allowedIps is default empty, therefore we enforce the user to set it
     * 
     * @param string
     */
    protected $allowedIps = '';

    /**
     * enableDebug
     * 
     * @param bool
     */
    protected $enableDebug = false;

    /**
     * The probes to check.
     * 
     * @var array
     */
    protected $probes = [];

    /**
     * Construct a new HealthcheckConfiguration using the extension configuration
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
                    $this->enableDebug = $extConf['enableDebug'];
                }
            }

            // Get all configured probes
            $this->probes = (array)$GLOBALS['TYPO3_CONF_VARS']['EXT']['healthcheck']['probe'] ?? [];
        } catch (\Exception $exception) {
            // Do nothing, use the default set property values
        }
    }

    /**
     * Return the secret value
     * @return string The secret
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Return the pathSegment from the configuration
     * 
     * @return string The path segment
     */
    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    /**
     * Returns the allowed IPs
     * 
     * @return string List of allowed IPs
     */
    public function getAllowedIps(): string
    {
        return $this->allowedIps;
    }

    /**
     * Returns whether debugging is enabled or not
     * 
     * @return bool Enabled debug?
     */
    public function isDebugEnabled(): bool
    {
        return $this->enableDebug;
    }

    /**
     * Returns the probes array
     * 
     * @return array<ProbeInterface> Array with probes
     */
    public function getProbes(): array
    {
        return $this->probes;
    }
}