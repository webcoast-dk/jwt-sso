<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Authentication;

use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use WEBcoast\JwtSso\Api\Client;
use WEBcoast\JwtSso\Exception\ApiResponseException;
use WEBcoast\JwtSso\Repository\FrontendGroupRepository;
use WEBcoast\JwtSso\Repository\FrontendUserRepository;

class SsoFrontendAuthentication extends AuthenticationService
{
    protected int $storagePid = 0;

    public function initAuth($mode, $loginData, $authInfo, $pObj)
    {
        parent::initAuth($mode, $loginData, $authInfo, $pObj);

        $typoScript = GeneralUtility::makeInstance(ConfigurationManagerInterface::class)->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->db_user['checkPidList'] = $this->storagePid = (int)($typoScript['plugin.']['tx_jwtsso.']['storagePid'] ?? 0);
        $this->db_user['check_pid_clause'] = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users')->expr()->in('pid', GeneralUtility::intExplode(',', $this->db_user['checkPidList']));
    }

    public function getUser()
    {
        $apiClient = GeneralUtility::makeInstance(Client::class);
        try {
            $payload = json_decode($this->login['uident_text'], true);
            $userData = $apiClient->getUser((int) $payload['user']);
            $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
            $userRecord = $userRepository->findByEmail($userData['email']);
            $this->simulateBackendUser();
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->bypassAccessCheckForRecords = true;
            $dataHandler->bypassWorkspaceRestrictions = true;
            $groups = GeneralUtility::makeInstance(FrontendGroupRepository::class)->findUidByRole($userData['roles']);
            if (!$userRecord) {
                $newId = 'NEW' . bin2hex(random_bytes(6));
                $dataHandler->start(
                    [
                        'fe_users' => [
                            $newId => [
                                'pid' => $this->storagePid,
                                'username' => $userData['email'],
                                'first_name' => $userData['person']['firstName'],
                                'last_name' => $userData['person']['lastName'],
                                'password' => bin2hex(random_bytes(10)),
                                'usergroup' => implode(',', $groups),
                            ]
                        ],
                    ],
                    [],
                );
                $dataHandler->process_datamap();
            } else {
                $uid = $userRecord['uid'];
                $dataHandler->start(
                    [
                        'fe_users' => [
                            $uid => [
                                'first_name' => $userData['person']['firstName'],
                                'last_name' => $userData['person']['lastName'],
                                'usergroup' => implode(',', $groups),
                            ],
                        ],
                    ],
                    []
                );
                $dataHandler->process_datamap();
            }
            $this->restoreBackendUser();

            if ($this->authInfo['session'] instanceof UserSession) {
                $this->authInfo['session']->set('sso_id', $userData['id']);
            }

            return $this->fetchUserRecord($userData['email']);
        } catch (ApiResponseException $e) {
            $this->logger->error(sprintf('The user data for id %d could not be fetched. Error was: %s', $payload['user'], $e->getMessage()));

            return false;
        }
    }

    public function authUser(array $user): int
    {
        if ($this->login['uname'] !== 'sso') {
            // User was not logged in using SSO
            return 100;
        }

        // Authenticated, no need to check other services
        return 200;
    }

    protected function simulateBackendUser()
    {
        if ($GLOBALS['BE_USER']) {
            $this->beUserBackup = $GLOBALS['BE_USER'];
        }

        Bootstrap::initializeBackendUser();
        $GLOBALS['BE_USER']->user = [
            'admin' => true
        ];
        Bootstrap::loadExtTables();
    }

    protected function restoreBackendUser() {
        if ($this->beUserBackup) {
            $GLOBALS['BE_USER'] = $this->beUserBackup;
        }
    }
}
