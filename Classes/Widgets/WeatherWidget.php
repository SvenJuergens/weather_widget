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

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface as Cache;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\ButtonProviderInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class WeatherWidget implements WidgetInterface, AdditionalCssInterface
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
     * @var ButtonProviderInterface|null
     */
    private $buttonProvider;

    public function __construct(
        WidgetConfigurationInterface $configuration,
        Cache $cache,
        StandaloneView $view,
        $buttonProvider = null,
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
    }

    public function renderWidgetContent(): string
    {
        $this->view->setTemplate('WeatherWidget');
        $this->view->assignMultiple([
            'weather' =>  $this->getWeather() ?: '',
            'options' => $this->options,
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
        return $this->options['location'];
    }

    public function getCssFiles(): array
    {
        return ['EXT:weather_widget/Resources/Public/Css/weatherWidget.css'];
    }

}
