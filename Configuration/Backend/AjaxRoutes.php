<?php

return [
    'add_userlocation' => [
        'path' => '/add/userlocation',
        'target' => \SvenJuergens\WeatherWidget\Controller\LocationController::class . '::addLocationAction',
    ],
];
