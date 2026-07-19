<?php
declare(strict_types=1);

namespace App\Infrastructure\Charts;

use App\Domain\Weather;

final class BarChart extends AbstractGdChart
{
    private const CITY_LABELS = [
        'Москва' => "Москва\n(Moscow)",
        'Санкт-Петербург' => "Санкт-Петербург\n(Saint Petersburg)",
        'Новосибирск' => "Новосибирск\n(Novosibirsk)",
        'Казань' => "Казань\n(Kazan)",
    ];

    /** @param Weather[] $rows */
    public function render(array $rows): string
    {
        $cityStats = [];
        $cityCounts = [];
        foreach ($rows as $row) {
            $city = $row->city;
            $cityStats[$city] = ($cityStats[$city] ?? 0) + $row->temperature;
            $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
        }

        $cities = array_keys($cityStats);
        $avgs = [];
        foreach ($cities as $city) {
            $avgs[] = $cityStats[$city] / $cityCounts[$city];
        }
        $labels = array_map(
            static fn(string $city): string => self::CITY_LABELS[$city] ?? $city,
            $cities
        );

        return $this->draw($labels, $avgs);
    }

    /**
     * @param string[] $labels
     * @param float[] $values
     */
    private function draw(array $labels, array $values): string
    {
        $width = 800;
        $height = 700;
        $image = $this->createImage($width, $height);

        $bg = $this->color($image, 255, 255, 255);
        $black = $this->color($image, 0, 0, 0);
        $gray = $this->color($image, 200, 200, 200);
        $barColors = [
            $this->color($image, 255, 127, 14),
            $this->color($image, 31, 119, 180),
            $this->color($image, 44, 160, 44),
            $this->color($image, 214, 39, 40),
        ];

        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        $padding = 80;
        $chartWidth = $width - $padding * 2;
        $chartHeight = $height - $padding * 2;
        $maxValue = max($values) ?: 1;
        $barWidth = (int) ($chartWidth / count($values) * 0.7);
        $spacing = (int) ($chartWidth / count($values) * 0.3);

        for ($i = 0; $i < count($values); $i++) {
            $barHeight = (int) (($values[$i] / $maxValue) * $chartHeight);
            $x = (int) ($padding + $i * ($barWidth + $spacing));
            $y = (int) ($height - $padding - $barHeight);
            imagefilledrectangle($image, $x, $y, $x + $barWidth, $height - $padding, $barColors[$i % count($barColors)]);
            imagerectangle($image, $x, $y, $x + $barWidth, $height - $padding, $black);
            $this->text($image, 12, $x + 5, (int) ($height - $padding + 5), (string) round($values[$i], 1), $black);
            $labelLines = explode("\n", $labels[$i]);
            foreach ($labelLines as $lineIndex => $line) {
                $this->text($image, 12, $x + 5, (int) ($height - $padding + 30 + $lineIndex * 20), $line, $black);
            }
        }

        imagerectangle($image, $padding, $padding, $width - $padding, $height - $padding, $gray);

        return $this->save($image, $this->path());
    }

    private function path(): string
    {
        return (new \App\Infrastructure\AppConfig())->chartsDir()
            . DIRECTORY_SEPARATOR . date('Ymd_His') . '_bar.png';
    }
}
