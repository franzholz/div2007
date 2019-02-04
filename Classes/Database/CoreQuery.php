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


use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

use TYPO3\CMS\Core\Utility\GeneralUtility;


class CoreQuery {
    /**
     * @var TypoScriptFrontendController
     */
    static protected $typoScriptFrontendController = null;

    /**
     * @param TypoScriptFrontendController $typoScriptFrontendController
     */
    public function __construct (TypoScriptFrontendController $typoScriptFrontendController = null)
    {
        if (is_object($typoScriptFrontendController)) {
            static::setTypoScriptFrontendController($typoScriptFrontendController);
        }
    }


    static public function setTypoScriptFrontendController (TypoScriptFrontendController $typoScriptFrontendController)
    {
        static::$typoScriptFrontendController = $typoScriptFrontendController;
    }

    /***********************************************
     *
     * Database functions, making of queries
     *
     ***********************************************/
    /**
     * Returns an UPDATE/DELETE sql query which will "delete" the record.
     * If the $GLOBALS['TCA'] config for the table tells us to NOT "physically" delete the record but rather set the "deleted" field to "1" then an UPDATE query is returned doing just that. Otherwise it truely is a DELETE query.
     *
     * @param string $table The table name, should be in $GLOBALS['TCA']
     * @param int $uid The UID of the record from $table which we are going to delete
     * @param bool $doExec If set, the query is executed. IT'S HIGHLY RECOMMENDED TO USE THIS FLAG to execute the query directly!!!
     * @return string The query, ready to execute unless $doExec was TRUE in which case the return value is FALSE.
     * @see DBgetUpdate(), DBgetInsert(), user_feAdmin
     */
    static public function DBgetDelete ($table, $uid, $doExec = false)
    {
        $uid = (int)$uid;
        if (!$uid) {
            return '';
        }
        $db = static::getDatabaseConnection();
        if ($GLOBALS['TCA'][$table]['ctrl']['delete']) {
            $updateFields = [];
            $updateFields[$GLOBALS['TCA'][$table]['ctrl']['delete']] = 1;
            if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
                $updateFields[$GLOBALS['TCA'][$table]['ctrl']['tstamp']] = $GLOBALS['EXEC_TIME'];
            }
            if ($doExec) {
                return $db->exec_UPDATEquery($table, 'uid=' . $uid, $updateFields);
            } else {
                return $db->UPDATEquery($table, 'uid=' . $uid, $updateFields);
            }
        } elseif ($doExec) {
            return $db->exec_DELETEquery($table, 'uid=' . $uid);
        } else {
            return $db->DELETEquery($table, 'uid=' . $uid);
        }
    }

    /**
     * Returns an UPDATE sql query.
     * If a "tstamp" field is configured for the $table tablename in $GLOBALS['TCA'] then that field is automatically updated to the current time.
     * Notice: It is YOUR responsibility to make sure the data being updated is valid according the tablefield types etc. Also no logging is performed of the update. It's just a nice general usage API function for creating a quick query.
     * NOTICE: From TYPO3 3.6.0 this function ALWAYS adds slashes to values inserted in the query.
     *
     * @param string $table The table name, should be in $GLOBALS['TCA']
     * @param int $uid The UID of the record from $table which we are going to update
     * @param array $dataArray The data array where key/value pairs are fieldnames/values for the record to update.
     * @param string $fieldList Comma list of fieldnames which are allowed to be updated. Only values from the data record for fields in this list will be updated!!
     * @param bool $doExec If set, the query is executed. IT'S HIGHLY RECOMMENDED TO USE THIS FLAG to execute the query directly!!!
     * @return string The query, ready to execute unless $doExec was TRUE in which case the return value is FALSE.
     * @see DBgetInsert(), DBgetDelete(), user_feAdmin
     */
    static public function DBgetUpdate ($table, $uid, array $dataArray, $fieldList, $doExec = false)
    {
        // uid can never be set
        unset($dataArray['uid']);
        $uid = (int) $uid;
        if ($uid) {
            $fieldList = implode(',', GeneralUtility::trimExplode(',', $fieldList, true));
            $updateFields = [];
            foreach ($dataArray as $f => $v) {
                if (GeneralUtility::inList($fieldList, $f)) {
                    $updateFields[$f] = $v;
                }
            }
            if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
                $updateFields[$GLOBALS['TCA'][$table]['ctrl']['tstamp']] = $GLOBALS['EXEC_TIME'];
            }
            if (!empty($updateFields)) {
                if ($doExec) {
                    return static::getDatabaseConnection()->exec_UPDATEquery($table, 'uid=' . $uid, $updateFields);
                }
                return static::getDatabaseConnection()->UPDATEquery($table, 'uid=' . $uid, $updateFields);
            }
        }
        return '';
    }

    /**
     * Returns an INSERT sql query which automatically added "system-fields" according to $GLOBALS['TCA']
     * Automatically fields for "tstamp", "crdate", "cruser_id", "fe_cruser_id" and "fe_crgroup_id" is updated if they are configured in the "ctrl" part of $GLOBALS['TCA'].
     * The "pid" field is overridden by the input $pid value if >= 0 (zero). "uid" can never be set as a field
     * NOTICE: From TYPO3 3.6.0 this function ALWAYS adds slashes to values inserted in the query.
     *
     * @param string $table The table name, should be in $GLOBALS['TCA']
     * @param int $pid The PID value for the record to insert
     * @param array $dataArray The data array where key/value pairs are fieldnames/values for the record to insert
     * @param string $fieldList Comma list of fieldnames which are allowed to be inserted. Only values from the data record for fields in this list will be inserted!!
     * @param bool $doExec If set, the query is executed. IT'S HIGHLY RECOMMENDED TO USE THIS FLAG to execute the query directly!!!
     * @return string The query, ready to execute unless $doExec was TRUE in which case the return value is FALSE.
     * @see DBgetUpdate(), DBgetDelete(), user_feAdmin
     */
    static public function DBgetInsert ($table, $pid, array $dataArray, $fieldList, $doExec = false)
    {
        $extraList = 'pid';
        if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
            $field = $GLOBALS['TCA'][$table]['ctrl']['tstamp'];
            $dataArray[$field] = $GLOBALS['EXEC_TIME'];
            $extraList .= ',' . $field;
        }
        if ($GLOBALS['TCA'][$table]['ctrl']['crdate']) {
            $field = $GLOBALS['TCA'][$table]['ctrl']['crdate'];
            $dataArray[$field] = $GLOBALS['EXEC_TIME'];
            $extraList .= ',' . $field;
        }
        if ($GLOBALS['TCA'][$table]['ctrl']['cruser_id']) {
            $field = $GLOBALS['TCA'][$table]['ctrl']['cruser_id'];
            $dataArray[$field] = 0;
            $extraList .= ',' . $field;
        }
        if ($GLOBALS['TCA'][$table]['ctrl']['fe_cruser_id']) {
            $field = $GLOBALS['TCA'][$table]['ctrl']['fe_cruser_id'];
            $dataArray[$field] = (int) static::getTypoScriptFrontendController()->fe_user->user['uid'];
            $extraList .= ',' . $field;
        }
        if ($GLOBALS['TCA'][$table]['ctrl']['fe_crgroup_id']) {
            $field = $GLOBALS['TCA'][$table]['ctrl']['fe_crgroup_id'];
            list($dataArray[$field]) = explode(',', static::getTypoScriptFrontendController()->fe_user->user['usergroup']);
            $dataArray[$field] = (int)$dataArray[$field];
            $extraList .= ',' . $field;
        }
        // Uid can never be set
        unset($dataArray['uid']);
        if ($pid >= 0) {
            $dataArray['pid'] = $pid;
        }
        // Set pid < 0 and the dataarr-pid will be used!
        $fieldList = implode(',', GeneralUtility::trimExplode(',', $fieldList . ',' . $extraList, true));
        $insertFields = [];
        foreach ($dataArray as $f => $v) {
            if (GeneralUtility::inList($fieldList, $f)) {
                $insertFields[$f] = $v;
            }
        }

        if ($doExec) {
            return static::getDatabaseConnection()->exec_INSERTquery($table, $insertFields);
        } else {
            return static::getDatabaseConnection()->INSERTquery($table, $insertFields);
        }
    }

    /**
     * Checks if a frontend user is allowed to edit a certain record
     *
     * @param string $table The table name, found in $GLOBALS['TCA']
     * @param array $row The record data array for the record in question
     * @param array $feUserRow The array of the fe_user which is evaluated, typ. $GLOBALS['TSFE']->fe_user->user
     * @param string $allowedGroups Commalist of the only fe_groups uids which may edit the record. If not set, then the usergroup field of the fe_user is used.
     * @param bool|int $feEditSelf TRUE, if the fe_user may edit his own fe_user record.
     * @return bool
     * @see user_feAdmin
     */
    static public function DBmayFEUserEdit ($table, $row, $feUserRow, $allowedGroups = '', $feEditSelf = 0)
    {
        if ($allowedGroups) {
            $groupList = implode(
                ',',
                array_intersect(
                    GeneralUtility::trimExplode(',', $feUserRow['usergroup'], true),
                    GeneralUtility::trimExplode(',', $allowedGroups, true)
                )
            );
        } else {
            $groupList = $feUserRow['usergroup'];
        }
        $ok = 0;
        // Points to the field that allows further editing from frontend if not set. If set the record is locked.
        if (!$GLOBALS['TCA'][$table]['ctrl']['fe_admin_lock'] || !$row[$GLOBALS['TCA'][$table]['ctrl']['fe_admin_lock']]) {
            // Points to the field (int) that holds the fe_users-id of the creator fe_user
            if ($GLOBALS['TCA'][$table]['ctrl']['fe_cruser_id']) {
                $rowFEUser = (int)$row[$GLOBALS['TCA'][$table]['ctrl']['fe_cruser_id']];
                if ($rowFEUser && $rowFEUser === (int)$feUserRow['uid']) {
                    $ok = 1;
                }
            }
            // If $feEditSelf is set, fe_users may always edit them selves...
            if ($feEditSelf && $table === 'fe_users' && (int)$feUserRow['uid'] === (int)$row['uid']) {
                $ok = 1;
            }
            // Points to the field (int) that holds the fe_group-id of the creator fe_user's first group
            if ($GLOBALS['TCA'][$table]['ctrl']['fe_crgroup_id']) {
                $rowFEUser = (int)$row[$GLOBALS['TCA'][$table]['ctrl']['fe_crgroup_id']];
                if ($rowFEUser) {
                    if (GeneralUtility::inList($groupList, $rowFEUser)) {
                        $ok = 1;
                    }
                }
            }
        }
        return $ok;
    }

    /**
     * Returns part of a where clause for selecting records from the input table name which the user may edit.
     * Conceptually close to the function DBmayFEUserEdit(); It does the same thing but not for a single record,
     * rather for a select query selecting all records which the user HAS access to.
     *
     * @param string $table The table name
     * @param array $feUserRow The array of the fe_user which is evaluated, typ. $GLOBALS['TSFE']->fe_user->user
     * @param string $allowedGroups Commalist of the only fe_groups uids which may edit the record. If not set, then the usergroup field of the fe_user is used.
     * @param bool|int $feEditSelf TRUE, if the fe_user may edit his own fe_user record.
     * @return string The where clause part. ALWAYS returns a string. If no access at all, then " AND 1=0
     * @see DBmayFEUserEdit(), user_feAdmin::displayEditScreen()
     */
    static public function DBmayFEUserEditSelect ($table, $feUserRow, $allowedGroups = '', $feEditSelf = 0)
    {
        // Returns where-definition that selects user-editable records.
        if ($allowedGroups) {
            $groupList = implode(
                ',',
                array_intersect(
                    GeneralUtility::trimExplode(',', $feUserRow['usergroup'], true),
                    GeneralUtility::trimExplode(',', $allowedGroups, true)
                )
            );
        } else {
            $groupList = $feUserRow['usergroup'];
        }
        $OR_arr = [];
        // Points to the field (int) that holds the fe_users-id of the creator fe_user
        if ($GLOBALS['TCA'][$table]['ctrl']['fe_cruser_id']) {
            $OR_arr[] = $GLOBALS['TCA'][$table]['ctrl']['fe_cruser_id'] . '=' . $feUserRow['uid'];
        }
        // Points to the field (int) that holds the fe_group-id of the creator fe_user's first group
        if ($GLOBALS['TCA'][$table]['ctrl']['fe_crgroup_id']) {
            $values = GeneralUtility::intExplode(',', $groupList);
            foreach ($values as $theGroupUid) {
                if ($theGroupUid) {
                    $OR_arr[] = $GLOBALS['TCA'][$table]['ctrl']['fe_crgroup_id'] . '=' . $theGroupUid;
                }
            }
        }
        // If $feEditSelf is set, fe_users may always edit them selves...
        if ($feEditSelf && $table === 'fe_users') {
            $OR_arr[] = 'uid=' . (int)$feUserRow['uid'];
        }
        $whereDef = ' AND 1=0';
        if (!empty($OR_arr)) {
            $whereDef = ' AND (' . implode(' OR ', $OR_arr) . ')';
            if ($GLOBALS['TCA'][$table]['ctrl']['fe_admin_lock']) {
                $whereDef .= ' AND ' . $GLOBALS['TCA'][$table]['ctrl']['fe_admin_lock'] . '=0';
            }
        }
        return $whereDef;
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    static protected function getDatabaseConnection ()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    static protected function getTypoScriptFrontendController ()
    {
        return static::$typoScriptFrontendController ?: $GLOBALS['TSFE'];
    }
}

