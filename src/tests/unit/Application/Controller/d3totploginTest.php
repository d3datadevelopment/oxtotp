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

namespace D3\Totp\tests\unit\Application\Controller;

use D3\Totp\Application\Controller\d3totplogin;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3totploginTest extends d3TotpUnitTestCase
{
    /** @var d3totplogin */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oController = oxNew(d3totplogin::class);

        Registry::getSession()->deleteVariable(d3totp::TOTP_SESSION_CURRENTUSER);
        Registry::getSession()->deleteVariable(d3totp::TOTP_SESSION_CURRENTCLASS);
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
    public function renderRedirectIfNoTotp()
    {
        /** @var Utils|PHPUnit_Framework_MockObject_MockObject $oUtilsMock */
        $oUtilsMock = $this->getMock(Utils::class, array(
            'redirect'
        ));
        $oUtilsMock->expects($this->once())->method('redirect')->willReturn(true);

        /** @var d3totplogin|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3totplogin::class, array(
            'getUtils'
        ));
        $oControllerMock->method('getUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;
        
        $this->callMethod($this->_oController, 'render');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function renderDontRedirect()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTUSER, 'foo');

        /** @var Utils|PHPUnit_Framework_MockObject_MockObject $oUtilsMock */
        $oUtilsMock = $this->getMock(Utils::class, array(
            'redirect'
        ));
        $oUtilsMock->expects($this->never())->method('redirect')->willReturn(true);

        /** @var d3totplogin|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3totplogin::class, array(
            'getUtils'
        ));
        $oControllerMock->method('getUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;

        $this->assertSame(
            'd3totplogin.tpl',
            $this->callMethod($this->_oController, 'render')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getUtilsReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Utils::class,
            $this->callMethod($this->_oController, 'getUtils')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBackupCodeCountMessageReturnMessage()
    {
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oBackupCodesListMock */
        $oBackupCodesListMock = $this->getMock(d3backupcodelist::class, array(
            'getAvailableCodeCount'
        ));
        $oBackupCodesListMock->method('getAvailableCodeCount')->willReturn(1);
        
        /** @var d3totplogin|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3totplogin::class, array(
            'getBackupCodeListObject'
        ));
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodesListMock);

        $this->_oController = $oControllerMock;

        $this->assertGreaterThan(
            0,
            strpos(
                $this->callMethod($this->_oController, 'getBackupCodeCountMessage'),
                ' 1 '
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBackupCodeCountMessageReturnNoMessage()
    {
        /** @var d3backupcodelist|PHPUnit_Framework_MockObject_MockObject $oBackupCodesListMock */
        $oBackupCodesListMock = $this->getMock(d3backupcodelist::class, array(
            'getAvailableCodeCount'
        ));
        $oBackupCodesListMock->method('getAvailableCodeCount')->willReturn(1234);

        /** @var d3totplogin|PHPUnit_Framework_MockObject_MockObject $oControllerMock */
        $oControllerMock = $this->getMock(d3totplogin::class, array(
            'getBackupCodeListObject'
        ));
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodesListMock);

        $this->_oController = $oControllerMock;

        $this->assertEmpty(
            $this->callMethod($this->_oController, 'getBackupCodeCountMessage')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getBackupCodeListObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            d3backupcodelist::class,
            $this->callMethod($this->_oController, 'getBackupCodeListObject')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canGetPreviousClass()
    {
        $className = "testClass";
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTCLASS, $className);

        $this->assertSame(
            $className,
            $this->callMethod($this->_oController, 'getPreviousClass')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function orderClassIsOrderStep()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTCLASS, 'order');

        $this->assertTrue(
            $this->callMethod(
                $this->_oController,
                'previousClassIsOrderStep'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function startClassIsNoOrderStep()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTCLASS, 'start');

        $this->assertFalse(
            $this->callMethod(
                $this->_oController,
                'previousClassIsOrderStep'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getIsOrderStepIsSameLikeOrderClass()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTCLASS, 'order');

        $this->assertTrue(
            $this->callMethod(
                $this->_oController,
                'getIsOrderStep'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getIsOrderStepIsSameLikeStartClass()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTCLASS, 'start');

        $this->assertFalse(
            $this->callMethod(
                $this->_oController,
                'getIsOrderStep'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function canGetBreadCrumb()
    {
        $aBreadCrumb = $this->callMethod($this->_oController, 'getBreadCrumb');

        $this->assertInternalType('string', $aBreadCrumb[0]['title']);
        $this->assertTrue(strlen($aBreadCrumb[0]['title']) > 1);
        $this->assertInternalType('string', $aBreadCrumb[0]['link']);
        $this->assertTrue(strlen($aBreadCrumb[0]['link']) > 1);
    }
}