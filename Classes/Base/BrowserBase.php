<?php

namespace JambageCom\Div2007\Base;

/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger (franz@ttproducts.de)
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
* Part of the div2007 (Collection of static functions) extension.
*
* interface for the language object
* You can use a pibase object for it.
*
* @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
* @maintainer	Franz Holzinger <franz@ttproducts.de>
* @package TYPO3
* @subpackage div2007
*
*/


    // this class must be overridden and filled out. You can use the init method.
class BrowserBase {
    public $ctrlVars = array();
    public $internal = array();
    public $autoCacheFields = array();
    public $autoCacheEnable = false;
    public $is_USER_INT_obj;
    public $tmpPageId = 0;
    public $moreParams = '';
    public $conf;

    public function getAutoCacheEnable () {
        return $this->autoCacheEnable;
    }

    public function getIsUserIntObject () {
        return $this->is_USER_INT_obj;
    }

    /**
    * Initializes the control class for the browser
    *
    * @param	array		configuration
    * @param	array		ctrlVars: control array with the pointer variable as index
    * @param	array		ctrlLimits: limits to the pointer variable: the first index corresponds to the control array
    * 						[...]['range'][0]  ... lowest range value
    * 						[...]['range'][1]  ... highest range value
    * 						[...]['list']      ... list of allowed values
    * @param	boolean		$autoCacheEnable: auto cache enabled
    * @param	boolean		$is_USER_INT_obj: if it is a USER_INT object
    * @param	integer		resCount: total number of items in the query
    * @param	integer		limit: maximum number of items shown in the list
    * @param	integer		maxPages: maximum number of pages to browse
    * @param	boolean		bShowFirstLast: if the first and last entry will have a marker
    * @param	boolean		bAlwaysPrev: if the previous text is always shown even if there is no previous possible
    * @param	integer		pagefloat: page float
    * @param	array		imageArray: path - path to the images
    *									onmouseover
    *									onmouseout
    * @param	array		imageActiveArray: same as imageArray but for actively selected images
    *
    * @return	void
    */
    public function init (
        $conf,
        $ctrlVars,
        $ctrlLimits,
        $autoCacheEnable,
        $is_USER_INT_obj,
        $resCount,
        $limit,
        $maxPages,
        $bShowFirstLast,
        $bAlwaysPrev,
        $pagefloat,
        $imageArray,
        $imageActiveArray
    ) {
        $this->conf = $conf;
        $this->ctrlVars = $ctrlVars;
        $this->autoCacheFields = $ctrlLimits;
        $this->autoCacheEnable = $autoCacheEnable;
        $this->is_USER_INT_obj = $is_USER_INT_obj;
        $this->internal['resCount'] = $resCount;
        $this->internal['limit'] = $limit;
        $this->internal['maxPages'] = $maxPages;
        $this->internal['bShowFirstLast'] = $bShowFirstLast;
        $this->internal['bAlwaysPrev'] = $bAlwaysPrev;
        $this->internal['pagefloat'] = $pagefloat;
        $this->internal['image'] = $imageArray;
        $this->internal['imageactive'] = $imageActiveArray;
    }
}

