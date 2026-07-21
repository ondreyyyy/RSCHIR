<?php

namespace App\Http\Controllers;

use App\Services\WeatherLocalizer;
use App\Infrastructure\EloquentWeatherRepository;
use App\Services\PreferencesService;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $redirect = (new PreferencesService())->handleForm($request);
        if ($redirect) {
            return $redirect;
        }

        $preferences = $this->preferences($request);

        $weatherService = new WeatherService(new EloquentWeatherRepository());
        $latest = $weatherService->latestPerCity();
        $rows = (new WeatherLocalizer())->localize($preferences->language, $latest);

        return $this->renderPage($request, 'weatherTitle', 'weather', [
            'rows' => $rows,
            'now' => date('Y-m-d H:i:s'),
        ]);
    }
}
