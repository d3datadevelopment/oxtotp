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

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Controller\Admin\d3totpadminlogin;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Wrapper\LoggerWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionException;

class d3totpadminloginTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3totpadminlogin */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3totpadminlogin::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::_authorize
     */
    public function testAuthorize()
    {
        $this->assertTrue(
            $this->callMethod(
                $this->_oController,
                '_authorize'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpGetTotpObject
     */
    public function d3TotpGetTotpObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod(
                $this->_oController,
                'd3TotpGetTotpObject'
            )
        );
    }

    /**
     * @test
     * @param $hasAuthAlready
     * @param $totpActive
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::isTotpIsNotRequired
     * @dataProvider isTotpIsNotRequiredPassedDataProvider
     */
    public function isTotpIsNotRequiredPassed($hasAuthAlready, $totpActive, $expected)
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('isActive')->willReturn($totpActive);
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'hasVariable'
            ])
            ->getMock();
        $hasVariableMap = [
            [d3totp_conf::SESSION_AUTH, $hasAuthAlready]
        ];
        $oSessionMock->method('hasVariable')->willReturnMap($hasVariableMap);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
            ->onlyMethods([
                'd3TotpGetSession',
                'd3TotpGetTotpObject'
            ])
            ->getMock();
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->method('d3TotpGetTotpObject')->willReturn($oTotpMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'isTotpIsNotRequired'
            )
        );
    }

    /**
     * @return array
     */
    public function isTotpIsNotRequiredPassedDataProvider(): array
    {
        return [
            'auth already finished' => [true, true, true],
            'auth required'         => [false, true, false],
            'totp inactive'         => [false, false, true],
        ];
    }

    /**
     * @test
     * @param $hasAdminAuth
     * @param $hasCurrentUser
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @covers       \D3\Totp\Application\Controller\Admin\d3totpadminlogin::isTotpLoginNotPossible
     * @dataProvider isTotpLoginNotPossiblePassedDataProvider
     */
    public function isTotpLoginNotPossiblePassed($hasAdminAuth, $hasCurrentUser, $expected)
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'hasVariable'
            ])
            ->getMock();
        $hasVariableMap = [
            [d3totp_conf::OXID_ADMIN_AUTH, $hasAdminAuth],
            [d3totp_conf::SESSION_CURRENTUSER, $hasCurrentUser],
        ];
        $oSessionMock->method('hasVariable')->willReturnMap($hasVariableMap);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
            ->onlyMethods([
                'd3TotpGetSession'
            ])
            ->getMock();
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'isTotpLoginNotPossible'
            )
        );
    }

    /**
     * @return array
     */
    public function isTotpLoginNotPossiblePassedDataProvider(): array
    {
        return [
            'no admin auth, no user'    => [false, false, true],
            'has admin auth'            => [true, false, false],
            'has current user'          => [false, true, false],
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::render
     * @dataProvider canRenderDataProvider
     */
    public function canRender($totpRequired, $totpNotPossible, $redirect)
    {
        /** @var Utils|MockObject $oUtilsMock */
        $oUtilsMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['redirect'])
            ->getMock();
        $oUtilsMock
            ->expects(is_null($redirect) ? $this->never() : $this->once())
            ->method('redirect')
            ->with($this->identicalTo('index.php?cl='.$redirect))
            ->willReturn(true);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
            ->onlyMethods([
                'isTotpIsNotRequired',
                'isTotpLoginNotPossible',
                'd3TotpGetUtils'
            ])
            ->getMock();
        $oControllerMock->method('isTotpIsNotRequired')->willReturn($totpRequired);
        $oControllerMock->method('isTotpLoginNotPossible')->willReturn($totpNotPossible);
        $oControllerMock->method('d3TotpGetUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;

        $this->callMethod(
            $this->_oController,
            'render'
        );
    }

    /**
     * @return array[]
     */
    public function canRenderDataProvider(): array
    {
        return [
            'not required'  => [true, true, 'admin_start'],
            'not possible'  => [false, true, 'login'],
            'do auth'       => [false, false, null],
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3GetBackupCodeListObject
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
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::getBackupCodeCountMessage
     */
    public function getBackupCodeCountMessageShowMessage()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodeListMock->method('getAvailableCodeCount')->willReturn(2);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
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
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::getBackupCodeCountMessage
     */
    public function getBackupCodeCountMessageDontShowMessage()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodeListMock */
        $oBackupCodeListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodeListMock->method('getAvailableCodeCount')->willReturn(10);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
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
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3CancelLogin
     */
    public function canCancelLogin()
    {
        /** @var d3_totp_user|MockObject $userMock */
        $userMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['logout'])
            ->getMock();
        $userMock->expects($this->once())->method('logout')->willReturn(true);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
            ->onlyMethods(['d3TotpGetUserObject'])
            ->getMock();
        $oControllerMock->method('d3TotpGetUserObject')->willReturn($userMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'login',
            $this->callMethod(
                $this->_oController,
                'd3CancelLogin'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpGetUserObject
     */
    public function d3GetUserObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oController, 'd3TotpGetUserObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::checklogin
     */
    public function checkloginUnvalidTotp()
    {
        /** @var LoggerWrapper|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(LoggerWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error', 'debug'])
            ->getMock();
        $loggerMock->expects($this->atLeastOnce())->method('error')->willReturn(true);
        $loggerMock->expects($this->atLeastOnce())->method('debug')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'initNewSession',
                'setVariable',
                'deleteVariable',
            ])
            ->getMock();
        $oSessionMock->expects($this->never())->method('initNewSession')->willReturn(false);
        $oSessionMock->expects($this->never())->method('setVariable')->willReturn(false);
        $oSessionMock->expects($this->never())->method('deleteVariable')->willReturn(false);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
            ->onlyMethods([
                'getLogger',
                'd3TotpHasValidTotp',
                'd3TotpGetSession'
            ])
            ->getMock();
        $oControllerMock->method('d3TotpHasValidTotp')
            ->willThrowException(oxNew(d3totp_wrongOtpException::class));
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->method('getLogger')->willReturn($loggerMock);

        $this->_oController = $oControllerMock;

        $this->callMethod(
            $this->_oController,
            'checklogin'
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::checklogin
     */
    public function checkloginValidTotp()
    {
        /** @var LoggerWrapper|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(LoggerWrapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error', 'debug'])
            ->getMock();
        $loggerMock->expects($this->never())->method('error')->willReturn(true);
        $loggerMock->expects($this->never())->method('debug')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByUserId'])
            ->getMock();
        $oTotpMock->method('loadByUserId')->willReturn(true);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods([
                'initNewSession',
                'setVariable',
                'deleteVariable',
            ])
            ->getMock();
        $oSessionMock->expects($this->atLeastOnce())->method('initNewSession')->willReturn(false);
        $oSessionMock->expects($this->atLeastOnce())->method('setVariable')->willReturn(false);
        $oSessionMock->expects($this->atLeastOnce())->method('deleteVariable')->willReturn(false);

        /** @var d3totpadminlogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totpadminlogin::class)
            ->onlyMethods([
                'getLogger',
                'd3TotpHasValidTotp',
                'd3TotpGetSession'
            ])
            ->getMock();
        $oControllerMock->method('d3TotpHasValidTotp')->willReturn(true);
        $oControllerMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oControllerMock->method('getLogger')->willReturn($loggerMock);

        $this->_oController = $oControllerMock;

        $this->callMethod(
            $this->_oController,
            'checklogin'
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpHasValidTotp
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
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpHasValidTotp
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
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpHasValidTotp
     */
    public function hasValidTotpFalseMissingTotp()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_AUTH, false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->onlyMethods(['verify'])
            ->disableOriginalConstructor()
            ->getMock();
        $oTotpMock->method('verify')->willThrowException(oxNew(d3totp_wrongOtpException::class));

        $this->expectException(d3totp_wrongOtpException::class);
        $this->callMethod($this->_oController, 'd3TotpHasValidTotp', [null, $oTotpMock]);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpHasValidTotp
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
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpGetUtils
     */
    public function d3TotpGetUtilsReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Utils::class,
            $this->callMethod(
                $this->_oController,
                'd3TotpGetUtils'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::d3TotpGetSession
     */
    public function d3GetSessionReturnsRightObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod(
                $this->_oController,
                'd3TotpGetSession'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3totpadminlogin::getLogger
     */
    public function getLoggerReturnsRightObject()
    {
        $this->assertInstanceOf(
            LoggerInterface::class,
            $this->callMethod(
                $this->_oController,
                'getLogger'
            )
        );
    }
}
