<?php

/*********************************************************************
* Extension configuration file for ext "div2007".
*
*********************************************************************/

$EM_CONF[$_EXTKEY] = [
  'title' => 'Static Methods since 2007',
  'description' => 'This library offers classes and functions to other TYPO3 extensions. It provides a modified t3lib_div of TYPO3 4.7.10. Replacement for tslib_pibase methods and t3skin images.',
  'category' => 'misc',
  'version' => '1.14.0',
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
      'php' => '7.4.0-8.1.99',
      'typo3' => '10.4.0-11.5.99',
    ],
    'conflicts' =>
    [
    ],
  ]
];

