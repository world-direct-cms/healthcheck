<?php

namespace WorldDirect\Healthcheck\Probe;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Probe\ProbeBase;
use WorldDirect\Healthcheck\Domain\Model\Status;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use WorldDirect\Healthcheck\Domain\Model\ProbeResult;
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
     * Constant holding the null backend class name to compare.
     */
    const NULL_BACKEND = 'TYPO3\CMS\Core\Cache\Backend\NullBackend';

    /**
     * This probe should be disabled on systems running the
     * TYPO3_CONTEXT "Development". This because, the development
     * systems often have disabled caching configurations. Therefore
     * nothing can be written, and the probe would fail on these systems.
     *
     * @return bool True or false, depending on the current context
     */
    public function useProbe(): bool
    {
        // if (Environment::getContext() == 'Development') {
        //     return false;
        // }
        return true;
    }

    /**
     * Get the title of the probe.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Cache System';
    }

    /**
     * Run the cache probe. Check if caches can be written.
     *
     * @return void The probes result
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
            $cacheKey = md5(microtime() . '.healthcheck');

            // TODO: Check for backend of frontend cache, NullBackend

            foreach ($cacheConfigs as $id => $cacheConfig) {
                // Ignore certain caches, as they are not implemented in a way that works
                // with the CacheManager.
                // 
                // autoloader7...Does not work, therefore ignore it (EXT:autoloader)
                //
                if (!in_array($id, ['autoloader7'])) {
                    if ($this->checkForNullBackend($cacheConfig)) {
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
                }
            }
        } catch (\Throwable $throwable) {
            // Error message
            $this->result->addErrorMessage(
                $this->langService->sL(HealthcheckUtility::LANG_PREFIX . 'probe.cache.error')
            );
        }

        // Stop the probe
        parent::stop();
    }

    /**
     * Function checks if the cache configuration of 'frontend' or 'backend'
     * contains the NULL BACKEND class name. If it does, the cache configuration
     * cannet be tested for writing, as this would not work. Therefore "false"
     * is returned.
     *
     * @param array<mixed> $cacheConfig The cache Configuration array
     *
     * @return bool Wheter the cacheConfig array contains a NULL BACKEND in frontend or backend configuration.
     */
    private function checkForNullBackend(array $cacheConfig): bool
    {
        $parts = ['frontend', 'backend'];
        foreach ($parts as $part) {
            if (isset($cacheConfig[$part]) && $cacheConfig[$part] == self::NULL_BACKEND) {
                return false;
            }
        }
        return true;
    }
}
