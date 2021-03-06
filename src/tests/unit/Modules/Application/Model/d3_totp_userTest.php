<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

namespace D3\Totp\tests\unit\Modules\Application\Model;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3_totp_userTest extends d3TotpUnitTestCase
{
    /** @var d3_totp_user */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oModel = oxNew(User::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function logout()
    {
        /** @var Session|PHPUnit_Framework_MockObject_MockObject $oSessionMock */
        $oSessionMock = $this->getMock(Session::class, array(
            'deleteVariable'
        ));
        $oSessionMock->expects($this->once())->method('deleteVariable')->willReturn(true);

        /** @var d3_totp_user|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(User::class, array(
            'd3GetSession'
        ));
        $oModelMock->method('d3GetSession')->willReturn($oSessionMock);

        $this->_oModel = $oModelMock;

        $this->assertTrue(
            $this->callMethod(
                $this->_oModel,
                'logout'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3getTotpReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3totp::class,
            $this->callMethod($this->_oModel, 'd3getTotp')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetSessionReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->callMethod($this->_oModel, 'd3GetSession')
        );
    }
}