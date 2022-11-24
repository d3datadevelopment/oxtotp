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

namespace D3\Totp\tests\unit\Application\Model;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Application\Model\d3RandomGenerator;
use D3\Totp\tests\unit\d3TotpUnitTestCase;
use ReflectionException;

class d3RandomGeneratorTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /** @var d3RandomGenerator */
    protected $_oModel;

    /**
     * setup basic requirements
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3RandomGenerator::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @test
     * @throws ReflectionException
     * @covers \D3\Totp\Application\Model\d3RandomGenerator::getRandomTotpBackupCode
     */
    public function getRandomTotpBackupCodeReturnsRightCode()
    {
        $this->assertRegExp(
            '@[0-9]{6}@',
            $this->callMethod($this->_oModel, 'getRandomTotpBackupCode')
        );
    }
}
