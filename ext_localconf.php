<?php

defined('TYPO3_MODE') || die();

/***************
 * Register custom EXT:form configuration
 */

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
module.tx_dashboard {
        view {
            templateRootPaths {
                110 = EXT:weather_widget/Resources/Private/Templates/Widgets/
            }
        }
    }
'));

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['dashboard_weather'] ?? null)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['dashboard_weather'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
        'options' => [
            'defaultLifetime' => 900,
        ],
    ];
}
