<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;
use App\Http\Response;

final class WeatherController extends AbstractController
{
    public function index(): void
    {
        $this->container->preferences()->handleForm();
        $preferences = $this->preferences();

        $latest = $this->container->weatherService()->latestPerCity();
        $rows = $this->container->weatherLocalizer()->localize($preferences->language, $latest);

        $this->renderPage('weatherTitle', 'weather', [
            'rows' => $rows,
            'now' => date('Y-m-d H:i:s'),
        ]);
    }
}
