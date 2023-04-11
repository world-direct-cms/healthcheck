<?php

namespace WorldDirect\Healthcheck\Utility;

use TYPO3\CMS\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use WorldDirect\Healthcheck\Utility\BasicUtility;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckConfiguration;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;
use WorldDirect\Healthcheck\Probe\ProbeInterface;

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
 * Class holding function to deal with the healthcheck middleware. It checks the
 * access, provides the extension configuration and much more.
 *
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Utility
 */
class HealthcheckUtility
{
    /**
     * Language prefix for getting labels
     */
    const LANG_PREFIX = 'LLL:EXT:healthcheck/Resources/Private/Language/locallang.xlf:';

    /**
     * The error response http status code
     */
    const ERROR_RESPONSE_HTTP_STATUS = 403;

    /**
     * The healthcheck configuration
     *
     * @var HealthcheckConfiguration
     */
    public $config;

    /**
     * Language service
     *
     * @var LanguageService
     */
    public $langService;

    /**
     * Constructor for new HealthcheckUility objects
     *
     * @return void
     */
    public function __construct(HealthcheckConfiguration $healthcheckConfiguration)
    {
        $this->config = $healthcheckConfiguration;
        $this->langService = BasicUtility::getLanguageService();
    }

    /**
     * Function checks if the access is authorized, looking at the secret.
     * If an errors occured a response is build and depending on the extension
     * configuration settings an error message is attached.
     * When everything is ok, return "null".
     *
     * @param ServerRequestInterface $request The server request
     *
     * @return null|ResponseInterface A response when an error has occured, or null when ok.
     */
    public function checkSecret(ServerRequestInterface $request): ?ResponseInterface
    {
        $errorMessage = '';

        // Check if the secret in the extension configuration is set
        if (empty($this->config->getSecret()) || strlen($this->config->getSecret()) < 10) {
            $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.secret.isEmpty');
        }

        // Check if the given secret matches the secret in the extension configuration
        if (empty($errorMessage) && $this->config->getSecret() !== $this->getSecretFromRequest($request)) {
            $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.secret.isFalse');
        }

        // If there is an error message set, build a response and return it
        if (!empty($errorMessage)) {
            return $this->returnErrorResponse($errorMessage);
        }

        // Everything is OK
        return null;
    }

    /**
     * Method check if the current requesting IP address is allowed according to the
     * extension configuration.
     *
     * @return null|ResponseInterface A response when an error has occured, or null when ok.
     */
    public function checkAllowedIps(): ?ResponseInterface
    {
        $errorMessage = '';

        //Check if the allowed ips is set in the extension configuration
        if (empty($this->config->getAllowedIps())) {
            $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.allowedIps.isEmpty');
        }

        // Check if the allowedIps match the current request IP
        /** @var string $remoteIp */
        $remoteIp = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (empty($errorMessage) && !GeneralUtility::cmpIP($remoteIp, $this->config->getAllowedIps())) {
            $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.allowedIps.dontMatch');
        }

        if (!empty($errorMessage)) {
            return $this->returnErrorResponse($errorMessage);
        }

        // Everything is OK
        return null;
    }

    /**
     * Function checks if there are any probes configured.
     *
     * @return null|ResponseInterface A response when an error has occured, or null when ok.
     */
    public function checkProbes(): ?ResponseInterface
    {
        $errorMessage = '';

        // Check if there are any probes configured
        if (empty($this->config->getProbes())) {
            $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.probes.isEmpty');
        }

        // Check if all probes implement the interface
        if (empty($errorMessage)) {
            foreach ($this->config->getProbes() as $probeClass) {
                $interfaces = class_implements($probeClass);
                if (!isset($interfaces['WorldDirect\Healthcheck\Probe\ProbeInterface'])) {
                    $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.probes.wrongType');
                    break;
                }
            }
        }

        if (!empty($errorMessage)) {
            return $this->returnErrorResponse($errorMessage);
        }

        // Everything is OK
        return null;
    }

    /**
     * Run all configured probes.
     *
     * @return HealthcheckResult Return the HealthcheckResult.
     */
    public function runProbes(): HealthcheckResult
    {
        /** @var HealthcheckResult $result */
        $result = GeneralUtility::makeInstance(HealthcheckResult::class);

        foreach ($this->config->getProbes() as $probeClass) {
            /** @var ProbeInterface $probe */
            $probe = GeneralUtility::makeInstance($probeClass); /** @phpstan-ignore-line */
            $probe->run();

            // Add probe with results to Healthcheck result
            $result->addProbe($probe);

            // Update the overall status of the HealthcheckResult.
            $result->updateStatus();
        }

        return $result;
    }

    /**
     * Build a reponse with a optional error message to return
     *
     * @param string $errorMessage The error message to be contained in the response (when debug is enabled)
     *
     * @return ResponseInterface The response
     */
    private function returnErrorResponse(string $errorMessage): ResponseInterface
    {
        /** @var Response $response */
        $response = GeneralUtility::makeInstance(Response::class);
        $response->withStatus(self::ERROR_RESPONSE_HTTP_STATUS);
        if ($this->config->isDebugEnabled()) {
            $response->getBody()->write($errorMessage);
        }
        return $response;
    }

    /**
     * Function extracts the secret from the given Request.
     * The secret is the third part of the target. The first entry being empty
     * and the second being the pathSegment to trigger the Middleware at all.
     *
     * @param ServerRequestInterface $request The server request
     *
     * @return string The secret from the request
     */
    private function getSecretFromRequest(ServerRequestInterface $request): string
    {
        return $this->getPartOfRequestTarget($request, 2);
    }

    /**
     * Return the fourth part of the requestTarget to get the desired output format.
     *
     * @param ServerRequestInterface $request The Request holding the requestTarget.
     * @return string
     */
    // private function getOutputFromRequest(ServerRequestInterface $request): string
    // {
    //     return $this->getPartOfRequestTarget($request, 3);
    // }

    /**
     * Function returns the desired number part from the given requestTarget.
     *
     * @param ServerRequestInterface $request The request with the target.
     * @param int $number The numbered part of the requestTarget when split by '/'.
     *
     * @return string The desired string part of the request target.
     */
    private function getPartOfRequestTarget(ServerRequestInterface $request, int $number): string
    {
        $target = $request->getRequestTarget();
        $parts = explode('/', $target);
        return $parts[$number];
    }
}
