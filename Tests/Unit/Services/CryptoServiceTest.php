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

namespace D3\Totp\Tests\Unit\Services;

use D3\Totp\Application\Model\Constants;
use D3\Totp\Services\CryptoService;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\String\UnicodeString;

class CryptoServiceTest extends TestCase
{
    private ModuleSettingServiceInterface|MockObject $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = $this->createMock(
            ModuleSettingServiceInterface::class
        );
    }

    public function testGetKeyReturnsLegacyKey(): void
    {
        $service = new CryptoService($this->settings);

        $this->assertSame(
            'fq45QS09_fqyx09239QQ',
            $service->getKey(CryptoService::KEY_VERSION_LEGACY)
        );
    }

    public function testGetKeyReturnsMasterKey(): void
    {
        $settingValue = $this->createMock(UnicodeString::class);
        $settingValue
            ->method('toString')
            ->willReturn('my-master-key');

        $this->settings
            ->method('getString')
            ->willReturn($settingValue);

        $service = new CryptoService($this->settings);

        $this->assertSame(
            'my-master-key',
            $service->getKey(CryptoService::KEY_VERSION_MASTER_KEY)
        );
    }

    public function testGetKeyThrowsExceptionForUnknownVersion(): void
    {
        $service = new CryptoService($this->settings);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown key version 999');

        $service->getKey(999);
    }

    public function testGetMasterKeyReturnsExistingKey(): void
    {
        $settingValue = $this->createMock(UnicodeString::class);
        $settingValue
            ->method('toString')
            ->willReturn('existing-master-key');

        $this->settings
            ->expects($this->exactly(2))
            ->method('getString')
            ->with(
                CryptoService::MASTER_KEY,
                Constants::OXID_MODULE_ID
            )
            ->willReturn($settingValue);

        $service = new CryptoService($this->settings);

        $this->assertSame(
            'existing-master-key',
            $service->getMasterKey()
        );
    }

    public function testGetMasterKeyInitializesMissingKey(): void
    {
        $emptySetting = $this->createMock(UnicodeString::class);
        $emptySetting
            ->method('toString')
            ->willReturn('');

        $generatedSetting = $this->createMock(UnicodeString::class);
        $generatedSetting
            ->method('toString')
            ->willReturn('generated-key');

        $this->settings
            ->expects($this->exactly(2))
            ->method('getString')
            ->with(
                CryptoService::MASTER_KEY,
                Constants::OXID_MODULE_ID
            )
            ->willReturnOnConsecutiveCalls(
                $emptySetting,
                $generatedSetting
            );

        $this->settings
            ->expects($this->once())
            ->method('saveString')
            ->with(
                CryptoService::MASTER_KEY,
                $this->isType('string'),
                Constants::OXID_MODULE_ID
            );

        $this->settings
            ->expects($this->once())
            ->method('saveInteger')
            ->with(
                CryptoService::MASTER_KEY_VERSION,
                CryptoService::KEY_VERSION_MASTER_KEY,
                Constants::OXID_MODULE_ID
            );

        $service = new CryptoService($this->settings);

        $this->assertSame(
            'generated-key',
            $service->getMasterKey()
        );
    }
}
