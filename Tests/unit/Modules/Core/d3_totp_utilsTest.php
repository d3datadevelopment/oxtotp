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

namespace D3\Totp\tests\unit\Modules\Core;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Core\d3_totp_utils;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_utilsTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3_totp_utils */
    protected $_oCoreClass;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oCoreClass = oxNew(d3_totp_utils::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oCoreClass);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::checkAccessRights
     */
    public function checkAccessRightsNoAuth()
    {
        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['d3GetTotpObject'])
            ->getMock();
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oCoreClass = $oCoreMock;

        $this->assertFalse(
            $this->callMethod($this->_oCoreClass, 'checkAccessRights')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::checkAccessRights
     */
    public function checkAccessRightsForce2FA()
    {
        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['d3GetTotpObject', 'd3AuthHook', 'redirect', 'd3IsAdminForce2FA'])
            ->getMock();
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->method('d3AuthHook')->willReturn(true);
        $oCoreMock->expects($this->once())->method('redirect')
            ->with($this->stringContains('d3force_2fa'))->willReturn(true);
        $oCoreMock->method('d3IsAdminForce2FA')->willReturn(true);

        $this->_oCoreClass = $oCoreMock;

        $this->assertTrue(
            $this->callMethod($this->_oCoreClass, 'checkAccessRights')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::checkAccessRights
     */
    public function checkAccessRightsTotpNotActive()
    {
        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, 'foo');

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'fetchRightsForUser',
            ])
            ->getMock();
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->method('fetchRightsForUser')->willReturn('malladmin');

        $this->_oCoreClass = $oCoreMock;

        $this->assertTrue(
            $this->callMethod($this->_oCoreClass, 'checkAccessRights')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::checkAccessRights
     */
    public function checkAccessRightsTotpFinished()
    {
        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, 'foo');

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', true));

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
                'fetchRightsForUser',
                'redirect',
            ])
            ->getMock();
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oCoreMock->method('fetchRightsForUser')->willReturn('malladmin');
        $oCoreMock->expects($this->never())->method('redirect')->willReturn(true);

        $this->_oCoreClass = $oCoreMock;

        $this->assertTrue(
            $this->callMethod($this->_oCoreClass, 'checkAccessRights')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::checkAccessRights
     */
    public function checkAccessRightsTotpUnfinished()
    {
        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, 'foo');

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', false));

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', false));

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
                'fetchRightsForUser',
                'redirect',
            ])
            ->getMock();
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oCoreMock->method('fetchRightsForUser')->willReturn('malladmin');
        $oCoreMock->expects($this->once())->method('redirect')->willReturn(true);

        $this->_oCoreClass = $oCoreMock;

        $this->callMethod($this->_oCoreClass, 'checkAccessRights');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3TotpGetSessionObject
     */
    public function d3GetSessionObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oCoreClass, 'd3TotpGetSessionObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3GetTotpObject
     */
    public function d3GetTotpObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oCoreClass, 'd3GetTotpObject')
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3GetConfig
     */
    public function d3GetConfigReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Config::class,
            $this->callMethod(
                $this->_oCoreClass,
                'd3GetConfig'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @dataProvider d3IsAdminForce2FADataProvider
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3IsAdminForce2FA
     */
    public function d3IsAdminForce2FA($isAdmin, $hasConfig, $expected)
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->with($this->equalTo('D3_TOTP_ADMIN_FORCE_2FA'))->willReturn($hasConfig);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['isAdmin', 'd3GetConfig'])
            ->getMock();
        $oCoreMock->method('isAdmin')->willReturn($isAdmin);
        $oCoreMock->method('d3GetConfig')->willReturn($configMock);

        $this->_oCoreClass = $oCoreMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oCoreClass,
                'd3IsAdminForce2FA'
            )
        );
    }

    /**
     * @return array
     */
    public function d3IsAdminForce2FADataProvider(): array
    {
        return [
            //'noAdmin, noConfig'   => [false, false, false],
            //'noAdmin'   => [false, true, false],
            //'noConfig'   => [true, false, false],
            'passed'   => [true, true, true],
        ];
    }

    /**
     * @test
     * @return void
     * @dataProvider d3AuthHookDataProvider
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3AuthHook
     */
    public function d3AuthHook($argument)
    {
        $this->assertSame(
            $argument,
            $this->callMethod(
                $this->_oCoreClass,
                'd3AuthHook',
                [$argument]
            )
        );
    }

    /**
     * @return array
     */
    public function d3AuthHookDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
