<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Elmar Hinz (elmar.hinz@team)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
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
 * The pluripotent stem cell of div2007
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
 * Parent class for tx_div2007_object
 *
 * <b>Don't use this class directly. Always use tx_div2007_object.</b>
 * <b>Please also see tx_div2007_object!!!</b>
 *
 * Depends on: tx_div2007, tx_div2007_selfAwareness, tx_div2007_spl_arrayIterator, tx_div2007_spl_arrayObject
 * Used by: tx_div2007_object
 *
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @package    TYPO3
 * @subpackage div2007
 * @see        tx_div2007_object
 */
class tx_div2007_objectBase extends tx_div2007_selfAwareness {
	public $controller;
	public $_iterator;

	/**
	 * Constructor of the data object
	 *
	 * You can set the controller by one of the 2 parameters.
	 * You can set the data by one of the 2 prameters. Order doesn't matter.
	 *
	 * If you don't set the controller in the constructor you MUST set it by one of the functions:
	 * $this->controller($controller), $this->setController($controller);
	 *
	 * @param	mixed		controller or data array or data object
	 * @param	mixed		controller or data array or data object
	 * @return	void
	 */
	public function __construct($parameter1 = null, $parameter2 = null) {
		$this->_iterator = new tx_div2007_spl_arrayIterator();
		if(method_exists($this, 'preset')) {
			$this->preset();
		}
		if(is_object($parameter1) && is_subclass_of($parameter1, 'tx_div2007_controller')) {
			$this->controller = $parameter1;
		} elseif(isset($parameter1)) {
			$this->overwriteArray($parameter1);
		}
		if(is_object($parameter2) && is_subclass_of($parameter2, 'tx_div2007_controller')) {
			$this->controller = $parameter2;
		} elseif(isset($parameter2)) {
			$this->overwriteArray($parameter2);
		}
		if(method_exists($this, 'construct')) {
			$this->construct();
		}
	}

	// -------------------------------------------------------------------------------------
	// Interface to _div2007_spl_arrayObject
	// -------------------------------------------------------------------------------------

	/**
	 * Appends the given value as element to this array.
	 *
	 * @param	mixed		value to append
	 */
	public function append ($value) {
		$this->_iterator->append($value);
	}

	/**
	 * Sorts this array using the asort() function of PHP.
	 */
	public function asort () {
		$this->_iterator->asort();
	}

	/**
	 * Counts the elements in the array.
	 *
	 * @return	integer		number of elements
	 */
	public function count () {
		return $this->_iterator->count();
	}

	/**
	 * Replaces the current array handled by this object with the new one
	 * given as argument.
	 *
	 * @param	array		the new array to be set
	 */
	public function exchangeArray ($array) {
		$this->_iterator->exchangeArray($array);
	}

	/**
	 * Returns a copy of the array handled by this object.
	 *
	 * @return	array		a copy of the array
	 */
	public function getArrayCopy () {
		return $this->_iterator->getArrayCopy();
	}

	/**
	 * Returns the flags associated with this object.
	 *
	 * @return	integer		the flags
	 */
	public function getFlags () {
		return $this->_iterator->getFlags();
	}

	/**
	 * Returns a new iterator object for this array.
	 *
	 * @return	object		the new iterator
	 */
	public function getIterator () {
		return $this->_iterator->getIterator();
	}

	/**
	 * Returns the class name of the iterator associated with this object.
	 *
	 * @return	string		iterator class name
	 */
	public function getIteratorClass () {
		return $this->_iterator->getIteratorClass();
	}

	/**
	 * Sorts this array using the ksort() function of PHP.
	 */
	public function ksort () {
		$this->_iterator->ksort();
	}

	/**
	 * Sorts this array using the natcasesort() function of PHP.
	 */
	public function natcasesort () {
		$this->_iterator->natcasesort();
	}

	/**
	 * Sorts this array using the natsort() function of PHP.
	 */
	public function natsort () {
		$this->_iterator->natsort();
	}

	/**
	 * Tests if an element exists at the given offset.
	 *
	 * @param	integer		array offset to test
	 * @return	boolean		true if element exists, false otherwise
	 */
	public function offsetExists ($index) {
		$result = $this->_iterator->offsetExists($index);
		return $result;
	}

	/**
	 * Returns the element at the given offset.
	 *
	 * @param	integer		the index of the element to be returned
	 * @return	mixed		the element at given index
	 */
	public function offsetGet ($index) {
		$result = $this->_iterator->offsetGet($index);
		return $result;
	}

	/**
	 * Writes a value to a given offset in the array.
	 *
	 * @param	integer		the offset to write to
	 * @param	mixed		the new value
	 */
	public function offsetSet ($index, $newval) {
		$this->_iterator->offsetSet($index, $newval);
	}

	/**
	 * Unsets the element at the given offset.
	 *
	 * @param	integer		position of array to unset
	 */
	public function offsetUnset ($index) {
		$this->_iterator->offsetUnset($index);
	}

	/**
	 * Sets the flags.
	 *
	 * @param	integer		the flags
	 */
	public function setFlags ($flags) {
		$this->_iterator->setFlags($flags);
	}

	/**
	 * Set the name of the iterator class to the one given as argument.
	 *
	 * @param	string		name of iterator class
	 */
	public function setIteratorClass ($iteratorClass) {
		$this->_iterator->setIteratorClass($iteratorClass);
	}

	/**
	 * Sorts this array using the uasort() function of PHP.
	 *
	 * @param	function		a function used as callback for sorting
	 */
	public function uasort ($userFunction) {
		$this->_iterator->uasort($userFunction);
	}

	/**
	 * Sorts this array using the uksort() function of PHP.
	 *
	 * @param	function		a function used as callback for sorting
	 */
	public function uksort ($userFunction) {
		$this->_iterator->uksort($userFunction);
	}

	// -------------------------------------------------------------------------------------
	// Interface to tx_div2007_spl_arrayIterator
	// -------------------------------------------------------------------------------------

	/**
	 * Returns the current element in the iterated array.
	 *
	 * @return	mixed		the current element
	 */
	public function current () {
		return $this->_iterator->current();
	}

	/**
	 * Returns the key of the current element in array.
	 *
	 * @return	mixed		the key of the current element
	 */
	public function key () {
		return $this->_iterator->key();
	}

	/**
	 * Moves the iterator to next element in array.
	 *
	 * @return	boolean		true if there is a next element, false otherwise
	 */
	public function next () {
		$this->_iterator->next();
	}

	/**
	 * Resets the iterator to the first element of array.
	 *
	 * @return	boolean		true if the array is not empty, false otherwise
	 */
	public function rewind () {
		$this->_iterator->rewind();
	}

	/**
	 * Returns the element of array at index $index.
	 *
	 * @param	integer		the position of the requested element in array
	 * @return	mixed		an array element
	 */
	public function seek ($index) {
		return $this->_iterator->seek($index);
	}

	/**
	 * Returns the actual state of this iterator.
	 *
	 * @return	boolean		true if iterator is valid, false otherwise
	 */
	public function valid () {
		return $this->_iterator->valid();
	}

	// -------------------------------------------------------------------------------------
	// Setters
	// -------------------------------------------------------------------------------------

	/**
	 * Import the data as an object containing a list of hash objects
	 *
	 * Takes a list array or list object of hash data as first argument
	 * and a class (SPL type) as second argument. For each of the hash
	 * data an object of that class is created and appended to the
	 * internal array.
	 *
	 * @param	object		the data object list (i.e. from the model)
	 * @param	string		classname of output entries, defaults to _div2007_object
	 * @return	void
	 */
	public function asObjectOfObjects ($objectList, $entryClassName = '_div2007_object') {
		$this->checkController(__FILE__, __LINE__);
		$this->clear();
		for($objectList->rewind(); $objectList->valid(); $objectList->next()) {
			$entryClassObject = tx_div2007::makeInstance(
				$entryClassName, $this->controller, tx_div2007::toHashArray($objectList->current())
			);
			$this->append($entryClassObject);
		}
	}

	/**
	 * Convert the internal elmements to objects of the given class name
	 *
	 * All (hash) elements of the internal array are transformed to objects of
	 * the class given as parameter.
	 *
	 * By default the function tx_div2007::makeInstance() is applied. That means:
	 *
	 * - The file is loaded automatically.
	 * - XCLASS is used if available.
	 *
	 * @param  string   Class name for the internal elements.
	 * @return void
	 * @see    tx_div2007::makeInstance()
	 */
	public function castElements ($entryClassName = 'tx_div2007_object') {

		for($this->rewind(); $this->valid(); $this->next()) {
			$entryObject = tx_div2007::makeInstance(
				$entryClassName,
				$this->controller,
				tx_div2007::toHashArray($this->current())
			);
			$this->set($this->key(), $entryObject);
		}
	}

	/**
	 * Empty the object
	 *
	 * Clear the objects array.
	 *
	 * @return	void
	 */
	public function clear () {
		$this->exchangeArray(array());
	}

	/**
	 * Overwrite some of the internal array values
	 *
	 * Overwrite a selection of the internal values by providing new ones
	 * in form of a data structure of the tx_div2007 hash family.
	 *
	 * @param	mixed		hash array, SPL object or2007 hash string ( i.e. "key1 : value1, key2 : valu2, ... ")
	 * @param	string		possible split charaters in case the first parameter is a hash string
	 * @return	void
	 * @see		tx_div2007::toHashArray()
	 */
	public function overwriteArray ($hashData, $splitCharacters = ',;:\s') {
		$array = tx_div2007::toHashArray($hashData, $splitCharacters);
		foreach((array) $array as $key => $value) {
			$this->set($key, $value);
		}
	}

	/**
	 * Assign a value to a key
	 *
	 * It's just a convenient way to use the offsetSet() function from _div2007_spl_arrayObject.
	 *
	 * @param	mixed		key
	 * @param	mixed		value
	 * @return	void
	 * @see		div2007_spl_arrayObject::offsetSet()
	 */
	public function set ($key, $value) {
		$this->offsetSet($key, $value);
	}

	/**
	 * Set or exchange all array values
	 *
	 * On the one hand it works as an alias to $this->exchangeArray().
	 * On the other it is a little more flexible, as it takes all data members
	 * of the tx_div2007 hash family as parameters.
	 *
	 * @param	mixed		hash array, SPL object or hash string ( i.e. "key1 : value1, key2 : valu2, ... ")
	 * @param	string		possible split charaters in case the first parameter is a hash string
	 * @return	void
	 * @see		tx_div2007::toHashArray()
	 */
	public function setArray ($hashData, $splitCharacters = ',;:\s') {
		$this->exchangeArray(tx_div2007::toHashArray($hashData, $splitCharacters));
	}

	// -------------------------------------------------------------------------------------
	// Getters
	// -------------------------------------------------------------------------------------

	/**
	 * Dump the internal data array
	 *
	 * If a key is given, only the value is selected.
	 *
	 * @param   optional key
	 * @return	void
	 */
	public function dump ($key = NULL) {
		if($key)
			$value = $this->get($key);
		else
			$value = $this->getHashArray();
		print '<pre>';
		print htmlspecialchars(print_r($value, 1));
		print '</pre>';
	}

	/**
	 * Get a value for a key
	 *
	 * It's just a convenient way to use the offsetGet() function from _div2007_spl_arrayObject.
	 *
	 * @param	mixed		key
	 * @return	mixed		value
	 * @see		tx_div2007_spl_arrayObject::offsetGet()
	 */
	public function get ($key) {
		$result = $this->offsetGet($key);
		return $result;
	}

	/**
	 * Alias for getArrayCopy
	 *
	 * @return	array		Copy of the internal array
	 */
	public function getHashArray () {
		$result = $this->getArrayCopy();
		return $result;
	}

	/**
	 * Export the data as an object containing a list of objects
	 *
	 * This object has to contain a list of hash data.
	 * The hash data is created into the exported object as hash objects.
	 * The classes of the exported object and the entries are take as arguments.
	 *
	 * @param	string		Classname of exported object, defaults to tx_div2007_object.
	 * @param	string		Classname of exported entries, defaults to tx_div2007_object.
	 * @return	object  The exported object.
	 */
	public function toObjectOfObjects ($outputListClass = 'tx_div2007_object', $outputEntryClass = 'tx_div2007_object') {
		$this->checkController(__FILE__, __LINE__);
		$outputList = tx_div2007::makeInstance($outputListClass);
		$outputList->controller = $this->controller;

		for($this->rewind(); $this->valid(); $this->next()) {
			$outputEntryObject = tx_div2007::makeInstance(
				$outputEntryClass,
				$this->controller,
				tx_div2007::toHashArray($this->current())
			);
			$outputList->append($outputEntryObject);
		}
		return $outputList;
	}

	/**
	 * Find out if there is a content for this key
	 *
	 * Returns true if something has been set for the variable,
	 * even if it is 0 or the empty string.
	 *
	 * @param	mixed		key of internal data array
	 * @return	boolean		is something set?
	 */
	public function has ($key) {
		$result = ($this->get($key) != null);
		return $result;
	}

	/**
	 * Find out if this object has something in his data array
	 *
	 * @return	boolean		is it empty?
	 */
	public function isEmpty () {
		$result = ($this->count() == 0);
		return $result;
	}

	/**
	 * Find out if this object has something in his data array
	 *
	 * @return	boolean		is something in it?
	 */
	public function isNotEmpty () {
		$result = ($this->count() > 0);
		return $result;
	}

	/**
	 * Return a selection of the object values as hash array.
	 *
	 * The parameter is of the list family defined in tx_div2007. (object, array, string)
	 * The return value is an of the hash type defind in tx_div2007.
	 *
	 * @param	mixed		string, array or object of the tx_div2007 list family
	 * @param	string		a string of characters to split the keys string
	 * @return	array		selected values associative array
	 * @see		tx_div2007:toListArray();
	 */
	public function selectHashArray ($keys, $splitCharacters = ',;:\s') {
		foreach(tx_div2007::toListArray($keys, $splitCharacters) as $key) {
			$return[$key] = $this->get($key);
		}
		return (array) $return;
	}

	// -------------------------------------------------------------------------------------
	// Session
	// -------------------------------------------------------------------------------------

	/**
	 * Stores this object data under the key "key" into the current session.
	 *
	 * @param	mixed		the key
	 * @return	void
	 */
	public function storeToSession ($key) {
		session_start();
		$_SESSION[$key] = new tx_div2007_object($this); // use a copy resp. a new object (for PHP4)
		$_SESSION[$key . '.']['className'] = $this->getClassName();
	}

	/**
	 * Retrieves data from the current session. The data is accessed by "key".
	 *
	 * @param	mixed		the key
	 * @return	void
	 */
	public function loadFromSession ($key) {
		session_start();
		if($className = $_SESSION[$key . '.']['className']){
			tx_div2007::load($className);
			session_write_close();
			session_start();
			$this->overwriteArray($_SESSION[$key]);
		}
	}

	// -------------------------------------------------------------------------------------
	// GetSetters for the controller
	// -------------------------------------------------------------------------------------

	/**
	 * Check presence of the controller
	 *
	 * @param	string		set the __FILE__ constant
	 * @param	string		set the __LINE__ constant
	 * @return	object		tx_div2007_controller
	 */
	public function checkController ($file, $line) {
		if(!is_object($this->controller)) {
			$this->_die('Missing the controller.', $file, $line);
		} else {
			return $this->controller;
		}
	}

	/**
	 * Set and get the controller object
	 *
	 * @param	object		tx_div2007_controller type
	 * @return	object		tx_div2007_controller type
	 */
	public function controller ($object = NULL) {
		$object = $this->controller = $object ? $object : $this->controller;
		if (!$object) {
			die('Missing controller in ' . __CLASS__ . ' line ' . __LINE__);
		}
		return $object;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_objectbase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_objectbase.php']);
}

?>