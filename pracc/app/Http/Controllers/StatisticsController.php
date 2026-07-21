<?php

namespace App\Http\Controllers;

use App\Infrastructure\EloquentWeatherRepository;
use App\Services\ChartService;
use App\Services\PreferencesService;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $redirect = (new PreferencesService())->handleForm($request);
        if ($redirect) {
            return $redirect;
        }

        $weatherService = new WeatherService(new EloquentWeatherRepository());
        $weatherService->ensureFixtures(50);

        $preferences = $this->preferences($request);
        $rows = $weatherService->all();
        $chartService = new ChartService(new \App\Infrastructure\ChartWatermark(), new \App\Infrastructure\AppConfig());
        $charts = $chartService->build($rows);

        $chartUrls = [
            'bar' => '/chart?file=' . rawurlencode(basename($charts['bar'])),
            'line' => '/chart?file=' . rawurlencode(basename($charts['line'])),
            'pie' => '/chart?file=' . rawurlencode(basename($charts['pie'])),
        ];

        return $this->renderPage($request, 'statsTitle', 'statistics', [
            'rows' => $rows,
            'chartUrls' => $chartUrls,
        ]);
    }
}
