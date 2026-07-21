<?php

namespace App\Infrastructure\Charts;

use App\Domain\Weather;

final class LineChart extends AbstractGdChart
{
    private const MOSCOW = 'Москва';

    /** @param Weather[] $rows */
    public function render(array $rows): string
    {
        $sorted = $rows;
        usort($sorted, static fn(Weather $a, Weather $b): int => $a->recordedAt <=> $b->recordedAt);

        $moscowRows = array_values(array_filter($sorted, static fn(Weather $r): bool => $r->city === self::MOSCOW));

        $values = array_map(static fn(Weather $r): float => $r->temperature, $moscowRows);
        $labels = array_map(static fn(Weather $r): string => substr($r->recordedAt, 5, 5), $moscowRows);

        return $this->draw($labels, $values);
    }

    /**
     * @param string[] $labels
     * @param float[] $values
     */
    private function draw(array $labels, array $values): string
    {
        $width = 800;
        $height = 600;
        $image = $this->createImage($width, $height);

        $bg = $this->color($image, 255, 255, 255);
        $black = $this->color($image, 0, 0, 0);
        $blue = $this->color($image, 31, 119, 180);
        $gray = $this->color($image, 200, 200, 200);

        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        $padding = 60;
        $chartWidth = $width - $padding * 2;
        $chartHeight = $height - $padding * 2;
        $maxValue = max($values) ?: 1;
        $minValue = min($values);
        $range = $maxValue - $minValue ?: 1;
        $stepX = $chartWidth / max(1, count($values) - 1);

        for ($i = 0; $i < count($values); $i++) {
            $x = (int) ($padding + $i * $stepX);
            $y = (int) ($height - $padding - (($values[$i] - $minValue) / $range) * $chartHeight);
            if ($i > 0) {
                $prevX = (int) ($padding + ($i - 1) * $stepX);
                $prevY = (int) ($height - $padding - (($values[$i - 1] - $minValue) / $range) * $chartHeight);
                imageline($image, $prevX, $prevY, $x, $y, $blue);
            }
            imagefilledellipse($image, $x, $y, 6, 6, $blue);
            if ($i % max(1, (int) (count($values) / 10)) === 0) {
                $this->text($image, 8, $x - 10, (int) ($height - $padding + 5), $labels[$i], $black);
            }
        }

        imagerectangle($image, $padding, $padding, $width - $padding, $height - $padding, $gray);

        return $this->save($image, $this->path());
    }

    private function path(): string
    {
        return (new \App\Infrastructure\AppConfig())->chartsDir()
            . DIRECTORY_SEPARATOR . date('Ymd_His') . '_line.png';
    }
}
