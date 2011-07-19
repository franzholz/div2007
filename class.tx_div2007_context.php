<?php

/**
 * Access to context values of the plugin
 *
 * PHP version 5
 *
 * Copyright (c) 2006-2011 Elmar Hinz
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
 * @version    SVN: $Id$
 * @since      0.1
 */

/**
 * Give access to different properties of the context the controller lives in
 *
 * Member of the central quad: $controller, $parameters, $configurations, $context.	<br>
 * Address it from everywhere as: $this->controller->context.
 *
 * Gives access to $TSFE and $cObj and their many properties.
 *
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @package    TYPO3
 * @subpackage div2007
 */
class tx_div2007_context extends tx_div2007_object {

	public $contentObject;

	public function setContentObject (&$contentObject) {
		return $this->contentObject =& $contentObject;
	}

	public function getContentData () {
		$classObject = tx_div2007::makeInstance('tx_div2007_object', $this->controller, $this->contentObject->data);
		return $classObject;
	}

	public function getContentObject () {
		return $this->contentObject;
	}

	public function getData ($key) {
		return $this->contentObject->getData($key, $this->contentObject->data);
	}

	public function getFrontEnd () {
		return $GLOBALS['TSFE'];
	}

	public function getPageData () {
		$classObject = tx_div2007::makeInstance('tx_div2007_object', $this->controller, $GLOBALS['TSFE']->page);
		return $classObject;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_context.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_context.php']);
}
?>
