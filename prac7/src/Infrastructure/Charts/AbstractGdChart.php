<?php
declare(strict_types=1);

namespace App\Infrastructure\Charts;

use GdImage;

abstract class AbstractGdChart implements ChartRenderer
{
    protected function createImage(int $width, int $height): GdImage
    {
        return imagecreatetruecolor($width, $height);
    }

    protected function color(GdImage $image, int $r, int $g, int $b): int
    {
        return imagecolorallocate($image, $r, $g, $b);
    }

    protected function fontFile(): ?string
    {
        $candidates = [
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $font) {
            if (is_file($font)) {
                return $font;
            }
        }

        return null;
    }

    protected function text(GdImage $image, int $size, int $x, int $y, string $text, int $color): void
    {
        $font = $this->fontFile();
        if ($font !== null) {
            imagettftext($image, $size, 0, $x, $y + $size, $color, $font, $text);
        } else {
            imagestring($image, $size, $x, $y, $text, $color);
        }
    }

    protected function save(GdImage $image, string $path): string
    {
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }
}
