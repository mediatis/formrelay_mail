<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form Relay - Mail Plugin',
    'description' => 'Send form data via Mail',
    'category' => 'be',
    'author' => '',
    'author_email' => '',
    'author_company' => 'Mediatis AG',
    'state' => 'alpha',
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
            'formrelay' => '>=5.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
