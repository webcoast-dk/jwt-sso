<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService('jwt_sso', 'auth', \WEBcoast\JwtSso\Authentication\SsoFrontendAuthentication::class, [
    'title' => 'JWT SSO Frontend user authentication',
    'subtype' => 'getUserFE,authUserFE',
    'available' => true,
    'priority' => 70,
    'quality' => 80,
    'className' => \WEBcoast\JwtSso\Authentication\SsoFrontendAuthentication::class,
]);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][1693402120] = \WEBcoast\JwtSso\Hook\SessionDataHook::class . '->addUserIdToSession';
