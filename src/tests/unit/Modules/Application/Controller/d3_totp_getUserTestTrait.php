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

namespace D3\Totp\tests\unit\Modules\Application\Controller;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Controller\d3_totp_OrderController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

trait d3_totp_getUserTestTrait
{
    use CanAccessRestricted;

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserHasNoUser()
    {
        /** @var d3_totp_orderController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder($this->sControllerClass)
            ->onlyMethods(['d3GetTotpObject'])
            ->getMock();
        $oControllerMock->expects($this->never())->method('d3GetTotpObject');

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserTotpNotActive()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_orderController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder($this->sControllerClass)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
                'd3CallMockableParent'
            ])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oControllerMock->method('d3CallMockableParent')->willReturn($oUserMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserTotpFinished()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_orderController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder($this->sControllerClass)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
                'd3CallMockableParent'
            ])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oControllerMock->method('d3CallMockableParent')->willReturn($oUserMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserTotpNotFinished()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_orderController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder($this->sControllerClass)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
                'd3CallMockableParent'
            ])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oControllerMock->method('d3CallMockableParent')->willReturn($oUserMock);

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::d3GetTotpObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::d3GetTotpObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::d3GetTotpObject
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
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::d3TotpGetSessionObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::d3TotpGetSessionObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::d3TotpGetSessionObject
     */
    public function d3GetSessionObjectReturnsRightObject()
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
     * @te__st
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::d3CallMockableParent
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::d3CallMockableParent
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::d3CallMockableParent
     */
    public function d3callMockableParentTest()
    {
        $this->callMockableParentTest($this->_oController);
    }
}
