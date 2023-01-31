<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUserRepository
{
    /**
     * @param string $email
     *
     * @throws DBALException
     * @throws Exception
     *
     * @return false|array
     */
    public function findByEmail(string $email)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $queryBuilder
            ->from('fe_users')
            ->select('uid', 'username', 'first_name', 'last_name')
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($email))
            );

        return $queryBuilder->execute()->fetchAssociative();
    }
}
