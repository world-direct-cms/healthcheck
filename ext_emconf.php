<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 Healthcheck',
    'description' => 'Check the health of your TYPO3 installation.',
    'category' => 'plugin',
    'author' => 'Klaus Hörmann-Engl',
    'author_email' => 'kho@world-direct.at',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '4.5.3',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-14.9.99'
        ],
        'conflicts' => [],
        'suggests' => [
            // Suggest "buildinfo" extension
            'buildinfo' => '2.0.0-2.99.99'
        ],
    ],
];
