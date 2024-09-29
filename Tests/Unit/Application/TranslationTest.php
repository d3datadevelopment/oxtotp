<?php

namespace Unit\Application;

use D3\Totp\Tests\Unit\d3TotpUnitTestCase;
use Generator;

class TranslationTest extends d3TotpUnitTestCase
{
    /**
     * @test
     * @return void
     * @dataProvider canGetTranslationDataProvider
     */
    public function canGetTranslation(string $path): void
    {
        $list = require __DIR__.'/../../../'.$path;

        $this->assertIsArray($list);
        $this->assertTrue(count($list)   > 1);
        $this->assertTrue($list['charset'] === 'UTF-8');
    }

    public static function canGetTranslationDataProvider(): Generator
    {
        yield 'frontend DE' => ['Application/translations/de/translations.php'];
        yield 'frontend EN' => ['Application/translations/en/translations.php'];
        yield 'backend DE' => ['Application/views/de/translations.php'];
        yield 'backend EN' => ['Application/views/en/translations.php'];
    }
}