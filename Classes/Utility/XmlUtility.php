<?php

namespace JambageCom\Div2007\Utility;


/***************************************************************
*  Copyright notice
*
*  (c) 2016 Franz Holzinger (franz@ttproducts.de)
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
 * XML functions.
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 */


class XmlUtility {

	static public function xml_to_object ($xml) {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $tags);
		xml_parser_free($parser);

		$elements = [];  // the currently filling [child] XmlElement array
		$stack = [];
		foreach ($tags as $tag) {
			$index = count($elements);
			if (
				$tag['type'] == 'complete' ||
				$tag['type'] == 'open'
			) {
				$elements[$index] = new XmlElement;
				$elements[$index]->name = $tag['tag'];
				$elements[$index]->attributes = $tag['attributes'];
				$elements[$index]->content = $tag['value'];
				if ($tag['type'] == 'open') {  // push
					$elements[$index]->children = [];
					$stack[count($stack)] = &$elements;
					$elements = &$elements[$index]->children;
				}
			}

			if ($tag['type'] == 'close') {  // pop
				$elements = &$stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
			}
		}
		return $elements[0];  // the single top-level element
	}
}

