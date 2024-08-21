<?php

namespace WorldDirect\Healthcheck\SystemInformation;

use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\InformationStatus;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
  * This class adds the Healthcheck link to the SystemInformation toolbar for easy access.
  * 
  * @author Klaus Hörmann-Engl
  * @package WorldDirect\Healthcheck\SystemInformation
  */
final class ToolbarItemProvider
{
    public function getHealthcheckLink(SystemInformationToolbarCollectorEvent $event): void
    {
        /** @var HealthcheckUtility $healthcheckUtility */
        $healthcheckUtility = GeneralUtility::makeInstance(HealthcheckUtility::class);
        $event->getToolbarItem()->addSystemInformation(
            'Healthcheck Link', // Titlt of the entry
            $healthcheckUtility->getHealthcheckLink(), // The healthcheck url
            'actions-check-square', // Check icon
            InformationStatus::OK // Information
        );
    }
}