<?php

// Extension configuration file for ext "div2007".

$EM_CONF[$_EXTKEY] = [
  'title' => 'Extension Library since 2007',
  'description' => 'This library offers classes and functions to other TYPO3 extensions. Replacement for tslib_pibase methods.',
  'category' => 'misc',
  'version' => '2.4.0',
  'state' => 'stable',
  'author' => 'Franz Holzinger',
  'author_email' => 'franz@ttproducts.de',
  'author_company' => 'jambage.com',
  'constraints' => [
    'depends' => [
      'php' => '8.2.0-8.5.99',
      'typo3' => '12.4.0-14.3.99',
    ],
    'suggests' => [
        'typo3db_legacy' => '1.2.0-1.3.99',
    ],
    'conflicts' => [
    ],
  ],
];
