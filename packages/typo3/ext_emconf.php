<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Feature Toggles',
    'description' => 'Feature toggles bridge to acme/feature-toggles-core',
    'category' => 'be',
    'state' => 'beta',
    'clearCacheOnLoad' => 1,
    'author' => 'ACME',
    'author_email' => 'dev@example.invalid',
    'author_company' => 'ACME',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.99.99',
            'php' => '8.1.0-8.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
