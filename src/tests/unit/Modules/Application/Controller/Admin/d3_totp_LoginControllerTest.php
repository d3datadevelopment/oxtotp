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

namespace D3\Totp\tests\unit\Modules\Application\Controller\Admin;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_LoginControllerTest extends d3TotpUnitTestCase
{
    /** @var d3_totp_LoginController */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3_totp_LoginController::class);
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
    public function canRenderNoAuth()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->never())->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'getVariable',
                'setVariable'
            ])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls(false, true));
        $oSessionMock->expects($this->never())->method('setVariable')->willReturn(false);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetSession',
                'd3GetTotpObject'
            ])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame('login.tpl', $this->callMethod($this->_oController, 'render'));
        $this->assertNotTrue($this->callMethod($this->_oController, 'getViewDataElement', array('request_totp')));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canRenderTotpNotActive()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isActive',
                'loadByUserId'
            ])
            ->getMock();
        $oTotpMock->expects($this->once())->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'getVariable',
                'setVariable',
            ])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls(true, true));
        $oSessionMock->expects($this->never())->method('setVariable')->willReturn(false);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetSession',
                'd3GetTotpObject'
            ])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame('login.tpl', $this->callMethod($this->_oController, 'render'));
        $this->assertNotTrue($this->callMethod($this->_oController, 'getViewDataElement', array('request_totp')));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canRenderInTotpLoginProcess()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->once())->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'getVariable',
                'setVariable',
            ])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls(true, true));
        $oSessionMock->expects($this->never())->method('setVariable')->willReturn(false);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetSession',
                'd3GetTotpObject'
            ])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame('login.tpl', $this->callMethod($this->_oController, 'render'));
        $this->assertNotTrue($this->callMethod($this->_oController, 'getViewDataElement', array('request_totp')));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canRenderRequestTotp()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->once())->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'getVariable',
                'setVariable',
            ])
            ->getMock();
        $oSessionMock->method('getVariable')->will($this->onConsecutiveCalls(true, false));
        $oSessionMock->expects($this->once())->method('setVariable')->willReturn(false);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetSession',
                'd3GetTotpObject'
            ])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame('login.tpl', $this->callMethod($this->_oController, 'render'));
        $this->assertTrue($this->callMethod($this->_oController, 'getViewDataElement', array('request_totp')));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetTotpObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oController, 'd3GetTotpObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetBackupCodeListObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            d3backupcodelist::class,
            $this->callMethod($this->_oController, 'd3GetBackupCodeListObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetUtilsViewReturnsRightObject()
    {
        $this->assertInstanceOf(
            UtilsView::class,
            $this->callMethod($this->_oController, 'd3GetUtilsView')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetSessionReturnsRightObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oController, 'd3GetSession')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkloginNoTotp()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'isNoTotpOrNoLogin',
                'hasValidTotp',
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('isNoTotpOrNoLogin')->willReturn(true);
        $oControllerMock->method('hasValidTotp')->willReturn(false);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'login',
            $this->callMethod(
                $this->_oController,
                'checklogin'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkloginInvalidTotp()
    {
        /** @var d3totp_wrongOtpException|MockObject $oUtilsViewMock */
        $oTotpExceptionMock = $this->getMockBuilder(d3totp_wrongOtpException::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->once())->method('addErrorToDisplay')->willReturn(true);
        
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'isNoTotpOrNoLogin',
                'hasValidTotp',
                'd3GetUtilsView'
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('isNoTotpOrNoLogin')->willReturn(false);
        $oControllerMock->method('hasValidTotp')->willThrowException($oTotpExceptionMock);
        $oControllerMock->method('d3GetUtilsView')->willReturn($utilsViewMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'login',
            $this->callMethod($this->_oController, 'checklogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function checkloginValidTotp()
    {
        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->never())->method('addErrorToDisplay')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['loadByUserId'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['setVariable'])
            ->getMock();
        $oSessionMock->expects($this->once())->method('setVariable')->willReturn(false);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'isNoTotpOrNoLogin',
                'hasValidTotp',
                'd3GetUtilsView',
                'd3GetSession'
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('isNoTotpOrNoLogin')->willReturn(false);
        $oControllerMock->method('hasValidTotp')->willReturn(true);
        $oControllerMock->method('d3GetUtilsView')->willReturn($utilsViewMock);
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertSame('admin_start', $this->callMethod($this->_oController, 'checklogin'));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBackupCodeCountMessageShowMessage()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodeListMock->method('getAvailableCodeCount')->willReturn(2);
        
        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3GetBackupCodeListObject'])
            ->getMock();
        $oControllerMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->assertGreaterThan(
            0,
            strpos(
                $this->callMethod($this->_oController, 'getBackupCodeCountMessage'),
                ' 2 '
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBackupCodeCountMessageDontShowMessage()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodeListMock->method('getAvailableCodeCount')->willReturn(10);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3GetBackupCodeListObject'])
            ->getMock();
        $oControllerMock->method('d3GetBackupCodeListObject')->willReturn($oBackupCodeListMock);

        $this->_oController = $oControllerMock;

        $this->assertEmpty(
            $this->callMethod($this->_oController, 'getBackupCodeCountMessage')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function isNoTotpOrNoLoginIsAuth()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3GetSession'])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'isNoTotpOrNoLogin', array($oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function isNoTotpOrNoLoginTotpNotActive()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3GetSession'])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'isNoTotpOrNoLogin', array($oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function isNoTotpOrNoLoginPass()
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(false);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(false);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3GetSession'])
            ->getMock();
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertTrue(
            $this->callMethod($this->_oController, 'isNoTotpOrNoLogin', array($oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function hasValidTotpTrueSessionVarname()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(false);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'hasValidTotp', array('123456', $oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function hasValidTotpTrueValidTotp()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(true);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'hasValidTotp', array('123456', $oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function hasValidTotpFalseMissingTotp()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(true);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'hasValidTotp', array(null, $oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function hasValidTotpFalseUnverifiedTotp()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(false);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'hasValidTotp', array('123456', $oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3CancelLoginPass()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['logout'])
            ->getMock();
        $oUserMock->expects($this->once())->method('logout')->willReturn(true);
        
        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3GetUserObject'])
            ->getMock();
        $oControllerMock->method('d3GetUserObject')->willReturn($oUserMock);

        $this->_oController = $oControllerMock;
        
        $this->callMethod($this->_oController, 'd3CancelLogin');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetUserObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oController, 'd3GetUserObject')
        );
    }
}