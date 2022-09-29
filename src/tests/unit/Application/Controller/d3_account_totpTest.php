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

namespace D3\Totp\tests\unit\Application\Controller;

use D3\Totp\Application\Controller\d3_account_totp;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use Exception;
use OxidEsales\Eshop\Application\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_account_totpTest extends d3TotpUnitTestCase
{
    /** @var d3_account_totp */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3_account_totp::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::render
     * @covers \D3\Totp\Application\Controller\d3_account_totp::getViewDataElement
     */
    public function renderReturnsDefaultTemplate()
    {
        $oUser = oxNew(User::class);
        $oUser->setId('foo');
        $oUser->assign(
            [
                'oxpassword'    => 'foo'
            ]
        );

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods(['getUser'])
            ->getMock();
        $oControllerMock->method('getUser')->willReturn($oUser);

        $this->_oController = $oControllerMock;

        $sTpl = $this->callMethod($this->_oController, 'render');
        $tplUser = $this->callMethod($this->_oController, 'getViewDataElement', array('user'));

        $this->assertSame('d3_account_totp.tpl', $sTpl);
        $this->assertSame($tplUser, $oUser);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::render
     * @covers \D3\Totp\Application\Controller\d3_account_totp::getViewDataElement
     */
    public function renderReturnsLoginTemplateIfNotLoggedIn()
    {
        $oUser = false;

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods(['getUser'])
            ->getMock();
        $oControllerMock->method('getUser')->willReturn($oUser);

        $this->_oController = $oControllerMock;

        $sTpl = $this->callMethod($this->_oController, 'render');
        $tplUser = $this->callMethod($this->_oController, 'getViewDataElement', array('user'));

        $this->assertSame('page/account/login.tpl', $sTpl);
        $this->assertNull($tplUser);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::getBackupCodes
     * @covers \D3\Totp\Application\Controller\d3_account_totp::setBackupCodes
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
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::getBackupCodeListObject
     */
    public function canGetBackupCodeListReturnsRightInstance()
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
     * @covers \D3\Totp\Application\Controller\d3_account_totp::getAvailableBackupCodeCount
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

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
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

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::create
     */
    public function cantCreateIfTotpNotActive()
    {
        $_GET['totp_use'] = 0;

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods(['getTotpObject'])
            ->getMock();
        $oControllerMock->expects($this->never())->method('getTotpObject')->willReturn(true);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'create');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::create
     */
    public function cantCreateIfTotpNotVerfiable()
    {
        $_GET['totp_use'] = '1';

        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'generateBackupCodes',
                'save'
            ])
            ->getMock();
        $oBackupCodeListMock->expects($this->never())->method('generateBackupCodes');
        $oBackupCodeListMock->expects($this->never())->method('save');

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'saveSecret',
                'assign',
                'verify',
                'save'
            ])
            ->getMock();
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);
        $oTotpMock->expects($this->once())->method('verify')->willThrowException(new Exception('foo'));
        $oTotpMock->expects($this->never())->method('save');

        $oUser = oxNew(User::class);
        $oUser->setId('foo');

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods([
                'getTotpObject',
                'getUser',
                'getBackupCodeListObject'
            ])
            ->getMock();
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getUser')->willReturn($oUser);
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'create');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::create
     */
    public function canCreate()
    {
        $_GET['totp_use'] = '1';

        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods([
                'generateBackupCodes',
                'save'
            ])
            ->getMock();
        $oBackupCodeListMock->method('generateBackupCodes')->willReturn(['0123', '1234']);
        $oBackupCodeListMock->expects($this->once())->method('save')->willReturn(true);
        $oBackupCodeListMock->method('save')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'saveSecret',
                'assign',
                'verify',
                'save',
                'setId',
            ])
            ->getMock();
        $oTotpMock->method('saveSecret')->willReturn(true);
        $oTotpMock->method('assign')->willReturn(true);
        $oTotpMock->method('verify')->willReturn(true);
        $oTotpMock->method('setId')->willReturn(true);
        $oTotpMock->expects($this->once())->method('save')->willReturn(true);

        $oUser = oxNew(User::class);
        $oUser->setId('foo');

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods([
                'getTotpObject',
                'getUser',
                'getBackupCodeListObject'
            ])
            ->getMock();
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getUser')->willReturn($oUser);
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'create');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::delete
     */
    public function cantDeleteIfTotpActive()
    {
        $_GET['totp_use'] = '1';

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods(['getTotpObject'])
            ->getMock();
        $oControllerMock->expects($this->never())->method('getTotpObject')->willReturn(true);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'delete');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::delete
     */
    public function cantDeleteIfNoUser()
    {
        $_GET['totp_use'] = '0';

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])
            ->getMock();
        $oTotpMock->expects($this->never())->method('delete');

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods([
                'getTotpObject',
                'getUser'
            ])
            ->getMock();
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->expects($this->once())->method('getUser')->willReturn(false);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'delete');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::delete
     */
    public function canDelete()
    {
        $_GET['totp_use'] = '0';

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'delete',
                'loadByUserId'
            ])
            ->getMock();
        $oTotpMock->expects($this->once())->method('delete')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        $oUser = oxNew(User::class);
        $oUser->setId('foo');

        /** @var d3_account_totp|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_account_totp::class)
            ->onlyMethods([
                'getTotpObject',
                'getUser'
            ])
            ->getMock();
        $oControllerMock->method('getTotpObject')->willReturn($oTotpMock);
        $oControllerMock->expects($this->once())->method('getUser')->willReturn($oUser);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'delete');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3_account_totp::getTotpObject
     */
    public function getTotpObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod(
                $this->_oController,
                'getTotpObject'
            )
        );
    }
}