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

namespace D3\Totp\tests\unit\Modules\Application\Controller\Admin;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_LoginControllerTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3_totp_LoginController */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3_totp_LoginController::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3GetTotpObject
     */
    public function d3GetTotpObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oController, 'd3GetTotpObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpGetSession
     */
    public function d3GetSessionReturnsRightObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oController, 'd3TotpGetSession')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::checklogin
     */
    public function checkloginMissingTotp()
    {
        $fixture = 'returnString';

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_user|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['logout'])
            ->getMock();
        $userMock->expects($this->once())->method('logout')->willReturn(true);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetUserObject',
                'd3TotpLoginMissing',
                'd3CallMockableParent'
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetUserObject')->willReturn($userMock);
        $oControllerMock->method('d3TotpLoginMissing')->with($this->identicalTo($oTotpMock))
                                                      ->willReturn(true);
        $oControllerMock->method('d3CallMockableParent')->willReturn($fixture);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totpadminlogin',
            $this->callMethod(
                $this->_oController,
                'checklogin'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::checklogin
     */
    public function checkloginNotMissingTotp()
    {
        $fixture = 'returnString';

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_user|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['logout'])
            ->getMock();
        $userMock->expects($this->never())->method('logout')->willReturn(true);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetUserObject',
                'd3TotpLoginMissing',
                'd3CallMockableParent'
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetUserObject')->willReturn($userMock);
        $oControllerMock->method('d3TotpLoginMissing')->with($this->identicalTo($oTotpMock))
                                                      ->willReturn(false);
        $oControllerMock->method('d3CallMockableParent')->willReturn($fixture);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $fixture,
            $this->callMethod(
                $this->_oController,
                'checklogin'
            )
        );
    }

    /**
     * @test
     * @param $totpActive
     * @param $loggedin
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpLoginMissing
     * @dataProvider d3TotpLoginMissingTestDataProvider
     */
    public function d3TotpLoginMissingTest($totpActive, $loggedin, $expected)
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isActive'])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn($totpActive);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->with(d3totp_conf::SESSION_ADMIN_AUTH)->willReturn($loggedin);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3TotpGetSession'
            ])
            ->getMock();
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'd3TotpLoginMissing',
                [$oTotpMock]
            )
        );
    }

    /**
     * @return array
     */
    public function d3TotpLoginMissingTestDataProvider(): array
    {
        return [
            'totp not active, not logged in'=> [false, false, false],
            'totp active, logged in'        => [true , true, false],
            'totp active, not logged in'    => [true , false, true],
            'totp not active, logged in'    => [false, true, false],
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpGetUserObject
     */
    public function d3GetUserObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oController, 'd3TotpGetUserObject')
        );
    }
}
