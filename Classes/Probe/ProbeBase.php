<?php

namespace WorldDirect\Healthcheck\Probe;

use ReflectionClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use WorldDirect\Healthcheck\Utility\BasicUtility;
use WorldDirect\Healthcheck\Domain\Model\ProbeResult;

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
 * Base class for all probes. Contains some useful and necessary
 * method and constants.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Probe
 */
class ProbeBase
{
    /**
     * LanguageService to read language files.
     *
     * @var LanguageService
     */
    public $langService;

    /**
     * The probe result.
     *
     * @var ProbeResult
     */
    protected $result;

    /**
     * Construct new ProbeResults.
     *
     * @return void
     */
    public function __construct()
    {
        $this->langService = BasicUtility::getLanguageService();
        $this->result = GeneralUtility::makeInstance(ProbeResult::class);
    }

    /**
     * Returns the probe result.
     *
     * @return ProbeResult The probe result
     */
    public function getResult(): ProbeResult
    {
        return $this->result;
    }

    /**
     * Run this when the probe starts.
     *
     * @return void
     */
    public function start()
    {
        $this->result->setStarttime(microtime(true));
    }

    /**
     * Set when the probe stops.
     *
     * @return void
     */
    public function stop()
    {
        $this->result->setEndtime(microtime(true));
    }

    /**
     * Return the probe id.
     *
     * @param ProbeInterface $object The actual object extending this class.
     *
     * @return string The id of the probe depending on the classname.
     */
    public function getProbeId(ProbeInterface $object): string
    {
        return str_replace('probe', '', strtolower((new ReflectionClass($object))->getShortName()));
    }
}
