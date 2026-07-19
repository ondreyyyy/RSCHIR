<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;
use App\Http\Response;

final class StatisticsController extends AbstractController
{
    public function index(): void
    {
        $this->container->preferences()->handleForm();
        $this->container->weatherService()->ensureFixtures(50);

        $preferences = $this->preferences();
        $rows = $this->container->weatherService()->all();
        $charts = $this->container->chartService()->build($rows);

        $chartUrls = [
            'bar' => '/chart.php?file=' . rawurlencode(basename($charts['bar'])),
            'line' => '/chart.php?file=' . rawurlencode(basename($charts['line'])),
            'pie' => '/chart.php?file=' . rawurlencode(basename($charts['pie'])),
        ];

        $this->renderPage('statsTitle', 'statistics', [
            'rows' => $rows,
            'chartUrls' => $chartUrls,
        ]);
    }
}
