<?php

/*********************************************************************
* Extension configuration file for ext "div2007".
*
*********************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Static Methods since 2007',
  'description' => 'This library is called by several extensions. It provides a modified t3lib_div of TYPO3 4.7.10 and backwards compatibility to TYPO3 6.2 and 4.5.',
  'category' => 'misc',
  'shy' => 0,
  'version' => '1.7.12',
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
      'php' => '5.2.0-7.99.99',
      'typo3' => '4.5.0-8.99.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  )
);

