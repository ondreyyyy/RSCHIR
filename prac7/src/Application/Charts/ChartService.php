<?php
declare(strict_types=1);

namespace App\Application\Charts;

use App\Domain\Weather;
use App\Infrastructure\ChartWatermark;
use App\Infrastructure\Charts\BarChart;
use App\Infrastructure\Charts\LineChart;
use App\Infrastructure\Charts\PieChart;

final class ChartService
{
    public function __construct(
        private ChartWatermark $watermark,
        private string $watermarkText = 'MoiseevAM'
    ) {
    }

    /**
     * @param Weather[] $rows
     * @return array{bar:string,line:string,pie:string}
     */
    public function build(array $rows): array
    {
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
}
