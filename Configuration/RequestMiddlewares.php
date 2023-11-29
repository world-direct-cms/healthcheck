<?php

return [
    'frontend' => [
        'healthcheck-pause' => [
            'target' => \WorldDirect\Healthcheck\Middleware\ProbePauseMiddleware::class,
            'before' => [
                'healthcheck'
            ],
        ],
        'healthcheck' => [
            'target' => \WorldDirect\Healthcheck\Middleware\HealthcheckMiddleware::class,
            'after' => [
                'typo3/cms-core/verify-host-header'
            ],
            'before' => [
                'typo3/cms-frontend/eid'
            ]
        ]
    ],
];
