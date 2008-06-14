<?php
/**
 * Collection of static functions to work in cooperation with the extension lib (lib/div)
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 Elmar Hinz
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    TYPO3
 * @subpackage div2007
 * @copyright  2006-2008 Elmar Hinz
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Id: class.tx_div_alpha.php 6790 2007-10-05 18:52:14Z franzholz $
 * @since      0.1
 */

/**
 * Collection of static functions contributed by different people 
 *
 * This class contains diverse staticfunctions in "alphpa" status.
 * It is a kind of quarantine for newly suggested functions.
 *
 * The class offers the possibilty to quickly add new functions to div,
 * without much planning before. In a second step the functions will be reviewed,
 * adapted and fully implemented into the system of lib/div classes.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     different Members of Extension Coordination Team
 */

class tx_div2007_alpha {

	/**
	 * Returns informations about the table and foreign table
	 * This is used by various tables.
	 *
	 * @param	string		name of the table
	 * @param	string		field of the table
	 * 				
	 * @return	array		infos about the table and foreign table:
					table         ... name of the table
					foreign_table ... name of the foreign table
					mmtable       ... name of the mm table 
					foreign_field ... name of the field in the mm table which joins with
					                  the foreign table
	 * @access	public
	 * 
	 */
	function getForeignTableInfo_fh001 ($functablename,$fieldname)	{
		global $TCA;

		$cnf = &t3lib_div::getUserObj('&tx_ttproducts_config');
		$tablename = $cnf->getTableName ($functablename);
		$tableConf = $TCA[$tablename]['columns'][$fieldname]['config'];

		if ($tableConf['type'] == 'inline') 	{
			$mmTablename = $tableConf['foreign_table'];
			$foreignFieldname = $tableConf['foreign_selector'];
		} else if ($tableConf['type'] == 'select') 	{
			$mmTablename = $tableConf['MM'];
			$foreignFieldname = 'uid_foreign';
		}
		$mmTableConf = $TCA[$mmTablename]['columns'][$foreignFieldname]['config'];
		$rc = array();
		$rc['table'] = $tablename;
		$rc['foreign_table'] = $mmTableConf['foreign_table'];
		$rc['mmtable'] = $mmTablename;
		$rc['foreign_field'] = $foreignFieldname;
		return $rc;
	}


	/**
	 * Returns informations about the table and foreign table
	 * This is used by IRRE compatible tables.
	 *
	 * @param	string		name of the table
	 * @param	string		field of the table
	 * @param	string		reference to the mm table
	 * @param	string		reference to the foreign field
	 * @param	string		reference to the foreign selector
	 * @param	string		field of the table
	 * @param	string		field of the table
	 * 				
	 * @return	void
	 * @access	public
	 * 
	 */
	function getTablenames_fh001 ($theTable, $field, &$foreignMMtable, &$foreignField, &$foreignSelector, &$foreignTable, $bIsMMRelation=TRUE) {
		global $TCA;

		$foreignMMtable = $TCA[$theTable]['columns'][$field]['config']['foreign_table'];
		$foreignField = $TCA[$theTable]['columns'][$field]['config']['foreign_field'];
		$foreignSelector = $TCA[$theTable]['columns'][$field]['config']['foreign_selector'];
		$foreignTable = $TCA[$foreignMMtable]['columns'][$foreignSelector]['config']['foreign_table'];

		if ($bIsMMRelation && (!$foreignMMtable || !$foreignField || !$foreignSelector || !$foreignTable))	{
			die ('internal error: no #2 TCA tables for field \''.$field.'\' of table \''.$theTable.'\' are missing.  $foreignMMtable='.$foreignMMtable.'  $foreignField='.$foreignField.'  $foreignSelector='.$foreignSelector.'  $foreignTable='.$foreignTable);
		}
	}


	/**
	 * Returns the help page with a mini guide how to setup the extension
	 * 
	 * example:
	 * 	$content .= tx_fhlibrary_view::displayHelpPage($this->cObj->fileResource('EXT:'.TT_PRODUCTS_EXTkey.'/template/products_help.tmpl'));
	 * 	unset($this->errorMessage);
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		path and filename of the template file
	 * 				
	 * @return	string		HTML to display the help page
	 * @access	public
	 * 
	 * @see fhlibrary_pibase::pi_displayHelpPage
	 */
	function displayHelpPage_fh001(&$langObj, $helpTemplate, $extKey, $errorMessage='', $theCode='') {
			// Get language version
		$helpTemplate_lang='';
		if ($langObj->LLkey)	{
			$helpTemplate_lang = $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_'.$langObj->LLkey.'###');
		}

		$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang : $this->cObj->getSubpart($helpTemplate,'###TEMPLATE_DEFAULT###');
			// Markers and substitution:
		$markerArray['###PATH###'] = t3lib_extMgm::siteRelPath($extKey);
		$markerArray['###ERROR_MESSAGE###'] = ($errorMessage ? '<b>'.$errorMessage.'</b><br/>' : '');
		$markerArray['###CODE###'] = $theCode;
		$rc = $langObj->cObj->substituteMarkerArray($helpTemplate,$markerArray);
		return $rc;
	}


	/* loadTcaAdditions($ext_keys)
	*
	* Your extension may depend on fields that are added by other
	* extensios. For reasons of performance parts of the TCA are only
	* loaded on demand. To ensure that the extended TCA is loaded for
	* the extensions you depend on or which extend your extension by
	* hooks, you shall apply this function.
	*
	* @param array     extension keys which have TCA additions to load
	*/
	function loadTcaAdditions_fh001($ext_keys){
		global $_EXTKEY, $TCA;

		//Merge all ext_keys
		if (is_array($ext_keys)) {
			for($i = 0; $i < sizeof($ext_keys); $i++)	{
				if (t3lib_extMgm::isLoaded($ext_keys[$i]))	{
					//Include the ext_table
					$_EXTKEY = $ext_keys[$i];
					include(t3lib_extMgm::extPath($ext_keys[$i]).'ext_tables.php');
				}
			}
		}
	}


	/**
	 * Gets information for an extension, eg. version and most-recently-edited-script
	 *
	 * @param	string		Extension key
	 * @return	array		Information array (unless an error occured)
	 */
	function getExtensionInfo_fh001($extKey)	{
		$rc = '';

		if (t3lib_extMgm::isLoaded($extKey))	{
			$path = t3lib_extMgm::extPath($extKey);
			$file = $path.'/ext_emconf.php';
			if (@is_file($file))	{
				$_EXTKEY = $extKey;
				$EM_CONF = array();
				include($file);

				$eInfo = array();
					// Info from emconf:
				$eInfo['title'] = $EM_CONF[$extKey]['title'];
				$eInfo['author'] = $EM_CONF[$extKey]['author'];
				$eInfo['author_email'] = $EM_CONF[$extKey]['author_email'];
				$eInfo['author_company'] = $EM_CONF[$extKey]['author_company'];
				$eInfo['version'] = $EM_CONF[$extKey]['version'];
				$eInfo['CGLcompliance'] = $EM_CONF[$extKey]['CGLcompliance'];
				$eInfo['CGLcompliance_note'] = $EM_CONF[$extKey]['CGLcompliance_note'];
				if (is_array($EM_CONF[$extKey]['constraints']) && is_array($EM_CONF[$extKey]['constraints']['depends']))	{
					$eInfo['TYPO3_version'] = $EM_CONF[$extKey]['constraints']['depends']['typo3'];
				} else {
					$eInfo['TYPO3_version'] = $EM_CONF[$extKey]['TYPO3_version'];
				}
				$filesHash = unserialize($EM_CONF[$extKey]['_md5_values_when_last_written']);
				$eInfo['manual'] = @is_file($path.'/doc/manual.sxw');
				$rc = $eInfo;
			} else {
				$rc = 'ERROR: No emconf.php file: '.$file;
			}
		} else {
			$rc = 'Error: Extension '.$extKey.' has not been installed. (tx_fhlibrary_system::getExtensionInfo)';
		}

		return $rc;
	}


	/**
	 * This is the original pi_getLL from tslib_pibase
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If true, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG.
	 */
	function getLL(&$langObj,$key,$alt='',$hsc=FALSE)	{
		if (isset($langObj->LOCAL_LANG[$langObj->LLkey][$key]))	{
			$word = $GLOBALS['TSFE']->csConv($langObj->LOCAL_LANG[$langObj->LLkey][$key], $langObj->LOCAL_LANG_charset[$langObj->LLkey][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif ($langObj->altLLkey && isset($langObj->LOCAL_LANG[$langObj->altLLkey][$key]))	{
			$word = $GLOBALS['TSFE']->csConv($langObj->LOCAL_LANG[$langObj->altLLkey][$key], $langObj->LOCAL_LANG_charset[$langObj->altLLkey][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif (isset($langObj->LOCAL_LANG['default'][$key]))	{
			$word = $langObj->LOCAL_LANG['default'][$key];	// No charset conversion because default is english and thereby ASCII
		} else {
			$word = $langObj->LLtestPrefixAlt.$alt;
		}
		$output = $langObj->LLtestPrefix.$word;
		if ($hsc)	$output = htmlspecialchars($output);

		return $output;
	}


	/**
	 * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($this->scriptRelPath) and if found includes it.
	 * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.php" file.
	 * Allows to add a language file name like this: 'EXT:tt_products/locallang_db.xml'
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		relative path and filename of the language file
	 * @param	boolean		overwrite ... if current settings should be overwritten
	 * 
	 * @return	void
	 */
	function loadLL_fh001(&$langObj,$langFileParam,$overwrite=TRUE)	{
		$langFile = ($langFileParam ? $langFile = $langFileParam : 'locallang.php');

		if ($langObj->scriptRelPath)	{
			if (substr($langFile,0,4)==='EXT:')	{
				$basePath = $langFile;
			} else {
				$basePath = t3lib_extMgm::extPath($langObj->extKey).dirname($langObj->scriptRelPath).'/'.$langFile;
			}

				// php or xml as source: In any case the charset will be that of the system language.
				// However, this function guarantees only return output for default language plus the specified language (which is different from how 3.7.0 dealt with it)
			$tempLOCAL_LANG = t3lib_div::readLLfile($basePath,$langObj->LLkey);
			if (count($langObj->LOCAL_LANG) && is_array($tempLOCAL_LANG))	{
				foreach ($langObj->LOCAL_LANG as $langKey => $tempArray)	{
					if (is_array($tempLOCAL_LANG[$langKey]))	{
						if ($overwrite)	{
							$langObj->LOCAL_LANG[$langKey] = array_merge($langObj->LOCAL_LANG[$langKey],$tempLOCAL_LANG[$langKey]);
						} else {
							$langObj->LOCAL_LANG[$langKey] = array_merge($tempLOCAL_LANG[$langKey], $langObj->LOCAL_LANG[$langKey]);
						}
					}
				}
			} else {
				$langObj->LOCAL_LANG = $tempLOCAL_LANG; 
			}
			if ($langObj->altLLkey)	{
				$tempLOCAL_LANG = t3lib_div::readLLfile($basePath,$langObj->altLLkey);

				if (count($langObj->LOCAL_LANG) && is_array($tempLOCAL_LANG))	{
					foreach ($langObj->LOCAL_LANG as $langKey => $tempArray)	{
						if (is_array($tempLOCAL_LANG[$langKey]))	{
							if ($overwrite)	{
								$langObj->LOCAL_LANG[$langKey] = array_merge($langObj->LOCAL_LANG[$langKey],$tempLOCAL_LANG[$langKey]);
							} else {
								$langObj->LOCAL_LANG[$langKey] = array_merge($tempLOCAL_LANG[$langKey],$langObj->LOCAL_LANG[$langKey]);
							}
						} 
					}
				} else {
					$langObj->LOCAL_LANG = $tempLOCAL_LANG; 
				}
			}

				// Overlaying labels from TypoScript (including fictious language keys for non-system languages!):
			if (is_array($langObj->conf['_LOCAL_LANG.']))	{
				reset($langObj->conf['_LOCAL_LANG.']);

				while(list($k,$lA)=each($langObj->conf['_LOCAL_LANG.']))	{
					if (is_array($lA))	{
						$k = substr($k,0,-1);
						foreach($lA as $llK => $llV)	{
							if (is_array($llV))	{
								foreach ($llV as $llk2 => $llV2) {
									if (is_array($llV2))	{
										foreach ($llV2 as $llk3 => $llV3) {
											if (is_array($llV3))	{
												foreach ($llV3 as $llk4 => $llV4) {
													 if (is_array($llV4))	{
													 } else {
														$langObj->LOCAL_LANG[$k][$llK.$llk2.$llk3.$llk4] = $llV4;
														if ($k != 'default')	{
															$langObj->LOCAL_LANG_charset[$k][$llK.$llk2.$llk3.$llk4] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
														}
													 }
												}
											} else {
												$langObj->LOCAL_LANG[$k][$llK.$llk2.$llk3] = $llV3;
												if ($k != 'default')	{
													$langObj->LOCAL_LANG_charset[$k][$llK.$llk2.$llk3] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
												}
											}
										}
									} else {
										$langObj->LOCAL_LANG[$k][$llK.$llk2] = $llV2;
										if ($k != 'default')	{
											$langObj->LOCAL_LANG_charset[$k][$llK.$llk2] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
										}
									}
								}
							} else	{
								$langObj->LOCAL_LANG[$k][$llK] = $llV;
								if ($k != 'default')	{
									$langObj->LOCAL_LANG_charset[$k][$llK] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];	// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
								}
							}
						}
					}
				}
			}
		}
	}


	/**
	 * Split Label function for front-end applications.
	 *
	 * @param	string		Key string. Accepts the "LLL:" prefix.
	 * @return	string		Label value, if any.
	 */
	function sL_fh001($input)	{
		$restStr = trim(substr($input,4));
		$extPrfx='';
		if (!strcmp(substr($restStr,0,4),'EXT:'))	{
			$restStr = trim(substr($restStr,4));
			$extPrfx='EXT:';
		}
		$parts = explode(':',$restStr);
		return ($parts[1]);
	}


	/**
	 * Returns the values from the setup field or the field of the flexform converted into the value
	 * The default value will be used if no return value would be available.
	 * This can be used fine to get the CODE values or the display mode dependant if flexforms are used or not.
	 * And all others fields of the flexforms can be read.
	 * 
	 * example:
	 * 	$config['code'] = tx_fhlibrary_flexform::getSetupOrFFvalue(
	 * 					$this->conf['code'], 
	 * 					$this->conf['code.'], 
	 * 					$this->conf['defaultCode'], 
	 * 					$this->cObj->data['pi_flexform'],
	 * 					'display_mode',
	 * 					$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TT_PRODUCTS_EXTkey]['useFlexforms']);
	 * 
	 * You have to call $this->pi_initPIflexForm(); before you call this method!
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		TypoScript configuration
	 * @param	string		extended TypoScript configuration
	 * @param	string		default value to use if the result would be empty
	 * @param	boolean		if flexforms are used or not
	 * @param	string		name of the flexform which has been used in ext_tables.php
	 * 						$TCA['tt_content']['types']['list']['subtypes_addlist']['5']='pi_flexform';
	 * @return	string		name of the field to look for in the flexform
	 * @access	public
	 *
	 * @see fhlibrary_pibase::pi_getSetupOrFFvalue
	 */

	function getSetupOrFFvalue_fh001(&$langObj, $code, $codeExt, $defaultCode, $T3FlexForm_array, $fieldName='display_mode', $useFlexforms=1, $sheet='sDEF',$lang='lDEF',$value='vDEF') {
		$rc = '';
		if (empty($code)) {
			if ($useFlexforms) {
				// Converting flexform data into array:
				$rc = $langObj->pi_getFFvalue($T3FlexForm_array, $fieldName, $sheet, $lang, $value);
			} else {
				$rc = strtoupper(trim($langObj->cObj->stdWrap($code, $codeExt)));
			}
			if (empty($rc)) {
				$rc = strtoupper($defaultCode);
			}
		} else {
			$rc = $code;
		}
		return $rc;
	}


	/**
	 * Returns a linked string made from typoLink parameters.
	 *
	 * This function takes $label as a string, wraps it in a link-tag based on the $params string, which should contain data like that you would normally pass to the popular <LINK>-tag in the TSFE.
	 * Optionally you can supply $urlParameters which is an array with key/value pairs that are rawurlencoded and appended to the resulting url.
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Text string being wrapped by the link.
	 * @param	string		Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
	 * @param	array		An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
	 * @param	string		Specific target set, if any. (Default is using the current)
	 * @param	array		Configuration
 	 * @return	string		The wrapped $label-text string
	 * @see getTypoLink_URL()
	 */
	function getTypoLink_fh001(&$langObj, $label,$params,$urlParameters=array(),$target='',$conf=array())	{
		$conf['parameter'] = $params;
		if ($target)	{
			$conf['target']=$target;
			$conf['extTarget']=$target;
		}
		if (is_array($urlParameters))	{
			if (count($urlParameters))	{
				$conf['additionalParams'].= t3lib_div::implodeArrayForUrl('',$urlParameters);
			}
		} else {
			$conf['additionalParams'].=$urlParameters;
		}
		$out = $langObj->cObj->typolink($label,$conf);
		return $out;
	}


	/**
	 * Returns the URL of a "typolink" create from the input parameter string, url-parameters and target
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Link parameter; eg. "123" for page id, "kasperYYYY@typo3.com" for email address, "http://...." for URL, "fileadmin/blabla.txt" for file.
	 * @param	array		An array with key/value pairs representing URL parameters to set. Values NOT URL-encoded yet.
	 * @param	string		Specific target set, if any. (Default is using the current)
	 * @param	array		Configuration
	 * @return	string		The URL
	 * @see getTypoLink()
	 */
	function getTypoLink_URL_fh001(&$langObj, $params,$urlParameters=array(),$target='',$conf=array())	{
		self::getTypoLink_fh001($langObj,'',$params,$urlParameters,$target,$conf);
		$rc = $langObj->cObj->lastTypoLinkUrl;
		return $rc;
	}

	/***************************
	 *
	 * Link functions
	 *
	 **************************/

	/**
	 * Get URL to some page.
	 * Returns the URL to page $id with $target and an array of additional url-parameters, $urlParameters
	 * Simple example: $this->pi_getPageLink(123) to get the URL for page-id 123.
	 *
	 * The function basically calls $this->cObj->getTypoLink_URL()
	 *
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @param	array		Configuration
	 * @return	string		The resulting URL
	 * @see pi_linkToPage()
	 */
	function getPageLink_fh001(&$langObj, $id,$target='',$urlParameters=array(),$conf=array())	{
		$rc = self::getTypoLink_URL_fh001($langObj,$id,$urlParameters,$target, $conf);
		return $rc;
	}


	/**
	 * Get External CObjects
	 * @param	object		tx_div2007_alpha_language_base object
	 * @param	string		Configuration Key
	 */
	function getExternalCObject_fh001(&$langObj, $mConfKey)	{
		if ($langObj->conf[$mConfKey] && $langObj->conf[$mConfKey.'.'])	{
			$langObj->cObj->regObj = &$langObj;
			return $langObj->cObj->cObjGetSingle($langObj->conf[$mConfKey],$langObj->conf[$mConfKey.'.'],'/'.$mConfKey.'/').'';
		}
	}


	/**
	 * run function from external cObject
	 * @param	object		tx_div2007_alpha_language_base object
	 */
	function load_noLinkExtCobj_fh001(&$langObj)	{
		if ($langObj->conf['externalProcessing_final'] || is_array($langObj->conf['externalProcessing_final.']))	{	// If there is given another cObject for the final order confirmation template!
			$langObj->externalCObject = self::getExternalCObject_fh001($langObj, 'externalProcessing_final');
		}
	} // load_noLinkExtCobj



	/**
	 * Calls user function
	 */
	function userProcess_fh001(&$pObject, &$conf, $mConfKey, $passVar)	{
		global $TSFE;

		if (isset($conf) && is_array($conf) && $conf[$mConfKey])	{
			$funcConf = $conf[$mConfKey.'.'];
			$funcConf['parentObj']=&$pObject;
			$passVar = $TSFE->cObj->callUserFunction($conf[$mConfKey], $funcConf, $passVar);
		}
		return $passVar;
	} // userProcess



	/**
	 * This is the original pi_RTEcssText from tslib_pibase
	 * Will process the input string with the parseFunc function from tslib_cObj based on configuration set in "lib.parseFunc_RTE" in the current TypoScript template.
	 * This is useful for rendering of content in RTE fields where the transformation mode is set to "ts_css" or so.
	 * Notice that this requires the use of "css_styled_content" to work right.
	 *
	 * @param	object		cOject of class tslib_cObj
	 * @param	string		The input text string to process
	 * @return	string		The processed string
	 * @see tslib_cObj::parseFunc()
	 */
	function RTEcssText(&$cObj, $str)	{
		global $TSFE;

		$parseFunc = $TSFE->tmpl->setup['lib.']['parseFunc_RTE.'];
		if (is_array($parseFunc))	{
			$str = $cObj->parseFunc($str, $parseFunc);
		}
		return $str;
	}


	/**
	 * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the piVars "pointer" will be pointing to the "result page" to show.
	 * Using $this->piVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
	 * Using $this->internal['res_count'], $this->internal['results_at_a_time'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
	 * Using $this->internal['showFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
	 * Using $this->internal['pagefloat']: this defines were the current page is shown in the list of pages in the Pagebrowser. If this var is an integer it will be interpreted as position in the list of pages. If its value is the keyword "center" the current page will be shown in the middle of the pagelist.
	 * Using $this->internal['showRange']: this var switches the display of the pagelinks from pagenumbers to ranges f.e.: 1-5 6-10 11-15... instead of 1 2 3...
	 * Using $this->pi_isOnlyFields: this holds a comma-separated list of fieldnames which - if they are among the GETvars - will not disable caching for the page with pagebrowser.
	 *
	 * The third parameter is an array with several wraps for the parts of the pagebrowser. The following elements will be recognized:
	 * disabledLinkWrap, inactiveLinkWrap, activeLinkWrap, browseLinksWrap, showResultsWrap, showResultsNumbersWrap, browseBoxWrap.
	 *
	 * If $wrapArr['showResultsNumbersWrap'] is set, the formatting string is expected to hold template markers (###FROM###, ###TO###, ###OUT_OF###, ###FROM_TO###, ###CURRENT_PAGE###, ###TOTAL_PAGES###)
	 * otherwise the formatting string is expected to hold sprintf-markers (%s) for from, to, outof (in that sequence)
	 *
	 * @param	object		tslib_pibase object
	 * @param	integer		determines how the results of the pagerowser will be shown. See description below
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the browse links
	 * @param	array		Array with elements to overwrite the default $wrapper-array.
	 * @param	string		varname for the pointer.
	 * @param	boolean		enable htmlspecialchars() for the pi_getLL function (set this to FALSE if you want f.e use images instead of text for links like 'previous' and 'next').
	 * @return	string		Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
	 */
	function list_browseresults_fh001(&$pObject, $showResultCount=1,$tableParams='',$wrapArr=array(), $pointerName = 'pointer', $hscText = TRUE)	{

			// Initializing variables:
		$pointer = intval($pObject->piVars[$pointerName]);
		$count = intval($pObject->internal['res_count']);
		$results_at_a_time = t3lib_div::intInRange($pObject->internal['results_at_a_time'],1,1000);
		$totalPages = ceil($count/$results_at_a_time);
		$maxPages = t3lib_div::intInRange($pObject->internal['maxPages'],1,100);
		$pi_isOnlyFields = $pObject->pi_isOnlyFields($pObject->pi_isOnlyFields);

			// $showResultCount determines how the results of the pagerowser will be shown.
			// If set to 0: only the result-browser will be shown
			//	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.
			//	 		 2: only the text "Displaying results..." will be shown
		$showResultCount = intval($showResultCount);

			// if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
		$showFirstLast = $pObject->internal['showFirstLast'];

			// if this has a value the "previous" button is always visible (will be forced if "showFirstLast" is set)
		$alwaysPrev = $showFirstLast?1:$pObject->pi_alwaysPrev;

		if (isset($pObject->internal['pagefloat'])) {
			if (strtoupper($pObject->internal['pagefloat']) == 'CENTER') {
				$pagefloat = ceil(($maxPages - 1)/2);
			} else {
				// pagefloat set as integer. 0 = left, value >= $pObject->internal['maxPages'] = right
				$pagefloat = t3lib_div::intInRange($pObject->internal['pagefloat'],-1,$maxPages-1);
			}
		} else {
			$pagefloat = -1; // pagefloat disabled
		}

			// default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
		$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
		$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
		$wrapper['activeLinkWrap'] = '<td'.$pObject->pi_classParam('browsebox-SCell').' nowrap="nowrap"><p>|</p></td>';
		$wrapper['browseLinksWrap'] = trim('<table '.$tableParams).'><tr>|</tr></table>';

		if ($pObject->internal['imagePath'])	{
			$onMouseOver = ($pObject->internal['imageOnMouseOver'] ? 'onmouseover="'.$pObject->internal['imageOnMouseOver'].'" ': ''); 		
			$onMouseOut = ($pObject->internal['imageOnMouseOut'] ? 'onmouseout="'.$pObject->internal['imageOnMouseOut'].'" ': ''); 		
			$onMouseOverActive = ($pObject->internal['imageActiveOnMouseOver'] ? 'onmouseover="'.$pObject->internal['imageActiveOnMouseOver'].'" ': ''); 		
			$onMouseOutActive = ($pObject->internal['imageActiveOnMouseOut'] ? 'onmouseout="'.$pObject->internal['imageActiveOnMouseOut'].'" ': ''); 		
			$wrapper['browseTextWrap'] = '<img src="'.$pObject->internal['imagePath'].$pObject->internal['imageFilemask'].'" '.$onMouseOver.$onMouseOut.'>';
			$wrapper['activeBrowseTextWrap'] = '<img src="'.$pObject->internal['imagePath'].$pObject->internal['imageActiveFilemask'].'" '.$onMouseOverActive.$onMouseOutActive.'>';
		}
		$wrapper['showResultsWrap'] = '<p>|</p>';
		$wrapper['browseBoxWrap'] = '
		<!--
			List browsing box:
		-->
		<div '.$pObject->pi_classParam('browsebox').'>
			|
		</div>';

			// now overwrite all entries in $wrapper which are also in $wrapArr
		$wrapper = array_merge($wrapper,$wrapArr);

		if ($showResultCount != 2) { //show pagebrowser
			if ($pagefloat > -1) {
				$lastPage = min($totalPages,max($pointer+1 + $pagefloat,$maxPages));
				$firstPage = max(0,$lastPage-$maxPages);
			} else {
				$firstPage = 0;
				$lastPage = t3lib_div::intInRange($totalPages,1,$maxPages);
			}
			$links=array();

				// Make browse-table/links:
			if ($showFirstLast) { // Link to first page
				if ($pointer>0)	{
					$links[]=$pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars(htmlspecialchars($pObject->pi_getLL('pi_list_browseresults_first','<< First',$hscText)),array($pointerName => null),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				} else {
					$links[]=$pObject->cObj->wrap(htmlspecialchars($pObject->pi_getLL('pi_list_browseresults_first','<< First',$hscText)),$wrapper['disabledLinkWrap']);
				}
			}
			if ($alwaysPrev>=0)	{ // Link to previous page
				$previousText = $pObject->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText);
				if ($pointer>0)	{
					$links[]=$pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($previousText,array($pointerName => ($pointer-1?$pointer-1:'')),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				} elseif ($alwaysPrev)	{
					$links[]=$pObject->cObj->wrap($previousText,$wrapper['disabledLinkWrap']);
				}
			}
			for($a=$firstPage;$a<$lastPage;$a++)	{ // Links to pages
				if ($pObject->internal['showRange']) {
					$pageText = (($a*$results_at_a_time)+1).'-'.min($count,(($a+1)*$results_at_a_time));
				} else if ($totalPages > 1)	{
					if ($wrapper['browseTextWrap'])	{
						if ($pointer == $a) { // current page
							$pageText = $pObject->cObj->wrap(($a+1),$wrapper['activeBrowseTextWrap']);
						} else {
							$pageText = $pObject->cObj->wrap(($a+1),$wrapper['browseTextWrap']);
						}
					} else {
						$pageText = trim($pObject->pi_getLL('pi_list_browseresults_page','Page',$hscText)).' '.($a+1);
					}
				}
				if ($pointer == $a) { // current page
					if ($pObject->internal['dontLinkActivePage']) {
						$links[] = $pObject->cObj->wrap($pageText,$wrapper['activeLinkWrap']);
					} else {
						$linkArray = array($pointerName  => ($a?$a:''));
						$link = $pObject->pi_linkTP_keepPIvars($pageText,$linkArray,$pi_isOnlyFields);
						$links[] = $pObject->cObj->wrap($link,$wrapper['activeLinkWrap']);
					}
				} else {
					$links[] = $pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($pageText,array($pointerName => ($a?$a:'')),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				}
			}
			if ($pointer<$totalPages-1 || $showFirstLast)	{
				$nextText = $pObject->pi_getLL('pi_list_browseresults_next','Next >',$hscText);
				if ($pointer==$totalPages-1) { // Link to next page
					$links[]=$pObject->cObj->wrap($nextText,$wrapper['disabledLinkWrap']);
				} else {
					$links[]=$pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($nextText,array($pointerName => $pointer+1),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				}
			}
			if ($showFirstLast) { // Link to last page
				if ($pointer<$totalPages-1) {
					$links[]=$pObject->cObj->wrap($pObject->pi_linkTP_keepPIvars($pObject->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),array($pointerName => $totalPages-1),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				} else {
					$links[]=$pObject->cObj->wrap($pObject->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),$wrapper['disabledLinkWrap']);
				}
			}
			$theLinks = $pObject->cObj->wrap(implode(chr(10),$links),$wrapper['browseLinksWrap']);
		} else {
			$theLinks = '';
		}

		$pR1 = $pointer*$results_at_a_time+1;
		$pR2 = $pointer*$results_at_a_time+$results_at_a_time;

		if ($showResultCount) {
			if (isset($wrapper['showResultsNumbersWrap'])) {
				// this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
				// the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

				$markerArray['###FROM###'] = $pObject->cObj->wrap($pObject->internal['res_count'] > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TO###'] = $pObject->cObj->wrap(min($pObject->internal['res_count'],$pR2),$wrapper['showResultsNumbersWrap']);
				$markerArray['###OUT_OF###'] = $pObject->cObj->wrap($pObject->internal['res_count'],$wrapper['showResultsNumbersWrap']);
				$markerArray['###FROM_TO###'] = $pObject->cObj->wrap(($pObject->internal['res_count'] > 0 ? $pR1 : 0).' '.$pObject->pi_getLL('pi_list_browseresults_to','to').' '.min($pObject->internal['res_count'],$pR2),$wrapper['showResultsNumbersWrap']);
				$markerArray['###CURRENT_PAGE###'] = $pObject->cObj->wrap($pointer+1,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TOTAL_PAGES###'] = $pObject->cObj->wrap($totalPages,$wrapper['showResultsNumbersWrap']);
				$pi_list_browseresults_displays = $pObject->pi_getLL('pi_list_browseresults_displays','Displaying results ###FROM### to ###TO### out of ###OUT_OF###');
				// substitute markers
				$resultCountMsg = $pObject->cObj->substituteMarkerArray($pi_list_browseresults_displays,$markerArray);
			} else {
				// render the resultcount in the "traditional" way using sprintf
				$resultCountMsg = sprintf(
					str_replace('###SPAN_BEGIN###','<span'.$pObject->pi_classParam('browsebox-strong').'>',$pObject->pi_getLL('pi_list_browseresults_displays','Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')),
					$count > 0 ? $pR1 : 0,
					min($count,$pR2),
					$count);
			}
			$resultCountMsg = $pObject->cObj->wrap($resultCountMsg,$wrapper['showResultsWrap']);
		} else {
			$resultCountMsg = '';
		}

		$sTables = $pObject->cObj->wrap($resultCountMsg.$theLinks,$wrapper['browseBoxWrap']);

		return $sTables;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_alpha.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_alpha.php']);
}
?>
