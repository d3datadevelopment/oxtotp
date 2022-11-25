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

namespace unit\Modules\Core;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\Modules\Core\totpSystemEventHandler;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\SystemEventHandler;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\TestingLibrary\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class totpSystemEventHandlerTest extends UnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\totpSystemEventHandler::onAdminLogin
     */
    public function runOnAdminLogin()
    {
        /** @var totpSystemEventHandler|MockObject $sut */
        $sut = $this->getMockBuilder(SystemEventHandler::class)
            ->onlyMethods(['d3CallMockableParent', 'd3requestTotp'])
            ->getMock();

        $sut->method('d3CallMockableParent')->willReturn(true);
        $sut->expects($this->once())->method('d3requestTotp')->willReturn(true);

        $this->callMethod(
            $sut,
            'onAdminLogin'
        );
    }

    /**
     * @test
     *
     * @param $totpMissing
     * @param $doLogout
     * @param $doRedirect
     *
     * @return void
     * @throws ReflectionException
     * @dataProvider canRequestTotpDataProvider
     * @covers       \D3\Totp\Modules\Core\totpSystemEventHandler::d3requestTotp
     */
    public function canRequestTotp($totpMissing, $doLogout, $doRedirect)
    {
        /** @var Session|MockObject $sessionMock */
        $sessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $sessionMock->method('getVariable')->willReturn('myUserId');

        /** @var d3_totp_user|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['logout'])
            ->getMock();
        $userMock->expects($doLogout)->method('logout')->willReturn(true);

        /** @var Utils|MockObject $utilsMock */
        $utilsMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['redirect'])
            ->getMock();
        $utilsMock->expects($doRedirect)->method('redirect')->willReturn(true);

        /** @var d3totp|MockObject $totpMock */
        $totpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $totpMock->expects($this->atLeastOnce())->method('loadByUserId')->with('myUserId')->willReturn(1);

        /** @var totpSystemEventHandler|MockObject $sut */
        $sut = $this->getMockBuilder(SystemEventHandler::class)
            ->onlyMethods(['d3GetTotpObject', 'd3TotpGetSession', 'd3TotpLoginMissing',
                'd3TotpGetUserObject', 'getUtilsObject', ])
            ->getMock();
        $sut->method('d3GetTotpObject')->willReturn($totpMock);
        $sut->method('d3TotpGetSession')->willReturn($sessionMock);
        $sut->method('d3TotpLoginMissing')->with($totpMock)->willReturn($totpMissing);
        $sut->method('d3TotpGetUserObject')->willReturn($userMock);
        $sut->method('getUtilsObject')->willReturn($utilsMock);

        $this->callMethod(
            $sut,
            'd3requestTotp'
        );
    }

    /**
     * @return array
     */
    public function canRequestTotpDataProvider(): array
    {
        return [
            'no totp missing'   => [false, $this->never(), $this->never()],
            'totp missing'      => [true, $this->once(), $this->once()],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\totpSystemEventHandler::d3GetTotpObject
     */
    public function canGetTotpObject()
    {
        /** @var totpSystemEventHandler $sut */
        $sut = oxNew(SystemEventHandler::class);

        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod(
                $sut,
                'd3GetTotpObject'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\totpSystemEventHandler::getUtilsObject
     */
    public function canGetUtilsObject()
    {
        /** @var totpSystemEventHandler $sut */
        $sut = oxNew(SystemEventHandler::class);

        $this->assertInstanceOf(
            Utils::class,
            $this->callMethod(
                $sut,
                'getUtilsObject'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\totpSystemEventHandler::d3TotpGetSession
     */
    public function canGetSessionObject()
    {
        /** @var totpSystemEventHandler $sut */
        $sut = oxNew(SystemEventHandler::class);

        $this->assertInstanceOf(
            Session::class,
            $this->callMethod(
                $sut,
                'd3TotpGetSession'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Core\totpSystemEventHandler::d3TotpGetUserObject
     */
    public function canGetUserObject()
    {
        /** @var totpSystemEventHandler $sut */
        $sut = oxNew(SystemEventHandler::class);

        $this->assertInstanceOf(
            User::class,
            $this->callMethod(
                $sut,
                'd3TotpGetUserObject'
            )
        );
    }

    /**
     * @test
     * @param $isActive
     * @param $hasTotpAuth
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider checkTotpLoginMissingDataProvider
     * @covers \D3\Totp\Modules\Core\totpSystemEventHandler::d3TotpLoginMissing
     */
    public function checkTotpLoginMissing($isActive, $hasTotpAuth, $expected)
    {
        /** @var Session|MockObject $sessionMock */
        $sessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $sessionMock->method('getVariable')->willReturn($hasTotpAuth);

        /** @var d3totp|MockObject $totpMock */
        $totpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->getMock();
        $totpMock->method('isActive')->willReturn($isActive);

        /** @var totpSystemEventHandler|MockObject $sut */
        $sut = $this->getMockBuilder(SystemEventHandler::class)
            ->onlyMethods(['d3TotpGetSession'])
            ->getMock();
        $sut->method('d3TotpGetSession')->willReturn($sessionMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'd3TotpLoginMissing',
                [$totpMock]
            )
        );
    }

    /**
     * @return array
     */
    public function checkTotpLoginMissingDataProvider(): array
    {
        return [
            'totp not active'   => [false, false, false],
            'missing totp'      => [true, false, true],
            'totp exists'       => [true, true, false],
        ];
    }
}
