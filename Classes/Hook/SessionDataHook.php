<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Hook;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class SessionDataHook implements SingletonInterface
{
    protected ?int $userId = null;

    public function setSsoUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function addUserIdToSession($parameters, AbstractUserAuthentication $authentication)
    {
        if ($authentication instanceof FrontendUserAuthentication && $this->userId > 0) {
            $authentication->getSession()->set('sso_id', $this->userId);
        }
    }
}
