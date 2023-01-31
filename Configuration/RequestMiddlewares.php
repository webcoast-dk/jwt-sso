<?php

return [
    'frontend' => [
        'webcoast/jwt-sso/redirect' => [
            'target' => \WEBcoast\JwtSso\Middleware\SsoRedirectMiddleware::class,
            'before' => [
                'typo3/cms-frontend/authentication',
            ],
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
        ],
        'webcoast/jwt-sso/callback' => [
            'target' => \WEBcoast\JwtSso\Middleware\SsoCallbackMiddleware::class,
            'before' => [
                'typo3/cms-frontend/authentication',
            ],
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
                'webcoast/jwt-sso/redirect'
            ],
        ],
        'webcoast/jwt-sso/after-login-redirect' => [
            'target' => \WEBcoast\JwtSso\Middleware\AfterLoginRedirectMiddleware::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler'
            ],
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
        'webcoast/jwt-sso/logout' => [
            'target' => \WEBcoast\JwtSso\Middleware\SsoLogoutMiddleware::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler'
            ],
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
