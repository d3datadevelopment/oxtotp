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

namespace D3\Totp\Tests\Unit\Modules\Application\Controller;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Controller\d3_totp_OrderController;
use D3\Totp\Modules\Application\Controller\d3_totp_PaymentController;
use D3\Totp\Modules\Application\Controller\d3_totp_UserController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

trait d3_totp_getUserTestTrait
{
    use CanAccessRestricted;

    protected string $userFixtureId = 'userIdFixture1';

    /** @var User */
    protected User $userFixture;

    public function setUp(): void
    {
        $this->userFixture = oxNew(User::class);
        $this->userFixture->setId($this->userFixtureId);
        $this->userFixture->assign(['oxlname'    => __METHOD__, 'oxusername' => __METHOD__, 'oxpassword' => __METHOD__]);
        $this->userFixture->save();
        $this->userFixture->load($this->userFixtureId);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function tearDown(): void
    {
        $this->userFixture->delete($this->userFixtureId);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserHasNoUser(): void
    {
        /** @var d3_totp_orderController|d3_totp_UserController|d3_totp_PaymentController|MockObject $oControllerMock */
        $oControllerMock = $this->d3getMockBuilder($this->sControllerClass)
            ->onlyMethods(['d3GetTotpObject'])
            ->getMock();
        $oControllerMock->expects($this->never())->method('d3GetTotpObject');

        $this->setValue($oControllerMock, '_oActUser', false);

        $this->assertFalse(
            $this->callMethod($oControllerMock, 'getUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserTotpNotActive(): void
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->d3getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->d3getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(false);
        $oTotpMock->method('loadByUserId');

        /** @var d3_totp_orderController|d3_totp_UserController|d3_totp_PaymentController|MockObject $oControllerMock */
        $oControllerMock = $this->d3getMockBuilder($this->sControllerClass)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
            ])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oControllerMock->setUser($this->userFixture);

        $this->assertSame(
            $this->userFixture,
            $this->callMethod($oControllerMock, 'getUser')
        );

        $oControllerMock->setUser(null);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserTotpFinished(): void
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->d3getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(true);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->d3getMockBuilder(d3totp::class)
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId');

        /** @var d3_totp_orderController|d3_totp_UserController|d3_totp_PaymentController|MockObject $oControllerMock */
        $oControllerMock = $this->d3getMockBuilder($this->sControllerClass)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
            ])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oControllerMock->setUser($this->userFixture);

        $this->assertSame(
            $this->userFixture,
            $this->callMethod($oControllerMock, 'getUser')
        );

        $oControllerMock->setUser(null);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::getUser
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::getUser
     */
    public function getUserTotpNotFinished(): void
    {
        /** @var Session|MockObject $oSessionMock */
        $oSessionMock = $this->d3getMockBuilder(Session::class)
            ->onlyMethods(['getVariable'])
            ->getMock();
        $oSessionMock->method('getVariable')->willReturn(false);

        /** @var d3totp|MockObject $oTotpMock */
        $oTotpMock = $this->d3getMockBuilder(d3totp::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isActive',
                'loadByUserId',
            ])
            ->getMock();
        $oTotpMock->method('isActive')->willReturn(true);
        $oTotpMock->method('loadByUserId');

        /** @var d3_totp_orderController|d3_totp_UserController|d3_totp_PaymentController|MockObject $oControllerMock */
        $oControllerMock = $this->d3getMockBuilder($this->sControllerClass)
            ->onlyMethods([
                'd3GetTotpObject',
                'd3TotpGetSessionObject',
            ])
            ->getMock();
        $oControllerMock->expects($this->once())->method('d3GetTotpObject')->willReturn($oTotpMock);
        $oControllerMock->method('d3TotpGetSessionObject')->willReturn($oSessionMock);
        $oControllerMock->setUser($this->userFixture);

        $this->assertFalse(
            $this->callMethod($oControllerMock, 'getUser')
        );

        $oControllerMock->setUser(null);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::d3GetTotpObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::d3GetTotpObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::d3GetTotpObject
     */
    public function d3GetTotpObjectReturnsRightObject(): void
    {
        /** @var d3_totp_UserController|d3_totp_PaymentController|d3_totp_OrderController $oController */
        $oController = oxNew($this->sControllerClass);

        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($oController, 'd3GetTotpObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_OrderController::d3TotpGetSessionObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_PaymentController::d3TotpGetSessionObject
     * @covers \D3\Totp\Modules\Application\Controller\d3_totp_UserController::d3TotpGetSessionObject
     */
    public function d3GetSessionObjectReturnsRightObject(): void
    {
        /** @var d3_totp_UserController|d3_totp_PaymentController|d3_totp_OrderController $oController */
        $oController = oxNew($this->sControllerClass);

        $this->assertInstanceOf(
            Session::class,
            $this->callMethod(
                $oController,
                'd3TotpGetSessionObject'
            )
        );
    }
}
