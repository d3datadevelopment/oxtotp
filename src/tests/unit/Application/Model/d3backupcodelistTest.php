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

namespace D3\Totp\tests\unit\Application\Model;

use D3\Totp\Application\Model\d3backupcode;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3backupcodelistTest extends d3TotpUnitTestCase
{
    /** @var d3backupcodelist */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oModel = oxNew(d3backupcodelist::class);
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
    public function generateBackupCodes()
    {
        /** @var FrontendController|PHPUnit_Framework_MockObject_MockObject $oConfigMock */
        $oViewMock = $this->getMock(FrontendController::class, array(
            'setBackupCodes'
        ));
        $oViewMock->expects($this->once())->method('setBackupCodes')->willReturn(true);

        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oConfigMock */
        $oConfigMock = $this->getMock(d3backupcode::class, array(
            'getActiveView'
        ));
        $oConfigMock->method('getActiveView')->willReturn($oViewMock);

        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMock(d3backupcode::class, array(
            'generateCode'
        ));
        $oBackupCodeMock->expects($this->exactly(10))->method('generateCode');
        $oBackupCodeMock->method('getD3BackupCodeObject')->willReturn($oBackupCodeMock);

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'deleteAllFromUser',
            'getD3BackupCodeObject',
            'd3GetConfig'
        ));
        $oModelMock->expects($this->once())->method('deleteAllFromUser')->willReturn(true);
        $oModelMock->method('getD3BackupCodeObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetConfig')->willReturn($oConfigMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'generateBackupCodes', array('123456'));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getD3BackupCodeObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            d3backupcode::class,
            $this->callMethod($this->_oModel, 'getD3BackupCodeObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetConfigReturnsRightObject()
    {
        $this->assertInstanceOf(
            Config::class,
            $this->callMethod($this->_oModel, 'd3GetConfig')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function savePass()
    {
        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMock(d3backupcode::class, array(
            'save'
        ));
        $oBackupCodeMock->expects($this->once())->method('save')->willReturn(true);

        $aBackupCodeArray = [
            $oBackupCodeMock
        ];

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'getArray'
        ));
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBaseObjectReturnsRightObject()
    {
        $oBaseObject = $this->callMethod($this->_oModel, 'getBaseObject');

        $this->assertInternalType('object', $oBaseObject);
        $this->assertInstanceOf(d3backupcode::class, $oBaseObject);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function verifyFoundTotp()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId'
        ));
        $oUserMock->method('getId')->willReturn('foobar');

        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMock(d3backupcode::class, array(
            'delete'
        ));
        $oBackupCodeMock->expects($this->once())->method('delete')->willReturn(true);

        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne',
            'quoteIdentifier',
            'quote',
        ), array(), '', false);
        $oDbMock->expects($this->once())->method('getOne')->willReturn('foobar');
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'd3GetDb',
            'getBaseObject',
            'd3GetUser'
        ));
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);
        $oModelMock->method('getBaseObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verify', array('123456'))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function verifyNotFoundTotp()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId'
        ));
        $oUserMock->method('getId')->willReturn('foobar');
        
        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMock(d3backupcode::class, array(
            'delete'
        ));
        $oBackupCodeMock->expects($this->never())->method('delete')->willReturn(true);

        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne',
            'quoteIdentifier',
            'quote',
        ), array(), '', false);
        $oDbMock->expects($this->once())->method('getOne')->willReturn(null);
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'd3GetDb',
            'getBaseObject',
            'd3GetUser'
        ));
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);
        $oModelMock->method('getBaseObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'verify', array('123456'))
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
    public function deleteAllFromUserCodesFound()
    {
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'quoteIdentifier',
            'quote',
        ), array(), '', false);
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMock(d3backupcode::class, array(
            'delete'
        ));
        $oBackupCodeMock->expects($this->once())->method('delete')->willReturn(true);

        $aBackupCodeArray = [
            $oBackupCodeMock
        ];

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'getArray',
            'selectString',
            'd3GetDb'
        ));
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);
        $oModelMock->expects($this->once())->method('selectString')->willReturn(true);
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'deleteAllFromUser', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function deleteAllFromUserNoCodesFound()
    {
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'quoteIdentifier',
            'quote',
        ), array(), '', false);
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMock(d3backupcode::class, array(
            'delete'
        ));
        $oBackupCodeMock->expects($this->never())->method('delete')->willReturn(true);

        $aBackupCodeArray = [];

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'getArray',
            'selectString',
            'd3GetDb'
        ));
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);
        $oModelMock->expects($this->once())->method('selectString')->willReturn(true);
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'deleteAllFromUser', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getAvailableCodeCountPass()
    {
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne',
            'quoteIdentifier',
            'quote',
        ), array(), '', false);
        $oDbMock->expects($this->once())->method('getOne')->willReturn('25');
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcodelist::class, array(
            'd3GetDb',
        ));
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->assertSame(
            25,
            $this->callMethod($this->_oModel, 'getAvailableCodeCount', ['foobar'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetUserReturnsRightInstance()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oModel, 'd3GetUser')
        );
    }
}