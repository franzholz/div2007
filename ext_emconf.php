<?php

########################################################################
# Extension Manager/Repository config file for ext "div2007".
#
# Auto generated 23-01-2012 21:19
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Static Methods for Extensions since 2007',
	'description' => 'This is a replacement of div and parts of lib. It brings t3lib_div from TYPO3 4.6',
	'category' => 'misc',
	'shy' => 0,
	'version' => '0.8.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Franz Holzinger',
	'author_email' => 'franz@ttproducts.de',
	'author_company' => 'jambage.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'excludeXCLASScheck' => array(
		'0' => '*div*',
		'1' => 'hooks/*',
	),
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-5.3.99',
			'typo3' => '4.3-4.6.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:29:{s:9:"ChangeLog";s:4:"b333";s:10:"README.txt";s:4:"ee2d";s:20:"class.tx_div2007.php";s:4:"c38b";s:26:"class.tx_div2007_alpha.php";s:4:"2a0b";s:27:"class.tx_div2007_alpha5.php";s:4:"4ec9";s:38:"class.tx_div2007_alpha_browse_base.php";s:4:"70f6";s:40:"class.tx_div2007_alpha_language_base.php";s:4:"135d";s:25:"class.tx_div2007_cobj.php";s:4:"241d";s:35:"class.tx_div2007_configurations.php";s:4:"f871";s:28:"class.tx_div2007_context.php";s:4:"8098";s:31:"class.tx_div2007_controller.php";s:4:"aec5";s:24:"class.tx_div2007_div.php";s:4:"93f2";s:26:"class.tx_div2007_email.php";s:4:"6ae4";s:26:"class.tx_div2007_error.php";s:4:"eaee";s:23:"class.tx_div2007_ff.php";s:4:"0cd7";s:27:"class.tx_div2007_object.php";s:4:"84b9";s:31:"class.tx_div2007_parameters.php";s:4:"f494";s:34:"class.tx_div2007_selfAwareness.php";s:4:"dd2e";s:29:"class.tx_div2007_t3Loader.php";s:4:"a56e";s:12:"ext_icon.gif";s:4:"b4e6";s:17:"ext_localconf.php";s:4:"070c";s:14:"doc/manual.sxw";s:4:"1482";s:14:"doc/manual.txt";s:4:"0cd4";s:14:"doc/phpdoc.ini";s:4:"5e47";s:36:"hooks/class.tx_div2007_hooks_cms.php";s:4:"c3e3";s:37:"hooks/class.tx_div2007_hooks_eval.php";s:4:"f5f7";s:25:"lang/locallang_common.xml";s:4:"4034";s:42:"spl/class.tx_div2007_spl_arrayIterator.php";s:4:"6e61";s:40:"spl/class.tx_div2007_spl_arrayObject.php";s:4:"b53f";}',
	'suggests' => array(
	),
);

?>