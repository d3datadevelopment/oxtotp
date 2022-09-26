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
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3totpTest extends d3TotpUnitTestCase
{
    /** @var d3totp */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oModel = oxNew(d3totp::class);
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
    public function constructCallsInit()
    {
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'init',
        ));
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
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne'
        ), array(), '', false);
        $oDbMock->expects($this->once())->method('getOne')->willReturnOnConsecutiveCalls(false, true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetDb',
            'load'
        ));
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
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne',
            'quote'
        ), array(), '', false);
        $oDbMock->expects($this->exactly(2))->method('getOne')->willReturnOnConsecutiveCalls(true, true);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetDb',
            'load'
        ));
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
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'load'
        ));
        $oUserMock->method('load')->with('foobar')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetUser',
            'getFieldData',
        ));
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

        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'load'
        ));
        $oUserMock->method('load')->with('barfoo')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetUser',
            'getFieldData',
        ));
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
        /** @var Database|PHPUnit_Framework_MockObject_MockObject $oDbMock */
        $oDbMock = $this->getMock(Database::class, array(
            'getOne',
            'quote'
        ), array(), '', false);
        $oDbMock->expects($this->once())->method('getOne')->willReturn(1);
        $oDbMock->method('quote')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetDb'
        ));
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'UserUseTotp',
        ));
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'UserUseTotp',
        ));
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'UserUseTotp',
        ));
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'UserUseTotp',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
            'decrypt',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
            'decrypt',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getFieldData',
            'decrypt',
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(), array(), '', false);

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
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getFieldData',
        ));
        $oUserMock->method('getFieldData')->willReturn('username');

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getUser',
            'getSavedSecret'
        ));
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
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getFieldData',
        ));
        $oUserMock->method('getFieldData')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getUser',
            'getSavedSecret'
        ));
        $oModelMock->method('getUser')->willReturn($oUserMock);
        $oModelMock->method('getSavedSecret')->willReturn('savedSecret');

        $this->_oModel = $oModelMock;

        /** @var TOTP $oTotp */
        $oTotp = $this->callMethod($this->_oModel, 'getTotp', ['givenSeed']);

        $this->assertInstanceOf(TOTP::class, $oTotp);
        $this->assertSame(null, $oTotp->getLabel());
        $this->assertSame('GIVENSEED', $oTotp->getSecret());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getQrCodeUriPass()
    {
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'getQrCodeUri'
        ));
        $oTotpMock->expects($this->once())->method('getQrCodeUri')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getTotp'
        ));
        $oModelMock->method('getTotp')->willReturn($oTotpMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'getQrCodeUri');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getQrCodeElement()
    {
        $renderer = BaconQrCodeFactory::renderer(200);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'getProvisioningUri'
        ));
        $oTotpMock->method('getProvisioningUri')->willReturn(true);

        /** @var Writer|PHPUnit_Framework_MockObject_MockObject $oWriterMock */
        $oWriterMock = $this->getMock(Writer::class, array(
            'writeString'
        ), array($renderer));
        $oWriterMock->expects($this->once())->method('writeString')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetWriter',
            'getTotp'
        ));
        $oModelMock->method('d3GetWriter')->willReturn($oWriterMock);
        $oModelMock->method('getTotp')->willReturn($oTotpMock);

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'getQrCodeElement');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetWriterReturnsRightInstance()
    {
        $renderer = BaconQrCodeFactory::renderer(200);;

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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'getSecret'
        ));
        $oTotpMock->expects($this->once())->method('getSecret')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getTotp'
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'encrypt'
        ));
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify'
        ));
        $oTotpMock->expects($this->once())->method('verify')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getTotp'
        ));
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
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'verify'
        ));
        $oBackupCodeListMock->expects($this->once())->method('verify')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify'
        ));
        $oTotpMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getTotp',
            'd3GetBackupCodeListObject'
        ));
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
        $this->setExpectedException(d3totp_wrongOtpException::class);

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'verify'
        ));
        $oBackupCodeListMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify'
        ));
        $oTotpMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getTotp',
            'd3GetBackupCodeListObject'
        ));
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
        $this->setExpectedException(d3totp_wrongOtpException::class);

        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'verify'
        ));
        $oBackupCodeListMock->expects($this->never())->method('verify')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify'
        ));
        $oTotpMock->expects($this->once())->method('verify')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'getTotp',
            'd3GetBackupCodeListObject'
        ));
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
        $this->assertInternalType('string', $sReturn);
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
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3Base64_decode',
        ));
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
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'deleteAllFromUser'
        ));
        $oBackupCodeListMock->expects($this->once())->method('deleteAllFromUser')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3totp::class, array(
            'd3GetBackupCodeListObject',
            'getFieldData',
            'canDelete'
        ));
        $oModelMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);
        $oModelMock->method('getFieldData')->willReturn('newId');
        $oModelMock->method('canDelete')->willReturn(false);

        $this->_oModel = $oModelMock;

        $this->assertFalse(
            $this->callMethod($this->_oModel, 'delete')
        );
    }
}
