<?php

namespace WorldDirect\Healthcheck\Middleware;

use RuntimeException;
use InvalidArgumentException;
use TYPO3\CMS\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;
use TYPO3\CMS\Core\Localization\Exception\InvalidParserException;

/**
 * The HealthcheckMiddleware intercepts the requests depending on the set path
 * and returns a Healthcheck frontend holding the done checks and each state.
 * 
 * @author Klaus Hörmann-Engl
 * @package WorldDirect\Healthcheck\Middleware
 */
class HealthcheckMiddleware implements MiddlewareInterface
{
    /**
     * The healthcheckUtility
     * 
     * @param HealthcheckUtility
     */
    protected $utility;

    /**
     * The middleware response
     * 
     * @param Response
     */
    protected $response;

    /**
     * Constructor for new HealthcheckUtility instances
     * 
     * @param HealthcheckUtility $healthcheckUtility Utility for healthchecks object
     * @param Response $response The middleware response
     * 
     * @return void 
     */
    public function __construct(HealthcheckUtility $healthcheckUtility, Response $response)
    {
        $this->utility = $healthcheckUtility;
        $this->response = $response;
    }

    /**
     * Process the healthcheck middleware
     * 
     * @param ServerRequestInterface $request The server request
     * @param RequestHandlerInterface $handler The request handler
     * 
     * @return ResponseInterface Returns a response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if the pathSegment (route) is relevant for the Healthcheck
        if (str_starts_with($request->getRequestTarget(), '/' . $this->utility->config->getPathSegment() . '/')) {
            
            // Check for possible "secret" errors
            if ($response = $this->utility->checkSecret($request)) {
                return $response;
            }

            // Check for possible "allowedIps" errors
            if ($response = $this->utility->checkAllowedIps()) {
                return $response;
            }

            // Check if there are probes configured
            if ($response = $this->utility->checkProbes()) {
                return $response;
            }

            // Run the healthcheck checks
            $result = $this->utility->runProbes();

            // TODO: Add configuration and interface for output --> Multiple outputs possible
            // TODO: Use fluid view to output HTML or JSON

            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($result);
            exit;
        }

        // If the request target does not start with the configured pathSegment let another middleware
        // handle this.
        return $handler->handle($request);
    }
}