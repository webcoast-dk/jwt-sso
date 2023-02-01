<?php

$GLOBALS['SiteConfiguration']['site_language']['columns']['jwtsso_login_route'] = [
    'label' => 'LLL:EXT:jwt_sso/Resources/Private/Language/locallang_backend.xlf:site_language.login_route',
    'config' => [
        'type' => 'input',
        'eval' => 'required',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['columns']['jwtsso_logout_route'] = [
    'label' => 'LLL:EXT:jwt_sso/Resources/Private/Language/locallang_backend.xlf:site_language.logout_route',
    'config' => [
        'type' => 'input',
        'eval' => 'required',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['palettes']['jwt-sso'] = [
    'label' => 'LLL:EXT:jwt_sso/Resources/Private/Language/locallang_backend.xlf:site_language.palettes.jwt-sso',
    'showitem' => 'jwtsso_login_route, jwtsso_logout_route',
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = preg_replace('/(--palette--;;rendering-related),/', '$1, --palette--;;jwt-sso,', $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']);
