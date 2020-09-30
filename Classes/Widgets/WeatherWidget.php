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
use TYPO3\CMS\Dashboard\Widgets\ButtonProviderInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Concrete RSS widget implementation
 *
 * The widget will show a certain number of items of the given RSS feed. The feed will be set by the feedUrl option. You
 * can add a button to the widget by defining a button provider.
 *
 * The following options are available during registration:
 * - feedUrl        string                      Defines the URL or file providing the RSS Feed.
 *                                              This is read by the widget in order to fetch entries to show.
 * - limit          int     default: 5          Defines how many RSS items should be shown.
 * - lifetime       int     default: 43200      Defines how long to wait, in seconds, until fetching RSS Feed again
 *
 * @see ButtonProviderInterface
 */
class WeatherWidget implements WidgetInterface
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
                'lifeTime' => 43200
            ],
            $options
        );
        $this->buttonProvider = $buttonProvider;
    }

    public function renderWidgetContent(): string
    {
        $this->view->setTemplate('WeatherWidget');
        $this->view->assignMultiple([
            'weather' =>  $this->getWeather() ?: '',
            'options' => $this->options,
            'button' => $this->buttonProvider,
            'configuration' => $this->configuration,
        ]);
        return $this->view->render();
    }


    protected function getWeather(): array
    {

        $url = 'https://de.wttr.in/' . $this->getLocation() .  '?format=%c|%C|%t|%w|%l';

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
        return 'Berlin';
    }
}
