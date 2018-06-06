<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Elmar Hinz (elmar.hinz@team-red.net)
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
 * Collection of static functions to work in cooperation with the extension lib (lib/div)
 *
 * PHP version 5
 *
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @copyright  2006-2011 Elmar Hinz
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */

/**
 * Collection of static functions to work in cooperation with the extension lib (lib/div)
 *
 *
 * This is a library that results of the work of the Extension Coordination Team (ECT).
 *
 * In this class we collect diverse static functions that are usefull for extension development,
 * but that didn't made their way into t3lib_div. A part of the functions are peer functions
 * to classes of the extension lib.
 *
 * <b>Contribute your own functions</b>
 *
 * You are invited to share your own most usefull functions with the world of TYPO3 developers.
 *
 * Advantages for you:
 * - You don't need to spoil the TER with new extensions only to provide a few functions.
 * - You don't need to bother with the maintainance of an individual extension.
 * - You have a unique short extension kex (tx_div2007) to access the different contributed functions.
 * - It is more likely that others will really use your contribution, because this library is
 *   documented in books and magazines.
 * - Your function will become a common standard.
 *
 * You can contribute via the SVN repository, by becomming a member of the project typo3xdev
 * on sourceforge.net. Please ask the project admin (Andreas Otto) for a membership to
 * {@link http://sourceforge.net/projects/typo3xdev/ typo3xdev project on Sourceforge}.
 *
 * This collection of functions is "moderated". We want to keep the style coherent and the quality high.
 * So we will not automatically accept any contribution as it is.  We will check if your contribution
 * is usefull and adheres to the coding guidelines. We will maybe keep the leading idea of your contribution
 * but we may adapt it to fit into the style of this exension. Anyway we thank you for all your contributions.
 *
 * Style guidelines in short
 *
 * - Use camalCase.
 * - Avoid underscores and abbreviations.
 * - Use speaking function names in the imperative form.
 * - Provide documentation in Java format.
 *
 *
 * <b>Definition of data structures</b>
 *
 * <b>The list family</b>
 *
 * - listString:
 *    This is a CSV like string of values that are separated by whitespace and/or other characters.
 *    <pre>
 *     Example: 'one, two, three'
 *     Example: 'alpha beta gamma'
 *    </pre>
 *    The default splitting characters are '\s,;:': whitespace, comma, semicolon, colon.
 *    Other splitting characters can be given to the splitting functions as parameters.
 *    Elments that contain whitespace or the splitting characters are currently not supported. That may
 *    by optimized in future. Until then only use it as human written and controlled input format.
 *
 * - listArray:
 *    This is an array with integers as keys:
 *    <pre>
 *     Example: array( 'red', 'yellow', 'green')
 *     Example: split(' ', 'alpha beta gamma')
 *    </pre>
 *
 * - listObject:
 *    This is an object of the SPL type i.e. tx_div2007_data with integers as keys to the internal value:
 *    <pre>
 *     Example: new tx_div2007_data(array( 'red', 'yellow', 'green'))
 *     Example: new tx_div2007_data(split(' ', 'alpha beta gamma'))
 *    </pre>
 *
 * <b> The hash family</b>
 *
 * - hashString:
 *    This is a string of values that are separated by whitespace and/or other characters which are ordered as pairs.
 *    The even items are the keys, the odd items are the values.
 *    <pre>
 *     Example: 'firstname Peter surname Potter email peter@example.org'
 *     Example: 'firstname: Peter,  surname: Potter,  email: peter@example.org'
 *    </pre>
 *    The default splitting characters are '\s,;:': whitespace, comma, semicolon, colon.
 *    Other splitting characters can be given to the splitting functions as parameters.
 *    Elments that contain whitespace or the splitting characters are currently not supported. That may
 *    by optimized in future. Until then only use it as human written and controlled input format.
 *
 * - hashArray:
 *    This is an array of key value pairs:
 *    <pre>
 *     Example: array( 'firstname' => 'Peter', 'surname' => 'Potter')
 *    </pre>
 *
 * - hashObject:
 *    This is an object of the SPL type i.e. tx_div2007_data with keys and values:
 *    <pre>
 *     Example: new tx_div2007_data(array( 'firstname' => 'Peter', 'surname' => 'Potter'))
 *    </pre>
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 */

class tx_div2007 {

	/**
	 * Do a recursive require_once for all classes of an extension or subdirectory
	 *
	 * This function is a workaround for the __autoload() function from PHP5.
	 * - Limitation: Doesn't work for "intra extension inheritance". (see below)
	 * - Disadvantage: All classfiles are required, even if not used.
	 * - Advantage: It doesn't require the same __autoload pattern for all extensions.
	 * - Alternative: tx_div2007::load() to require on demand.
	 *
	 * Usage example:
	 *
	 * In ext_localconf.php
	 * <code>
	 *    require_once(PATH_BE_div2007 . 'class.tx_div2007.php');
	 *    if(TYPO3_MODE == 'FE') tx_div2007::autoLoadAll($_EXTKEY);
	 * </code>
	 *
	 * Intra extension inheritance limitation:
	 *
	 * This function doesn't work trustworthy, for classes that inherit from other
	 * classes within the same extension, because it doesn't respect the required
	 * order. Workaround: Use tx_div2007::load() to require an extension internal
	 * class as parent.
	 *
	 * @todo    Improve Intra extension inheritance limitation
	 * @see     tx_div2007::load()
	 *
	 * @param	string		preg pattern matching the filenames to require
	 * @param	string		extension Key
	 * @param	string		subdirectory
	 * @return	void
	 */
	static public function autoLoadAll ($extensionKey, $subdirectory = '', $pregFileNamePattern = '/^class[.]tx_(.*)[.]php$/') {
		// Format subdirectory first to '.../' or ''
		preg_match('/^\/?(.*)\/?$/', $subdirectory, $matches);
		$subdirectory = strlen($matches[1]) ? $matches[1] . '/' : '';
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . $subdirectory;
		if(is_dir($path)) {
			$handle = opendir($path);
			while($entry = readdir($handle)) {
				if(preg_match($pregFileNamePattern, $entry)) {
					require_once($path . $entry);
				} elseif(is_dir($path . $entry) && !preg_match('/\./', $entry)) {
					self::autoLoadAll($extensionKey, $subdirectory . $entry, $pregFileNamePattern);
				}
			}
		} else {
			die('No such directory: ' . $path . ' in ' . __FILE__ . ' line ' . __LINE__);
		}
	}

	/**
	 * Using the browser session
	 *
	 * The browser session is bound to the browser not to the frontend user.
	 *
	 * The value for the given key is returned.
	 * If a value is given it is stored into the session before.
	 *
	 * @param  session 	key
	 * @param  mixed 	sesion value
	 * @return mixed	session value
	 * @see    userSeesion()
	 * @see    session()
	 */
	static public function browserSession ($key, $value = NULL) {
		if($value != NULL) {
			$GLOBALS['TSFE']->fe_user->setKey('ses', $key, $value);
		}
		return $GLOBALS['TSFE']->fe_user->getKey('ses', $key);
	}

	/**
	 * Clear all caches
	 *
	 * WARNING: Only use during development!!!!
	 * It's not a runtime function. If you use it during development keep in mind,
	 * that functionality may depend on the cached content. So the use can lead to
	 * irritating results.
	 *
	 * @return	void
	 */
	static public function clearAllCaches () {
		$pathT3lib = PATH_site . 't3lib/';
		require_once($pathT3lib . 'class.t3lib_tcemain.php');
		$tce = new t3lib_tcemain();
		$tce->admin = true;
		$tce->clear_cacheCmd('all');
	}

	/**
	 * Get the database object TYPO3_DB
	 *
	 * Alias for the function getDataBase().
	 *
	 * @return object TYPO3_DB
	 * @see tx_div2007::getDataBase()
	 */
	static public function db (){
		return self::getDataBase();
	}

	/**
	 * Exits the current script with die() and prints a message and a file/line pair.
	 *
	 * @param	string		message to display
	 * @param	string		filename the script died
	 * @param	integer		linenumber the script died
	 * @return	void
	 */
	static public function end ($text, $file, $line) {
		debug ($text, 'end $text'); // keep this
		print '<h1>You died:</h1>';
		print '<pre><strong>' . chr(10) . $text . chr(10) . '</strong></pre>';
		print '<p>File: ' . $file . '</p>';
		print '<p>Line: ' . $line . '</p>';
		die();
	}

	/**
	 * Explode a list into an array
	 *
	 * Explodes a string by any number of the given charactrs.
	 * By default it uses comma, semicolon, colon and whitespace.
	 *
	 * The returned values are trimmed.
	 *
	 * @param	string		string to split
	 * @param	string		regular expression that defines the splitter
	 * @return	array		with the results
	 */
	static public function explode ($value, $splitCharacters = ',;:\s') {
		$pattern = '/[' . $splitCharacters . ']+/';
		$results = preg_split($pattern, $value, -1, PREG_SPLIT_NO_EMPTY);
		$return = array();
		foreach($results as $result)
		 $return[] = trim($result);
		return (array) $return;
	}

	/**
	 * Returns a TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer to access typolink, ect.
	 *
	 * If $this->controller is set it returns the plugins cObject.
	 * If it is called outside a controllers context, it creates a cObject as singleton.
	 *
	 * @return	TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer		a TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	static public function findCObject () {
		if(is_object($this->controller) && is_object($this->controller->cObject)){
			return $this->controller->cObject;
		}
		if(is_object($this->controller) && is_object($this->controller->cObj)){
			return $this->controller->cObj;
		}
		static $cObject;
		if(!is_object($cObject))
		  require_once(PATH_tslib.'class.tslib_content.php');
			$cObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
		return	$cObject;
	}

	/**
	 * Get an instance of the T3 core engine (TCE)
	 *
	 * This function requires that a BE user is logged in.
	 * You can log in a BE user into the FE i.e. by using
	 * the extension "simulatebe". Alternatively you can
	 * work with a "faked" $BE_USER object.
	 *
	 * @return	object		TCE
	 */
	static public function findTce () {
		global $BE_USER, $TCA, $PAGES_TYPES, $ICON_TYPES, $LANG_GENERAL_LABELS, $TBE_STYLES, $TBE_MODULES, $FILEICONS;
		ob_start();
		$pathT3lib = PATH_site . 't3lib/';
		require_once($pathT3lib . 'stddb/tables.php');
		require_once($pathT3lib . 'stddb/load_ext_tables.php');
		require_once($pathT3lib . 'class.t3lib_tcemain.php');
		ob_end_clean();
		if(!isset($tce)) {
			static $tce; // Singleton.
			$tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_tcemain');
			$tce->stripslashes_value = 0;
		}
		return $tce;
	}

	/**
	 * Get the database object TYPO3_DB
	 *
	 * @return object TYPO3_DB
	 * @see tx_div2007::db();
	 */
	static public function getDataBase () {
		return self::getGlobal('TYPO3_DB');
	}

    /**
    * Get the logged in front end user
    *
    * @param	string		field of the user if set
    *
    * @return object The current frontend user or string value of the field or boolean false.
    * @see tx_div2007::user();
    */
    static public function getFrontEndUser ($field = '') {
        $result = false;

        if (
            isset($GLOBALS['TSFE']->fe_user) &&
            is_object($GLOBALS['TSFE']->fe_user) &&
            is_array($GLOBALS['TSFE']->fe_user->user) &&
            isset($GLOBALS['TSFE']->fe_user->user['username']) &&
            $GLOBALS['TSFE']->fe_user->user['username'] != ''
        ) {
            $result = $GLOBALS['TSFE']->fe_user;

            if (
                $field != '' &&
                isset($GLOBALS['TSFE']->fe_user->user[$field])
            ) {
                $result = $GLOBALS['TSFE']->fe_user->user[$field];
            }
        }

        return $result;
    }

	/**
	 * Load the site relative extension path for the given extension key.
	 *
	 * @param string Extension key to resolve.
	 * @return string Site relative path. false if not found.
	 */
	static public function getSiteRelativeExtensionPath ($key) {
		global $TYPO3_LOADED_EXT;
		if(isset($TYPO3_LOADED_EXT[$key]['siteRelPath']) ) {
			return $TYPO3_LOADED_EXT[$key]['siteRelPath'];
		} else {
			return false;
		}
	}

	/**
	 * Check if the given extension key is within the loaded extensions
	 *
	 * The key can be given in the regular format or with underscores stripped.
	 *
	 * @param	string		extension key to check
	 * @return	boolean		is the key valid?
	 */
	static public function getValidKey ($rawKey) {
		$result = false;

		$rawKey = str_replace('_', '', $rawKey);
		$uKeys = array_keys(self::getGlobal('TYPO3_LOADED_EXT'));
		if (isset($uKeys) && is_array($uKeys)) {
			foreach($uKeys as $uKey) {
				if (
					str_replace('_', '', $uKey) == $rawKey
				) {
					$result =  $uKey;
					break;
				}
			}
		} else {
			$packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Package\\PackageManager');
			foreach ($packageManager->getActivePackages() as $package) {
				$packageKey = $package->getPackageKey();
				if (
					str_replace('_', '', $packageKey) == $rawKey
				) {
					$result =  $packageKey;
					break;
				}
			}
		}

		return $result;
	}


	/**
	 * Guess the key from the given information
	 *
	 * Guessing has the following order:
	 *
	 * 1. A KEY itself is tried.
	 *    <pre>
	 *     Example: my_extension
	 *    </pre>
	 * 2. A classname of the pattern tx_KEY_something_else is tried.
	 *    <pre>
	 *     Example: tx_myextension_view
	 *    </pre>
	 * 3. A full classname of the pattern ' * tx_KEY_something_else.php' is tried.
	 *    <pre>
	 *     Example: class.tx_myextension_view.php
	 *     Example: brokenPath/class.tx_myextension_view.php
	 *    </pre>
	 * 4. A path that starts with the KEY is tried.
	 *    <pre>
	 *     Example: my_extension/class.view.php
	 *    </pre>
	 *
	 * @param	string		the minimal necessary information (see 1-4)
	 * @return	string		the guessed key, false if no result
	 */
	static public function guessKey ($minimalInformation) {
		$info=trim($minimalInformation);
		$key = false;
		if($info){
			// Can it be the key itself?
			if(!$key && preg_match('/^([A-Za-z_]*)$/', $info, $matches ) ) {
				$key = $matches[1];
				$key = self::getValidKey($key);
			}
			// Is it a classname that contains the key?
			if(!$key && (preg_match('/^tx_([^_]*)(.*)$/', $info, $matches ) || preg_match('/^user_([^_]*)(.*)$/', $info, $matches )) ) {
				$key = $matches[1];
				$key = self::getValidKey($key);
			}
			// Is there a full filename that contains the key in it?
			if(!$key && (preg_match('/^.*?tx_([^_]*)(.*)\.php$/', $info, $matches ) || preg_match('/^.*?user_([^_]*)(.*)\.php$/', $info, $matches )) ) {
				$key = $matches[1];
				$key = self::getValidKey($key);
			}
			// Is it a path that starts with the key?
			if(!$key && $last = strstr('/',$info)) {
				$key = substr($info, 0, $last);
				$key = self::getValidKey($key);
			}
		}
		return $key ? $key : false;
	}

	/**
	 * Get a global variable
	 *
	 * @param string   The key of the global variable
	 * @return mixed   The global variable.
	 */
	static public function getGlobal ($key) {
		return $GLOBALS[$key];
	}

	/**
	 * This function is an alias for tx_div2007::loadClass() for your convinience
	 *
	 * @param	string		classname or path matching for the type of loader
	 * @return	boolean		true if successfull else false
	 * @see     tx_div2007::loadClass()
	 */
	static public function load ($classNameOrPathInformation) {
		return self::loadClass($classNameOrPathInformation);
	}


	/**
	 * Load the class file
	 *
	 * Load the file for a given classname 'tx_key_path_file'
	 * or a given part of the filepath that contains enough information to find the class.
	 *
	 * @param	string		classname or path matching for the type of loader
	 * @return	boolean		true if successfull, false otherwise
	 * @see     tx_div2007_loader
	 */
	static public function loadClass ($classNameOrPathInformation) {
		require_once(PATH_BE_div2007 . 'class.tx_div2007_t3Loader.php');
		if(tx_div2007_t3Loader::load($classNameOrPathInformation)) {
			return true;
		}
		return false;
	}


	/**
	 * Loads TCA additions of other extensions
	 *
	 * Your extension may depend on fields that are added by other
	 * extensions. For reasons of performance parts of the TCA are only
	 * loaded on demand. To ensure that the extended TCA is loaded for
	 * the extensions yours depends you can apply this function.
	 *
	 * @param       array  extension keys which have TCA additions to load
	 * @return      void
	 * @author      Franz Holzinger
	 */
	static public function loadTcaAdditions ($ext_keys) {
		global $_EXTKEY, $TCA;

		$loadTcaAdditions = true;

		//Merge all ext_keys
		if (is_array($ext_keys)) {
			for($i = 0; $i < sizeof($ext_keys); $i++){
				//Include the ext_table
				$_EXTKEY = $ext_keys[$i];
				include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($ext_keys[$i]) . 'ext_tables.php');
			}
		}
	}


	/**
	 * Load the class file and make an instance of the class
	 *
	 * This is an extension to \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(). The advantage
	 * is that it tries to autoload the file wich in combination
	 * with the shorter notation simplyfies the generation of objects.
	 *
	 * @param	string		classname
	 * @return	object		the instance else false
	 * @see     tx_div2007_loader
	 */
	static public function makeInstance ($className) {
		$instance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($className);
		if(!is_object($instance)) {
			return false;
		} else {
			return $instance;
		}
	}


	/**
	 * Resolves the "EXT:" prefix relative to PATH_site. If not given the path is untouched.
	 *
	 * @param string Path to resolve.
	 * @return string Resolved path.
	 */
	static public function resolvePathWithExtPrefix ($path) {
		if(substr($path, 0, 4) == 'EXT:') {
			list($extKey, $local) = explode('/', substr($path, 4), 2);
			if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey)) {
				$path = self::getSiteRelativeExtensionPath($extKey) . $local;
			}
		}
		return $path;
	}

	/**
	 * Using the browser session
	 *
	 * This is an alias for the function tx_div2007::browserSession()
	 *
	 * @param  session	key
	 * @param  mixed	sesion value
	 * @return mixed	session value
	 * @see    userSeesion()
	 * @see    browserSession()
	 */
	static public function session ($key, $value = NULL) {
		return self::browserSession($key, $value);
	}

	/**
	 * Get an instance of the T3 core engine (TCE)
	 * Alias for the function findTce()
	 *
	 * @return object TYPO3_DB
	 * @see tx_div2007::findTce()
	 */
	static public function tce (){
		return self::findTce();
	}

	/**
	 * Converts the given mixed data into an hashArray
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  array       an hashArray
	 */
	static public function toHashArray ($mixed, $splitCharacters = ',;:\s' ) {
		if(is_string($mixed)) {
			$array = self::explode($mixed, $splitCharacters); // TODO: Enable empty values by defining a better explode functions.
			for($i = 0; $i < count($array); $i = $i + 2) {
				$hashArray[$array[$i]] = $array[$i+1];
			}
		} elseif(is_array($mixed)) {
			$hashArray = $mixed;
		} elseif(is_object($mixed) && method_exists($mixed, 'getArrayCopy')) {
			$hashArray = $mixed->getArrayCopy();
		} else {
			$hashArray = array();
		}
		return $hashArray;
	}


	/**
	 * Converts the given mixed data into an hashObject
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  object      an hashObject
	 */
	static public function toHashObject ($mixed, $splitCharacters = ',;:' ) {
// Todo: tx_div2007_data does not exist
		return new tx_div2007_data(self::toHashArray($mixed, $splitCharacters));
	}

	/**
	 * Converts the given mixed data into an hashString
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  string      an hashString
	 */
	static public function toHashString ($mixed, $splitCharacters = ',;:' ) {
		$array = self::toHashArray($mixed, $splitCharacters);
		$string = '';
		for($i = 0; $i < count($array); $i = $i + 2) {
			$string .= $array[$i] . ' : ' . $array[$i + 1] . ', ';
		}
		return $string ? substr($sting, 0, -1) : false;
	}


	/**
	 * Converts the given mixed data into a listArray
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  array       a listArray
	 */
	static public function toListArray ($mixed, $splitCharacters = ',;:\s') {
		if(is_string($mixed)) {
			$listArray = self::explode($mixed, $splitCharacters);
		} elseif(is_array($mixed)) {
			$listArray = array_values($mixed);
		} elseif(is_object($mixed) && method_exists($mixed, 'getArrayCopy')) {
			$listArray = array_values($mixed->getArrayCopy());
		} else {
			$listArray = array();
		}
		return $listArray;
	}


	/**
	 * Converts the given mixed data into a listObject
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  object      a listObject
	 */
	static public function toListObject ($mixed, $splitCharacters = ',;:' ) {
		return new tx_div2007_data(self::toListArray($mixed, $splitCharacters));
	}


	/**
	 * Converts the given mixed data into a listString
	 *
	 * @param   mixed       data to be converted
	 * @param   string      string of characters used to split first argument
	 * @return  string      a listString
	 */
	static public function toListString ($mixed, $splitCharacters = ',;:' ) {
		return implode(', ', self::toListArray($mixed, $splitCharacters));
	}

	/**
	 * Get the frontend user
	 *
	 * Alias to getFrontEndUser();
	 *
	 * @return	object	The current frontend user.
	 */
	static public function user () {
		return self::getFrontEndUser();
	}

	/**
	 * Using the user session
	 *
	 * The user session is bound to the frontend user.
	 *
	 * The value for the given key is returned.
	 * If a value is given it is stored into the session before.
	 *
	 * @param  session key
	 * @param  mixed sesion value
	 * @return	mixed	session value
	 * @see    browserSeesion()
	 */
	static public function userSession ($key, $value = NULL) {
		if($value != NULL) {
			$GLOBALS['TSFE']->fe_user->setKey('user', $key, $value);
		}
		return $GLOBALS['TSFE']->fe_user->getKey('user', $key);
	}

}

?>
