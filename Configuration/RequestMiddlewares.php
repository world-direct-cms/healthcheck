<?php

return [
    'frontend' => [
        'healthecheck-pause' => [
            'target' => \WorldDirect\Healthcheck\Middleware\ProbePauseMiddleware::class,
            'before' => [
                'healthcheck'
            ],
        ],
        'healthcheck' => [
            'target' => \WorldDirect\Healthcheck\Middleware\HealthcheckMiddleware::class,
            'after' => [
                'typo3/cms-frontend/static-route-resolver'
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver'
            ]
        ]
    ],
];
