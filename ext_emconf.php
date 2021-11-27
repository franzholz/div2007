<?php

/*********************************************************************
* Extension configuration file for ext "div2007".
*
*********************************************************************/

$EM_CONF[$_EXTKEY] = [
  'title' => 'Static Methods since 2007',
  'description' => 'This library offers classes and functions to other TYPO3 extensions. It provides a modified t3lib_div of TYPO3 4.7.10. Replacement for tslib_pibase methods and t3skin images.',
  'category' => 'misc',
  'version' => '1.12.1',
  'state' => 'stable',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearcacheonload' => 0,
  'author' => 'Franz Holzinger',
  'author_email' => 'franz@ttproducts.de',
  'author_company' => 'jambage.com',
  'constraints' =>
  [
    'depends' =>
    [
      'php' => '7.2.0-7.4.99',
      'typo3' => '7.6.0-11.5.99',
    ],
    'conflicts' =>
    [
    ],
  ]
];

