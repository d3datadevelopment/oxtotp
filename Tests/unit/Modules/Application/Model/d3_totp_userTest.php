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

namespace D3\Totp\tests\unit\Modules\Application\Model;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3_totp_userTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Model\d3_totp_user::logout
     */
    public function logout()
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['deleteVariable'])
            ->getMock();
        $oSessionMock->expects($this->atLeast(2))->method('deleteVariable')->willReturn(true);

        /** @var d3_totp_user|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['d3TotpGetSession'])
            ->getMock();
        $oModelMock->method('d3TotpGetSession')->willReturn($oSessionMock);

        $sut = $oModelMock;

        $this->assertTrue(
            $this->callMethod(
                $sut,
                'logout'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Model\d3_totp_user::d3getTotp
     */
    public function d3getTotpReturnsRightInstance()
    {
        $sut = oxNew(User::class);

        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod(
                $sut,
                'd3getTotp'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Model\d3_totp_user::d3TotpGetSession
     */
    public function d3GetSessionReturnsRightInstance()
    {
        $sut = oxNew(User::class);

        $this->assertInstanceOf(
            Session::class,
            $this->callMethod(
                $sut,
                'd3TotpGetSession'
            )
        );
    }

    /**
     * @test
     * @param $currentUser
     * @param $isAdmin
     * @param $adminAuth
     * @param $frontendAuth
     * @param $expected
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Model\d3_totp_user::d3TotpGetCurrentUser
     * @dataProvider d3TotpGetCurrentUserTestDataProvider
     */
    public function d3TotpGetCurrentUserTest($currentUser, $isAdmin, $adminAuth, $frontendAuth, $expected)
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['hasVariable', 'getVariable'])
            ->getMock();
        $oSessionMock->expects($this->once())->method('hasVariable')->willReturn((bool) $currentUser);
        $getVariableMap = [
            [d3totp_conf::SESSION_CURRENTUSER, $currentUser],
            [d3totp_conf::SESSION_ADMIN_CURRENTUSER, $currentUser],
            [d3totp_conf::OXID_ADMIN_AUTH, $adminAuth],
            [d3totp_conf::OXID_FRONTEND_AUTH, $frontendAuth],
        ];
        $oSessionMock->method('getVariable')->willReturnMap($getVariableMap);

        /** @var d3_totp_user|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['d3TotpGetSession', 'isAdmin'])
            ->getMock();
        $oModelMock->method('d3TotpGetSession')->willReturn($oSessionMock);
        $oModelMock->method('isAdmin')->willReturn($isAdmin);

        $sut = $oModelMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'd3TotpGetCurrentUser'
            )
        );
    }

    /**
     * @return array[]
     */
    public function d3TotpGetCurrentUserTestDataProvider(): array
    {
        return [
            'adm login request'     => ['currentFixture', true, 'adminFixture', 'frontendFixture', 'currentFixture'],
            'frnt login request'    => ['currentFixture', false, 'adminFixture', 'frontendFixture', 'currentFixture'],
            'admin auth'            => [null, true, 'adminFixture', 'frontendFixture', 'adminFixture'],
            'frontend auth'         => [null, false, 'adminFixture', 'frontendFixture', 'frontendFixture'],
        ];
    }
}
