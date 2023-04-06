<?php

namespace WorldDirect\Healthcheck\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

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