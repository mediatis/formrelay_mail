<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Relay - Mail Plugin',
    'description' => 'Send form data via Mail',
    'category' => 'be',
    'author' => '',
    'author_email' => '',
    'author_company' => 'Mediatis AG',
    'state' => 'alpha',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.3.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.5.0-9.5.99',
            'formrelay' => '>=2.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'suggests' => [
    ],
];
