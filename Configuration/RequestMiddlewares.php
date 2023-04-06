<?php

return [
  'frontend' => [
      'healthcheck' => [
          'target' => \WorldDirect\Healthcheck\Middleware\HealthcheckMiddleware::class,
          // we need to hook in before page resolver, in order to treat arbitrary paths (which might not actually exist)
          'before' => [
            'typo3/cms-frontend/eid'
          ],
      ],
  ],
];
