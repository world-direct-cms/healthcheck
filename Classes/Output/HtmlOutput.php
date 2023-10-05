<?php

namespace WorldDirect\Healthcheck\Output;

use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WorldDirect\Healthcheck\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

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

class HtmlOutput extends OutputBase implements OutputInterface
{
    /**
     * The content type of the rendered content.
     *
     * @return string The content type for this output type.
     */
    public function getContentType(): string
    {
        return 'text/html';
    }

    /**
     * Function renders the HTML output and returns the HTML content.
     *
     * @param HealthcheckResult $result The healthcheck result to be rendered
     *
     * @return string The html content of the renderd healthcheck result
     */
    public function getContent(HealthcheckResult $result): string
    {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);

        try {
            $view->setLayoutRootPaths([
                GeneralUtility::getFileAbsFileName('EXT:healthcheck/Resources/Private/Layouts/')
            ]);
            $view->setTemplateRootPaths([
                GeneralUtility::getFileAbsFileName('EXT:healthcheck/Resources/Private/Templates/')
            ]);
            $view->setPartialRootPaths([
                GeneralUtility::getFileAbsFileName('EXT:healthcheck/Resources/Private/Partials/')
            ]);
            $view->setFormat('html');
            $view->setTemplate('HtmlOutput');

            $view->assignMultiple(
                [
                    'result' => $result,
                    'extConfig' => $this->getExtensionConfiguration(),
                    'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']
                ]
            );

            // Check if additionaInfo is enabled, then add these informations also
            if ($this->getExtensionConfiguration()->getEnableAdditionalInfo()) {
                $view->assignMultiple(
                    [
                        // Additional info
                        'datetime' => date('d.m.Y H:i:s'),
                        'ip' => GeneralUtility::getIndpEnv('REMOTE_ADDR')
                    ]
                );
            }

            // Return the rendered view
            return $view->render();
        } catch (InvalidTemplateResourceException $e) {
            // TODO: Show error message or something similar
            return '';
        }
    }
}
