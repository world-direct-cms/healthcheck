<?php

namespace WorldDirect\Healthcheck\Probe;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use WorldDirect\Healthcheck\Domain\Model\ProbeResult;
use WorldDirect\Healthcheck\Domain\Model\Status;
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
 * Cache probe
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class CacheProbe extends ProbeBase implements ProbeInterface
{
    /**
     * This probe can always be run.
     *
     * @return bool True
     */
    public function useProbe(): bool
    {
        return true;
    }

    /**
     * Run the cache probe. Check if caches can be written.
     *
     * @return void The probes result
     */
    public function run(): void
    {
        // Start the probe
        parent::start();

        // Check all caches
        try {
            // Get all cache configurations
            $cacheConfigs = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];

            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $cacheManager->setCacheConfigurations($cacheConfigs);
            $cacheKey = md5(microtime() . '.healthcheck');

            foreach ($cacheConfigs as $id => $cacheConfig) {
                $cache = $cacheManager->getCache($id);
                $cache->set($cacheKey, 'healthcheck');

                // Check if the cache contains the cacheKey
                if ($cache->has($cacheKey)) {
                    $cache->remove($cacheKey);
                    // Success message
                    $this->result->addSuccessMessage(
                        sprintf($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.cache.success'), $id)
                    );
                } else {
                    // Error message
                    $this->result->addErrorMessage(
                        sprintf($this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.cache.error.notWriteable'), $id)
                    );
                }
            }
        } catch(\Throwable $throwable) {
            // Error message
            $this->result->addErrorMessage(
                $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.cache.error')
            );
        }

        // Stop the probe
        parent::stop();
    }
}
