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

use BaconQrCode\Writer;
use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Factory\BaconQrCodeFactory;
use D3\Totp\Application\Model\d3backupcode;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\Exceptions\replayException;
use D3\Totp\Application\Model\Exceptions\tooManyAttemptsException;
use D3\Totp\Application\Model\Exceptions\wrongOtpException;
use D3\Totp\Tests\Unit\d3TotpUnitTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Generator;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class d3totpTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3totp */
    protected d3totp $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3totp::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    protected function getUserFixture(string $username = 'username'): User
    {
        $user = oxNew(User::class);
        $user->setId('foo');
        $user->assign(['oxusername' => $username]);

        return $user;
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::__construct
     */
    public function constructCallsInit()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['init'])
            ->getMock();
        $oModelMock->expects($this->once())->method('init');

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, '__construct');
    }

    /**
     * @test
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @covers \D3\Totp\Application\Model\d3totp::loadByUserId
     */
    public function loadByUserIdTableNotExist()
    {
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

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getQueryBuilder',
                'load',
            ])
            ->getMock();
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);
        $oModelMock->expects($this->never())->method('load')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'loadByUserId', ['foobar']);
    }

    /**
     * @test
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::loadByUserId
     */
    public function loadByUserIdTableExist()
    {
        $resultMock = $this->d3getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $resultMock->method('fetchOne')->willReturn('oxid');

        $qbMock = $this->d3getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get()])
            ->onlyMethods(['execute'])
            ->getMock();
        $qbMock->method('execute')->willReturn($resultMock);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getQueryBuilder',
                'load',
            ])
            ->getMock();
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);
        $oModelMock->expects($this->once())->method('load')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'loadByUserId', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getUser
     */
    public function getUserFromMember()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->d3getMockBuilder(User::class)
            ->onlyMethods(['load'])
            ->getMock();
        $oUserMock->method('load')->with('foobar')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'd3GetUser',
                'getFieldData',
            ])
            ->getMock();
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);
        $oModelMock->expects($this->never())->method('getFieldData')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->setValue($this->_oModel, 'userId', 'foobar');

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oModel, 'getUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getUser
     */
    public function getUserFromObject()
    {
        $this->setValue($this->_oModel, 'userId', null);

        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->d3getMockBuilder(User::class)
            ->onlyMethods(['load'])
            ->getMock();
        $oUserMock->method('load')->with('barfoo')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'd3GetUser',
                'getFieldData',
            ])
            ->getMock();
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);
        $oModelMock->expects($this->once())->method('getFieldData')->willReturn('barfoo');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oModel, 'getUser')
        );
    }

    /**
     * @test
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::checkIfAlreadyExist
     */
    public function checkIfAlreadyExistPass()
    {
        $resultMock = $this->d3getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $resultMock->method('fetchOne')->willReturn(2);

        $qbMock = $this->d3getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get()])
            ->onlyMethods(['execute'])
            ->getMock();
        $qbMock->method('execute')->willReturn($resultMock);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getQueryBuilder'])
            ->getMock();
        $oModelMock->method('getQueryBuilder')->willReturn($qbMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'checkIfAlreadyExist', ['testUserId'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::d3GetUser
     */
    public function d3GetUserReturnsRightInstance()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oModel, 'd3GetUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::isActive
     */
    public function isActivePass()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['UserUseTotp'])
            ->getMock();
        $oModelMock->method('UserUseTotp')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'isActive')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::isActive
     */
    public function isActiveFailedBecauseNoTotpUse()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['UserUseTotp'])
            ->getMock();
        $oModelMock->method('UserUseTotp')->willReturn(false);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'isActive')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::isActive
     */
    public function isActiveFailedBecauseConfigParam()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['UserUseTotp'])
            ->getMock();
        $oModelMock->method('UserUseTotp')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'isActive')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::isActive
     */
    public function isActiveFailedBecauseNoTotpUseAndConfigParam()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['UserUseTotp'])
            ->getMock();
        $oModelMock->method('UserUseTotp')->willReturn(false);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'isActive')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::UserUseTotp
     */
    public function UserUseTotpPass()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturnOnConsecutiveCalls(true, true);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'UserUseTotp')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::UserUseTotp
     */
    public function UserUseTotpNoTotp()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturnOnConsecutiveCalls(false, true);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'UserUseTotp')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::UserUseTotp
     */
    public function UserUseTotpNoSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturnOnConsecutiveCalls(true, false);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'UserUseTotp')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::UserUseTotp
     */
    public function UserUseTotpNoTotpAndNoSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturnOnConsecutiveCalls(false, false);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'UserUseTotp')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getSavedSecret
     */
    public function getSavedSecretExistingSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getFieldData',
                'decrypt',
            ])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturn('seed');
        $oModelMock->method('decrypt')->willReturn('unencseed');

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'unencseed',
            $this->callMethod($this->_oModel, 'getSavedSecret')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getSavedSecret
     */
    public function getSavedSecretNoSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getFieldData',
                'decrypt',
            ])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturn(null);
        $oModelMock->method('decrypt')->willReturn('unencseed');

        $this->_oModel = $oModelMock;

        $this->assertNull(
            $this->callMethod($this->_oModel, 'getSavedSecret')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getSavedSecret
     */
    public function getSavedSecretCantDecrypt()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getFieldData',
                'decrypt',
            ])
            ->getMock();
        $oModelMock->method('getFieldData')->willReturn('seed');
        $oModelMock->method('decrypt')->willReturn(false);

        $this->_oModel = $oModelMock;

        $this->assertNull(
            $this->callMethod($this->_oModel, 'getSavedSecret')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getTotp
     */
    public function getTotpReturnsCachedObject()
    {
        $otpMock = TOTP::createFromSecret('abc');

        $this->setValue($this->_oModel, 'totp', $otpMock);

        $this->assertSame(
            $otpMock,
            $this->callMethod($this->_oModel, 'getTotp', [$this->getUserFixture()])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getTotp
     */
    public function getTotpReturnsNewObject()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->d3getMockBuilder(User::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oUserMock->method('getFieldData')->willReturn('username');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getSavedSecret',
            ])
            ->getMock();
        $oModelMock->method('getSavedSecret')->willReturn('savedSecret');

        $this->_oModel = $oModelMock;

        /** @var TOTP $oTotp */
        $oTotp = $this->callMethod($this->_oModel, 'getTotp', [$oUserMock]);

        $this->assertInstanceOf(TOTP::class, $oTotp);
        $this->assertSame('username', $oTotp->getLabel());
        $this->assertSame('SAVEDSECRET', $oTotp->getSecret());
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getTotp
     */
    public function getTotpReturnsNewObjectNoUserGivenSeed()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->d3getMockBuilder(User::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oUserMock->method('getFieldData')->willReturnMap(
            [['oxusername', 'oxusername']]
        );

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getSavedSecret',
            ])
            ->getMock();
        $oModelMock->method('getSavedSecret')->willReturn('savedSecret');

        $this->_oModel = $oModelMock;

        /** @var TOTP $oTotp */
        $oTotp = $this->callMethod($this->_oModel, 'getTotp', [$oUserMock, 'givenSeed']);

        $this->assertInstanceOf(TOTP::class, $oTotp);
        $this->assertSame('oxusername', $oTotp->getLabel());
        $this->assertSame('GIVENSEED', $oTotp->getSecret());
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getQrCodeElement
     */
    public function getQrCodeElement()
    {
        BaconQrCodeFactory::renderer(200);

        $otpMock = TOTP::createFromSecret('abc');
        $otpMock->setLabel('label');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getTotp'])
            ->disableOriginalConstructor()
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($otpMock);

        $this->_oModel = $oModelMock;

        $this->assertIsString(
            $this->callMethod($this->_oModel, 'getQrCodeElement', [$this->getUserFixture()])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::d3GetWriter
     */
    public function d3GetWriterReturnsRightInstance()
    {
        $renderer = BaconQrCodeFactory::renderer(200);

        $this->assertInstanceOf(
            Writer::class,
            $this->callMethod($this->_oModel, 'd3GetWriter', [$renderer])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getSecret
     */
    public function getSecretPass()
    {
        $otpMock = TOTP::createFromSecret('abc');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getTotp'])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($otpMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'getSecret', [$this->getUserFixture()]);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::setSecret
     * @covers \D3\Totp\Application\Model\d3totp::getFieldData
     */
    public function setSecretPass()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['encrypt'])
            ->getMock();
        $oModelMock->method('encrypt')->willReturn('enc_secret');

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'setSecret', ['newSecret']);
        $this->assertSame(
            'enc_secret',
            $this->callMethod($this->_oModel, 'getFieldData', ['seed'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::verify
     * @covers \D3\Totp\Application\Model\d3totp::getClock
     */
    public function verifyPass()
    {
        $otpMock = new TOTPMock();

        $userMock = oxNew(User::class);
        $userMock->setId('foo');
        $userMock->assign(['oxpasssalt' => '6162636465666768696A6B']);

        $backupCodeMock = $this->d3getMockBuilder(d3backupcode::class)
            ->onlyMethods(['d3TotpGetUserObject'])
            ->getMock();
        $backupCodeMock->method('d3TotpGetUserObject')->willReturn($userMock);

        $backupCodeListMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['d3GetUser', 'getBaseObject', 'verify'])
            ->getMock();
        $backupCodeListMock->method('d3GetUser')->willReturn($userMock);
        $backupCodeListMock->method('getBaseObject')->willReturn($backupCodeMock);
        $backupCodeListMock->method('verify')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['getTotp', 'd3GetBackupCodeListObject', 'resetFailedAttempts'])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($otpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($backupCodeListMock);
        $oModelMock->expects($this->once())->method('resetFailedAttempts');

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verify', [$userMock, '012345', ''])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::verify
     * @covers \D3\Totp\Application\Model\d3totp::getClock
     */
    public function verifyBackupCodePass()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('verify')->willReturn(true);

        $otpMock = TOTP::createFromSecret('abc');

        $userMock = $this->getUserFixture();

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getTotp',
                'd3GetBackupCodeListObject',
            ])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($otpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verify', [$userMock, '012345', 'backupCode'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::verify
     * @covers \D3\Totp\Application\Model\d3totp::getClock
     */
    public function verifyFailed()
    {
        $this->expectException(wrongOtpException::class);

        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('verify')->willReturn(false);

        $otpMock = TOTP::createFromSecret('abc');

        $userMock = $this->getUserFixture();

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getTotp',
                'd3GetBackupCodeListObject',
            ])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($otpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'verify', [$userMock, '012345', 'backupCode']);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::verify
     * @covers \D3\Totp\Application\Model\d3totp::getClock
     */
    public function verifyWithSeedFailed()
    {
        $this->expectException(wrongOtpException::class);

        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oBackupCodeListMock->expects($this->never())->method('verify')->willReturn(false);

        $otpMock = TOTP::createFromSecret('abc');

        $userMock = $this->getUserFixture();

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getTotp',
                'd3GetBackupCodeListObject',
            ])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($otpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'verify', [$userMock, '012345', '', 'abcdef']);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::d3GetBackupCodeListObject
     */
    public function d3GetBackupCodeListObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3backupcodelist::class,
            $this->callMethod($this->_oModel, 'd3GetBackupCodeListObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::encrypt
     */
    public function encryptDecryptPass()
    {
        $sReturn = $this->callMethod($this->_oModel, 'encrypt', ['foobar']);

        // indirect tests, because string changes on every call
        $this->assertIsString($sReturn);
        $this->assertNotSame('foobar', $sReturn);
        $this->assertStringEndsWith('==', $sReturn);
        $this->assertTrue(strlen($sReturn) === 88);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::decrypt
     * @covers \D3\Totp\Application\Model\d3totp::decryptWithKey
     */
    public function decryptMasterKeyPass()
    {
        $encrypted = $this->callMethod($this->_oModel, 'encrypt', ['foobar']);

        $sReturn = $this->callMethod(
            $this->_oModel,
            'decrypt',
            [$encrypted]
        );

        $this->assertSame('foobar', $sReturn);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::decrypt
     * @covers \D3\Totp\Application\Model\d3totp::decryptWithKey
     */
    public function decryptLegacyKeyPass()
    {
        $sut = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['save'])
            ->getMock();
        $sut->expects($this->once())->method('save');

        $encrypted = "+shEHpneNIIyxif+WP1L62vDNWJDx34XFB5PVzicAxvB42Lln2BAyXtllrJ3RO6UEB+XDaasVpPEsii8C6JDcA==";

        $sReturn = $this->callMethod(
            $sut,
            'decrypt',
            [$encrypted]
        );

        $this->assertSame('foobar', $sReturn);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::decrypt
     */
    public function decryptFailed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods(['d3Base64_decode'])
            ->getMock();
        $oModelMock->method('d3Base64_decode')->willReturn(
            str_pad('foobar', 50, '0', STR_PAD_LEFT)
        );

        $this->_oModel = $oModelMock;

        $sReturn = $this->callMethod(
            $this->_oModel,
            'decrypt',
            ['L5cSqld/1jpoSHnbxF1/+lGqN8OM7FWt2CagEkqNeRMvkyogrl0msvSuOpLwDwngvSa80bfDnfwWrPe5c6pdww==']
        );

        $this->assertFalse($sReturn);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::d3Base64_decode
     */
    public function d3Base64_decodePass()
    {
        $this->assertSame(
            'foobar',
            $this->callMethod($this->_oModel, 'd3Base64_decode', [base64_encode('foobar')])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::delete
     */
    public function deletePass()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->d3getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['deleteAllFromUser'])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('deleteAllFromUser');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'd3GetBackupCodeListObject',
                'getFieldData',
            ])
            ->getMock();
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);
        $oModelMock->method('getFieldData')->willReturn('newId');

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'delete')
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getQueryBuilder
     */
    public function canGetQueryBuilder(): void
    {
        $this->assertInstanceOf(
            QueryBuilder::class,
            $this->callMethod(
                $this->_oModel,
                'getQueryBuilder'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3totp::getDbConnection
     */
    public function canGetDbConnection(): void
    {
        $this->assertInstanceOf(
            Connection::class,
            $this->callMethod(
                $this->_oModel,
                'getDbConnection'
            )
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testAssertReplayProtection(): void
    {
        $this->_oModel->assign([
            'lastacceptedtimeslice' => 375
        ]);

        $this->expectException(replayException::class);

        $this->callMethod(
            $this->_oModel,
            'assertReplayProtection',
            [375]
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testAssertNotLocked(): void
    {
        $this->_oModel->assign([
            'lockeduntil' => date(
                'Y-m-d H:i:s',
                time() + 50
            )
        ]);

        $this->expectException(tooManyAttemptsException::class);

        $this->callMethod(
            $this->_oModel,
            'assertNotLocked'
        );
    }

    /**
     * @param string|null $id
     * @param int         $invocationCount
     *
     * @return void
     * @throws ReflectionException
     * @dataProvider testResetFailedAttemptsDataProvider
     */
    public function testResetFailedAttempts(?string $id, int $invocationCount): void
    {
        $sut = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['getId', 'save'])
            ->getMock();
        $sut->method('getId')->willReturn($id);
        $sut->expects($this->exactly($invocationCount))->method('save');
        $time = time();
        $sut->assign([
            'failedattempts' => 3,
            'lockeduntil' => $time,
        ]);

        $this->callMethod(
            $sut,
            'resetFailedAttempts'
        );

        $this->assertSame($id ? 0 : 3, $sut->getFieldData('failedattempts'));
        $this->assertSame($id ? null : $time, $sut->getFieldData('lockeduntil'));
    }

    public static function testResetFailedAttemptsDataProvider(): Generator
    {
        yield 'has id'  => ['myId', 1];
        yield 'has no id'  => [null, 0];
    }

    /**
     * @param string|null $id
     * @param int         $invocationCount
     *
     * @return void
     * @throws ReflectionException
     * @dataProvider testResetFailedAttemptsDataProvider
     */
    public function testRegisterFailedAttempt(?string $id, int $invocationCount): void
    {
        $sut = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['getId', 'save'])
            ->getMock();
        $sut->method('getId')->willReturn($id);
        $sut->assign([
            'failedattempts'    => 6,
            'lockeduntil'       => time() - 1
        ]);
        $sut->expects($this->exactly($invocationCount))->method('save');

        $this->callMethod(
            $sut,
            'registerFailedAttempt'
        );

        if ($id === null) {
            $this->assertSame(6, $sut->getFieldData('failedattempts'));
            $this->assertLessThan(time(), $sut->getFieldData('lockeduntil'));
        } else {
            $this->assertSame(7, $sut->getFieldData('failedattempts'));
            $this->assertGreaterThanOrEqual(time(), $sut->getFieldData('lockeduntil'));
        }
    }
}
