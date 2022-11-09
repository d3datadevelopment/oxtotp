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

namespace D3\Totp\tests\unit\Application\Controller;

use D3\Totp\Application\Controller\d3totplogin;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3totploginTest extends d3TotpUnitTestCase
{
    /** @var d3totplogin */
    protected $_oController;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew(d3totplogin::class);

        Registry::getSession()->deleteVariable(d3totp_conf::SESSION_CURRENTUSER);
        Registry::getSession()->deleteVariable(d3totp_conf::SESSION_CURRENTCLASS);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::render
     */
    public function renderRedirectIfNoTotp()
    {
        /** @var Utils|MockObject $oUtilsMock */
        $oUtilsMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['redirect'])
            ->getMock();
        $oUtilsMock->expects($this->once())->method('redirect')->willReturn(true);

        /** @var d3totplogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totplogin::class)
            ->onlyMethods(['getUtils'])
            ->getMock();
        $oControllerMock->method('getUtils')->willReturn($oUtilsMock);

        $this->_oController = $oControllerMock;

        $this->callMethod($this->_oController, 'render');
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::render
     */
    public function renderDontRedirect()
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTUSER, 'foo');

        /** @var Utils|MockObject $oUtilsMock */
        $oUtilsMock = $this->getMockBuilder(Utils::class)
            ->onlyMethods(['redirect'])
            ->getMock();
        $oUtilsMock->expects($this->never())->method('redirect')->willReturn(true);

        /** @var d3totplogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totplogin::class)
            ->onlyMethods(['getUtils'])
            ->getMock();
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
     * @covers \D3\Totp\Application\Controller\d3totplogin::getUtils
     */
    public function getUtilsReturnsRightInstance()
    {
        $this->assertInstanceOf(
            Utils::class,
            $this->callMethod(
                $this->_oController,
                'getUtils'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::getBackupCodeCountMessage
     */
    public function getBackupCodeCountMessageReturnMessage()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodesListMock */
        $oBackupCodesListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodesListMock->method('getAvailableCodeCount')->willReturn(1);

        /** @var d3totplogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totplogin::class)
            ->onlyMethods(['getBackupCodeListObject'])
            ->getMock();
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
     * @covers \D3\Totp\Application\Controller\d3totplogin::getBackupCodeCountMessage
     */
    public function getBackupCodeCountMessageReturnNoMessage()
    {
        /** @var d3backupcodelist|MockObject $oBackupCodesListMock */
        $oBackupCodesListMock = $this->getMockBuilder(d3backupcodelist::class)
            ->onlyMethods(['getAvailableCodeCount'])
            ->getMock();
        $oBackupCodesListMock->method('getAvailableCodeCount')->willReturn(1234);

        /** @var d3totplogin|MockObject $oControllerMock */
        $oControllerMock = $this->getMockBuilder(d3totplogin::class)
            ->onlyMethods(['getBackupCodeListObject'])
            ->getMock();
        $oControllerMock->method('getBackupCodeListObject')->willReturn($oBackupCodesListMock);

        $this->_oController = $oControllerMock;

        $this->assertEmpty(
            $this->callMethod($this->_oController, 'getBackupCodeCountMessage')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::getBackupCodeListObject
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
     * @covers \D3\Totp\Application\Controller\d3totplogin::getPreviousClass
     */
    public function canGetPreviousClass()
    {
        $className = "testClass";
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTCLASS, $className);

        $this->assertSame(
            $className,
            $this->callMethod($this->_oController, 'getPreviousClass')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::previousClassIsOrderStep
     * @dataProvider classIsOrderStepDataProvider
     */
    public function classIsOrderStep($className, $expected)
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTCLASS, $className);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'previousClassIsOrderStep'
            )
        );
    }

    /**
     * @return array[]
     */
    public function classIsOrderStepDataProvider(): array
    {
        return [
            'order step class'   => ['order', true],
            'no order step class'   => ['start', false],
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::getIsOrderStep
     * @dataProvider classIsOrderStepDataProvider
     */
    public function getIsOrderStepIsSameLikeOrderClass($className, $expected)
    {
        Registry::getSession()->setVariable(d3totp_conf::SESSION_CURRENTCLASS, $className);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'getIsOrderStep'
            )
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Controller\d3totplogin::getBreadCrumb
     */
    public function canGetBreadCrumb()
    {
        $aBreadCrumb = $this->callMethod($this->_oController, 'getBreadCrumb');

        $this->assertIsString($aBreadCrumb[0]['title']);
        $this->assertTrue(strlen($aBreadCrumb[0]['title']) > 1);
        $this->assertIsString($aBreadCrumb[0]['link']);
        $this->assertTrue(strlen($aBreadCrumb[0]['link']) > 1);
    }
}
