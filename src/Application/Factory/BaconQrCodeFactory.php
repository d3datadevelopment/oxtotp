<?php

declare(strict_types=1);

namespace D3\Totp\Application\Factory;

use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Renderer\Image\Svg;                   // v1.0.3
use BaconQrCode\Renderer\ImageRenderer;               // v2.0.0
use BaconQrCode\Renderer\Image\SvgImageBackEnd;       // v2.0.0
use BaconQrCode\Renderer\RendererStyle\RendererStyle; // v2.0.0


class BaconQrCodeFactory
{
    /**
     * @return RendererInterface
     */
    public static function renderer($size)
    {
        if (class_exists(Svg::class)) {
            return self::v100($size);
        }

        return self::v200($size);
    }

    private static function v200($size)
    {
        $renderer = oxNew(
            ImageRenderer::class,
            oxNew(RendererStyle::class, $size),
            oxNew(SvgImageBackEnd::class),
        );

        return $renderer;
    }

    private static function v100($size)
    {
        $renderer = oxNew(Svg::class);
        $renderer->setHeight($size);
        $renderer->setWidth($size);

        return $renderer;
    }
}
