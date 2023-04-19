<?php

namespace WorldDirect\Healthcheck\Middleware;

use TYPO3\CMS\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;
use WorldDirect\Healthcheck\Domain\Model\HealthcheckResult;

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
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
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

            // Check if there are output formats configured in the url
            $requestedOutput = $this->utility->getOutputFromRequest($request);
            if ($response = $this->utility->checkOutputs($requestedOutput)) {
                return $response;
            }

            // Run the healthcheck probes
            /** @var HealthcheckResult $result */
            $result = $this->utility->runProbes();

            // Return the desired output
            return $this->utility->getHealthcheckResponse(
                $result,
                $this->utility->config->getOutputs()[$requestedOutput]
            );
        }

        // If the request target does not start with the configured pathSegment let another middleware
        // handle this.
        return $handler->handle($request);
    }
}
