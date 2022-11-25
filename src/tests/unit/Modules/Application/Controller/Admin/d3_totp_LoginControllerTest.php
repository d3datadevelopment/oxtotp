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

namespace D3\Totp\tests\unit\Modules\Application\Controller\Admin;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsServer;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_LoginControllerTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

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
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3GetTotpObject
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
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpGetSession
     */
    public function d3GetSessionReturnsRightObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oController, 'd3TotpGetSession')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::checklogin
     */
    public function canChecklogin()
    {
        $fixture = 'returnString';

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
            ->onlyMethods(['d3CallMockableParent'])
            ->getMock();
        $oControllerMock->method('d3CallMockableParent')->willReturn($fixture);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            $fixture,
            $this->callMethod(
                $this->_oController,
                'checklogin'
            )
        );
    }

    /**
     * @test
     *
     * @param $selectedProfile
     * @param $setCookie
     * @param $expectedCookie
     * @param $setSession
     *
     * @throws ReflectionException
     * @dataProvider canRunTotpAfterLoginDataProvider
     * @covers       \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3totpAfterLogin
     */
    public function canRunTotpAfterLogin($selectedProfile, $setCookie, $expectedCookie, $setSession)
    {
        /** @var Session|MockObject $sessionMock */
        $sessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable', 'setVariable'])
            ->getMock();
        $variableMap = [
            [d3totp_conf::SESSION_ADMIN_PROFILE, $selectedProfile],
            ['aAdminProfiles', [
                0   => ['abc', 0],
                1   => ['def', 1],
                2   => ['geh', 2],
            ]],
        ];
        $sessionMock->method('getVariable')->willReturnMap($variableMap);
        $sessionMock->expects($setSession)->method('setVariable')->willReturnMap($variableMap);

        /** @var UtilsServer|MockObject $utilsServerMock */
        $utilsServerMock = $this->getMockBuilder(UtilsServer::class)
            ->onlyMethods(['setOxCookie'])
            ->getMock();
        $utilsServerMock->expects($setCookie)->method('setOxCookie')->with(
            $this->anything(),
            $expectedCookie
        );

        /** @var d3_totp_LoginController|MockObject $sut */
        $sut = $this->getMockBuilder(LoginController::class)
            ->onlyMethods(['d3TotpGetUtilsServer', 'd3TotpGetSession', 'd3totpAfterLoginSetLanguage'])
            ->getMock();
        $sut->method('d3TotpGetUtilsServer')->willReturn($utilsServerMock);
        $sut->method('d3TotpGetSession')->willReturn($sessionMock);
        $sut->expects($this->once())->method('d3totpAfterLoginSetLanguage')->willReturn($sessionMock);

        $this->callMethod(
            $sut,
            'd3totpAfterLogin'
        );
    }

    /**
     * @return array
     */
    public function canRunTotpAfterLoginDataProvider(): array
    {
        return [
            'no profile selected'       => [null, $this->once(), '', $this->never()],
            'valid profile selected'    => [2, $this->once(), '2@geh@2', $this->once()],
            'invalid profile selected'  => [5, $this->never(), false, $this->never()]
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers       \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3totpAfterLoginSetLanguage
     * @dataProvider canRunTotpAfterLoginSetLanguageDataProvider
     */
    public function canRunTotpAfterLoginSetLanguage($languageId)
    {
        /** @var Session|MockObject $sessionMock */
        $sessionMock = $this->getMockBuilder(Session::class)
                            ->onlyMethods(['getVariable'])
                            ->getMock();
        $sessionMock->method('getVariable')->willReturn($languageId);

        /** @var UtilsServer|MockObject $utilsServerMock */
        $utilsServerMock = $this->getMockBuilder(UtilsServer::class)
            ->onlyMethods(['setOxCookie'])
            ->getMock();
        $utilsServerMock->expects($this->once())->method('setOxCookie');

        /** @var Language|MockObject $langMock */
        $langMock = $this->getMockBuilder(Language::class)
            ->onlyMethods(['setTplLanguage'])
            ->getMock();
        $langMock->expects($this->once())->method('setTplLanguage');

        /** @var d3_totp_LoginController|MockObject $sut */
        $sut = $this->getMockBuilder(LoginController::class)
                    ->onlyMethods(['d3TotpGetUtilsServer', 'd3TotpGetSession', 'd3TotpGetLangObject'])
                    ->getMock();
        $sut->method('d3TotpGetUtilsServer')->willReturn($utilsServerMock);
        $sut->method('d3TotpGetSession')->willReturn($sessionMock);
        $sut->method('d3TotpGetLangObject')->willReturn($langMock);

        $this->callMethod(
            $sut,
            'd3totpAfterLoginSetlanguage'
        );
    }

    /**
     * @return array
     */
    public function canRunTotpAfterLoginSetLanguageDataProvider(): array
    {
        return [
            'existing language'     => [0],
            'not existing language' => [50],
        ];
    }

    /**
     * @test
     * @param $totpActive
     * @param $loggedin
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpLoginMissing
     * @dataProvider d3TotpLoginMissingTestDataProvider
     */
    public function d3TotpLoginMissingTest($totpActive, $loggedin, $expected)
    {
        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isActive'])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn($totpActive);

        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->with(d3totp_conf::SESSION_ADMIN_AUTH)->willReturn($loggedin);

        /** @var d3_totp_LoginController|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3_totp_LoginController::class)
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
                'd3TotpLoginMissing',
                [$oTotpMock]
            )
        );
    }

    /**
     * @return array
     */
    public function d3TotpLoginMissingTestDataProvider(): array
    {
        return [
            'totp not active, not logged in'=> [false, false, false],
            'totp active, logged in'        => [true , true, false],
            'totp active, not logged in'    => [true , false, true],
            'totp not active, logged in'    => [false, true, false],
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpGetUtilsServer
     */
    public function d3GetUtilsServerObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            UtilsServer::class,
            $this->callMethod($this->_oController, 'd3TotpGetUtilsServer')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::d3TotpGetLangObject
     */
    public function d3GetLangObjectReturnsRightObject()
    {
        $this->assertInstanceOf(
            Language::class,
            $this->callMethod($this->_oController, 'd3TotpGetLangObject')
        );
    }
}
