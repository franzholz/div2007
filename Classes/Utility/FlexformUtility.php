<?php

namespace JambageCom\Div2007\Utility;


/***************************************************************
*  Copyright notice
*
*  (c) 2017 Fabien Udriot (fudriot@omic.ch)
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
 * Collection of static functions for flexforms
 *
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Fabien Udriot <fudriot@omic.ch>
 * @copyright  2006-2007 Fabien Udriot
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since      0.1
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Collection of static functions for flexforms
 *
 * This class contains diverse static functions to support flexform handling.
 *
 * @package    TYPO3
 * @subpackage div2007
 * @author     Fabien Udriot <fudriot@omic.ch>
 */
class FlexformUtility {

    /**
    * The current loaded flexform
    *
    * @var array
    */
    static protected $flexForm = array(); //the current loaded flexform

    /**
    * A set of flexforms that are stored in case they are going to be used.
    *
    * @var array
    */
    static protected $flexForms = array();

    /**
    * Load a flexform into memory
    *
    * @param   mixed       $flexForm can be an xml string or an array or a key array.
    * @param   string      $flexFormName (optinal) give a name to the flexform in order to be stored for further use.
    * @return  void
    */
    static public function load (
        $flexForm,
        $flexFormName = ''
    ) {
        //handle the case $flexForm is a string. It can be a xml string or key array
        if(is_string($flexForm)) {
            //test if $flexForm already exists in the memory. In this case load the flexform according to its key
            if(array_key_exists($flexForm, static::$flexForms)) {
                static::$flexForm = static::$flexForms[$flexForm];
            } else {
                //if false, it means it is *still* a string to convert in an array
                static::$flexForm = GeneralUtility::xml2array($flexForm);
            }
        } else {
            //else it is right away an array, load it in memory
            static::$flexForm = $flexForm;
        }

        //true when the flexform is going to be stored for further use
        if($flexFormName != '') {
            static::setFlexForm($flexFormName, static::$flexForm);
        }
    }

    /**
    * Add a flexform in memory
    *
    * @param   string     the flexForm name
    * @param   array      the flexForm
    * @return  void
    */
    static public function setFlexForm (
        $flexFormName,
        $flexForm
    ) {
        static::$flexForms[$flexFormName] = $flexForm;
    }

    /**
    * Get a flexform from memory
    *
    * @param   string     the flexForm name
    * @return  array      the flexform
    */
    static public function getFlexForm ($flexFormName) {
        $result = false;
        if(array_key_exists($flexFormName, static::$flexForms)) {
            $result = static::$flexForms[$flexFormName];
        }
        return $result;
    }

    /**
    * Return value from somewhere inside the loaded flexForm structure
    *
    * @param   mixed      $flexForm, (optional) a flexForm array or a key array that contains a flexform
    * @param   string     $fieldName, Field name to extract. Can be given like "test/el/2/test/el/field_templateObject" where each part will dig a level deeper in the FlexForm data.
    * @param   string     $sheet Sheet pointer, eg. "sDEF"
    * @param   string     $lang Language pointer, eg. "lDEF"
    * @param   string     $value Value pointer, eg. "vDEF"
    * @return  array      The content.
    */
    static public function get () {

        //true when the first argument is a flexForm or a reference to flexForm
        if(
            is_array(func_get_arg(0)) ||
            array_key_exists(
                func_get_arg(0),
                static::$flexForms
            )
        ) {
            //case 1, $args 1 is an array...     case 2, $args 1 is a key array that contains a flexform
            is_array(func_get_arg(0)) ? $_flexForm = func_get_arg(0) : $_flexForm = static::getFlexForm(func_get_arg(0));
            $index = 1;
        } else {
            $_flexForm = static::$flexForm;
            $index = 0;
        }
        $fieldName = func_get_arg($index);
        @func_get_arg($index + 1) ? $sheet = func_get_arg($index + 1) : $sheet = 'sDEF';
        @func_get_arg($index + 2) ? $lang = func_get_arg($index + 2) : $lang = 'lDEF';
        @func_get_arg($index + 3) ? $value = func_get_arg($index + 3) : $value = 'vDEF';

        is_array($_flexForm) ? $sheetArray = $_flexForm['data'][$sheet][$lang] : $sheetArray = '';
        $result = null;
        if (is_array($sheetArray)) {
            $result =
                static::_getFFValueFromSheetArray(
                    $sheetArray,
                    explode('/', $fieldName),
                    $value
                );
        }
        return $result;
    }

    /**
    * Returns part of $sheetArray pointed to by the keys in $fieldNameArray
    *
    * @param   array      Multidimensiona array, typically FlexForm contents
    * @param   array      Array where each value points to a key in the FlexForms content - the input array will have the value returned pointed to by these keys. All integer keys will not take their integer counterparts, but rather traverse the current position in the array an return element number X (whether this is right behavior is not settled yet...)
    * @param   string     Value for outermost key, typ. "vDEF" depending on language.
    * @return  mixed      The value, typ. string. private
    */
    static public function _getFFValueFromSheetArray (
        $sheetArray,
        $fieldNameArr,
        $value
    ) {
        $tempArr = $sheetArray;
        foreach($fieldNameArr as $k => $v) {
            if (
                MathUtility::canBeInterpretedAsInteger($v)
            ) {
                if (is_array($tempArr)) {
                    $c = 0;
                    foreach($tempArr as $values) {
                        if ($c == $v) {
                            $tempArr = $values;
                            break;
                        }
                        $c++;
                    }
                }
            } else {
                $tempArr = $tempArr[$v];
            }
        }
        return $tempArr[$value];
    }
}

