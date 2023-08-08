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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface as Cache;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\JavaScriptInterface;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

class WeatherWidget implements WidgetInterface, RequestAwareWidgetInterface, AdditionalCssInterface, JavaScriptInterface
{
    protected ServerRequestInterface $request;

    /**
     * @var string
     */
    protected string $backendUserLocation = '';

    /**
     * WeatherWidget constructor.
     * @param WidgetConfigurationInterface $configuration
     * @param Cache $cache
     * @param BackendViewFactory $backendViewFactory
     * @param UriBuilder $uriBuilder
     * @param array $options
     */
    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly Cache $cache,
        private readonly BackendViewFactory $backendViewFactory,
        private readonly UriBuilder $uriBuilder,
        private array $options = []
    ) {
        $this->backendUserLocation = $this->getBackendUserLocation();
        $this->options = array_merge(
            [
                'lifeTime' => 3600,
                'language' => $this->getBackendUserAuthentication()->user['lang'] ?: 'en',
            ],
            $options
        );
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function renderWidgetContent(): string
    {
        // The second argument is the Composer 'name' of the extension that adds the widget.
        // It is needed to instruct BackendViewFactory to look up templates in this package
        // next to the default location 'typo3/cms-dashboard', too.
        $view = $this->backendViewFactory->create($this->request, ['typo3/cms-dashboard', 'svenjuergens/weather-widget']);
        $view->assignMultiple([
            'weather' =>  $this->getWeather() ?: '',
            'options' => $this->options,
            'button' => $this->getButton(),
            'location' => $this->backendUserLocation,
            'configuration' => $this->configuration,
        ]);
        return $view->render('Widgets/WeatherWidget');
    }

    protected function getWeather(): array
    {
        $url = 'https://' . $this->options['language'] . '.wttr.in/' . $this->getLocation() . '?format=%c|%C|%t|%w|%l';
        $cacheHash = md5($url);
        if ($weatherDetails = $this->cache->get($cacheHash)) {
            return $weatherDetails;
        }

        $weather = GeneralUtility::getUrl($url);
        $weatherDetails = [];
        if (mb_substr_count($weather, '|') === 4) {
            $weatherDetails = explode('|', trim($weather));
        }

        if (!empty($weatherDetails)) {
            $this->cache->set($cacheHash, $weatherDetails, ['dashboard_weather'], $this->options['lifeTime']);
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

    /**
     * @throws RouteNotFoundException
     */
    public function getButton(): array
    {
        return [
            'link' => $this->uriBuilder->buildUriFromRoute('dashboard', ['location' => $this->backendUserLocation]),
        ];
    }
    protected function getBackendUserLocation(): string
    {
        $backendUser = $this->getBackendUserAuthentication();

        if (!$backendUser instanceof BackendUserAuthentication) {
            return '';
        }

        return $backendUser->uc['DashboardWeatherWidget']['location'] ?? 'Berlin';
    }

    protected function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    public function getJavaScriptModuleInstructions(): array
    {
        return [
            JavaScriptModuleInstruction::create(
                '@svenjuergens/weather-widget/addUserLocation.js'
            )->invoke('initialize'),
        ];
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
