<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2019 Franz Holzinger (franz@ttproducts.de)
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
 *
 * functions for the TYPO3 File Abstraction Layer (FAL)
 *
 * @author Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */



class FileAbstractionUtility {

    /**
    * Gets the file records
    * looking up the MM relations of this record to the
    * table name defined in the local field 'table_name'.
    *
    * @return array
    */
    static public function getFileRecords (
        $tableName,
        $fieldName,
        array $uidArray = array(),
        $orderBy = 'sorting'
    )
    {
        $result = array();

        if (count($uidArray)) {
            $where_clause = 'uid_foreign IN (' . implode(',', $uidArray) . ') AND tablenames="' . $tableName . '" AND fieldname="' . $fieldName . '"' ;
            $where_clause .= \JambageCom\Div2007\Utility\TableUtility::enableFields('sys_file_reference');
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

    static public function getFileInfo (
        &$fileObj,
        &$fileInfo,
        $fileReferenceUid
    ) {
        $result = false;

        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\StorageRepository');
        $storage = $storageRepository->findByUid(1);
        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $fileObj = $resourceFactory->getFileReferenceObject(intval($fileReferenceUid));

        if (is_object($fileObj)) {
            $fileInfo = $storage->getFileInfo($fileObj);
            $result = true;
        }

        return $result;
    }

    
    /**
     * Create folder
     *
     * @param $path
     * @return void
     * @throws \Exception
     */
    static public function createFolderIfNotExists ($path)
    {
        $result = true;

        if (!is_dir($path) && !GeneralUtility::mkdir($path)) {
            $result = false;
        }

        return $result;
    }

}


