<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:healthcheck/Resources/Private/Language/locallang_db.xlf:tx_healthcheck_domain_model_probe_pause',
        'label' => 'class_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'rootLevel' => '-1', // Allow to create entries on the pid "0"
        'searchFields' => 'class_name',
        'iconfile' => 'EXT:healthcheck/Resources/Public/Icons/tx_healthcheck_domain_model_probe_pause.png',
    ],
    'types' => [
        '1' => ['showitem' => 'class_name'],
    ],
    'columns' => [
        'class_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:healthcheck/Resources/Private/Language/locallang_db.xlf:tx_healthcheck_domain_model_probe_pause.class_name',
            'config' => [
                'type' => 'input',
                'size' => 100,
                'eval' => 'trim'
            ],
        ],
    ],
];
