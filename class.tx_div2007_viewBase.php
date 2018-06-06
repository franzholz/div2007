<?php

/**
 * Base class for all views in tx_div2007
 *
 * Copyright (c) 2006-2007 Elmar Hinz
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
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @copyright  2006-2007 Elmar Hinz
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */

/**
 * Base class for all views in tx_div2007
 *
 * Depends on: tx_div2007_object
 *
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @package    TYPO3
 * @subpackage div2007
 */
class tx_div2007_viewBase extends tx_div2007_object {
	var $pathToTemplateDirectory = '';
	var $dateFormatKey = 'dateFormat';
	var $floatFormatKey = 'floatFormat';
	var $parseFuncTextKey = 'parseFuncText';
	var $parseFuncRteKey = 'parseFuncRte';
	var $timeFormatKey = 'timeFormat';

	//------------------------------------------------------------------------------------
	// Setters
	//------------------------------------------------------------------------------------

	/**
	 * Set the path of the template directory (ALTERNATIVE WAY).
	 *
	 * The DEFAULT WAY is to set the template path by the configurations object.
	 *
	 * This method gives you the possibility to specify a template path from the controller.
	 * This can become usefull, if you work with different template directories.
	 * You can make use the syntax  EXT:myextension/somepath.
	 * It will be evaluated to the absolute path by \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName()
	 *
	 * @param	string		path to the directory containing the php templates
	 * @return	void
	 */
	public function setPathToTemplateDirectory($pathToTemplateDirectory) {
		$this->pathToTemplateDirectory = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($pathToTemplateDirectory);
	}

	//-------------------------------------------------------------------------------------
	// Getters
	//-------------------------------------------------------------------------------------

	/**
	 * Call this function to find the the path to the template directory.
	 *
	 * $this->pathToTemplateDirectory is checked first, if it has been set actively.
	 * If it is missing, call $configurations->get('pathToTemplateDirectory'). (RECOMMENDED)
	 * The path can make use the syntax  EXT:myextension/somepath.
	 *
	 * @param	string		path to the directory containing the php templates
	 * @return	void
	 */
	public function getPathToTemplateDirectory() {
		$pathToTemplateDirectory = $this->pathToTemplateDirectory ?  $this->pathToTemplateDirectory :
			$this->controller->configurations->get('pathToTemplateDirectory');
		if(!$pathToTemplateDirectory)
			$this->_die(__FILE__, __LINE__, 'Please set the path to the template directory.
			Do it either with the method setPathToTemplateDirectory($path)
			or by choosing the default name "pathToTemplateDirectory" in the TS setup.');
		return \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($pathToTemplateDirectory);
	}

	/**
	 * Render the template.
	 *
	 * Abstract function, to be adapted in child classes
	 *
	 * The parameter can be a key of an element in the $configurations object,
	 * that points to a filename or the filename itself.
	 *
	 * Usage:
	 *
	 * 1.) $view->render('exampleTemplateKey');
	 * 2.) $view->render('exampleTemplateFileName.php');
	 *
	 * @param	  string    configuration key or filename of template file
	 * @return  string    typically an (x)html string
	 * @abstract
	 */
	public function render($configurationKeyOrFileName) {
		return '<p>Abstract function: render()</p>';
	}


	//-------------------------------------------------------------------------------------
	// Getters typically called within the template
	//-------------------------------------------------------------------------------------

	/**
	 * Get human readability and localized date for a timestamp out of the internal data array.
	 *
	 * If no format parameter is provided, the function tries to find one in the configurations
	 * by using the pathKey $this->dateFormatKey.
	 *
	 * @param	mixed		key of internal data array
	 * @param	string		format string
	 * @return	string		human readable date string
	 * @see		http://php.net/strftime
	 */
	public function asDate($key, $format = null) {
		$format = $format ? $format : $this->controller->configurations->get($this->dateFormatKey);
		if($format) {
			return strftime($format, $this->get($key));
		} else {
			$message = 'No date format has been defined.';
			$this->_die($message, __FILE__, __LINE__);
		}
	}

	/**
	 * Make an external Typolink to an email.
	 *
	 * Alias to asUrl
	 *
	 * If no label key is given the url is displayed as label.
	 * If the label is available but no url the label is returned
	 * without the tag. If both fails nothing is returned.
	 *
	 * @param	mixed		key of the email field
	 * @param	mixed		key of the label field
	 * @return	string		the linktag
	 * @see		tx_div2007_link
	 * @see		asUrl()
	 */
	public function asEmail($emailKey, $labelKey = null ) {
		return $this->asUrl($emailKey, $labelKey);
	}

	/**
	 * Get a formatted float value out of the internal data array.
	 *
	 * If no format paramter is provided, the function tries
	 * to find one in the configurations by using the pathKey $this->floatFormatKey.
	 *
	 * The last fallback is ',.2';
	 *
	 * <pre>
	 * Examples for 1234,123456789012:
	 * ------------------------------
	 * ',.2' =>  1,234.12              // fallback
	 * ',.3' =>  1,234.123
	 * '.2' =>  1234.12
	 * ' ,3' =>  1 234.123
	 * '.,12' =>  1.234,123456789012
	 * </pre>
	 *
	 * The decimal value at the end is the value of decimals.
	 * The char before it is the decimal point charcter.
	 * The char before it (at the beginning of any) is the thousands seperator
	 *
	 * @param	mixed		key of internal data array
	 * @param	string		format string
	 * @return	string		human readable date string
	 * @see		http://php.net/strftime
	 */
	public function asFloat($key, $format = null) {
		if(!$format && is_object($this->configuraton)) {
			$format = $this->configurator->get($this->floatFormatKey);
		}
		if(!$format) {
			$format = ',.2'; //fallback
		}
		if(preg_match('/^(\D?)(\D)(\d*)$/', $format, $matches)) {
				$thousandsSeparator = $matches[1];
				$decimalPoint = $matches[2];
				$decimalsAmount = $matches[3];
				$value = 0 + $this->get($key);
				return number_format($value, $decimalsAmount, $decimalPoint, $thousandsSeparator);
		} else {
			return false;
		}

	}

	/**
	 * Get a formatted form out of the internal data array.
	 *
	 * @param	mixed		key of internal data array
	 * @return	string		human readable date string
	 */
	public function asForm($key) {
		return htmlspecialchars($this->get($key));
	}

	/**
	 * Get a string parsed for standard html input (parseFunc).
	 *
	 * This includes parsing of http://xxxx and mailto://xxxx to links.
	 *
	 * The second parameter has to be a pathKey for the configurator object.
	 * If it is not provided we take $this->parseFuncKey alternatively.
	 * With this parseFuncKey we query for the parseFunc setup.
	 * If no setup is found we fall back to  "< lib.parseFunc";
	 *
	 * @param	mixed		key of internal data
	 * @param	string		key of configurator for parseFunc setup
	 * @return	mixed		parsed string
	 */
	public function asHtml($key, $parseFuncKey = '') {
		if(is_object($this->configurator)) {
			$parseFuncKey = $parseFuncKey	? $parseFuncKey	: $this->parseFuncTextKey;
			$parseFunc = $this->configurator->get($parseFuncKey);
		}
		if(is_array($parseFunc)) {
			$setup['parseFunc.'] = $parseFunc;
		} elseif($parseFunc) {
			$setup['parseFunc'] = $parseFunc;
		} else {
			$setup['parseFunc'] = '< lib.parseFunc';
		}
		$setup['value'] = $this->get($key);
		$cObject = $this->findCObject();
		return $cObject->cObjGetSingle('TEXT', $setup);
	}

	/**
	 * Get an integer from the internal data array by key.
	 *
	 * @param	mixed		key of the internal data array
	 * @return	integer		value assigned to the key
	 */
	public function asInteger($key) {
		return (integer) $this->get($key);
	}

	/**
	 * Get a raw value from the internal data array by key.
	 *
	 * Just an alias to $this->get(); to have an analogous name to printRaw();
	 *
	 * @param	mixed		key of the internal data array
	 * @return	mixed		array of string assigned to the key
	 * @see		get()
	 */
	public function asRaw($key) {
		return $this->get($key);
	}

	/**
	 * Get a String parsed for RTE input (parseFunc_RTE).
	 *
	 * The second parameter has to be a pathKey for the configurator object.
	 * If it is not provided we take $this->parseFuncKey alternatively.
	 * With this parseFuncKey we query for the parseFunc setup.
	 * If no setup is found we fall back to  "< lib.pareseFunc_RTE";
	 *
	 * But typically use the fallback variant for this.
	 *
	 * @param	mixed		key of internal data
	 * @param	string		key of configurator for parseFunc setup
	 * @return	mixed		parsed string
	 */
	public function asRte($key, $parseFuncKey = '') {
		if(is_object($this->configurator)) {
			$parseFuncKey = $parseFuncKey	? $parseFuncKey	: $this->parseFuncRteKey;
			$parseFunc = $this->configurator->get($parseFuncKey);
		}
		if(is_array($parseFunc)) {
			$setup['parseFunc.'] = $parseFunc;
		} elseif($parseFunc) {
			$setup['parseFunc'] = $parseFunc;
		} else {
			$setup['parseFunc'] = '< lib.parseFunc_RTE'; // fallback
		}
		$setup['value'] = $this->get($key);
		$cObject = $this->findCObject();
		return $cObject->cObjGetSingle('TEXT', $setup);
	}

	/**
	 * Get a string parsed for standard text input (parseFunc).
	 *
	 * This includes HTMLSPECIALCHARS
	 * and parsing of http://xxxx and mailto://xxxx to links.
	 *
	 * Behaves identical to asHtml() but additionally escapes html special characters.
	 *
	 * @param	mixed		key of internal data
	 * @param	string		key of configurator for parseFunc setup
	 * @return	mixed		parsed string
	 * @see		asHtml()
	 */
	public function asText($key, $parseFuncKey = '') {
		if(is_object($this->configurator)) {
			$parseFuncKey = $parseFuncKey	? $parseFuncKey	: $this->parseFuncTextKey;
			$parseFunc = $this->configurator->get($parseFuncKey);
		}
		if(is_array($parseFunc)) {
			$setup['parseFunc.'] = $parseFunc;
		} elseif($parseFunc) {
			$setup['parseFunc'] = $parseFunc;
		} else {
			$setup['parseFunc'] = '< lib.parseFunc';
		}
		$setup['value'] = htmlspecialchars($this->get($key));
		$cObject = $this->findCObject();
		return $cObject->cObjGetSingle('TEXT', $setup);
	}

	/**
	 * Get human readability and localized time for a timestamp out of the internal data array.
	 *
	 * If no format parameter is provided, the function tries to find one in the configurator
	 * by using the pathKey $this->timeFormatKey.
	 *
	 * @param	mixed		key of internal data array
	 * @param	string		format string
	 * @return	string		human readable date string
	 * @see		http://php.net/strftime
	 */
	public function asTime($key, $format = null) {
		if(!$format && is_object($this->configuraton)) {
			$format = $this->configurator->get($this->timeFormatKey);
		}
		if($format) {
			return strftime($format, $this->get($key));
		} else {
			return false;
		}
	}

	/**
	 * Make an external Typolink to an url.
	 *
	 * If no label key is given the url is displayed as label.
	 * If the label is available but no url the label is returned
	 * without the tag. If both fails nothing is returned.
	 *
	 * @param	mixed		key of the url field
	 * @param	mixed		key of the label field
	 * @return	string		the linktag
	 * @see		tx_div2007_link
	 */
	public function asUrl($urlKey, $labelKey = null ) {
		$link = tx_div::makeInstance('tx_div2007_link');
		$link->destination($this->get($urlKey));
		if($labelKey) {
			$link->label($this->get($labelKey), 1);
		}
		return $link->makeTag();
	}

	/**
	 * Returns the captcha question previously generated by a captcha class.
	 *
	 * @return	string		the captcha question
	 * @see		tx_div2007_captcha
	 */
	public function getCaptchaQuestion() {
		if(!$this->get('_captchaQuestion')) {
			$this->_die('Please include a captcha class into the SPL chain and call createTest($id) before you can call it', __FILE__, __LINE__);
		}
		return $this->get('_captchaQuestion');
	}

	/**
	 * Returns an input field to enter the answer to the captcha question.
	 *
	 * @param	mixed		an id to add to the input field
	 * @return	string		typically an (x)html string
	 * @see		tx_lib_captcha
	 */
	public function getCaptchaInput($id = null) {
		if(!$this->get('_captchaInput')) {
			$this->_die('Please include a captcha class into the SPL chain and call createTest($id) before you can call it', __FILE__, __LINE__);
		}
		return sprintf($this->get('_captchaInput'), ($id ? ' id="' . $id . '"' : ''));
	}

	/**
	 * Print a html formatted error list.
	 *
	 * @param	string		class name
	 * @param	string		key
	 * @return	void
	 */
	public function getErrorList($class = 'errors', $key = '_errorList') {
		$errorList = (array) $this->get($key);
		if(count($errorList)) {
			foreach($errorList as $error) {
				$out .= '<li>' . $error['message'] . '</li>';
			}
			printf('<ul%s>%s</ul>', $class ? ' class="' . trim($class) . '"' : '', $out);
		}
	}

	/**
	 * Print hidden fields.
	 *
	 * @return	string		typically an (x)html string
	 */
	public function getHiddenFields() {
		$parameters = $this->getParameters();
		return $parameters->toHiddenFields();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_viewBase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_viewBase.php']);
}
?>