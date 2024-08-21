<?php

namespace WorldDirect\Healthcheck\Domain\Model;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * This class is used to get the TypoScript settings/configuration.
 * E.g. "plugin.tx_healthcheck.settings".
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Domain\Model
 */
class TypoScriptConfiguration
{
    /**
     * The output html background image
     *
     * @var string
     */
    protected $outputHtmlBackgroundimage;

    /**
     * The output html logo image
     *
     * @var string
     */
    protected $outputHtmlLogoimage;

    /**
     * The configuration manager.
     *
     * @var ConfigurationManager
     */
    protected $configManager;

    /**
     * Constructor for hew SettingsService objects.
     *
     * @param ConfigurationManager $configManager The configuration manager
     *
     * @return void
     */
    public function __construct(ConfigurationManager $configManager)
    {
        $this->configManager = $configManager;
        $bgImage = $this->get('plugin.tx_healthcheck.settings.output.html.backgroundimage');
        if (is_string($bgImage)) {
            $this->outputHtmlBackgroundimage = $bgImage;
        }
        $logoImage = $this->get('plugin.tx_healthcheck.settings.output.html.logoimage');
        if (is_string($logoImage)) {
            $this->outputHtmlLogoimage = $logoImage;
        }
    }

    /**
     * Return the output html backgroundimage
     *
     * @return string The background image path
     */
    public function getOutputHtmlBackgroundimage(): string
    {
        return $this->outputHtmlBackgroundimage;
    }

    /**
     * Return the output html logoimage
     *
     * @return string The logo image path
     */
    public function getOutputHtmlLogoimage(): string
    {
        return $this->outputHtmlLogoimage;
    }

    /**
    * Function returns the value of the requests typoscript settings path.
    * You can traverse into the TypoScript full settings by giving a settingsPath.
    * E.g.: plugin.tx_healthcheck.settings.output.html.backgroundImage
    *
    * @param string $settingsPath The path to the desired TypoScript setting
    *
    * @return string|array<mixed> The setting value or settings array
    */
    public function get(string $settingsPath): string|array
    {
        // Get the full TypoScript configuration
        $fullConfig = $this->configManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        // Split the settings path into its parts separated by a dot
        $pathParts = explode('.', $settingsPath);

        // Go through all pathParts entries, and dig deeper into the
        // fullConfig. The variable "subPart" is made more precise (dig deeper
        // down into the array). When the last item is readed, we do not add a "."
        // to the key, as this is the final value.
        $size = sizeof($pathParts);
        $subPart = $fullConfig;
        for ($i = 0; $i < $size; $i++) {
            // Last element, dont use "."
            if (($i + 1) == $size) {
                // There is a value element without "."
                if (isset($subPart[$pathParts[$i]])) {
                    $subPart = strval($subPart[$pathParts[$i]]);
                } else {
                    // We have an array
                    $subPart = $subPart[$pathParts[$i] . '.'];

                    // TODO: Sanitize array by removing the "." in the array keys
                    // foreach ($subPart as $key => $val) {
                    //     if (is_array($val)) {
                    //     }
                    //     $cKey = strval(str_replace('.', '', $key));
                    //     $clean[$cKey] = $val;
                    // }
                }
            } else {
                $subPart = $subPart[$pathParts[$i] . '.'];
            }
        }

        return $subPart;
    }
}
