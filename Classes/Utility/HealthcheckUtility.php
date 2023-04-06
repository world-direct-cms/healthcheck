<?php

namespace WorldDirect\Healthcheck\Utility;

use TYPO3\CMS\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use WorldDirect\Healthcheck\Utility\BasicUtility;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckConfiguration;
use WorldDirect\Healthcheck\Probe\ProbeInterface;

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
    CONST LANG_PREFIX = 'LLL:EXT:healthcheck/Resources/Private/Language/locallang.xlf:';

    /**
     * The error response http status code
     */
    CONST ERROR_RESPONSE_HTTP_STATUS = 403;

    /**
     * The healthcheck configuration
     * 
     * @param HealthcheckConfiguration
     */
    public $config;

    /**
     * Language service
     * 
     * @param LanguageService
     */
    public $langService;

    /**
     * Constructor for new HealthcheckUility objects
     * 
     * @return void
     */
    public function __construct(HealthcheckConfiguration $healthcheckConfiguration) {
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
     * Run all configured probes
     * 
     * @return array A result array to output
     */
    public function runProbes(): array
    {
        $result = [];
        
        foreach ($this->config->getProbes() as $probeClass) {
            /** @var ProbeInterface $probe */
            $probe = GeneralUtility::makeInstance($probeClass);

            // Get probeId to use with result array
            $probeId = str_replace('probe', '', strtolower($probe->getShortClassName($probe)));
            
            // Run the probe
            $result[$probeId] = $probe->run();
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
    private function returnErrorResponse(string $errorMessage): ResponseInterface {
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
     * @var ServerRequestInterface $request The server request
     * 
     * @return string The secret from the request
     */
    private function getSecretFromRequest(ServerRequestInterface $request): string
    {
        $target = $request->getRequestTarget();
        $parts = explode('/', $target );
        // Third element, index 2
        return $parts[2];
    }
}