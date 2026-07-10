<?php
declare(strict_types=1);

namespace JambageCom\Div2007\Utility;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Agency\Constants\Extension;


class ConfigurationUtility
{
    /**
     * @param string $extensionKey Extension key
     * @param string $path Configuration path - e.g. "featureCategory/coolThingIsEnabled"
     * @return string | array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getExtensionConfiguration(string $extensionKey, string $path = ''): string|array|null
    {
        $result = null;

        try {
            $result =  GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($extensionKey, $path);
        }
        catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException) {
        }
        return $result;
    }
}

