<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\Totp\Application\Factory;

use BaconQrCode\Renderer\RendererInterface;
use BaconQrCode\Renderer\ImageRenderer;               // v2.0.0
use BaconQrCode\Renderer\Image\SvgImageBackEnd;       // v2.0.0
use BaconQrCode\Renderer\RendererStyle\RendererStyle; // v2.0.0

class BaconQrCodeFactory
{
    /**
     * @param int $size
     * @return RendererInterface
     */
    public static function renderer(int $size): RendererInterface
    {
        return self::v200($size);
    }

    private static function v200(int $size): RendererInterface
    {
        return oxNew(
            ImageRenderer::class,
            oxNew(RendererStyle::class, $size),
            oxNew(SvgImageBackEnd::class),
        );
    }
}
