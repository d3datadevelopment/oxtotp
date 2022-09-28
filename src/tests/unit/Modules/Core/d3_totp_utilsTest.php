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
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_utilsTest extends d3TotpUnitTestCase
{
    /** @var d3_totp_utils */
    protected $_oCoreClass;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oCoreClass = oxNew(Utils::class);
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
        Registry::getSession()->setVariable("auth", false);

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
    public function checkAccessRightsTotpNotActive()
    {
        Registry::getSession()->setVariable("auth", 'foo');

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'fetchRightsForUser'
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
        Registry::getSession()->setVariable("auth", 'foo');

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls('foo', true));

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'loadByUserId',
                'isActive'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var d3_totp_utils|MockObject $oCoreMock */
        $oCoreMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3GetSessionObject',
                'fetchRightsForUser',
                'redirect'
            ])
            ->getMock();
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
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::checkAccessRights
     */
    public function checkAccessRightsTotpUnfinished()
    {
        Registry::getSession()->setVariable("auth", 'foo');

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
                'd3GetSessionObject',
                'fetchRightsForUser',
                'redirect'
            ])
            ->getMock();
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
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3GetSessionObject
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
     * @covers \D3\Totp\Modules\Core\d3_totp_utils::d3GetTotpObject
     */
    public function d3GetTotpObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oCoreClass, 'd3GetTotpObject')
        );
    }
}