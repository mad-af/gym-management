<?php

namespace App\Helpers;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeHelper
{
    public static function generateSvg(string $value, int $size = 192): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0))),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($value);

        return $svg;
    }

    public static function generateBase64Svg(string $value, int $size = 192): string
    {
        $svg = self::generateSvg($value, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
