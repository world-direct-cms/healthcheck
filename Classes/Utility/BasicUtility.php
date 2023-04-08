<?php

namespace WorldDirect\Healthcheck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

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

class BasicUtility
{
    /**
     * Method returns a LanguageService
     *
     * @return LanguageService The languageService to use
     */
    public static function getLanguageService(): LanguageService
    {
        /** @var LanguageServiceFactory */
        $langFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);

        // There is a backend user context, then return his language
        if (isset($GLOBALS['BE_USER'])) {
            return $langFactory->createFromUserPreferences($GLOBALS['BE_USER']);
        }

        // Otherwise return a new LanguageService
        /** @var LanguageService */
        $langService = GeneralUtility::makeInstance(LanguageService::class);
        return $langService;
    }
}
