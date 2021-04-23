<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'X-Ray',
    'description' => 'X-Rays your TYPO3 installation. Suggests and performs improvements.',
    'category' => 'be',
    'author' => 'b13 GmbH',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' =>
        [
            'depends' => ['typo3' => '10.4.0-11.4.99'],
            'conflicts' => [],
            'suggests' => [],
        ],
];
