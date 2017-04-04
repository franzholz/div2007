<?php

$fluidViewHelper = '\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\';

if (version_compare(TYPO3_version, '8.0.0', '<')) {

    $fluidViewHelper = '\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\';
}

if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    return;
}

require_once 'ClassAliasMapResult.php';

if (version_compare(TYPO3_version, '8.0.0', '>=')) {

    unset ($result['Tx_Fluid_Core_ViewHelper_Exception_InvalidVariableException']);
    unset ($result['Tx_Fluid_Core_ViewHelper_Exception_RenderingContextNotAccessibleException']);
    unset ($result['Tx_Fluid_Core_ViewHelper_Facets_ChildNodeAccessInterface']);
    unset ($result['Tx_Fluid_Core_ViewHelper_Facets_CompilableInterface']);
    unset ($result['Tx_Fluid_Core_ViewHelper_Facets_PostParseInterface']);

}

return $result;


