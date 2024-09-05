<?php

namespace D3\Totp\tests\unit\Application\Controller\Admin;

use D3\Totp\Application\Controller\Admin\d3force_2fa;
use D3\Totp\Application\Model\d3totp_conf;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3force_2faTest extends d3user_totpTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3force_2fa::class);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::render
     */
    public function testRender()
    {
        $expected = 'fixture';

        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, $expected);

        $this->callMethod(
            $this->_oController,
            'render'
        );

        $this->assertTrue(
            $this->_oController->getViewDataElement('force2FA')
        );
        $this->assertSame(
            $expected,
            $this->getValue(
                $this->_oController,
                '_sEditObjectId'
            )
        );
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::_authorize
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::d3IsAdminForce2FA
     * @dataProvider authorizeDataProvider
     */
    public function testAuthorize($expected, $isAdmin, $force2FA, $givenUserId)
    {
        /** @var d3force_2fa|MockObject $oController */
        $oController = $this->getMockBuilder(d3force_2fa::class)
            ->onlyMethods(['isAdmin'])
            ->getMock();
        $oController->expects($this->once())->method('isAdmin')->willReturn($isAdmin);

        Registry::getConfig()->setConfigParam('D3_TOTP_ADMIN_FORCE_2FA', $force2FA);

        Registry::getSession()->setVariable(d3totp_conf::OXID_ADMIN_AUTH, $givenUserId);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $oController,
                '_authorize'
            )
        );
    }

    /**
     * @return array[]
     */
    public function authorizeDataProvider(): array
    {
        return [
            'noAdmin'   => [false, false, true, 'userId'],
            'dont force'   => [false, true, false, 'userId'],
            'no user id'   => [false, true, true, null],
            'passed'   => [true, true, true, 'userId'],
        ];
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\Admin\d3force_2fa::d3TotpGetSessionObject
     */
    public function testD3GetSessionObject()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod(
                $this->_oController,
                'd3TotpGetSessionObject'
            )
        );
    }
}
