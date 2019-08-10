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

namespace D3\Totp\tests\unit\Application\Model\Exceptions;

use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use ReflectionException;

class d3totp_wrongOtpExceptionTest extends d3TotpUnitTestCase
{
    /** @var d3totp_wrongOtpException */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp()
    {
        parent::setUp();

        $this->_oModel = oxNew(d3totp_wrongOtpException::class);
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
    public function constructorHasRightDefaultMessage()
    {
        $this->_oModel = oxNew(d3totp_wrongOtpException::class);
        $this->assertSame(
            'D3_TOTP_ERROR_UNVALID',
            $this->callMethod($this->_oModel, 'getMessage')
        );
    }
}