<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2022 Franz Holzinger (franz@ttproducts.de)
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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;



/**
 * functions for the TYPO3 File Abstraction Layer (FAL).
 *
 * @author Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
class FileAbstractionUtility
{
    /**
     * Gets the referenced file records (sys_file_reference)
     * looking up the MM relations of this record to the
     * table name defined in the local field 'table_name'.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param array $uidArray
     * @param string $orderBy
     * @return array
     */
    public function getFileRecords(
        string $tableName,
        string $fieldName,
        array $uidArray = [],
        string $orderBy = 'sorting_foreign'
    ): array {
        $result = [];

        if (count($uidArray) > 0) {
            $table = 'sys_file_reference';
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->in(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($uidArray, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableName)
                ),
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter($fieldName)
                )
            );

            if ($orderBy !== '') {
                foreach (QueryHelper::parseOrderBy($orderBy) as $orderPair) {
                    [$fieldName, $direction] = $orderPair;
                    $queryBuilder->addOrderBy($fieldName, $direction);
                }
            }

            $statement = $queryBuilder->executeQuery();
            while ($row = $statement->fetchAssociative()) {
                $key = $row['uid_local'] ?? count($result);
                $result[$key] = $row;
            }
        }

        return $result;
    }

    public static function getFileInfo(
        &$fileObj,
        &$fileInfo,
        $fileReferenceUid
    ) {
        $result = false;

        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $fileObj = $resourceFactory->getFileReferenceObject(intval($fileReferenceUid));

        if (is_object($fileObj)) {
            $fileInfo = $fileObj->getStorage()->getFileInfo($fileObj);
            $result = true;
        }

        return $result;
    }

    /**
     * Create folder.
     *
     * @throws \Exception
     */
    public static function createFolderIfNotExists($path)
    {
        $result = true;

        if (!is_dir($path) && !GeneralUtility::mkdir($path)) {
            $result = false;
        }

        return $result;
    }
}
