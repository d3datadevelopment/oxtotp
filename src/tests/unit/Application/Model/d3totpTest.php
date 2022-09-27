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

use BaconQrCode\Writer;
use D3\Totp\Application\Factory\BaconQrCodeFactory;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OTPHP\TOTP;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use stdClass;

class d3totpTest extends d3TotpUnitTestCase
{
    /** @var d3totp */
    protected $_oModel;

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

    /**
     * @test
     * @throws ReflectionException
     */
    public function constructCallsInit()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['init'])
            ->getMock();
        $oModelMock->expects($this->once())->method('init');

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, '__construct');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function loadByUserIdTableNotExist()
    {
        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOne'])
            ->getMock();
        $oDbMock->expects($this->once())->method('getOne')->willReturnOnConsecutiveCalls(false, true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'd3GetDb',
                'load'
            ])
            ->getMock();
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);
        $oModelMock->expects($this->never())->method('load')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'loadByUserId', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function loadByUserIdTableExist()
    {
        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getOne',
                'quote'
            ])->getMock();
        $oDbMock->expects($this->exactly(2))->method('getOne')->willReturnOnConsecutiveCalls(true, true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'd3GetDb',
                'load'
            ])
            ->getMock();
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);
        $oModelMock->expects($this->once())->method('load')->willReturn(true);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'loadByUserId', ['foobar']);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getUserFromMember()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['load'])
            ->getMock();
        $oUserMock->method('load')->with('foobar')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function getUserFromObject()
    {
        $this->setValue($this->_oModel, 'userId', null);

        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['load'])
            ->getMock();
        $oUserMock->method('load')->with('barfoo')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     * @throws ReflectionException
     */
    public function checkIfAlreadyExistPass()
    {
        /** @var Database|MockObject $oDbMock */
        $oDbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods([
                'getOne',
                'quote'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oDbMock->expects($this->once())->method('getOne')->willReturn(1);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['d3GetDb'])
            ->getMock();
        $oModelMock->method('d3GetDb')->willReturn($oDbMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'checkIfAlreadyExist', array('testUserId'))
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
     */
    public function isActivePass()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function isActiveFailedBecauseNoTotpUse()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function isActiveFailedBecauseConfigParam()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function isActiveFailedBecauseNoTotpUseAndConfigParam()
    {
        Registry::getConfig()->setConfigParam('blDisableTotpGlobally', true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function UserUseTotpPass()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function UserUseTotpNoTotp()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function UserUseTotpNoSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function UserUseTotpNoTotpAndNoSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
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
     */
    public function getSavedSecretExistingSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getFieldData',
                'decrypt'
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
     */
    public function getSavedSecretNoSeed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getFieldData',
                'decrypt'
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
     */
    public function getSavedSecretCantDecrypt()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getFieldData',
                'decrypt'
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
     */
    public function getTotpReturnsCachedObject()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setValue($this->_oModel, 'totp', $oTotpMock);

        $this->assertSame(
            $oTotpMock,
            $this->callMethod($this->_oModel, 'getTotp')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getTotpReturnsNewObject()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oUserMock->method('getFieldData')->willReturn('username');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getUser',
                'getSavedSecret'
            ])
            ->getMock();
        $oModelMock->method('getUser')->willReturn($oUserMock);
        $oModelMock->method('getSavedSecret')->willReturn('savedSecret');

        $this->_oModel = $oModelMock;

        /** @var TOTP $oTotp */
        $oTotp = $this->callMethod($this->_oModel, 'getTotp');

        $this->assertInstanceOf(TOTP::class, $oTotp);
        $this->assertSame('username', $oTotp->getLabel());
        $this->assertSame('SAVEDSECRET', $oTotp->getSecret());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getTotpReturnsNewObjectNoUserGivenSeed()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['getFieldData'])
            ->getMock();
        $oUserMock->method('getFieldData')->willReturnMap(
            [['oxusername', 'oxusername']]
        );

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getUser',
                'getSavedSecret'
            ])
            ->getMock();
        $oModelMock->method('getUser')->willReturn($oUserMock);
        $oModelMock->method('getSavedSecret')->willReturn('savedSecret');

        $this->_oModel = $oModelMock;

        /** @var TOTP $oTotp */
        $oTotp = $this->callMethod($this->_oModel, 'getTotp', ['givenSeed']);

        $this->assertInstanceOf(TOTP::class, $oTotp);
        $this->assertSame('oxusername', $oTotp->getLabel());
        $this->assertSame('GIVENSEED', $oTotp->getSecret());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getQrCodeElement()
    {
        $renderer = BaconQrCodeFactory::renderer(200);

        /** @var stdClass|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['getProvisioningUri'])
            ->getMock();
        $oTotpMock->method('getProvisioningUri')->willReturn('uri');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['getTotp'])
            ->disableOriginalConstructor()
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($oTotpMock);

        $this->_oModel = $oModelMock;

        $this->assertIsString(
            $this->callMethod($this->_oModel, 'getQrCodeElement')
        );
    }

    /**
     * @test
     * @throws ReflectionException
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
     */
    public function getSecretPass()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['getSecret'])
            ->getMock();
        $oTotpMock->expects($this->once())->method('getSecret')->willReturn('fixture');

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['getTotp'])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($oTotpMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'getSecret');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function saveSecretPass()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['encrypt'])
            ->getMock();
        $oModelMock->method('encrypt')->willReturn('enc_secret');

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'saveSecret', ['newSecret']);
        $this->assertSame(
            'enc_secret',
            $this->callMethod($this->_oModel, 'getFieldData', ['seed'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function verifyPass()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oTotpMock->expects($this->once())->method('verify')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['getTotp'])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($oTotpMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verify', ['012345'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function verifyBackupCodePass()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('verify')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oTotpMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getTotp',
                'd3GetBackupCodeListObject'
            ])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($oTotpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod($this->_oModel, 'verify', ['012345'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function verifyFailed()
    {
        $this->expectException(d3totp_wrongOtpException::class);

        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oTotpMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getTotp',
                'd3GetBackupCodeListObject'
            ])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($oTotpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'verify', ['012345']);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function verifyWithSeedFailed()
    {
        $this->expectException(d3totp_wrongOtpException::class);

        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oBackupCodeListMock->expects($this->never())->method('verify')->willReturn(false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->getMock();
        $oTotpMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'getTotp',
                'd3GetBackupCodeListObject'
            ])
            ->getMock();
        $oModelMock->method('getTotp')->willReturn($oTotpMock);
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'verify', ['012345', 'abcdef']);
    }

    /**
     * @test
     * @throws ReflectionException
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
     */
    public function decryptPass()
    {
        $sReturn = $this->callMethod(
            $this->_oModel,
            'decrypt',
            ['L5cSqld/1jpoSHnbxF1/+lGqN8OM7FWt2CagEkqNeRMvkyogrl0msvSuOpLwDwngvSa80bfDnfwWrPe5c6pdww==']
        );

        $this->assertSame('foobar', $sReturn);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function decryptFailed()
    {
        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['d3Base64_decode'])
            ->getMock();
        $oModelMock->method('d3Base64_decode')->willReturn(
            str_pad('foobar', 16, 0, STR_PAD_LEFT)
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
     */
    public function deletePass()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['deleteAllFromUser'])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('deleteAllFromUser')->willReturn(true);

        /** @var d3totp|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'd3GetBackupCodeListObject',
                'getFieldData',
                'canDelete'
            ])
            ->getMock();
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);
        $oModelMock->method('getFieldData')->willReturn('newId');
        $oModelMock->method('canDelete')->willReturn(false);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'delete')
        );
    }
}
