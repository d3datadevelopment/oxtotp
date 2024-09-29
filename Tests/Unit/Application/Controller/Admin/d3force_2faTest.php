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

namespace D3\Totp\Tests\Unit\Application\Controller\Admin;

use D3\Totp\Application\Controller\Admin\d3force_2fa;
use D3\Totp\Application\Model\d3totp_conf;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\Setting;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3force_2faTest extends d3user_totpTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3force_2fa::class);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::render
     */
    public function testRender()
    {
        $expected = 'fixture';

        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, $expected);

        $this->callMethod(
            $this->_oController,
            'render'
        );

        $this->assertTrue(
            $this->_oController->getViewDataElement('force2FA')
        );
        $this->assertSame(
            $expected,
            $this->getValue(
                $this->_oController,
                '_sEditObjectId'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::authorize
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::d3IsAdminForce2FA
     * @dataProvider authorizeDataProvider
     */
    public function testAuthorize($expected, $isAdmin, $force2FA, $givenUserId)
    {
        $settingMock = $this->d3getMockBuilder(Setting::class)
            ->onlyMethods(['getValue'])
            ->getMock();
        $settingMock->method('getValue')->willReturn($force2FA);

        $moduleConfigurationMock = $this->d3getMockBuilder(ModuleConfiguration::class)
            ->onlyMethods(['getModuleSetting'])
            ->getMock();
        $moduleConfigurationMock->method('getModuleSetting')->willReturn($settingMock);

        /** @var d3force_2fa|MockObject $oController */
        $oController = $this->d3getMockBuilder(d3force_2fa::class)
            ->onlyMethods(['isAdmin', 'getModuleConfiguration'])
            ->getMock();
        $oController->expects($this->any())->method('isAdmin')->willReturn($isAdmin);
        $oController->method('getModuleConfiguration')->willReturn($moduleConfigurationMock);

        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, $givenUserId);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $oController,
                'authorize'
            )
        );
    }

    /**
     * @return array[]
     */
    public static function authorizeDataProvider(): array
    {
        return [
            'noAdmin'   => [false, false, true, 'userId'],
            'dont force'   => [false, true, false, 'userId'],
            'no user id'   => [false, true, true, null],
            'passed'   => [true, true, true, 'userId'],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::d3TotpGetSessionObject
     */
    public function testD3GetSessionObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod(
                $this->_oController,
                'd3TotpGetSessionObject'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::getModuleConfiguration
     */
    public function canGetModuleConfiguration()
    {
        $this->assertInstanceOf(
            ModuleConfiguration::class,
            $this->callMethod(
                $this->_oController,
                'getModuleConfiguration'
            )
        );
    }
}
