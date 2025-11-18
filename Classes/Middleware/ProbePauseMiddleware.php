<?php

namespace WorldDirect\Healthcheck\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WorldDirect\Healthcheck\Domain\Repository\ProbePauseRepository;
use WorldDirect\Healthcheck\Utility\HealthcheckUtility;

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

class ProbePauseMiddleware implements MiddlewareInterface
{
    /**
     * The healthcheckUtility
     *
     * @var HealthcheckUtility
     */
    protected $utility;

    /**
     * ProbePause repository to deal with probePause elements.
     *
     * @var ProbePauseRepository
     */
    protected $probePauseRepo;

    /**
     * Constructor for new HealthcheckUtility instances
     *
     * @param HealthcheckUtility $healthcheckUtility Utility for healthchecks object
     * @param ProbePauseRepository $probePauseRepo The repository to deal with probePause entries
     *
     * @return void
     */
    public function __construct(HealthcheckUtility $healthcheckUtility, ProbePauseRepository $probePauseRepo)
    {
        $this->utility = $healthcheckUtility;
        $this->probePauseRepo = $probePauseRepo;
    }

    /**
     * Process the request. Check for necessary parameters, and then return a JSON reponse
     * if "OK" or "ERROR".
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The request handler
     *
     * @return ResponseInterface The response
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check for "pause" or "play" request target urls
        if (str_starts_with($request->getRequestTarget(), '/' . $this->utility->config->getPathSegment() . '-pause' . '/') ||
            str_starts_with($request->getRequestTarget(), '/' . $this->utility->config->getPathSegment() . '-play' . '/')) {
            // Check for possible "trustedHostsPattern" errors
            if ($response = $this->utility->checkTrustedHostsPattern($request)) {
                return $response;
            }

            // Check for possible "allowedIps" errors
            if ($response = $this->utility->checkAllowedIps()) {
                return $response;
            }

            // Get parameters
            $queryParams = $request->getQueryParams();

            // Use urls to pause or play
            if (str_contains($this->utility->getPartOfRequestTarget($request, 1), 'pause')) {
                return $this->probePauseRepo->pauseProbe($queryParams['className']);
            } elseif (str_contains($this->utility->getPartOfRequestTarget($request, 1), 'play')) {
                return $this->probePauseRepo->playProbe($queryParams['className']);
            }
        }

        return $handler->handle($request);
    }
}
