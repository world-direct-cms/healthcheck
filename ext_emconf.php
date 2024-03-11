<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 Healthcheck',
    'description' => 'Check the health of your TYPO3 installation.',
    'category' => 'plugin',
    'author' => 'Klaus Hörmann-Engl',
    'author_email' => 'kho@world-direct.at',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.15.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
