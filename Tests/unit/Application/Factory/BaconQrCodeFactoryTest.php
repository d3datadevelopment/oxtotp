<?php

namespace D3\Totp\tests\unit\Application\Factory;

use BaconQrCode\Renderer\ImageRenderer;
use D3\Totp\Application\Factory\BaconQrCodeFactory;
use D3\Totp\tests\unit\d3TotpUnitTestCase;

class BaconQrCodeFactoryTest extends d3TotpUnitTestCase
{
    /** @var BaconQrCodeFactory */
    protected $factory;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->factory = oxNew(BaconQrCodeFactory::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->factory);
    }

    /**
     * @test
     * @return void
     * @covers \D3\Totp\Application\Factory\BaconQrCodeFactory::renderer
     * @covers \D3\Totp\Application\Factory\BaconQrCodeFactory::v200
     */
    public function testRenderer()
    {
        $this->assertInstanceOf(
            ImageRenderer::class,
            BaconQrCodeFactory::renderer(200)
        );
    }
}
