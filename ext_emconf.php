<?php

// Extension configuration file for ext "div2007".

$EM_CONF[$_EXTKEY] = [
  'title' => 'Static Methods since 2007',
  'description' => 'This library offers classes and functions to other TYPO3 extensions. It provides a modified t3lib_div of TYPO3 4.7.10. Replacement for tslib_pibase methods.',
  'category' => 'misc',
  'version' => '2.1.1',
  'state' => 'stable',
  'author' => 'Franz Holzinger',
  'author_email' => 'franz@ttproducts.de',
  'author_company' => 'jambage.com',
  'constraints' => [
    'depends' => [
      'php' => '8.0.0-8.4.99',
      'typo3' => '12.4.0-12.4.99',
    ],
    'suggests' => [
        'typo3db_legacy' => '1.2.0-1.2.99',
    ],
    'conflicts' => [
    ],
  ],
];
