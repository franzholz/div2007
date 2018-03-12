/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Universal formupdate-function
 *
 * @param formId
 * @param fieldname
 * @param value
 */
function updateForm(formId, fieldname, value) {
	function htmlSpecialChars_decode (str) {
		return str.replace(/&lt;/g, '<')
			.replace(/&gt;/g, '>')
			.replace(/&amp;/g, '&')
			.replace(/&quot;/g, '"');
	}

	var formObj = document.getElementById(formId);
	if (formObj && formObj[fieldname]) {
		var fObj = formObj[fieldname];
		var type=fObj.type;
		if (!fObj.type) {
			type="radio";
		}
		switch(type) {
			case "text":
			case "textarea":
			case "hidden":
				fObj.value = htmlSpecialChars_decode(value);
			break;
			case "password":
				fObj.value = value;
			break;
			case "checkbox":
				fObj.checked = ((value && value != 0) ? "1" : "");
			break;
			case "select-one":
				var l=fObj.length;
				for (a=0;a<l;a++) {
					if (fObj.options[a].value == value) {
						fObj.selectedIndex = a;
					}
				}
			break;
			case "select-multiple":
				var l=fObj.length;
				for (a=0;a<l;a++) {
					if (fObj.options[a].value == value) {
						fObj.options[a].selected = 1;
					}
				}
			break;
			case "radio":
				var l=fObj.length;
				for (a = 0; a < l; a++) {
					if (fObj[a].value == value) {
						fObj[a].checked = 1;
					}
				}
			break;
			default:
		}
	}
}
