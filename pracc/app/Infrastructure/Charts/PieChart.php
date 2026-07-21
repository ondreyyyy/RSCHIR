<?php

namespace App\Infrastructure\Charts;

use App\Domain\Weather;

final class PieChart extends AbstractGdChart
{
    /** @param Weather[] $rows */
    public function render(array $rows): string
    {
        $ranges = ['< 0°C' => 0, '0-15°C' => 0, '15-25°C' => 0, '> 25°C' => 0];
        foreach ($rows as $row) {
            $temp = $row->temperature;
            if ($temp < 0) {
                $ranges['< 0°C']++;
            } elseif ($temp < 15) {
                $ranges['0-15°C']++;
            } elseif ($temp < 25) {
                $ranges['15-25°C']++;
            } else {
                $ranges['> 25°C']++;
            }
        }

        return $this->draw(array_keys($ranges), array_values($ranges));
    }

    /**
     * @param string[] $labels
     * @param int[] $values
     */
    private function draw(array $labels, array $values): string
    {
        $width = 800;
        $height = 600;
        $image = $this->createImage($width, $height);

        $bg = $this->color($image, 255, 255, 255);
        $black = $this->color($image, 0, 0, 0);
        $colors = [
            $this->color($image, 255, 127, 14),
            $this->color($image, 31, 119, 180),
            $this->color($image, 44, 160, 44),
            $this->color($image, 214, 39, 40),
        ];

        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        $centerX = (int) ($width / 2);
        $centerY = (int) ($height / 2);
        $radius = (int) min($width, $height) / 3;
        $total = array_sum($values) ?: 1;
        $startAngle = 0;

        for ($i = 0; $i < count($values); $i++) {
            $sliceAngle = (int) (($values[$i] / $total) * 360);
            $endAngle = $startAngle + $sliceAngle;
            imagefilledarc($image, $centerX, $centerY, $radius * 2, $radius * 2, $startAngle, $endAngle, $colors[$i % count($colors)], IMG_ARC_PIE);
            $midAngle = deg2rad($startAngle + $sliceAngle / 2);
            $labelX = (int) ($centerX + cos($midAngle) * $radius * 0.6);
            $labelY = (int) ($centerY + sin($midAngle) * $radius * 0.6);
            $this->text($image, 12, $labelX - 10, $labelY - 10, $labels[$i], $black);
            $startAngle = $endAngle;
        }

        return $this->save($image, $this->path());
    }

    private function path(): string
    {
        return (new \App\Infrastructure\AppConfig())->chartsDir()
            . DIRECTORY_SEPARATOR . date('Ymd_His') . '_pie.png';
    }
}
