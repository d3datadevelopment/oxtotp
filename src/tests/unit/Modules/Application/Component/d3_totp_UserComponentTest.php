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

namespace D3\Totp\tests\unit\Modules\Application\Component;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\Modules\Application\Component\d3_totp_UserComponent;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3_totp_UserComponentTest extends d3TotpUnitTestCase
{
    /** @var d3_totp_UserComponent */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oController = oxNew(UserComponent::class);

        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, false);
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
    public function login_noredirectFailsIfNoUserLoggedIn()
    {
        $oUser = false;

        /** @var BaseController|PHPUnit_Framework_MockObject_MockObject $oParentMock */
        $oParentMock = $this->getMock(BaseController::class, array(
            'isEnabledPrivateSales',
        ));
        $oParentMock->method('isEnabledPrivateSales')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
        ), array(), '', false);
        $oTotpMock->expects($this->never())->method('isActive')->willReturn(false);
        
        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'getUser',
            'd3GetTotpObject',
            'getParent'
        ));
        $oControllerMock->method('getUser')->willReturn($oUser);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getParent')->willReturn($oParentMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'login_noredirect');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function login_noredirectFailTotpNotActive()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'logout',
            'getId',
        ));
        $oUserMock->expects($this->never())->method('logout')->willReturn(false);
        $oUserMock->method('getId')->willReturn('foo');

        /** @var BaseController|PHPUnit_Framework_MockObject_MockObject $oParentMock */
        $oParentMock = $this->getMock(BaseController::class, array(
            'isEnabledPrivateSales',
        ));
        $oParentMock->method('isEnabledPrivateSales')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->expects($this->once())->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'getUser',
            'd3GetTotpObject',
            'getParent'
        ));
        $oControllerMock->method('getUser')->willReturn($oUserMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getParent')->willReturn($oParentMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'login_noredirect');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function login_noredirectPass()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'logout',
            'getId',
        ));
        $oUserMock->expects($this->once())->method('logout')->willReturn(false);
        $oUserMock->method('getId')->willReturn('foo');

        /** @var BaseController|PHPUnit_Framework_MockObject_MockObject $oParentMock */
        $oParentMock = $this->getMock(BaseController::class, array(
            'isEnabledPrivateSales',
        ));
        $oParentMock->method('isEnabledPrivateSales')->willReturn(false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->expects($this->once())->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'getUser',
            'd3GetTotpObject',
            'getParent'
        ));
        $oControllerMock->method('getUser')->willReturn($oUserMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getParent')->willReturn($oParentMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totplogin',
            $this->callMethod($this->_oController, 'login_noredirect')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetTotpObjectReturnsRightInstance()
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
    public  function checkTotploginNoTotpLogin()
    {
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'isNoTotpOrNoLogin',
            'hasValidTotp',
            'd3TotpRelogin',
            'd3GetTotpObject'
        ));
        $oControllerMock->method('isNoTotpOrNoLogin')->willReturn(true);
        $oControllerMock->expects($this->never())->method('hasValidTotp')->willReturn(false);
        $oControllerMock->expects($this->never())->method('d3TotpRelogin')->willReturn(false);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totplogin',
            $this->callMethod($this->_oController, 'checkTotplogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public  function checkTotploginUnvalidTotp()
    {
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var d3totp_wrongOtpException|PHPUnit_Framework_MockObject_MockObject $oUtilsViewMock */
        $oTotpExceptionMock = $this->getMock(d3totp_wrongOtpException::class, array(), array(), '', false);

        /** @var UtilsView|PHPUnit_Framework_MockObject_MockObject $oUtilsViewMock */
        $oUtilsViewMock = $this->getMock(UtilsView::class, array(
            'addErrorToDisplay',
        ));
        $oUtilsViewMock->expects($this->atLeast(1))->method('addErrorToDisplay')->willReturn(true);
        
        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'isNoTotpOrNoLogin',
            'hasValidTotp',
            'd3TotpRelogin',
            'd3GetUtilsView',
            'd3GetTotpObject'
        ));
        $oControllerMock->method('isNoTotpOrNoLogin')->willReturn(false);
        $oControllerMock->expects($this->once())->method('hasValidTotp')->willThrowException($oTotpExceptionMock);
        $oControllerMock->expects($this->never())->method('d3TotpRelogin')->willReturn(false);
        $oControllerMock->method('d3GetUtilsView')->willReturn($oUtilsViewMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totplogin',
            $this->callMethod($this->_oController, 'checkTotplogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public  function checkTotploginValidTotp()
    {
        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'loadByUserId'
        ), array(), '', false);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var UtilsView|PHPUnit_Framework_MockObject_MockObject $oUtilsViewMock */
        $oUtilsViewMock = $this->getMock(UtilsView::class, array(
            'addErrorToDisplay',
        ));
        $oUtilsViewMock->expects($this->never())->method('addErrorToDisplay')->willReturn(true);

        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'isNoTotpOrNoLogin',
            'hasValidTotp',
            'd3TotpRelogin',
            'd3GetUtilsView',
            'd3GetTotpObject'
        ));
        $oControllerMock->method('isNoTotpOrNoLogin')->willReturn(false);
        $oControllerMock->expects($this->once())->method('hasValidTotp')->willReturn(true);
        $oControllerMock->expects($this->once())->method('d3TotpRelogin')->willReturn(true);
        $oControllerMock->method('d3GetUtilsView')->willReturn($oUtilsViewMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'checkTotplogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetUtilsViewReturnsRightInstance()
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
    public function canCancelTotpLogin()
    {
        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'd3TotpClearSessionVariables',
        ));
        $oControllerMock->expects($this->once())->method('d3TotpClearSessionVariables')->willReturn(false);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'cancelTotpLogin');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function isNoTotpOrNoLoginTrueNoSessionVariable()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTUSER, false);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('isActive')->willReturn(true);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'isNoTotpOrNoLogin', array($oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function isNoTotpOrNoLoginTrueTotpNotActive()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTUSER, true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('isActive')->willReturn(false);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'isNoTotpOrNoLogin', array($oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function isNoTotpOrNoLoginFalse()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTUSER, true);

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'isActive',
        ), array(), '', false);
        $oTotpMock->method('isActive')->willReturn(true);

        $this->assertFalse(
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify',
        ), array(), '', false);
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify',
        ), array(), '', false);
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify',
        ), array(), '', false);
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

        /** @var d3totp|PHPUnit_Framework_MockObject_MockObject $oTotpMock */
        $oTotpMock = $this->getMock(d3totp::class, array(
            'verify',
        ), array(), '', false);
        $oTotpMock->method('verify')->willReturn(false);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'hasValidTotp', array('123456', $oTotpMock))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3TotpReloginPass()
    {
        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'setVariable',
        ));
        $oSessionMock->expects($this->atLeast(2))->method('setVariable')->willReturn(false);
        
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(
            'getId',
        ));
        $oUserMock->method('getId')->willReturn('foo');

        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'd3GetSession',
            'setUser',
            'setLoginStatus',
            '_afterLogin',
        ));
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);
        $oControllerMock->expects($this->once())->method('setUser')->willReturn(false);
        $oControllerMock->expects($this->once())->method('setLoginStatus')->willReturn(false);
        $oControllerMock->expects($this->once())->method('_afterLogin')->willReturn(false);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'd3TotpRelogin', array($oUserMock, '123456'));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3TotpClearSessionVariablesPass()
    {
        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'deleteVariable',
        ));
        $oSessionMock->expects($this->atLeast(3))->method('deleteVariable')->willReturn(false);

        /** @var UserComponent|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(UserComponent::class, array(
            'd3GetSession',
        ));
        $oControllerMock->method('d3GetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'd3TotpClearSessionVariables');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetSessionReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oController, 'd3GetSession')
        );
    }
}