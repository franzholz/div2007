<?php

/*********************************************************************
* Extension configuration file for ext "div2007".
*
*********************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Static Methods since 2007',
  'description' => 'This library is called by other extensions. It provides a modified t3lib_div of TYPO3 4.7.10 and backwards compatibility to TYPO3 6.2 and 4.5.',
  'category' => 'misc',
  'shy' => 0,
  'version' => '1.5.0',
  'dependencies' => '',
  'conflicts' => '',
  'suggests' => '',
  'priority' => '',
  'loadOrder' => '',
  'module' => '',
  'state' => 'stable',
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
  'excludeXCLASScheck' =>
  array (
    0 => '*div*',
    1 => 'hooks/*',
  ),
  'constraints' =>
  array (
    'depends' =>
    array (
      'php' => '5.2.0-5.6.99',
      'typo3' => '4.5.0-7.4.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:32:{s:9:"ChangeLog";s:4:"7a0e";s:20:"class.tx_div2007.php";s:4:"c151";s:26:"class.tx_div2007_alpha.php";s:4:"df57";s:27:"class.tx_div2007_alpha5.php";s:4:"1214";s:38:"class.tx_div2007_alpha_browse_base.php";s:4:"1f80";s:40:"class.tx_div2007_alpha_language_base.php";s:4:"f4f0";s:25:"class.tx_div2007_cobj.php";s:4:"b281";s:35:"class.tx_div2007_configurations.php";s:4:"3921";s:28:"class.tx_div2007_context.php";s:4:"b7c1";s:31:"class.tx_div2007_controller.php";s:4:"8dbc";s:24:"class.tx_div2007_div.php";s:4:"9405";s:26:"class.tx_div2007_email.php";s:4:"1ebe";s:26:"class.tx_div2007_error.php";s:4:"eaee";s:23:"class.tx_div2007_ff.php";s:4:"271e";s:27:"class.tx_div2007_object.php";s:4:"df91";s:31:"class.tx_div2007_objectbase.php";s:4:"54ef";s:31:"class.tx_div2007_parameters.php";s:4:"9a7b";s:34:"class.tx_div2007_selfAwareness.php";s:4:"ffbb";s:26:"class.tx_div2007_store.php";s:4:"46ce";s:29:"class.tx_div2007_t3Loader.php";s:4:"2528";s:16:"ext_autoload.php";s:4:"978c";s:12:"ext_icon.gif";s:4:"b4e6";s:17:"ext_localconf.php";s:4:"6217";s:10:"README.txt";s:4:"ee2d";s:14:"doc/manual.sxw";s:4:"1482";s:14:"doc/manual.txt";s:4:"0cd4";s:14:"doc/phpdoc.ini";s:4:"5e47";s:36:"hooks/class.tx_div2007_hooks_cms.php";s:4:"18dc";s:37:"hooks/class.tx_div2007_hooks_eval.php";s:4:"bdcf";s:25:"lang/locallang_common.xml";s:4:"4034";s:42:"spl/class.tx_div2007_spl_arrayIterator.php";s:4:"7b32";s:40:"spl/class.tx_div2007_spl_arrayObject.php";s:4:"a5a4";}',
);

?>