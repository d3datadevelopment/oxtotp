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

namespace D3\Totp\Tests\Unit\Setup;

use D3\OxidServiceBridges\Internal\Framework\Templating\Cache\ShopTemplateCacheServiceBridge;
use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Setup\Actions;
use D3\Totp\Tests\Unit\d3TotpUnitTestCase;
use Generator;
use OxidEsales\DoctrineMigrationWrapper\Migrations;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\SeoEncoder;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Wrapper\LoggerWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ActionsTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var Actions */
    protected Actions $_sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->_sut = oxNew(Actions::class);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::getMigrationsBuilder
     */
    public function canGetMigrationsBuilder(): void
    {
        $this->assertInstanceOf(
            MigrationsBuilder::class,
            $this->callMethod(
                $this->_sut,
                'getMigrationsBuilder'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::runModuleMigrations
     */
    public function canRunModuleMigrations(): void
    {
        $migrationsMock = $this->d3getMockBuilder(Migrations::class)
            ->onlyMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();
        $migrationsMock->expects($this->once())->method('execute');

        $migrationsBuilderMock = $this->d3getMockBuilder(MigrationsBuilder::class)
            ->onlyMethods(['build'])
            ->getMock();
        $migrationsBuilderMock->method("build")->willReturn($migrationsMock);

        $sutMock = $this->d3getMockBuilder(Actions::class)
            ->onlyMethods(['getMigrationsBuilder'])
            ->getMock();
        $sutMock->method('getMigrationsBuilder')->willReturn($migrationsBuilderMock);

        $this->callMethod(
            $sutMock,
            'runModuleMigrations'
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::getDbMetaDataHandler
     */
    public function canGetDbMetaDataHandler(): void
    {
        $this->assertInstanceOf(
            DbMetaDataHandler::class,
            $this->callMethod(
                $this->_sut,
                'getDbMetaDataHandler'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::regenerateViews
     */
    public function canRegenerateViews(): void
    {
        $dbMetaDataHandlerMock = $this->d3getMockBuilder(DbMetaDataHandler::class)
            ->onlyMethods(['updateViews'])
            ->getMock();
        $dbMetaDataHandlerMock->expects($this->once())->method("updateViews");

        $sutMock = $this->d3getMockBuilder(Actions::class)
            ->onlyMethods(['getDbMetaDataHandler'])
            ->getMock();
        $sutMock->method('getDbMetaDataHandler')->willReturn($dbMetaDataHandlerMock);

        $this->callMethod(
            $sutMock,
            'regenerateViews'
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::getUtils
     */
    public function canGetUtils(): void
    {
        $this->assertInstanceOf(
            Utils::class,
            $this->callMethod(
                $this->_sut,
                'getUtils'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::getLogger
     */
    public function canGetLogger(): void
    {
        $this->assertInstanceOf(
            LoggerInterface::class,
            $this->callMethod(
                $this->_sut,
                'getLogger'
            )
        );
    }

    /**
     * @test
     * @param string|null $thrownException
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Totp\Setup\Actions::clearCache
     * @dataProvider canClearCacheDataProvider
     */
    public function canClearCache(string $thrownException = null): void
    {
        $shopTemplateCacheServiceMock = $this->d3getMockBuilder(ShopTemplateCacheServiceBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['invalidateCache'])
            ->getMock();
        $shopTemplateCacheServiceMock->expects($this->exactly((int) !$thrownException))->method("invalidateCache");

        $DIContainerMock = $this->d3getMockBuilder(Container::class)
            ->onlyMethods(['get', 'has'])
            ->getMock();
        if ($thrownException === null) {
            $DIContainerMock->method('get')->willReturn($shopTemplateCacheServiceMock);
        } else {
            /** @var ServiceNotFoundException|MockObject $exceptionMock */
            $exceptionMock = $this->d3getMockBuilder(ServiceNotFoundException::class)
                ->disableOriginalConstructor()
                ->getMock();
            $DIContainerMock->method('get')->willThrowException($exceptionMock);
        }

        $utilsMock = $this->d3getMockBuilder(Utils::class)
            ->onlyMethods(['resetLanguageCache'])
            ->getMock();
        $utilsMock->expects($this->exactly((int) !$thrownException))->method('resetLanguageCache');

        $loggerMock = $this->d3getMockBuilder(LoggerWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error'])
            ->getMock();
        $loggerMock->expects($this->exactly((int) $thrownException))->method('error');

        $sutMock = $this->d3getMockBuilder(Actions::class)
            ->onlyMethods(['getDIContainer', 'getUtils', 'getLogger'])
            ->getMock();
        $sutMock->method('getDIContainer')->willReturn($DIContainerMock);
        $sutMock->method('getUtils')->willReturn($utilsMock);
        $sutMock->method('getLogger')->willReturn($loggerMock);

        $this->callMethod(
            $sutMock,
            'clearCache'
        );
    }

    public static function canClearCacheDataProvider(): Generator
    {
        yield 'passed'  => [];
        yield 'container exception'  => [ServiceNotFoundException::class];
    }

    /**
     * @test
     * @param bool $hasSeoUrls
     * @param InvokedCount $createCount
     * @param bool $throwException
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Totp\Setup\Actions::seoUrl
     * @dataProvider canHandleSeoUrlsDataProvider
     */
    public function canHandleSeoUrls(bool $hasSeoUrls, InvokedCount $createCount, bool $throwException = false): void
    {
        $loggerMock = $this->d3getMockBuilder(LoggerWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error'])
            ->getMock();
        $loggerMock->expects($this->exactly((int) $throwException))->method('error');

        $sutMock = $this->d3getMockBuilder(Actions::class)
            ->onlyMethods(['hasSeoUrls', 'createSeoUrls', 'getLogger'])
            ->getMock();
        $sutMock->method('hasSeoUrls')->willReturn($hasSeoUrls);
        $sutMock->method('getLogger')->willReturn($loggerMock);
        if ($throwException) {
            $sutMock->expects($createCount)->method('createSeoUrls')->willThrowException(new StandardException());
        } else {
            $sutMock->expects($createCount)->method('createSeoUrls');
        }

        $this->callMethod(
            $sutMock,
            'seoUrl'
        );
    }

    public static function canHandleSeoUrlsDataProvider(): Generator
    {
        yield 'urls exists'     => [true, self::never()];
        yield 'urls not exists' => [false, self::once()];
        yield 'throw exception' => [false, self::once(), true];
    }

    /**
     * @test
     * @param bool $hasSeoUrl
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::hasSeoUrls
     * @dataProvider hasSeoUrlsDataProvider
     */
    public function testHasSeoUrls(bool $hasSeoUrl): void
    {
        $sutMock = $this->d3getMockBuilder(Actions::class)
            ->onlyMethods(['hasSeoUrl'])
            ->getMock();
        $sutMock->method('hasSeoUrl')->willReturn($hasSeoUrl);

        $this->assertSame(
            $hasSeoUrl,
            $this->callMethod(
                $sutMock,
                'hasSeoUrls',
                [oxNew(SeoEncoder::class)]
            )
        );
    }

    public static function hasSeoUrlsDataProvider(): Generator
    {
        yield 'url exists'      => [true];
        yield 'url not exists'  => [false];
    }

    /**
     * @test
     * @param string $staticUrl
     * @param bool $expected
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::hasSeoUrl
     * @dataProvider hasSeoUrlDataProvider
     */
    public function testHasSeoUrl(string $staticUrl, bool $expected)
    {
        $seoEncoderMock = $this->d3getMockBuilder(SeoEncoder::class)
            ->onlyMethods(['getStaticUrl'])
            ->getMock();
        $seoEncoderMock->expects($this->once())->method('getStaticUrl')->willReturn($staticUrl);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_sut,
                'hasSeoUrl',
                [$seoEncoderMock, 'item', 0]
            )
        );
    }

    public static function hasSeoUrlDataProvider(): Generator
    {
        yield 'passed'      => ['staticFixture', true];
        yield 'failed'      => ['', false];
    }

    /**
     * @test
     * @param bool $hasSeoUrl
     * @param InvokedCount $expectedCount
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Totp\Setup\Actions::createSeoUrls
     * @dataProvider canCreateSeoUrlsDataProvider
     */
    public function canCreateSeoUrls(bool $hasSeoUrl, invokedCount $expectedCount): void
    {
        $seoEncoderMock = $this->d3getMockBuilder(SeoEncoder::class)
            ->onlyMethods(['addSeoEntry'])
            ->getMock();
        $seoEncoderMock->expects($expectedCount)->method('addSeoEntry')->willReturn('addSeoEntry');

        $sutMock = $this->d3getMockBuilder(Actions::class)
            ->onlyMethods(['hasSeoUrl'])
            ->getMock();
        $sutMock->method('hasSeoUrl')->willReturn($hasSeoUrl);

        $this->callMethod(
            $sutMock,
            'createSeoUrls',
            [$seoEncoderMock]
        );
    }

    public static function canCreateSeoUrlsDataProvider(): Generator
    {
        yield 'url not exists'  => [false, self::exactly(2)];
        yield 'url exists'      => [true, self::never()];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Setup\Actions::getDIContainer
     */
    public function canGetDIContainer(): void
    {
        $this->assertInstanceOf(
            ContainerInterface::class,
            $this->callMethod(
                $this->_sut,
                'getDIContainer'
            )
        );
    }
}
