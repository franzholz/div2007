<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2016 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * functions for the TYPO3 extension static_info_tables.
 *
 * @author Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryHelper;


class SystemCategoryUtility
{
    public const type_local = 0;
    public const type_foreign = 1;

    /**
     * The table name collections are stored to.
     *
     * @var string
     */
    protected static $storageTableName = 'sys_category';

    /**
     * Name of the categories-relation field (used in the MM_match_fields/fieldname property of the TCA).
     *
     * @var string
     */
    protected static $relationFieldName = 'categories';

    /**
     * Gets the uids by
     * looking up the MM relations of this record to the
     * table name defined in the local field 'table_name'.
     *
     * @return array
     */
    public static function getForeignUids(
        $tableName,
        $fieldName,
        array $uidArray = [],
        $orderBy = ''
    ) {
        return static::getUids($tableName, $fieldName, $uidArray, $orderBy, type_foreign);
    }

    /**
    * Gets the uids by
    * looking up the MM relations of this record to the
    * table name defined in the local field 'table_name'.
    *
    * @param string $tableName Name der Datentabelle (z.B. tt_content, pages)
    * @param string $fieldName Feldname der Relation
    * @param string $type Entweder 'type_local' oder 'type_foreign' (Nutzen Sie am besten Strings/Konstanten)
    * @param array $uidArray Liste von UIDs zur Einschränkung
    * @param string $orderBy Optionale Sortierung
    * @return array Liste von reinen UIDs (Integers)
    */
    public static function getUids(
        $tableName,
        $fieldName,
        $type = 'type_local',
        array $uidArray = [],
        $orderBy = ''
    ): array {
        $relatedRecords = [];
        $storageTable = static::$storageTableName;
        $inputTable = $tableName;
        $outputTable = $storageTable;

        if ($type === 'type_foreign') {
            $inputTable = $storageTable;
            $outputTable = $tableName;
        }

        $mmTable = 'sys_category_record_mm';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($mmTable);

        $queryBuilder
            ->select($outputTable . '.uid')
            ->distinct()
            ->from($mmTable);

        $queryBuilder->innerJoin(
            $mmTable,
            $storageTable,
            $storageTable,
            $queryBuilder->expr()->eq(
                $mmTable . '.uid_local', $queryBuilder->quoteIdentifier($storageTable . '.uid')
            )
        );

        $queryBuilder->innerJoin(
            $mmTable,
            $tableName,
            $tableName,
            $queryBuilder->expr()->eq(
                $mmTable . '.uid_foreign', $queryBuilder->quoteIdentifier($tableName . '.uid')
            )
        );

        $queryBuilder->where(
            $queryBuilder->expr()->eq(
                $mmTable . '.tablenames',
                $queryBuilder->createNamedParameter($tableName)
            ),
            $queryBuilder->expr()->eq(
                $mmTable . '.fieldname',
                $queryBuilder->createNamedParameter($fieldName)
            )
        );

        if (!empty($uidArray)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    $inputTable . '.uid',
                    $queryBuilder->createNamedParameter(
                        $uidArray,
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );
        }

        if ($orderBy !== '') {
            foreach (QueryHelper::parseOrderBy($orderBy) as $orderPair) {
                [$orderField, $direction] = $orderPair;
                $queryBuilder->addOrderBy($orderField, $direction);
            }
        }

        $result = $queryBuilder->executeQuery();
        while ($record = $result->fetchAssociative()) {
            $relatedRecords[] = (int)$record['uid'];
        }

        return $relatedRecords;
    }
}
