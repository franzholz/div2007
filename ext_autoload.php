<?php
$callingClassName = '\\TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';

if (
	class_exists($callingClassName) &&
	method_exists($callingClassName, 'extPath')
) {
	// nothing
} else {
	$callingClassName = 't3lib_extMgm';
}

$extensionPath = call_user_func($callingClassName . '::extPath', 'div2007');
return array(
	'tx_div2007' => $extensionPath . 'class.tx_div2007.php',
	'tx_div2007_alpha' => $extensionPath . 'class.tx_div2007_alpha.php',
	'tx_div2007_alpha5' => $extensionPath . 'class.tx_div2007_alpha5.php',
	'tx_div2007_alpha_browse_base' => $extensionPath . 'class.tx_div2007_alpha_browse_base.php',
	'tx_div2007_alpha_language_base' => $extensionPath . 'class.tx_div2007_alpha_language_base.php',
	'tx_div2007_cobj' => $extensionPath . 'class.tx_div2007_cobj.php',
	'tx_div2007_compatibility6' => $extensionPath . 'class.tx_div2007_compatibility6.php',
	'tx_div2007_configurations' => $extensionPath . 'class.tx_div2007_configurations.php',
	'tx_div2007_context' => $extensionPath . 'class.tx_div2007_context.php',
	'tx_div2007_controller' => $extensionPath . 'class.tx_div2007_controller.php',
	'tx_div2007_core' => $extensionPath . 'class.tx_div2007_core.php',
	'tx_div2007_core_php53' => $extensionPath . 'class.tx_div2007_core_php53.php',
	'tx_div2007_div' => $extensionPath . 'class.tx_div2007_div.php',
	'tx_div2007_email' => $extensionPath . 'class.tx_div2007_email.php',
	'tx_div2007_error' => $extensionPath . 'class.tx_div2007_error.php',
	'tx_div2007_ff' => $extensionPath . 'class.tx_div2007_ff.php',
	'tx_div2007_link' => $extensionPath . 'class.tx_div2007_link.php',
	'tx_div2007_object' => $extensionPath . 'class.tx_div2007_object.php',
	'tx_div2007_objectbase' => $extensionPath . 'class.tx_div2007_objectbase.php',
	'tx_div2007_parameters' => $extensionPath . 'class.tx_div2007_parameters.php',
	'tx_div2007_phpTemplateEngine' => $extensionPath . 'class.tx_div2007_phpTemplateEngine.php',
	'tx_div2007_selfawareness' => $extensionPath . 'class.tx_div2007_selfAwareness.php',
	'tx_div2007_t3loader' => $extensionPath . 'class.tx_div2007_t3Loader.php',
	'tx_div2007_hooks_cms' => $extensionPath . 'hooks/class.tx_div2007_hooks_cms.php',
	'tx_div2007_spl_arrayiterator' => $extensionPath . 'spl/class.tx_div2007_spl_arrayIterator.php',
	'tx_div2007_spl_arrayobject' => $extensionPath . 'spl/class.tx_div2007_spl_arrayObject.php',
	'tx_div2007_staticinfotables' => $extensionPath . 'lib/class.tx_div2007_staticinfotables.php',
	'tx_div2007_store' => $extensionPath . 'class.tx_div2007_store.php',
	'tx_div2007_viewBase' => $extensionPath . 'class.tx_div2007_viewBase.php',
	'JambageCom\\Div2007\\Utility\\TableUtility' => $extensionPath . 'Classes/Utility/TableUtility.php',
	'JambageCom\\Div2007\\Utility\\ExtensionUtility' => $extensionPath . 'Classes/Utility/ExtensionUtility.php',
);
