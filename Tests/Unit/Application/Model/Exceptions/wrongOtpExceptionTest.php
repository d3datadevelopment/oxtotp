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

namespace D3\Totp\Tests\Unit\Application\Model\Exceptions;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\Exceptions\wrongOtpException;
use D3\Totp\Tests\Unit\d3TotpUnitTestCase;
use ReflectionException;

class wrongOtpExceptionTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var wrongOtpException */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(wrongOtpException::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\Exceptions\wrongOtpException::__construct
     */
    public function constructorHasRightDefaultMessage()
    {
        $this->_oModel = oxNew(wrongOtpException::class);
        $this->assertSame(
            'D3_TOTP_ERROR_UNVALID',
            $this->callMethod($this->_oModel, 'getMessage')
        );
    }
}
