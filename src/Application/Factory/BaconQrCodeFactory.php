<?php

declare(strict_types=1);

namespace D3\Totp\Application\Factory;

use BaconQrCode\Renderer\RendererInterface;
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
        return self::v200($size);
    }

    private static function v200($size)
    {
        return oxNew(
            ImageRenderer::class,
            oxNew(RendererStyle::class, $size),
            oxNew(SvgImageBackEnd::class),
        );
    }
}
