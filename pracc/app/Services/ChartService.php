<?php

namespace App\Services;

use App\Domain\Weather;
use App\Infrastructure\AppConfig;
use App\Infrastructure\ChartWatermark;
use App\Infrastructure\Charts\BarChart;
use App\Infrastructure\Charts\LineChart;
use App\Infrastructure\Charts\PieChart;

final class ChartService
{
    public function __construct(
        private ChartWatermark $watermark,
        private AppConfig $config = new AppConfig(),
        private string $watermarkText = 'MoiseevAM'
    ) {
    }

    /**
     * @param Weather[] $rows
     * @return array{bar:string,line:string,pie:string}
     */
    public function build(array $rows): array
    {
        $this->clearOldCharts();

        $bar = (new BarChart())->render($rows);
        $line = (new LineChart())->render($rows);
        $pie = (new PieChart())->render($rows);

        $this->watermark->apply($bar, $this->watermarkText);
        $this->watermark->apply($line, $this->watermarkText);
        $this->watermark->apply($pie, $this->watermarkText);

        return [
            'bar' => $bar,
            'line' => $line,
            'pie' => $pie,
        ];
    }

    private function clearOldCharts(): void
    {
        $directory = $this->config->chartsDir();
        if (!is_dir($directory)) {
            return;
        }

        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.png') ?: [] as $oldChart) {
            @unlink($oldChart);
        }
    }
}
