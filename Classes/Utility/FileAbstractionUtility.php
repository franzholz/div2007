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
     * Gets the file records
     * looking up the MM relations of this record to the
     * table name defined in the local field 'table_name'.
     *
     * @return array
     */
    public static function getFileRecords(
        $tableName,
        $fieldName,
        array $uidArray = [],
        $orderBy = 'sorting_foreign'
    ) {
        $result = [];

        if (count($uidArray)) {
            $where_clause = 'uid_foreign IN (' . implode(',', $uidArray) . ') AND tablenames="' . $tableName . '" AND fieldname="' . $fieldName . '"';
            $where_clause .= TableUtility::enableFields('sys_file_reference');
            $result =
                $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                    '*',
                    'sys_file_reference',
                    $where_clause,
                    '',
                    $orderBy,
                    '',
                    'uid_local'
                );
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
