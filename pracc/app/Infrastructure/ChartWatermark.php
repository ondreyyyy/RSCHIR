<?php

namespace App\Infrastructure;

final class ChartWatermark
{
    public function apply(string $imagePath, string $text): void
    {
        if (!is_file($imagePath) || !is_readable($imagePath)) {
            return;
        }

        $image = imagecreatefrompng($imagePath);
        if ($image === false) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $fontFile = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        if (!is_file($fontFile)) {
            $fontFile = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        }

        $color = imagecolorallocatealpha($image, 80, 80, 80, 40);

        $box = imagettfbbox(11, 0, $fontFile, $text);
        if ($box === false) {
            imagestring($image, 5, max(0, $width - 80), max(0, $height - 20), $text, $color);
            imagepng($image, $imagePath);
            imagedestroy($image);
            return;
        }

        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = $width - $textWidth - 10;
        $y = $height - $textHeight - 10;
        imagettftext($image, 11, 0, $x, $y, $color, $fontFile, $text);
        imagepng($image, $imagePath);
        imagedestroy($image);
    }
}
