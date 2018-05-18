<?php

/*********************************************************************
* Extension configuration file for ext "div2007".
*
*********************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Static Methods since 2007',
  'description' => 'This library offers classes and functions to other TYPO3 extensions. It provides a modified t3lib_div of TYPO3 4.7.10. It replaces most tslib_pibase methods.',
  'category' => 'misc',
  'shy' => 0,
  'version' => '1.10.4',
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
  'constraints' =>
  array (
    'depends' =>
    array (
      'php' => '5.5.0-7.99.99',
      'typo3' => '6.2.0-8.99.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
      'migration_core' => '0.0.1-0.99.99',
    ),
  )
);

