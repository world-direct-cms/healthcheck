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
        } else {
            return $langFactory->create('de-DE');
        }
    }

    /**
     * This function receives a fully qualified domain name class, and returns the
     * last part of it.
     *
     * @param string $fqdnClass The fully qualified domain name class
     *
     * @return string The last part of the fqdnClass
     */
    public static function getShortClassName(string $fqdnClass): string
    {
        $parts = explode('\\', $fqdnClass);

        if (is_array($parts)) {
            $lastPart = end($parts);
            return $lastPart;
        }
    }

    /**
     * Function returns the current domain and protocol as contained
     * in the $_SERVER variable.
     *
     * @return string The current domain (e.g. "https://www.google.com/")
     */
    public static function getCurrentDomain(): string
    {
        $domain = 'http://';
        if ($_SERVER['HTTPS'] == 'on') {
            $domain = 'https://';
        }

        return $domain . $_SERVER['HTTP_HOST'] . '/';
    }
}
