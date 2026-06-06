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

namespace D3\Totp\Tests\Unit\Application\Model;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3backupcode;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Tests\Unit\d3TotpUnitTestCase;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Generator;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class d3backupcodelistTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3backupcodelist */
    protected d3backupcodelist $_oModel;

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
        $oViewMock = $this->d3getMockBuilder(FrontendController::class)
            ->addMethods(['setBackupCodes'])
            ->getMock();
        $oViewMock->expects($this->once())->method('setBackupCodes')->willReturn(true);

        /** @var Config|MockObject $oConfigMock */
        $oConfigMock = $this->d3getMockBuilder(Config::class)
            ->onlyMethods(['getActiveView'])
            ->getMock();
        $oConfigMock->method('getActiveView')->willReturn($oViewMock);

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['generateCode'])
            ->getMock();
        $oBackupCodeMock->expects($this->exactly(10))->method('generateCode');

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'deleteAllFromUser',
                'getD3BackupCodeObject',
                'd3GetConfig',
            ])
            ->getMock();
        $oModelMock->expects($this->once())->method('deleteAllFromUser');
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
        $oBackupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['save'])
            ->getMock();
        $oBackupCodeMock->expects($this->once())->method('save')->willReturn(true);

        $aBackupCodeArray = [
            $oBackupCodeMock,
        ];

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getArray'])
            ->getMock();
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'save');
    }

    /**
     * @param string $code
     * @param bool $expected
     * @return void
     * @throws ReflectionException
     * @dataProvider verifyArgon2Provider
     */
    public function testVerifyArgon2(string $code, bool $expected)
    {
        $sut = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['loadUserArgonBackupCodes'])
            ->getMock();
        $sut->expects($this->once())->method('loadUserArgonBackupCodes');

        $backupCode = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $backupCode->expects($this->exactly((int) $expected))->method('delete');
        $backupCode->assign([
            'backupcode' => $backupCode->d3EncodeBC('foobar')
        ]);
        $sut->offsetSet('foo', $backupCode);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'verifyArgon2',
                [$code]
            )
        );
    }

    public static function verifyArgon2Provider(): Generator
    {
        yield 'passed' => ['foobar', true];
        yield 'failed' => ['barfoo', false];
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testLoadUserArgonBackupCodes(): void
    {
        $sut = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['selectString'])
            ->getMock();
        $sut->expects($this->once())->method('selectString');

        $this->callMethod($sut, 'loadUserArgonBackupCodes');
    }

    /**
     * @test
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::verifyLegacy
     */
    public function verifyLegacyFoundTotp()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->d3getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foobar');
        $oUserMock->assign(['oxpasssalt' => '6162636465666768696A6B']);

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete', 'd3TotpGetUserObject'])
            ->getMock();
        $oBackupCodeMock->expects($this->once())->method('delete')->willReturn(true);
        $oBackupCodeMock->method('d3TotpGetUserObject')->willReturn($oUserMock);

        $resultMock = $this->d3getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $resultMock->method('fetchOne')->willReturn('1');

        $qbMock = $this->d3getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get()])
            ->onlyMethods(['execute'])
            ->getMock();
        $qbMock->method('execute')->willReturn($resultMock);

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'getQueryBuilder',
                'getBaseObject',
                'd3GetUser',
            ])
            ->getMock();
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);
        $oModelMock->method('getBaseObject')->willReturn($oBackupCodeMock);
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verifyLegacy', ['123456'])
        );
    }

    /**
     * @test
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::verify
     */
    public function verifyNotFoundTotp()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->d3getMockBuilder(User::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $oUserMock->method('getId')->willReturn('foobar');
        $oUserMock->assign(['oxpasssalt' => '6162636465666768696A6B']);

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete', 'd3TotpGetUserObject'])
            ->getMock();
        $oBackupCodeMock->expects($this->never())->method('delete')->willReturn(true);
        $oBackupCodeMock->method('d3TotpGetUserObject')->willReturn($oUserMock);

        $resultMock = $this->d3getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $resultMock->method('fetchOne')->willReturn('');

        $qbMock = $this->d3getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get()])
            ->onlyMethods(['execute'])
            ->getMock();
        $qbMock->method('execute')->willReturn($resultMock);

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'getQueryBuilder',
                'getBaseObject',
                'd3GetUser',
            ])
            ->getMock();
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @covers \D3\Totp\Application\Model\d3backupcodelist::deleteAllFromUser
     */
    public function deleteAllFromUserCodesFound()
    {
        $qbMock = new QueryBuilder(ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get());

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $oBackupCodeMock->expects($this->once())->method('delete')->willReturn(true);

        $aBackupCodeArray = [
            $oBackupCodeMock,
        ];

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'getArray',
                'selectString',
                'getQueryBuilder',
            ])
            ->getMock();
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);
        $oModelMock->expects($this->once())->method('selectString')->willReturn(true);
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'deleteAllFromUser', ['foobar']);
    }

    /**
     * @test
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::deleteAllFromUser
     */
    public function deleteAllFromUserNoCodesFound()
    {
        $qbMock = new QueryBuilder(ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get());

        /** @var d3backupcode|MockObject $oBackupCodeMock */
        $oBackupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['delete'])
            ->getMock();
        $oBackupCodeMock->expects($this->never())->method('delete')->willReturn(true);

        $aBackupCodeArray = [];

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'getArray',
                'selectString',
                'getQueryBuilder',
            ])
            ->getMock();
        $oModelMock->expects($this->once())->method('getArray')->willReturn($aBackupCodeArray);
        $oModelMock->expects($this->once())->method('selectString')->willReturn(true);
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'deleteAllFromUser', ['foobar']);
    }

    /**
     * @test
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcodelist::getAvailableCodeCount
     */
    public function getAvailableCodeCountPass()
    {
        $resultMock = $this->d3getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $resultMock->method('fetchOne')->willReturn(25);

        $qbMock = $this->d3getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get()])
            ->onlyMethods(['execute'])
            ->getMock();
        $qbMock->method('execute')->willReturn($resultMock);

        /** @var d3backupcodelist|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getQueryBuilder'])
            ->getMock();
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);

        $this->_oModel = $oModelMock;

        $this->assertSame(
            25,
            $this->callMethod($this->_oModel, 'getAvailableCodeCount', ['foobar'])
        );
    }
}
