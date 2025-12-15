<?php

namespace JambageCom\Div2007\Database;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Access\RecordAccessVoter;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class QueryBuilderApi
{
    public static $comparator2FunctionArray = [
        '='    => 'eq',
        '<>'   => 'neq',
        '<='   => 'lte',
        '<'    => 'lt',
        '>='   => 'gte',
        '>'    => 'gt',
        'IN'   => 'iIn',
        'LIKE' => 'like',
        'NOT LIKE' => 'notLike',
        'NOT IN' => 'notIn',
    ];

    public static function convertComparator2Function($comparator)
    {
        return static::$comparator2FunctionArray[$comparator] ?? '';
    }

    /**
     * Generates a search where clause based on the input search words (AND operation - all search words must be found in record.)
     * Example: The $sw is "content management, system" (from an input form) and the $searchFieldList is "bodytext,header" then the output will be ' AND (bodytext LIKE "%content%" OR header LIKE "%content%") AND (bodytext LIKE "%management%" OR header LIKE "%management%") AND (bodytext LIKE "%system%" OR header LIKE "%system%")'.
     *
     * @param string $searchWords The search words. These will be separated by space and comma.
     * @param string $searchFieldList The fields to search in
     * @param string $searchTable The table name you search in (recommended for DBAL compliance. Will be prepended field names as well)
     *
     * @return CompositeExpression the WHERE clause
     */
    public static function searchWhere($searchWords, $searchFieldList, $searchTable)
    {
        if (!$searchWords) {
            return null;
        }

        $queryBuilder = static::getQueryBuilderForTable($searchTable);
        $prefixTableName = $searchTable ? $searchTable . '.' : '';
        $where = $queryBuilder->expr()->andX();
        $searchFields = explode(',', $searchFieldList);
        $searchWords = preg_split('/[ ,]/', $searchWords);
        foreach ($searchWords as $searchWord) {
            $searchWord = trim($searchWord);
            if (strlen($searchWord) < 3) {
                continue;
            }
            $searchWordConstraint = $queryBuilder->expr()->orX();
            $searchWord = $queryBuilder->escapeLikeWildcards($searchWord);
            foreach ($searchFields as $field) {
                $searchWordConstraint->add(
                    $queryBuilder->expr()->like($prefixTableName . $field, $queryBuilder->quote('%' . $searchWord . '%'))
                );
            }

            if ($searchWordConstraint->count()) {
                $where->add($searchWordConstraint);
            }
        }

        if ((string)$where === '') {
            return null;
        }

        return $where;
    }

    public static function accessGranted(
        Context $context,
        string $table,
        array $record,
        array $feUserRecord,
        bool $selfEdit = false
    ): bool
    {
        if (empty($feUserRecord)) {
            return false;
        }

        // If $selfEdit is set, fe_users may always edit themselves...
        if (
            $selfEdit &&
            $table == 'fe_users' &&
            (int) $feUserRecord['uid'] == (int) $record['uid']
        ) {
            $result = true;
        } else {
            $result =
                GeneralUtility::makeInstance(RecordAccessVoter::class)
                    ->groupAccessGranted($table, $record, $context);
        }

        return $result;
    }

    public static function getConnection(string $table): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }

    public static function getQueryBuilder(string $table): QueryBuilder
    {
        return static::getConnection($table)->createQueryBuilder();
    }
}
