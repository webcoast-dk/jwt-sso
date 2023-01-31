<?php

declare(strict_types=1);

namespace WEBcoast\JwtSso\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendGroupRepository
{
    /**
     * @param array $roles
     *
     * @throws DBALException
     * @throws Exception
     * @return false|array
     */
    public function findUidByRole(array $roles)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_groups');
        $queryBuilder
            ->from('fe_groups')
            ->select('uid');
        $roleConstraints = [];
        foreach ($roles as $role) {
            $roleConstraints[] = $queryBuilder->expr()->eq('role_name', $queryBuilder->createNamedParameter($role));
        }
        $queryBuilder
            ->where(
                $queryBuilder->expr()->or(...$roleConstraints)
            );

        return $queryBuilder->execute()->fetchFirstColumn();
    }
}
