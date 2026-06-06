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

use D3\Totp\Application\Model\Exceptions\tooManyAttemptsException;

class tooManyAttemptsExceptionTest extends abstractExceptionTest
{
    protected $sutClassName = tooManyAttemptsException::class;
    protected $expectedMessage = 'D3_TOTP_ERROR_LOCKOUT';
}
