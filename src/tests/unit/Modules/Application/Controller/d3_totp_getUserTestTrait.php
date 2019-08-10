<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

namespace D3\Totp\tests\unit\Modules\Application\Controller;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Controller\d3_totp_OrderController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use PHPUnit_Framework_MockObject_MockObject;

trait d3_totp_getUserTestTrait
{
    /**
     * @test
     */
    public function getUserHasNoUser()
    {
        /** @var d3_totp_orderController|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock($this->sControllerClass, array(
            'd3GetTotpObject',
        ));
        $oControllerMock->expects($this->never())->method('d3GetTotpObject');

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     */
    public function getUserTotpNotActive()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId'
        ));
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'getVariable',
        ));
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_orderController|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock($this->sControllerClass, array(
            'd3GetTotpObject',
            'd3GetSessionObject'
        ));
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3GetSessionObject')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->setValue($this->_oController, '_oActUser', $oUserMock);

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     */
    public function getUserTotpFinished()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId'
        ));
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'getVariable',
        ));
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_orderController|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock($this->sControllerClass, array(
            'd3GetTotpObject',
            'd3GetSessionObject'
        ));
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3GetSessionObject')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->setValue($this->_oController, '_oActUser', $oUserMock);

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
     */
    public function getUserTotpNotFinished()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId'
        ));
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'getVariable',
        ));
        $oSessionMock->method('getVariable')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_orderController|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock($this->sControllerClass, array(
            'd3GetTotpObject',
            'd3GetSessionObject'
        ));
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3GetSessionObject')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->setValue($this->_oController, '_oActUser', $oUserMock);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'getUser')
        );
    }

    /**
     * @test
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
     */
    public function d3GetSessionObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oController, 'd3GetSessionObject')
        );
    }
}