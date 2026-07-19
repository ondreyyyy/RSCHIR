<?php
declare(strict_types=1);

namespace App\Infrastructure\Charts;

interface ChartRenderer
{
    public function render(array $rows): string;
}
