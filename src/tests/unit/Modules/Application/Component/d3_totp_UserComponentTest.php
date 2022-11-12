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

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\Modules\Application\Component\d3_totp_UserComponent;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_UserComponentTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3_totp_UserComponent */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(UserComponent::class);

        Registry::getSession()->setVariable(d3totp_conf::SESSION_AUTH, false);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::_afterLogin
     */
    public function afterLoginFailsIfNoUserLoggedIn()
    {
        $oUser = false;

        /** @var Utils|MockObject $oUtilsMock */
        $oUtilsMock = $this->getMockBuilder(Utils::class)
                           ->onlyMethods(['redirect'])
                           ->getMock();
        $oUtilsMock->expects($this->never())->method('redirect')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['setVariable'])
            ->getMock();
        $oSessionMock->expects($this->never())->method('setVariable');

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->never())->method('isActive')->willReturn(false);

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSession',
                'd3TotpGetUtils',
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3TotpGetUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;

        $this->expectException( InvalidArgumentException::class);

        $this->callMethod($this->_oController, '_afterLogin', [$oUser]);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::_afterLogin
     * @dataProvider afterLoginFailTotpNotActiveOrAlreadyCheckedDataProvider
     */
    public function afterLoginFailTotpNotActiveOrAlreadyChecked($isActive, $checked)
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods([
                'logout',
                'getId',
            ])
            ->getMock();
        $oUserMock->expects($this->never())->method('logout')->willReturn(false);
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Utils|MockObject $oUtilsMock */
        $oUtilsMock = $this->getMockBuilder(Utils::class)
                           ->onlyMethods(['redirect'])
                           ->getMock();
        $oUtilsMock->expects($this->never())->method('redirect')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['setVariable', 'getVariable'])
            ->getMock();
        $oSessionMock->expects($this->never())->method('setVariable');
        $oSessionMock->method('getVariable')->willReturn($checked);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->once())->method('isActive')->willReturn($isActive);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSession',
                'd3TotpGetUtils',
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3TotpGetUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, '_afterLogin', [$oUserMock]);
    }

    /**
     * @return array
     */
    public function afterLoginFailTotpNotActiveOrAlreadyCheckedDataProvider(): array
    {
        return [
            'TOTP not active, not checked'   => [false, null],
            'TOTP not active, checked'   => [false, true],
            'TOTP active, checked'   => [true, true],
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::_afterLogin
     */
    public function afterLoginPass()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods([
                'logout',
                'getId',
            ])
            ->getMock();
        $oUserMock->expects($this->once())->method('logout')->willReturn(false);
        $oUserMock->method('getId')->willReturn('foo');

        /** @var Utils|MockObject $oUtilsMock */
        $oUtilsMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['redirect'])
            ->getMock();
        $oUtilsMock->expects($this->once())->method('redirect')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['setVariable', 'getVariable'])
            ->getMock();
        $oSessionMock->expects($this->atLeast(3))->method('setVariable');
        $oSessionMock->method('getVariable')->willReturn(null);

        /** @var BaseController|MockObject $oParentMock */
        $oParentMock = $this->getMockBuilder(BaseController::class)
            ->onlyMethods(['getClassKey'])
            ->getMock();
        $oParentMock->method('getClassKey')->willReturn('foo');

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->expects($this->once())->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSession',
                'd3TotpGetUtils',
                'getParent'
            ])
            ->getMock();
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('getParent')->willReturn($oParentMock);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3TotpGetUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, '_afterLogin', [$oUserMock]);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3GetTotpObject
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
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpCheckTotpLogin
     */
    public function checkTotploginNoTotpLogin()
    {
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
        $oSessionMock->expects($this->never())->method('setVariable');

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods([
                'd3TotpIsNoTotpOrNoLogin',
                'd3TotpHasValidTotp',
                'd3GetTotpObject',
                'd3TotpGetSession',
            ])
            ->getMock();
        $oControllerMock->method('d3TotpIsNoTotpOrNoLogin')->willReturn(true);
        $oControllerMock->expects($this->never())->method('d3TotpHasValidTotp')->willReturn(false);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totplogin',
            $this->callMethod($this->_oController, 'd3TotpCheckTotpLogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpCheckTotpLogin
     */
    public function checkTotploginUnvalidTotp()
    {
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
        $oSessionMock->expects($this->never())->method('setVariable');

        /** @var d3totp_wrongOtpException|MockObject $oUtilsViewMock */
        $oTotpExceptionMock = $this->getMockBuilder(d3totp_wrongOtpException::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var UtilsView|MockObject $oUtilsViewMock */
        $oUtilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $oUtilsViewMock->expects($this->atLeast(1))->method('addErrorToDisplay')->willReturn(true);

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods([
                'd3TotpIsNoTotpOrNoLogin',
                'd3TotpHasValidTotp',
                'd3TotpGetUtilsView',
                'd3GetTotpObject',
                'd3TotpGetSession',
            ])
            ->getMock();
        $oControllerMock->method('d3TotpIsNoTotpOrNoLogin')->willReturn(false);
        $oControllerMock->expects($this->once())->method('d3TotpHasValidTotp')->willThrowException($oTotpExceptionMock);
        $oControllerMock->method('d3TotpGetUtilsView')->willReturn($oUtilsViewMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totplogin',
            $this->callMethod($this->_oController, 'd3TotpCheckTotpLogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpCheckTotpLogin
     */
    public function checkTotploginValidTotp()
    {
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
        $oSessionMock->expects($this->atLeast(2))->method('setVariable');

        /** @var UtilsView|MockObject $oUtilsViewMock */
        $oUtilsViewMock = $this->getMockBuilder(UtilsView::class)
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $oUtilsViewMock->expects($this->never())->method('addErrorToDisplay')->willReturn(true);

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods([
                'd3TotpIsNoTotpOrNoLogin',
                'd3TotpHasValidTotp',
                'd3TotpGetUtilsView',
                'd3GetTotpObject',
                'd3TotpGetSession',
                'setLoginStatus'
            ])
            ->getMock();
        $oControllerMock->method('d3TotpIsNoTotpOrNoLogin')->willReturn(false);
        $oControllerMock->expects($this->once())->method('d3TotpHasValidTotp')->willReturn(true);
        $oControllerMock->method('d3TotpGetUtilsView')->willReturn($oUtilsViewMock);
        $oControllerMock->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->expects($this->once())->method('setLoginStatus')->with(
            $this->identicalTo(USER_LOGIN_SUCCESS)
        );

        $this->_oController = $oControllerMock;

        $this->assertFalse(
            $this->callMethod($this->_oController, 'd3TotpCheckTotpLogin')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpGetUtilsView
     */
    public function d3GetUtilsViewReturnsRightInstance()
    {
        $this->assertInstanceOf(
            UtilsView::class,
            $this->callMethod($this->_oController, 'd3TotpGetUtilsView')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpGetUtils
     */
    public function d3GetUtilsReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Utils::class,
            $this->callMethod($this->_oController, 'd3TotpGetUtils')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpCancelTotpLogin
     */
    public function canCancelTotpLogin()
    {
        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods(['d3TotpClearSessionVariables'])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3TotpClearSessionVariables')->willReturn(false);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'd3TotpCancelTotpLogin');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpIsNoTotpOrNoLogin
     */
    public function isNoTotpOrNoLoginTrueNoSessionVariable()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTUSER, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'd3TotpIsNoTotpOrNoLogin', [$oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpIsNoTotpOrNoLogin
     */
    public function isNoTotpOrNoLoginTrueTotpNotActive()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTUSER, true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(false);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'd3TotpIsNoTotpOrNoLogin', [$oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpIsNoTotpOrNoLogin
     */
    public function isNoTotpOrNoLoginFalse()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTUSER, true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['isActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'd3TotpIsNoTotpOrNoLogin', [$oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpHasValidTotp
     */
    public function hasValidTotpTrueSessionVarname()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_AUTH, true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(false);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'd3TotpHasValidTotp', ['123456', $oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpHasValidTotp
     */
    public function hasValidTotpTrueValidTotp()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_AUTH, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(true);

        $this->assertTrue(
            $this->callMethod($this->_oController, 'd3TotpHasValidTotp', ['123456', $oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpHasValidTotp
     */
    public function hasValidTotpFalseMissingTotp()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_AUTH, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(true);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'd3TotpHasValidTotp', [null, $oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpHasValidTotp
     */
    public function hasValidTotpFalseUnverifiedTotp()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_AUTH, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willReturn(false);

        $this->assertFalse(
            $this->callMethod($this->_oController, 'd3TotpHasValidTotp', ['123456', $oTotpMock])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpClearSessionVariables
     */
    public function d3TotpClearSessionVariablesPass()
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['deleteVariable'])
            ->getMock();
        $oSessionMock->expects($this->atLeast(3))->method('deleteVariable')->willReturn(false);

        /** @var UserComponent|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(UserComponent::class)
            ->onlyMethods(['d3TotpGetSession'])
            ->getMock();
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'd3TotpClearSessionVariables');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Component\d3_totp_UserComponent::d3TotpGetSession
     */
    public function d3GetSessionReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oController, 'd3TotpGetSession')
        );
    }
}
