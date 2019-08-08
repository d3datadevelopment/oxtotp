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

namespace D3\Totp\tests\unit\Application\Controller;

use D3\Totp\Application\Controller\Admin\d3user_totp;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use Exception;
use OxidEsales\Eshop\Application\Model\User;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3user_totpTest extends d3TotpUnitTestCase
{
    /** @var d3user_totp */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oController = oxNew(d3user_totp::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canRenderNoSelectedUser()
    {
        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getEditObjectId',
            'getUserObject'
        ));
        $oControllerMock->method('getEditObjectId')->willReturn('-1');
        $oControllerMock->expects($this->never())->method('getUserObject')->willReturn(false);

        $this->_oController = $oControllerMock;

        $sTpl = $this->callMethod($this->_oController, 'render');
        $tplUser = $this->callMethod($this->_oController, 'getViewDataElement', array('edit'));

        $this->assertSame('d3user_totp.tpl', $sTpl);
        $this->assertSame($tplUser, null);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canRenderSelectedUser()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId',
            'load',
        ));
        $oUserMock->expects($this->atLeast(1))->method('getId')->willReturn('foobar');
        $oUserMock->expects($this->atLeast(1))->method('load')->willReturn(true);

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getEditObjectId',
            'getUserObject'
        ));
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->expects($this->once())->method('getUserObject')->willReturn($oUserMock);

        $this->_oController = $oControllerMock;

        $sTpl = $this->callMethod($this->_oController, 'render');
        $tplUser = $this->callMethod($this->_oController, 'getViewDataElement', array('edit'));
        $oxid = $this->callMethod($this->_oController, 'getViewDataElement', array('oxid'));

        $this->assertSame('d3user_totp.tpl', $sTpl);
        $this->assertSame($tplUser, $oUserMock);
        $this->assertSame($oxid, 'foobar');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getUserObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oController, 'getUserObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getTotpObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oController, 'getTotpObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBackupCodeListObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3backupcodelist::class,
            $this->callMethod($this->_oController, 'getBackupCodeListObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function cantSaveBecauseOfWrongPassword()
    {
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'save',
        ));
        $oBackupCodeListMock->expects($this->never())->method('save')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'save',
        ));
        $oTotpMock->expects($this->never())->method('save')->willReturn(true);

        /** @var User|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oUserMock = $this->getMock(User::class, array(
            'load',
            'isSamePassword',
        ));
        $oUserMock->expects($this->atLeast(1))->method('load')->willReturn(true);
        $oUserMock->expects($this->atLeast(1))->method('isSamePassword')->willReturn(false);

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getEditObjectId',
            'getUserObject',
            'getTotpObject',
            'getBackupcodeListObject'
        ));
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->expects($this->once())->method('getUserObject')->willReturn($oUserMock);
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getBackupcodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function cantSaveBecauseOfNotVerifiable()
    {
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'save',
        ));
        $oBackupCodeListMock->expects($this->never())->method('save')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'load',
            'save',
            'verify',
            'saveSecret',
            'assign'
        ));
        $oTotpMock->method('load')->willReturn(true);
        $oTotpMock->expects($this->never())->method('save')->willReturn(true);
        $oTotpMock->expects($this->once())->method('verify')->willThrowException(new Exception());
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);

        /** @var User|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oUserMock = $this->getMock(User::class, array(
            'load',
            'isSamePassword',
        ));
        $oUserMock->expects($this->once())->method('load')->willReturn(true);
        $oUserMock->expects($this->once())->method('isSamePassword')->willReturn(true);

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getEditObjectId',
            'getUserObject',
            'getTotpObject',
            'getBackupcodeListObject'
        ));
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->expects($this->once())->method('getUserObject')->willReturn($oUserMock);
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getBackupcodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canSave()
    {
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'save',
            'generateBackupCodes'
        ));
        $oBackupCodeListMock->expects($this->once())->method('save')->willReturn(true);
        $oBackupCodeListMock->method('generateBackupCodes')->willReturn(true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'load',
            'save',
            'verify',
            'saveSecret',
            'assign'
        ));
        $oTotpMock->method('load')->willReturn(true);
        $oTotpMock->expects($this->once())->method('save')->willReturn(true);
        $oTotpMock->expects($this->once())->method('verify')->willReturn(true);
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);

        /** @var User|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oUserMock = $this->getMock(User::class, array(
            'load',
            'isSamePassword',
        ));
        $oUserMock->expects($this->atLeast(1))->method('load')->willReturn(true);
        $oUserMock->expects($this->atLeast(1))->method('isSamePassword')->willReturn(true);

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getEditObjectId',
            'getUserObject',
            'getTotpObject',
            'getBackupcodeListObject'
        ));
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->expects($this->once())->method('getUserObject')->willReturn($oUserMock);
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getBackupcodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canSetAndGetBackupCodes()
    {
        $aBackupList = [
            'foo1',
            'bar2'
        ];

        $this->callMethod($this->_oController, 'setBackupCodes', array($aBackupList));

        $aReturn = $this->callMethod($this->_oController, 'getBackupCodes');

        $this->assertSame('foo1'.PHP_EOL.'bar2', $aReturn);
    }

    /**
     * @te__st
     * @throws ReflectionException
     */
    public function cantDeleteIfNotSetOxid()
    {
        $editval = [];
        $_GET['editval'] = $editval;

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'delete'
        ));
        $oTotpMock->expects($this->never())->method('delete');

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getTotpObject'
        ));
        $oControllerMock->expects($this->never())->method('getTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'delete');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canDelete()
    {
        $editval = [
            'd3totp__oxid' => 'foo'
        ];
        $_GET['editval'] = $editval;

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'delete'
        ));
        $oTotpMock->expects($this->once())->method('delete')->willReturn(true);

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getTotpObject'
        ));
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'delete');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canGetAvailableBackupCodeCount()
    {
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMock(d3backupcodelist::class, array(
            'getAvailableCodeCount',
        ));
        $oBackupCodeListMock->method('getAvailableCodeCount')->willReturn(25);

        $oUser = oxNew(User::class);
        $oUser->setId('foo');

        /** @var d3user_totp|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3user_totp::class, array(
            'getBackupCodeListObject',
            'getUser'
        ));
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodeListMock);
        $oControllerMock->method('getUser')->willReturn($oUser);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            25,
            $this->callMethod($this->_oController, 'getAvailableBackupCodeCount')
        );
    }
}