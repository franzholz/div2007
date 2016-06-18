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
 * This is the "pluripotent stem cell" of div2007.
 *
 * <b>MOST CENTRAL OBJECT</b>
 *
 * This object is the common parent of almoust all objects used in div2007 development. It provides
 * functionality and an API that all lib/div objects have in common. By knowing this object you know
 * 90% of all objects.
 *
 * This class implements the powerfull PHP5 interfaces <b>ArrayAccess</b> and <b>Iterator</b> and
 * also backports them for PHP4. This is done by implementing the central SPL classes <b>ArrayObject</b>
 * and <b>ArrayIterator</b> in form of plain PHP code.
 *
 * <a href="http://de2.php.net/manual/en/ref.spl.php">See Standard PHP Library</a>
 *
 * <b>ArrayAccess</b>
 *
 * Access the values of an object by keys like an array.
 *
 *   $value = $this->parameters['exampleKey']
 * or
 *   $value = $this->parameters->get('exampleKey');
 *
 * <b>Iterator</b>
 *
 * Iterate over the values of an object just like an array.
 *
 *   foreach($this->parameters as $key => $value) { ... }
 * or:
 *   for($this->parameters->rewind(), $this->parameters->valid(), $this->parameters->next()) {
 *      $key = $this->parameters->key();
 *      $value = $this->parameters->current();
 *   }
 *
 * <b>The request cycle as a chain of SPL objects</b>
 *
 * A central feature of SPL objects is the possiblity to feed one SPL object into the constructor of the next.
 * By this list of values can be processed by a chain of SPL objects alwasys using the same simple API.
 * It is suggested to implement the different stations of the request cycle from request to response in form
 * of SPL objects.
 *
 * The class provides a lot of addiotional functions to make setting and getting still more comfortables.
 * Functions to store the data into the session are also provided.
 *
 *
 * Depends on: tx_div2007_objectBase
 * Used by: All object within this framework by direct or indirect inheritance.
 *
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @package    TYPO3
 * @subpackage div2007
 * @see        tx_div2007_objectBase
 */

// require_once (PATH_BE_div2007 . 'class.tx_div2007_objectbase.php');





class tx_div2007_object extends tx_div2007_objectBase implements ArrayAccess, SeekableIterator {

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_object.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/div2007/class.tx_div2007_object.php']);
}

?>