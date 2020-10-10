<?php

declare(strict_types=1);

namespace SvenJuergens\WeatherWidget\Widgets;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface as Cache;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Dashboard\Widgets\RequireJsModuleInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class WeatherWidget implements WidgetInterface, AdditionalCssInterface, RequireJsModuleInterface
{
    /**
     * @var WidgetConfigurationInterface
     */
    private $configuration;

    /**
     * @var StandaloneView
     */
    private $view;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var array
     */
    private $options;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var string
     */
    protected $backendUserLocation = '';

    /**
     * WeatherWidget constructor.
     * @param WidgetConfigurationInterface $configuration
     * @param Cache $cache
     * @param StandaloneView $view
     * @param UriBuilder $uriBuilder
     * @param array $options
     */
    public function __construct(
        WidgetConfigurationInterface $configuration,
        Cache $cache,
        StandaloneView $view,
        UriBuilder $uriBuilder,
        array $options = []
    ) {
        $this->configuration = $configuration;
        $this->view = $view;
        $this->cache = $cache;
        $this->options = array_merge(
            [
                'lifeTime' => 3600,
                'language' => 'de'
            ],
            $options
        );
        $this->uriBuilder = $uriBuilder;
    }

    public function renderWidgetContent(): string
    {
        $this->backendUserLocation = $this->getBackendUserLocation();

        $this->view->setTemplate('WeatherWidget');
        $this->view->assignMultiple([
            'weather' =>  $this->getWeather() ?: '',
            'options' => $this->options,
            'button' => $this->getButton(),
            'location' => $this->backendUserLocation,
            'configuration' => $this->configuration,
        ]);
        return $this->view->render();
    }


    protected function getWeather(): array
    {

        $url = 'https://' . $this->options['language'] . '.wttr.in/' . $this->getLocation() .  '?format=%c|%C|%t|%w|%l';
     #   $cacheHash = md5($url);
     #   if ($weatherDetails = $this->cache->get($cacheHash)) {
     #       return $weatherDetails;
     #   }

        $weather = GeneralUtility::getUrl($url);
        $weatherDetails = [];
        if (mb_substr_count($weather, '|') === 4) {
            $weatherDetails = explode('|', trim($weather));
        }

        if(!empty($weatherDetails)){
       #     $this->cache->set($cacheHash, $weatherDetails, ['dashboard_weather'], $this->options['lifeTime']);
        }
        return $weatherDetails;
    }

    protected function getLocation(): string
    {
        return $this->backendUserLocation ?: $this->options['location'];
    }

    public function getCssFiles(): array
    {
        return ['EXT:weather_widget/Resources/Public/Css/weatherWidget.css'];
    }

    public function getButton(): array
    {
        return [
            'link' => $this->uriBuilder->buildUriFromRoute('dashboard', ['location' => 'abc']),
            'title' => 'save location'
        ];
    }
    protected function getBackendUserLocation(): string
    {
        $backendUser = $this->getBackendUserAuthentication();

        if (!$backendUser instanceof BackendUserAuthentication) {
            return '';
        }

        return $backendUser->uc['DashboardWeatherWidget']['location'] ?? '';
    }


    protected function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    public function getRequireJsModules(): array
    {
        return [
            'TYPO3/CMS/WeatherWidget/AddUserLocation',
        ];
    }
}
