<?php

if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    return array();
}

$classArray = array();

require_once 'ClassAliasMapResult.php';

return $classArray;

