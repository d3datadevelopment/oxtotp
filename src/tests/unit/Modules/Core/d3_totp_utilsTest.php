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

namespace D3\Totp\tests\unit\Modules\Core;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Core\d3_totp_utils;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3_totp_utilsTest extends d3TotpUnitTestCase
{
    /** @var d3_totp_utils */
    protected $_oCoreClass;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oCoreClass = oxNew(Utils::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->_oCoreClass);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkAccessRightsNoAuth()
    {
        Registry::getSession()->setVariable("auth", false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId',
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var d3_totp_utils|PHPUnit_Framework_MockObject_MockObject $oCoreMock */
        $oCoreMock = $this->getMock(Utils::class, array(
            'd3GetTotpObject',
            'd3GetUtilsObject'
        ));
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->expects($this->never())->method('d3GetUtilsObject');

        $this->_oCoreClass = $oCoreMock;

        $this->assertFalse(
            $this->callMethod($this->_oCoreClass, 'checkAccessRights')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkAccessRightsTotpNotActive()
    {
        Registry::getSession()->setVariable("auth", 'foo');

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId',
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var d3_totp_utils|PHPUnit_Framework_MockObject_MockObject $oCoreMock */
        $oCoreMock = $this->getMock(Utils::class, array(
            'd3GetTotpObject',
            'd3GetUtilsObject',
            'fetchRightsForUser'
        ));
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->expects($this->never())->method('d3GetUtilsObject');
        $oCoreMock->method('fetchRightsForUser')->willReturn('malladmin');

        $this->_oCoreClass = $oCoreMock;

        $this->assertTrue(
            $this->callMethod($this->_oCoreClass, 'checkAccessRights')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkAccessRightsTotpFinished()
    {
        Registry::getSession()->setVariable("auth", 'foo');

        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'getVariable',
        ));
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', true));

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId',
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var d3_totp_utils|PHPUnit_Framework_MockObject_MockObject $oCoreMock */
        $oCoreMock = $this->getMock(Utils::class, array(
            'd3GetTotpObject',
            'd3GetSessionObject',
            'fetchRightsForUser',
            'redirect'
        ));
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->method('d3GetSessionObject')->willReturn($oSessionMock);
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
     */
    public function checkAccessRightsTotpUnfinished()
    {
        Registry::getSession()->setVariable("auth", 'foo');

        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'getVariable',
        ));
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', false));

        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'getVariable',
        ));
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', false));

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId',
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var d3_totp_utils|PHPUnit_Framework_MockObject_MockObject $oCoreMock */
        $oCoreMock = $this->getMock(Utils::class, array(
            'd3GetTotpObject',
            'd3GetSessionObject',
            'fetchRightsForUser',
            'redirect'
        ));
        $oCoreMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oCoreMock->method('d3GetSessionObject')->willReturn($oSessionMock);
        $oCoreMock->method('fetchRightsForUser')->willReturn('malladmin');
        $oCoreMock->expects($this->once())->method('redirect')->willReturn(true);

        $this->_oCoreClass = $oCoreMock;

        $this->callMethod($this->_oCoreClass, 'checkAccessRights');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetSessionObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oCoreClass, 'd3GetSessionObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetTotpObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oCoreClass, 'd3GetTotpObject')
        );
    }
}