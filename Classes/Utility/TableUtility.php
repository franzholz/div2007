<?php

namespace JambageCom\Div2007\Utility;

/***************************************************************
*  Copyright notice
*
*  (c) 2026 Kasper Skårhøj (kasperYYYY@typo3.com)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * table functions. It requires TYPO3 6.2.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 *
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage div2007
 */
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryConstraintService;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;




class TableUtility
{
    /**
     * Fields that are considered as system.
     *
     * @var array
     */
    protected static $systemFields = [
        'uid',
        'pid',
        'tstamp',
        'crdate',
        'deleted',
        'hidden',
        'starttime',
        'endtime',
        'sys_language_uid',
        'l18n_parent',
        'l18n_diffsource',
        't3ver_oid',
        't3ver_wsid',
        't3ver_state',
        't3ver_stage',
        't3ver_count',
        't3ver_tstamp',
        't3_origuid',
    ];

    /**
     * @var string|int|null
     */
    public static $checkPid_badDoktypeList;


    /**
     * Returns select statement for MM relations (as used by TCEFORMs etc) . Code borrowed from class.t3lib_befunc.php
     * Usage: 3.
     *
     * @param	array           $fieldValue Configuration array for the field, taken from $TCA
     * @param	string $field   Field name
     * @param	array $TSconfig TSconfig array from which to get further configuration
     *                          settings for the field name
     * @param	string $prefix	Prefix string for the key "*foreign_table_where" from $fieldValue array
     *
     * @return	string		resulting where string with accomplished marker substitution in WHERE clause
     *
     * @see t3lib_transferData::renderRecord(), t3lib_TCEforms::foreignTable()
     */
    public static function foreign_table_where_query(array $fieldValue, string $field = '', array $TSconfig = [], string $prefix = ''): string
    {
        $foreign_table = $fieldValue['config'][$prefix . 'foreign_table'] ?? '';
        if ($foreign_table === '') {
            return '';
        }

        $fTWHERE = $fieldValue['config'][$prefix . 'foreign_table_where'] ?? '';

        // 1. Use the native core service to replace standard markers
        // This automatically handles: ###CURRENT_PID###, ###THIS_UID###, ###THIS_CID###, ###STORAGE_PID### and ###REC_FIELD_...###
        $queryConstraintService = GeneralUtility::makeInstance(QueryConstraintService::class);

        $currentRow = $TSconfig['_THIS_ROW'] ?? $TSconfig;
        $currentPid = (int)($TSconfig['_CURRENT_PID'] ?? 0);

        $fTWHERE = $queryConstraintService->processForeignTableWhereClause($fTWHERE, $foreign_table, $currentRow, $currentPid);

        // 2. Custom logic for PAGE_TSCONFIG_ markers (not covered by the core service)
        if (isset($TSconfig[$field]) && is_array($TSconfig[$field])) {
            // ###PAGE_TSCONFIG_ID###
            $fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', (string)(int)$TSconfig[$field]['PAGE_TSCONFIG_ID'], $fTWHERE);

            // ###PAGE_TSCONFIG_IDLIST###
            $idList = GeneralUtility::intExplode(',', $TSconfig[$field]['PAGE_TSCONFIG_IDLIST'] ?? '', true);
            $fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', implode(',', $idList), $fTWHERE);

            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($foreign_table);

            // Explicitly cast to string to prevent errors with empty values
            $rawStr = (string)($TSconfig[$field]['PAGE_TSCONFIG_STR'] ?? '');
            $quotedStr = $connection->quote($rawStr);

            // Since quote() wraps the string in single quotes ('my_value'),
            // we trim them here so it matches the behavior of the legacy quoteStr().
             $quotedStr = trim($quotedStr, "'");

            $fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', $quotedStr, $fTWHERE);
        } else {
            // Fallback replacements if no TSconfig context is available for the field
            $fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', (string)$currentPid, $fTWHERE);
            $fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', (string)$currentPid, $fTWHERE);
            $fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', '', $fTWHERE);
        }

        return $fTWHERE;
    }

    // SQL-related, selecting records, searching
    /**
     * Returns the WHERE clause " AND NOT [tablename].[deleted-field]" if a deleted-field
     * is configured in $GLOBALS['TCA'] for the tablename, $table
     * This function should ALWAYS be called in the backend for selection on tables which
     * are configured in $GLOBALS['TCA'] since it will ensure consistent selection of records,
     * even if they are marked deleted (in which case the system must always treat them as non-existent!)
     * In the frontend a function, ->enableFields(), is known to filter hidden-field, start- and endtime
     * and fe_groups as well. But that is a job of the frontend, not the backend. If you need filtering
     * on those fields as well in the backend you can use ->BEenableFields() though.
     *
     * @param string $table Table name present in $GLOBALS['TCA']
     * @param string $tableAlias Table alias if any
     *
     * @return string WHERE clause for filtering out deleted records, eg " AND tablename.deleted=0
     *
     * @deprecated since TYPO3 v9, will be removed in TYPO3 v10.0, the DeletedRestriction functionality should be used instead.
     */
    public static function deleteClause($table, $tableAlias = '')
    {
        if (empty($GLOBALS['TCA'][$table]['ctrl']['delete'])) {
            return '';
        }
        $expressionBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table)
            ->expr()
        ;

        return ' AND ' . $expressionBuilder->eq(
            ($tableAlias ?: $table) . '.' . $GLOBALS['TCA'][$table]['ctrl']['delete'],
            0
        );
    }

    /**
    * Generates a raw SQL WHERE clause snippet to match comma-separated frontend groups.
    *
    * Fully compatible with TYPO3 v13!
    *
    * @param string $field The database table field containing the comma-separated group list (e.g., 'usergroup')
    * @param string $table The database table name (e.g., 'tt_content')
    * @return string The SQL WHERE clause snippet starting with ' AND (...)'
    */
    public static function getMultipleGroupsWhereClause(string $field, string $table): string
    {
        // Retrieve group IDs safely from the UserAspect context (TYPO3 v13 standard)
        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');
        $groupIds = method_exists($userAspect, 'getGroupIds') ? $userAspect->getGroupIds() : [];

        // Ensure we have a clean array of integers
        $memberGroups = GeneralUtility::intExplode(',', implode(',', $groupIds), true);

        // Get the platform-specific database connection to securely quote literal values
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        $orChecks = [];

        // Check if the field is empty, null, or zero
        $orChecks[] = $connection->quoteIdentifier($field) . ' = ' . $connection->quote('');
        $orChecks[] = $connection->quoteIdentifier($field) . ' IS NULL';
        $orChecks[] = $connection->quoteIdentifier($field) . ' = 0';

        // Generates standard SQL FIND_IN_SET or platform equivalent via literal strings.
        foreach ($memberGroups as $value) {
            if ($value > 0) {
                $orChecks[] = 'FIND_IN_SET('
                    . $connection->quote((string)$value)
                    . ', '
                    . $connection->quoteIdentifier($field)
                    . ')';
            }
        }

        // Returns the exact string structure expected by your legacy calling functions
        return ' AND (' . implode(' OR ', $orChecks) . ')';
    }

    /**
     * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields set to values that should de-select them according to the current time, preview settings or user login. Generated by the QueryBuilder.
     **/
    public static function enableFieldsDefault(string $table): string
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);
        $constraints = $pageRepository->getDefaultConstraints($table);
        $plainWhereString = (string)$queryBuilder->expr()->and(...array_values($constraints));

        return $plainWhereString;
    }

    /**
     * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields set to values that should de-select them according to the current time, preview settings or user login. Definitely a frontend function.
     * Is using the $GLOBALS['TCA'] arrays "ctrl" part where the key "enablefields" determines for each table which of these features applies to that table.
     *
     * @param	string		Table name found in the $GLOBALS['TCA'] array
     * @param	int		If $show_hidden is set (0/1), any hidden-fields in records are ignored. NOTICE: If you call this function, consider what to do with the show_hidden parameter. Maybe it should be set? See TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer->enableFields where it's implemented correctly.
     * @param	array		Array you can pass where keys can be "disabled", "starttime", "endtime", "fe_group" (keys from "enablefields" in TCA) and if set they will make sure that part of the clause is not added. Thus disables the specific part of the clause. For previewing etc.
     * @param	bool		If set, enableFields will be applied regardless of any versioning preview settings which might otherwise disable enableFields
     *
     * @return	string		The clause starting like " AND ...=... AND ...=..."
     *
     * @see TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::enableFields(), deleteClause()
     */
    public static function enableFields($table, $show_hidden = -1, $ignore_array = [], $noVersionPreview = true)
    {
        if ($show_hidden == -1) { // If show_hidden was not set from outside, set it based on showHiddenPage and showHiddenRecords
            $show_hidden = $table == 'pages' ? CompatibilityUtility::includeHiddenPages() : CompatibilityUtility::includeHiddenContent();
        }
        if ($show_hidden == -1) {
            $show_hidden = 0;
        } // If show_hidden was not changed during the previous evaluation, do it here.

        $ctrl = $GLOBALS['TCA'][$table]['ctrl'];
        $query = '';
        if (is_array($ctrl)) {
            // Delete field check:
            if (isset($ctrl['delete'])) {
                $query .= ' AND ' . $table . '.' . $ctrl['delete'] . '=0';
            }

            // Filter out new place-holder records in case we are NOT in a versioning preview (that means we are online!)
            if (isset($ctrl['versioningWS']) && $noVersionPreview) {
                $query .= ' AND ' . $table . '.t3ver_state<=0 AND ' . $table . '.pid<>-1'; // Shadow state for new items MUST be ignored!
            }

            // Enable fields:
            if (isset($ctrl['enablecolumns']) && is_array($ctrl['enablecolumns'])) {
                if (empty($ctrl['versioningWS']) || $noVersionPreview) { // In case of versioning-preview, enableFields are ignored (checked in versionOL())
                    if (
                        !empty($ctrl['enablecolumns']['disabled']) &&
                        !$show_hidden &&
                        empty($ignore_array['disabled'])
                    ) {
                        $field = $table . '.' . $ctrl['enablecolumns']['disabled'];
                        $query .= ' AND ' . $field . '=0';
                    }
                    if (
                        !empty($ctrl['enablecolumns']['starttime']) &&
                        empty($ignore_array['starttime'])
                    ) {
                        $field = $table . '.' . $ctrl['enablecolumns']['starttime'];
                        $query .= ' AND ' . $field . '<=' . $GLOBALS['SIM_ACCESS_TIME'];
                    }
                    if (
                        !empty($ctrl['enablecolumns']['endtime']) &&
                        empty($ignore_array['endtime'])
                    ) {
                        $field = $table . '.' . $ctrl['enablecolumns']['endtime'];
                        $query .= ' AND (' . $field . '=0 OR ' . $field . '>' . $GLOBALS['SIM_ACCESS_TIME'] . ')';
                    }
                    if (
                        !empty($ctrl['enablecolumns']['fe_group']) &&
                        empty($ignore_array['fe_group'])
                    ) {
                        $field = $table . '.' . $ctrl['enablecolumns']['fe_group'];
                        $query .= static::getMultipleGroupsWhereClause($field, $table);
                    }

                    // Call hook functions for additional enableColumns
                    // It is used by the extension ingmar_accessctrl which enables assigning more than one usergroup to content and page records
                    if (
                        isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns']) &&
                        is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns'])
                    ) {
                        $_params = [
                            'table' => $table,
                            'show_hidden' => $show_hidden,
                            'ignore_array' => $ignore_array,
                            'ctrl' => $ctrl,
                        ];
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns'] as $_funcRef) {
                            $query .= GeneralUtility::callUserFunction($_funcRef, $_params, $tmp = 'TableUtility');
                        }
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException(
                'There is no entry in the $GLOBALS[\'TCA\'] array for the table "' . $table .
                '". This means that the function enableFields() is ' .
                'called with an invalid table name as argument.',
                1283790586
            );
        }

        return $query;
    }

    /**
    * Validates an array of page IDs against existing, active pages and excludes bad doktypes.
    *
    * @param array $listArr Array of page UIDs to check
    * @return array Array of valid and accessible page UIDs
    */
    public static function checkPidArray($listArr): array
    {
        $outArr = [];

        if (is_array($listArr) && count($listArr) > 0) {
            $table = 'pages';
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            // Ensure we only have a clean list of integers for the bad doktypes
            // Changed from $this->checkPid_badDoktypeList to static::$checkPid_badDoktypeList
            $badDoktypes = GeneralUtility::intExplode(',', static::$checkPid_badDoktypeList ?? '', true);

            $queryBuilder
                ->select('uid')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder->createNamedParameter($listArr, Connection::PARAM_INT_ARRAY)
                    )
                );

            // Append the doktype exclusion if bad doktypes are defined
            if (count($badDoktypes) > 0) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->notIn(
                        'doktype',
                        $queryBuilder->createNamedParameter($badDoktypes, Connection::PARAM_INT_ARRAY)
                    )
                );
            }

            try {
                $result = $queryBuilder->executeQuery();
                while ($row = $result->fetchAssociative()) {
                    $outArr[] = (int)$row['uid'];
                }
            } catch (\Exception $e) {
                // Modern TYPO3 replacement for TimeTracker/sql_error logging
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(static::class);
                $logger->error($e->getMessage(), [
                    'sql' => $queryBuilder->getSQL(),
                    'parameters' => $queryBuilder->getParameters()
                ]);
            }
        }

        return $outArr;
    }

    /**
     * @return array
     */
    public static function getSystemFields()
    {
        return static::$systemFields;
    }

    /**
     * Returns an array containing the regular field names.
     *
     * @return array
     */
    public static function getFields($table, $prefix = false)
    {
        $result = false;

        if (
            isset($GLOBALS['TCA'][$table]['columns']) &&
            is_array($GLOBALS['TCA'][$table]['columns'])
        ) {
            $tcaFields = array_keys($GLOBALS['TCA'][$table]['columns']);
            $systemFields = static::getSystemFields();
            $result = array_diff($tcaFields, $systemFields);
            if ($prefix) {
                $prefixArray = [];
                foreach ($result as $key => $value) {
                    $prefixArray[] = $table . '.' . $value;
                }
                $result = $prefixArray;
            }
        }

        return $result;
    }

    /**
     * Returns informations about the table and foreign table
     * This is used by various tables.
     *
     * @param	string		name of the table
     * @param	string		field of the table
     *
     * @return	array		infos about the table and foreign table:
     * table         ... name of the table
     * foreign_table ... name of the foreign table
     * foreign_table_field ... name of the field which contains the table name of the first table
     * mmtable       ... name of the mm table
     * foreign_field ... name of the field in the mm table which joins with
     * the foreign table
     *
     * @access	public
     */
    public static function getForeignTableInfo($tablename, $fieldname)
    {
        $result = [];
        if (
            $tablename != '' &&
            $fieldname != '' &&
            isset($GLOBALS['TCA'][$tablename]['columns'][$fieldname]['config'])
        ) {
            $tableConf = $GLOBALS['TCA'][$tablename]['columns'][$fieldname]['config'];
            $localFieldname = '';
            $foreignFieldname = '';
            $foreignTableFieldname = '';
            $foreignTable = '';
            $mmTablename = '';
            $mmTableConf = '';

            $type = $tableConf['type'];
            if ($type == 'group') {
                $type = 'select';
            }

            if ($type == 'inline' && !isset($tableConf['MM'])) {  // This method is wrong for a mm table.
                $mmTablename = $tableConf['foreign_table'];
                $localFieldname = $tableConf['foreign_field'];
                $foreignFieldname = $tableConf['foreign_selector'];
                $foreignTableFieldname = $tableConf['foreign_table_field'];
            } elseif (
                isset($tableConf['MM'])
            ) {
                $mmTablename = $tableConf['MM'];
                $localFieldname = 'uid_local';
                $foreignFieldname = 'uid_foreign';
            }

            if ($foreignFieldname != '') {
                $mmTableConf = $GLOBALS['TCA'][$mmTablename]['columns'][$foreignFieldname]['config'] ?? '';
            }

            if (
                $type == 'inline' &&
                is_array($mmTableConf) &&
                !isset($tableConf['MM'] // This method is wrong for a mm table.
                )) {
                $foreignTable = $mmTableConf['foreign_table'];
            } else {
                $foreignTable = $tableConf['foreign_table'];
            }

            $result['table'] = $tablename;
            $result['foreign_table'] = $foreignTable;
            $result['foreign_table_field'] = $foreignTableFieldname;
            $result['mmtable'] = $mmTablename;
            $result['local_field'] = $localFieldname;
            $result['foreign_field'] = $foreignFieldname;
            if (isset($tableConf['foreign_sortby'])) {
                $result['foreign_sortby'] = $tableConf['foreign_sortby'];
            }
        }

        return $result;
    }

    /**
     * Determine the recursive page ids including the given root page id.
     *
     * @param int $uid  root page id
     *
     * @return array
     */
    public static function getAllSubPages($uid)
    {
        $uidArray = [];
        $result = [];

        if (MathUtility::canBeInterpretedAsInteger($uid)) {
            $uidArray[] = $uid;
        } else {
            $uids = GeneralUtility::trimExplode(',', $uid);
            foreach ($uids as $currentUid) {
                if (MathUtility::canBeInterpretedAsInteger($currentUid)) {
                    $uidArray[] = $currentUid;
                }
            }
        }

        foreach ($uidArray as $currentUid) {
            $records = PageRepository::getRecordsByField(
                'pages',
                'pid',
                $currentUid
            );
            $result[] = $currentUid;
            if (count($records) > 0) {
                foreach ($records as $record) {
                    $result = array_merge($result, static::getAllSubPages($record['uid']));
                }
            }
        }
        $result = array_unique($result);

        return $result;
    }
}
