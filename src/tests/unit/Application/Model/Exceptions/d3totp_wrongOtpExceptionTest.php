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
    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3totp_wrongOtpException::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException::__construct
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
