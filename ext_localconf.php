<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService('jwt_sso', 'auth', \WEBcoast\JwtSso\Authentication\SsoFrontendAuthentication::class, [
    'title' => 'JWT SSO Frontend user authentication',
    'subtype' => 'getUserFE,authUserFE',
    'available' => true,
    'priority' => 70,
    'quality' => 80,
    'className' => \WEBcoast\JwtSso\Authentication\SsoFrontendAuthentication::class,
]);
