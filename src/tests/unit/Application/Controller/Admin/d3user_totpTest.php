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

namespace D3\Totp\tests\unit\Application\Controller\Admin;

use D3\Totp\Application\Controller\Admin\d3user_totp;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use Exception;
use OxidEsales\Eshop\Application\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3user_totpTest extends d3TotpUnitTestCase
{
    /** @var d3user_totp */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3user_totp::class);
    }

    public function tearDown(): void
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
        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject'
            ])
            ->getMock();
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
        /** @var User|MockObject $oControllerMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods([
                'getId',
                'load',
            ])
            ->getMock();
        $oUserMock->expects($this->atLeast(1))->method('getId')->willReturn('foobar');
        $oUserMock->expects($this->atLeast(1))->method('load')->willReturn(true);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject'
            ])
            ->getMock();
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
    public function canRenderUnloadableUser()
    {
        /** @var User|MockObject $oControllerMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods([
                'getId',
                'load',
            ])
            ->getMock();
        $oUserMock->expects($this->never())->method('getId');
        $oUserMock->expects($this->atLeast(1))->method('load')->willReturn(false);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject',
                'addTplParam'
            ])
            ->getMock();
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->expects($this->once())->method('getUserObject')->willReturn($oUserMock);
        $oControllerMock->expects($this->exactly(3))->method('addTplParam')->with(
            $this->logicalOr(
                $this->stringContains('sSaveError'),
                $this->stringContains('oxid'),
                $this->stringContains('edit')
            )
        );

        $this->_oController = $oControllerMock;

        $this->setValue($this->_oController, '_sSaveError', 'foo');

        $sTpl = $this->callMethod($this->_oController, 'render');
        $tplUser = $this->callMethod($this->_oController, 'getViewDataElement', array('edit'));
        $oxid = $this->callMethod($this->_oController, 'getViewDataElement', array('oxid'));

        $this->assertSame('d3user_totp.tpl', $sTpl);
        $this->assertNull($tplUser);
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
            $this->callMethod(
                $this->_oController,
                'getUserObject'
            )
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
            $this->callMethod(
                $this->_oController,
                'getTotpObject'
            )
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
            $this->callMethod(
                $this->_oController,
                'getBackupCodeListObject'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function cantSaveBecauseOfNotVerifiable()
    {
        /** @var d3backupcodelist|MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['save'])
            ->getMock();
        $oBackupCodeListMock->expects($this->never())->method('save')->willReturn(true);

        /** @var d3totp|MockObject $oControllerMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'load',
                'save',
                'verify',
                'saveSecret',
                'assign',
                'checkIfAlreadyExist'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('load')->willReturn(true);
        $oTotpMock->expects($this->never())->method('save')->willReturn(true);
        $oTotpMock->expects($this->once())->method('verify')->willThrowException(new Exception());
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);
        $oTotpMock->method('checkIfAlreadyExist')->willReturn(false);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject',
                'getTotpObject',
                'getBackupcodeListObject'
            ])
            ->getMock();
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getBackupcodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function cantSaveBecauseExistingRegistration()
    {
        /** @var d3backupcodelist|MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['save'])
            ->getMock();
        $oBackupCodeListMock->expects($this->never())->method('save')->willReturn(true);

        /** @var d3totp|MockObject $oControllerMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'load',
                'save',
                'verify',
                'saveSecret',
                'assign',
                'checkIfAlreadyExist'
            ])
            ->getMock();
        $oTotpMock->method('load')->willReturn(true);
        $oTotpMock->expects($this->never())->method('save')->willReturn(true);
        $oTotpMock->expects($this->never())->method('verify')->willThrowException(new Exception());
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);
        $oTotpMock->method('checkIfAlreadyExist')->willReturn(true);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject',
                'getTotpObject',
                'getBackupcodeListObject'
            ])
            ->getMock();
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
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
        /** @var d3backupcodelist|MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'save',
                'generateBackupCodes'
            ])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('save')->willReturn(true);
        $oBackupCodeListMock->method('generateBackupCodes')->willReturn(true);

        /** @var d3totp|MockObject $oControllerMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'load',
                'save',
                'verify',
                'saveSecret',
                'assign',
                'checkIfAlreadyExist'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->never())->method('load')->willReturn(true);
        $oTotpMock->expects($this->once())->method('save')->willReturn(true);
        $oTotpMock->expects($this->once())->method('verify')->willReturn(true);
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);
        $oTotpMock->method('checkIfAlreadyExist')->willReturn(false);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject',
                'getTotpObject',
                'getBackupcodeListObject'
            ])
            ->getMock();
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getBackupcodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'save');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canSaveWithKnownOXID()
    {
        $aEditval = [
            'd3totp__oxid'  => 'foo'
        ];
        $_GET['editval'] = $aEditval;

        /** @var d3backupcodelist|MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'save',
                'generateBackupCodes'
            ])
            ->getMock();
        $oBackupCodeListMock->expects($this->once())->method('save')->willReturn(true);
        $oBackupCodeListMock->method('generateBackupCodes')->willReturn(true);

        /** @var d3totp|MockObject $oControllerMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'load',
                'save',
                'verify',
                'saveSecret',
                'assign',
                'checkIfAlreadyExist'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->once())->method('load')->willReturn(true);
        $oTotpMock->expects($this->once())->method('save')->willReturn(true);
        $oTotpMock->expects($this->never())->method('verify')->willReturn(true);
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);
        $oTotpMock->method('checkIfAlreadyExist')->willReturn(false);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getEditObjectId',
                'getUserObject',
                'getTotpObject',
                'getBackupcodeListObject'
            ])
            ->getMock();
        $oControllerMock->method('getEditObjectId')->willReturn('foobar');
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

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])
            ->getMock();
        $oTotpMock->expects($this->never())->method('delete');

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods(['getTotpObject'])
            ->getMock();
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

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'delete',
                'load'
            ])
            ->getMock();
        $oTotpMock->expects($this->once())->method('delete')->willReturn(true);
        $oTotpMock->method('load')->willReturn(true);

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods(['getTotpObject'])
            ->getMock();
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
        /** @var d3backupcodelist|MockObject $oControllerMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodeListMock->method('getAvailableCodeCount')->willReturn(25);

        $oUser = oxNew(User::class);
        $oUser->setId('foo');

        /** @var d3user_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3user_totp::class)
            ->onlyMethods([
                'getBackupCodeListObject',
                'getUser'
            ])
            ->getMock();
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodeListMock);
        $oControllerMock->method('getUser')->willReturn($oUser);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            25,
            $this->callMethod($this->_oController, 'getAvailableBackupCodeCount')
        );
    }
}