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
use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;
use TYPO3\CMS\Core\Localization\Exception\InvalidParserException;

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
     * @var HealthcheckUtility
     */
    protected $utility;

    /**
     * The middleware response
     *
     * @var Response
     */
    protected $response;

    /**
     * The healthcheck result.
     *
     * @var HealthcheckResult
     */
    protected $healthcheckResult;

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

            // Check if there are probes configured and if they implement the correct interface
            if ($response = $this->utility->checkProbes()) {
                return $response;
            }

            // TODO: Check if the 3rd part of the url contains a valid string which can be then put into a
            // Output class "HtmlOutput" or "PrtgOutput". Then the rendering can do its job.
            // If not throw an error.

            // Run the healthcheck probes
            /** @var HealthcheckResult $result */
            $result = $this->utility->runProbes();

            // TODO: Add configuration and interface for output --> Multiple outputs possible
            // TODO: Use fluid view to output HTML or JSON

            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($result, 'The HealthcheckResult');
            exit;
        }

        // If the request target does not start with the configured pathSegment let another middleware
        // handle this.
        return $handler->handle($request);
    }
}
