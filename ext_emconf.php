<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JWT SSO',
    'description' => 'Single Sign-On based on JSON Web Token',
    'version' => '1.0.0',
    'category' => 'frontend',
    'constraints' => [
        'depends' => [
            'core' => '10.4.0-11.5.99',
            'frontend' => '10.4.0-11.5.99',
        ],
    ],
    'state' => 'stable',
    'author' => 'Thorben Nissen',
    'author_email' => 'thorben@webcoast.dk',
    'author_company' => 'WEBcoast',
    'autoload' => [
        'psr-4' => [
            'WEBcoast\\JwtSso\\' => 'Classes'
        ]
    ]
];
