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

namespace D3\Totp\tests\unit\Application\Model;

use D3\Totp\Application\Model\d3backupcode;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

class d3backupcodeTest extends d3TotpUnitTestCase
{
    /** @var d3backupcode */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oModel = oxNew(d3backupcode::class);
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
    public function generateCodePass()
    {
        $sTestUserId = 'testUserId';
        $sBackupCode = '123456';

        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcode::class, array(
            'getRandomTotpBackupCode',
            'd3EncodeBC',
        ));
        $oModelMock->method('getRandomTotpBackupCode')->willReturn($sBackupCode);
        $oModelMock->method('d3EncodeBC')->will(
            $this->returnCallback(function ($arg) {
                return $arg;
            })
        );

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'generateCode', array($sTestUserId));

        $this->assertSame($sTestUserId, $this->_oModel->getFieldData('oxuserid'));
        $this->assertSame($sBackupCode, $this->_oModel->getFieldData('backupcode'));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function getRandomTotpBackupCodePass()
    {
        $this->assertRegExp(
            '@[0-9]{6}@',
            $this->callMethod($this->_oModel, 'getRandomTotpBackupCode')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3EncodeBCPass()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(), array(), '', false);
        $oUserMock->assign(
            array(
                'oxpasssalt' => 'abcdefghijk'
            )
        );
        
        /** @var d3backupcode|PHPUnit_Framework_MockObject_MockObject $oModelMock */
        $oModelMock = $this->getMock(d3backupcode::class, array(
            'd3GetUser',
        ));
        $oModelMock->method('d3GetUser')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertSame(
            'e10adc3949ba59abbe56e057f20f883e',
            $this->callMethod($this->_oModel, 'd3EncodeBC', array('123456'))
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetUserReturnCachedUser()
    {
        /** @var User|PHPUnit_Framework_MockObject_MockObject $oUserMock */
        $oUserMock = $this->getMock(User::class, array(), array(), '', false);
        $oUserMock->assign(
            array(
                'oxid' => 'foobar'
            )
        );

        $this->_oModel->setUser($oUserMock);

        $this->assertSame(
            $oUserMock,
            $this->callMethod($this->_oModel, 'd3GetUser')
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function d3GetUserReturnCurrentUser()
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTUSER, 'foobar');

        $oUser = $this->callMethod($this->_oModel, 'd3GetUser');

        $this->assertInstanceOf(
            User::class,
            $oUser
        );
        $this->assertNull(
            $oUser->getId()
        );
    }
}