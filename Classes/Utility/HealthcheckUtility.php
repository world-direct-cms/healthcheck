<?php

namespace WorldDirect\Healthcheck\Utility;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use WorldDirect\Healthcheck\Domain\Model\Status;
use WorldDirect\Healthcheck\Probe\ProbeInterface;
use WorldDirect\Healthcheck\Utility\BasicUtility;
use WorldDirect\Healthcheck\Output\OutputInterface;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckConfiguration;

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
    const ERROR_RESPONSE_HTTP_STATUS = 503;

    /**
     * The success response http status code
     */
    const SUCCESS_RESPONSE_HTTP_STATUS = 200;

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
     * Response factory
     *
     * @var ResponseFactory
     */
    public $responseFactory;

    /**
     * Constructor for new HealthcheckUility objects
     *
     * @param HealthcheckConfiguration $healthcheckConfig The healthcheck extension configuration
     * @param ResponseFactory $responseFactory The factory object to create new response
     *
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(HealthcheckConfiguration $healthcheckConfig, ResponseFactory $responseFactory)
    {
        $this->config = $healthcheckConfig;
        $this->responseFactory = $responseFactory;
        $this->langService = BasicUtility::getLanguageService();
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
            return $this->getResponse($errorMessage, self::ERROR_RESPONSE_HTTP_STATUS);
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
                    $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.probes.wrongInterface');
                    break;
                }
            }
        }

        if (!empty($errorMessage)) {
            return $this->getResponse($errorMessage, self::ERROR_RESPONSE_HTTP_STATUS);
        }

        // Everything is OK
        return null;
    }

    /**
     * Function cehcks for the possible output
     *
     * @param string $requestedOutput The name of the requested output format (e.g. "html")
     *
     * @return null|ResponseInterface
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function checkOutputs(string $requestedOutput): ?ResponseInterface
    {
        $errorMessage = '';

        // Check if there are any outputs configured
        if (empty($this->config->getOutputs())) {
            $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.outputs.isEmpty');
        }

        // When no error has occured...
        if (empty($errorMessage)) {
            $possibleOutputs = [];

            // Check all output classes if they implement the OutputInterface
            foreach ($this->config->getOutputs() as $outputClass) {
                $interfaces = class_implements($outputClass);
                if (!isset($interfaces['WorldDirect\Healthcheck\Output\OutputInterface'])) {
                    $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.outputs.wrongInterface');
                    break;
                }
                $possibleOutputs[] = strtolower(str_replace('Output', '', BasicUtility::getShortClassName($outputClass)));
            }

            // If still no error has occured...
            if (empty($errorMessage)) {
                // Check if the requestOutput is present in the possibleOutputs
                if (!in_array($requestedOutput, array_keys($this->config->getOutputs()))) {
                    $errorMessage = $this->langService->sL(self::LANG_PREFIX . 'error.outputs.notPresent');
                }
            }
        }

        if (!empty($errorMessage)) {
            return $this->getResponse($errorMessage, self::ERROR_RESPONSE_HTTP_STATUS);
        }

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
            $probe = GeneralUtility::makeInstance($probeClass);

            if ($probe->useProbe()) {
                // Run the probe
                $probe->run();

                // Add probe with results to Healthcheck result
                $result->addProbe($probe);

                // Update the overall status of the HealthcheckResult.
                $result->updateStatus();
            }
        }

        return $result;
    }

    /**
     *
     *
     * @param HealthcheckResult $result The current status of the Healthcheck result
     * @param string $outputClass Which output to render
     *
     * @return ResponseInterface Return a response for the middleware to process
     */
    public function getHealthcheckResponse(HealthcheckResult $result, string $outputClass): ResponseInterface
    {
        /** @var OutputInterface $output */
        $output = GeneralUtility::makeInstance($outputClass);

        // Set the http status return code
        $httpStatus = self::SUCCESS_RESPONSE_HTTP_STATUS;
        if ($result->getStatus() == Status::ERROR) {
            $httpStatus = self::ERROR_RESPONSE_HTTP_STATUS;
        }

        // Return the created response
        return $this->getResponse(
            $output->getContent($result),
            $httpStatus,
            $output->getContentType()
        );
    }

    /**
     * Returns a Response with the given content, httpsStatus and contentType.
     *
     * @param string $content The content for the response
     * @param int $httpStatus The used http status code
     * @param string $contentType The contentType for the response header
     *
     * @return ResponseInterface The response object
     */
    private function getResponse(string $content, int $httpStatus, string $contentType = 'text/html')
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', $contentType)
            ->withStatus($httpStatus);
        $response->getBody()->write($content);
        return $response;
    }

    /**
     * Return the fourth part of the requestTarget to get the desired output format.
     *
     * @param ServerRequestInterface $request The Request holding the requestTarget.
     * @return string
     */
    public function getOutputFromRequest(ServerRequestInterface $request): string
    {
        try {
            // Try to get the third part of the url
            return $this->getPartOfRequestTarget($request, 3);
        } catch (\Throwable $throwable) {
            // If there is no third part of the url return a "default" value: html
            // Or any other exception
            return 'html';
        }
    }

    /**
     * Function returns the desired number part from the given requestTarget.
     *
     * @param ServerRequestInterface $request The request with the target.
     * @param int $number The numbered part of the requestTarget when split by '/'.
     *
     * @return string The desired string part of the request target.
     */
    public function getPartOfRequestTarget(ServerRequestInterface $request, int $number): string
    {
        $target = $request->getRequestTarget();
        $parts = explode('/', $target);
        if (isset($parts[$number])) {
            return $parts[$number];
        } else {
            return '';
        }
    }

    /**
     * Return a full url to the healthcheck of the current TYPO3 installation.
     *
     * @return string The healthcheck url
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getHealthcheckLink(): string
    {
        // Return the url of the healthcheck with the current domain
        return BasicUtility::getCurrentDomain() . $this->config->getPathSegment();
    }
}
