<?php

return [
    'frontend' => [
        'healthcheck' => [
            'target' => \WorldDirect\Healthcheck\Middleware\HealthcheckMiddleware::class,
            // We need to hook in very early in order to be upfront to check for database connection
            'before' => [
                'typo3/cms-frontend/eid'
            ],
        ],
    ],
];
