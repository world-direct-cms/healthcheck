<?php

namespace WorldDirect\Healthcheck\Probe;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;

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
 * This metadata probe checks the simplesamlphp metadata for expiration.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class SamlMetadataProbe extends ProbeBase implements ProbeInterface
{
    /**
     * Constant for the SimpleSAMLphp metarefresh module configuration filename
     */
    const SIMPLESAMLPHP_METAREFRESH_CONFIG_FILE = 'config-metarefresh.php';

    /**
     * Constant for the SimpleSAMLphp idp remote file
     */
    const SIMPLESAMLPHP_METAREFRESH_IDP_REMOTE_FILE = 'saml20-idp-remote.php';

    /**
     * The amount of time in minutes before which the probe throws an error.
     * This means, that if the expires + 30 minutes is now, an error is thrown.
     * The expires must at least be valid longer than 30 minutes, otherwise
     * an error is thrown.
     */
    const SIMPLESAMLPHP_METAREFRESH_TOLERANCE_MINUTES = 30;

    /**
     * Function checks if the probe is relevant for this installation by
     * trying to read the "expires" value in the metarefresch configuration.
     *
     * @return bool Check probe or not
     */
    public function useProbe(): bool
    {
        $config = $this->getMetarefreshConfig();
        if ($config) {
            $expires = $this->getMetarefreshExpires($config);
            if ($expires) {
                return true;
            }
        }

        // Prob not relevant
        return false;
    }

    /**
     * Run the probe by comparing the expires timestamp (- the tolerance) to the current time.
     *
     * @return void
     */
    public function run(): void
    {
        // Start the probe
        parent::start();

        // Check the expires value
        try {
            $config = $this->getMetarefreshConfig();
            if ($config) {
                $expires = $this->getMetarefreshExpires($config);
                if ($expires) {
                    $expiresDiff = ($expires - time());

                    // Used variables
                    $expiresDate = date('d.m.Y H:i:s', $expires);

                    // There is no time left --> Expired
                    if ($expiresDiff < 0) {
                        // ERROR message
                        $this->result->addErrorMessage(
                            sprintf(
                                $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.samlmetadata.error'),
                                $expiresDate, // Expires date
                                round((($expiresDiff * -1) / 60), 0), // Minutes since expiration
                                self::SIMPLESAMLPHP_METAREFRESH_TOLERANCE_MINUTES // Minutes tolerance
                            )
                        );
                    } else {
                        // SUCCESS message

                        $this->result->addSuccessMessage(
                            sprintf(
                                $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.samlmetadata.success'),
                                $expiresDate, // Expires date
                                round(($expiresDiff / 60), 0), // Minutes until expiration
                                self::SIMPLESAMLPHP_METAREFRESH_TOLERANCE_MINUTES // Minutes tolerance
                            )
                        );
                    }
                }
            }
        } catch (\Throwable $throwable) {
            // Handle error
            $this->result->addErrorMessage('todo');
        }

        // Stop the probe
        parent::stop();
    }

    /**
     * Function gets the metarefresh configuration array.
     * If it returns "null" there is no metarefresh configuration file.
     *
     * @return array<mixed>
     */
    private function getMetarefreshConfig(): ?array
    {
        // $config is included with the metarefresh configuration file
        global $config;

        // Check the env variable SIMPLESAMLPHP_CONFIG_DIR
        // If it is set, its possible to try to read the metadata
        if (getenv('SIMPLESAMLPHP_CONFIG_DIR') !== false) {
            $configPath = getenv('SIMPLESAMLPHP_CONFIG_DIR');

            // Check if there is a config-metarefresh.php file in the config directory
            if (file_exists($configPath . self::SIMPLESAMLPHP_METAREFRESH_CONFIG_FILE)) {
                // Include the metarefresh config here (yields $config variable)
                include_once $configPath . self::SIMPLESAMLPHP_METAREFRESH_CONFIG_FILE;

                if (is_array($config)) {
                    return $config;
                }
            }
        }

        // No metarefresh config set
        return null;
    }

    /**
     * Function returns the "expire" value in the metarefresh configuration
     *
     * @param array<mixed> $config The metarefresh configuration array
     *
     * @return null|int The expire value or null if not found
     */
    private function getMetarefreshExpires(array $config): ?int
    {
        // $metadata is included with the metarefresh idp data file
        global $metadata;

        // Get metarefresh output directory
        $outputDir = $this->recursiveFind($config, 'outputDir');

        // Check if it starts with a / (absolute), or not (relative)
        if ($outputDir[0] == '/') {
            // GOOD ❤

            // Include metadata file
            include_once $outputDir . self::SIMPLESAMLPHP_METAREFRESH_IDP_REMOTE_FILE;

            // Get first entry expires value
            $first = current($metadata);

            // If set return the expire value
            if (isset($first['expire'])) {
                return $first['expire'];
            }
        } else {
            // TODO: We currently cannot deal with relative paths, as we do not know where the simplesamlphp base dir is.
        }

        return null;
    }

    /**
     * Method to recursively find the value for a key (needle)
     * in an array.
     *
     * @param array<mixed> $haystack The array to search for
     * @param string $needle The array key to search for
     *
     * @return string Return found value
     */
    private function recursiveFind(array $haystack, string $needle): string
    {
        $iterator = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return strval($value);
            }
        }

        return '';
    }
}
