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

namespace D3\Totp\tests\unit\Setup;

use D3\Totp\Setup\Installation;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class InstallationTest extends d3TotpUnitTestCase
{
    /** @var Installation */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oModel = oxNew(Installation::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function doesTotpTableNotExistCallCheckMethod()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            '_checkTableNotExist',
        ));
        $oModelMock->expects($this->once())->method('_checkTableNotExist')->with('d3totp')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'testReturn',
            $this->callMethod($this->_oModel, 'doesTotpTableNotExist')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function addTotpTableNotExistingTable()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            'doesTotpTableNotExist',
            '_addTable2',
        ));
        $oModelMock->method('doesTotpTableNotExist')->willReturn(true);
        $oModelMock->expects($this->once())->method('_addTable2')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'testReturn',
            $this->callMethod($this->_oModel, 'addTotpTable')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function addTotpTableExistingTable()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            'doesTotpTableNotExist',
            '_addTable2',
        ));
        $oModelMock->method('doesTotpTableNotExist')->willReturn(false);
        $oModelMock->expects($this->never())->method('_addTable2')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'addTotpTable')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function doesTotpBCTableNotExistCallCheckMethod()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            '_checkTableNotExist',
        ));
        $oModelMock->expects($this->once())->method('_checkTableNotExist')->with('d3totp_backupcodes')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'testReturn',
            $this->callMethod($this->_oModel, 'doesTotpBCTableNotExist')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function addTotpBCTableNotExistingTable()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            'doesTotpBCTableNotExist',
            '_addTable2',
        ));
        $oModelMock->method('doesTotpBCTableNotExist')->willReturn(true);
        $oModelMock->expects($this->once())->method('_addTable2')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'testReturn',
            $this->callMethod($this->_oModel, 'addTotpBCTable')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function addTotpBCTableExistingTable()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            'doesTotpBCTableNotExist',
            '_addTable2',
        ));
        $oModelMock->method('doesTotpBCTableNotExist')->willReturn(false);
        $oModelMock->expects($this->never())->method('_addTable2')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'addTotpBCTable')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetDbReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Database::class,
            $this->callMethod($this->_oModel, 'd3GetDb')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkSEONotExistsPass()
    {
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne'
        ), array(), '', false);
        $oDbMock->expects($this->once())->method('getOne')->willReturn(true);

        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            'd3GetDb'
        ));
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->assertFalse($this->callMethod($this->_oModel, 'checkSEONotExists'));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function addSEOPass()
    {
        /** @var Installation|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(Installation::class, array(
            '_executeMultipleQueries'
        ));
        $oModelMock->expects($this->once())->method('_executeMultipleQueries')->willReturn('testReturn');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'testReturn',
            $this->callMethod($this->_oModel, 'addSEO')
        );
    }
}