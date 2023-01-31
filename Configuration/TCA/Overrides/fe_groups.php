<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', [
    'role_name' => [
        'label' => 'LLL:EXT:jwt_sso/Resources/Private/Language/locallang_backend.xlf:fe_users.role_name',
        'exclude' => true,
        'config' => [
            'type' => 'input',
            'size' => 20,
            'eval' => 'trim'
        ]
    ]
]);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'role_name', '', 'after:title');
