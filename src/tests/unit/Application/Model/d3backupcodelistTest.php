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

namespace D3\Totp\tests\unit\Application\Model;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3backupcode;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3backupcodelistTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3backupcodelist */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3backupcodelist::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::generateBackupCodes
     */
    public function generateBackupCodes()
    {
        /** @var FrontendController|MockObject $oViewMock */
        $oViewMock = $this->getMockBuilder(FrontendController::class)
            ->addMethods(['setBackupCodes'])
            ->getMock();
        $oViewMock->expects($this->once())->method('setBackupCodes')->willReturn(true);

        /** @var Config|MockObject $oConfigMock */
        $oConfigMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getActiveView'])
            ->getMock();
        $oConfigMock->method('getActiveView')->willReturn($oViewMock);

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['generateCode'])
            ->getMock();
        $oBackupCodeMock->expects($this->exactly(10))->method('generateCode');

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'deleteAllFromUser',
                'getD3BackupCodeObject',
                'd3GetConfig',
            ])
            ->getMock();
        $oModelMock->expects($this->once())->method('deleteAllFromUser')->willReturn(true);
        $oModelMock->method('getD3BackupCodeObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetConfig')->willReturn($oConfigMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'generateBackupCodes', ['123456']);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::getD3BackupCodeObject
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
     * @covers \D3\Totp\Application\Model\d3backupcodelist::d3GetConfig
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
     * @covers \D3\Totp\Application\Model\d3backupcodelist::save
     */
    public function savePass()
    {
        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['save'])
            ->getMock();
        $oBackupCodeMock->expects($this->once())->method('save')->willReturn(true);

        $aBackupCodeArray = [
            $oBackupCodeMock,
        ];

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getArray'])
            ->getMock();
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::getBaseObject
     */
    public function getBaseObjectReturnsRightObject()
    {
        $oBaseObject = $this->callMethod($this->_oModel, 'getBaseObject');

        $this->assertIsObject($oBaseObject);
        $this->assertInstanceOf(d3backupcode::class, $oBaseObject);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::verify
     */
    public function verifyFoundTotp()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foobar');

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $oBackupCodeMock->expects($this->once())->method('delete')->willReturn(true);

        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods([
                'getOne',
                'quoteIdentifier',
                'quote',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oDbMock->expects($this->once())->method('getOne')->willReturn('foobar');
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'd3GetDb',
                'getBaseObject',
                'd3GetUser',
            ])
            ->getMock();
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);
        $oModelMock->method('getBaseObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verify', ['123456'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::verify
     */
    public function verifyNotFoundTotp()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foobar');

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $oBackupCodeMock->expects($this->never())->method('delete')->willReturn(true);

        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods([
                'getOne',
                'quoteIdentifier',
                'quote',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oDbMock->expects($this->once())->method('getOne')->willReturn(null);
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'd3GetDb',
                'getBaseObject',
                'd3GetUser',
            ])
            ->getMock();
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);
        $oModelMock->method('getBaseObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'verify', ['123456'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::d3GetDb
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
     * @covers \D3\Totp\Application\Model\d3backupcodelist::deleteAllFromUser
     */
    public function deleteAllFromUserCodesFound()
    {
        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'quoteIdentifier',
                'quote',
            ])
            ->getMock();
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $oBackupCodeMock->expects($this->once())->method('delete')->willReturn(true);

        $aBackupCodeArray = [
            $oBackupCodeMock,
        ];

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'getArray',
                'selectString',
                'd3GetDb',
            ])
            ->getMock();
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);
        $oModelMock->expects($this->once())->method('selectString')->willReturn(true);
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'deleteAllFromUser', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::deleteAllFromUser
     */
    public function deleteAllFromUserNoCodesFound()
    {
        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods([
                'quoteIdentifier',
                'quote',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $oBackupCodeMock->expects($this->never())->method('delete')->willReturn(true);

        $aBackupCodeArray = [];

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'getArray',
                'selectString',
                'd3GetDb',
            ])
            ->getMock();
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);
        $oModelMock->expects($this->once())->method('selectString')->willReturn(true);
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'deleteAllFromUser', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::getAvailableCodeCount
     */
    public function getAvailableCodeCountPass()
    {
        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods([
                'getOne',
                'quoteIdentifier',
                'quote',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oDbMock->expects($this->once())->method('getOne')->willReturn('25');
        $oDbMock->method('quoteIdentifier')->willReturn(true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['d3GetDb'])
            ->getMock();
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
     * @covers \D3\Totp\Application\Model\d3backupcodelist::d3GetUser
     */
    public function d3GetUserReturnsRightInstance()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oModel, 'd3GetUser')
        );
    }
}
