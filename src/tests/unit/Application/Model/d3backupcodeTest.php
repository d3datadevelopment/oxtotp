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

namespace D3\Totp\tests\unit\Application\Model;

use D3\Totp\Application\Model\d3backupcode;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3backupcodeTest extends d3TotpUnitTestCase
{
    /** @var d3backupcode */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3backupcode::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcode::generateCode
     */
    public function generateCodePass()
    {
        $sTestUserId = 'testUserId';
        $sBackupCode = '123456';

        /** @var d3backupcode|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods([
                'getRandomTotpBackupCode',
                'd3EncodeBC',
            ])
            ->getMock();
        $oModelMock->method('getRandomTotpBackupCode')->willReturn($sBackupCode);
        $oModelMock->method('d3EncodeBC')->will(
            $this->returnCallback(function ($arg) {
                return $arg;
            })
        );

        $this->_oModel = $oModelMock;

        $this->callMethod($this->_oModel, 'generateCode', [$sTestUserId]);

        $this->assertSame($sTestUserId, $this->_oModel->getFieldData('oxuserid'));
        $this->assertSame($sBackupCode, $this->_oModel->getFieldData('backupcode'));
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcode::getRandomTotpBackupCode
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
     * @covers \D3\Totp\Application\Model\d3backupcode::d3EncodeBC
     */
    public function d3EncodeBCPass()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $oUserMock->method('load')->willReturn(true);
        $oUserMock->assign(
            [
                'oxpasssalt' => '6162636465666768696A6B',
            ]
        );

        /** @var d3backupcode|MockObject $oModelMock */
        $oModelMock = $this->getMockBuilder(d3backupcode::class)
            ->onlyMethods(['d3GetUserObject'])
            ->getMock();
        $oModelMock->method('d3GetUserObject')->willReturn($oUserMock);

        $this->_oModel = $oModelMock;

        $this->assertSame(
            '9f7f502a8148f90732a4aa4d880b8cf5',
            $this->callMethod($this->_oModel, 'd3EncodeBC', ['123456', 'userId'])
        );
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcode::d3GetUser
     */
    public function d3GetUserReturnCachedUser()
    {
        /** @var User|MockObject $oUserMock */
        $oUserMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oUserMock->assign(
            [
                'oxid' => 'foobar',
            ]
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
     * @covers \D3\Totp\Application\Model\d3backupcode::d3GetUser
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

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3backupcode::d3GetUserObject
     */
    public function d3getUserObjectReturnsRightInstance()
    {
        $this->assertInstanceOf(
            User::class,
            $this->callMethod($this->_oModel, 'd3GetUserObject')
        );
    }
}
