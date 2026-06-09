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

namespace D3\Totp\Tests\Unit\Core;

use D3\TestingTools\Development\CanAccessRestricted;
use D3\Totp\Core\Registry;
use D3\Totp\Tests\Unit\d3TotpUnitTestCase;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class RegistryTest extends d3TotpUnitTestCase
{
    use CanAccessRestricted;

    /**
     * @test
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetLoggerNewTestInstance()
    {
        $this->setValue( new Registry(), 'registry', []);

        $this->assertInstanceOf(
            Logger::class,
            Registry::getLogger()
        );
    }

    /**
     * @test
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetLoggerNewProductionInstance()
    {
        $_SESSION['ignoreTestState'] = true;
        $this->setValue( new Registry(), 'registry', []);

        $this->assertInstanceOf(
            Logger::class,
            Registry::getLogger()
        );
        unset($_SESSION['ignoreTestState']);
        Registry::resetLogger();
    }

    /**
     * @test
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    public function testGetLoggerCached()
    {
        // initial
        $this->assertInstanceOf(
            Logger::class,
            Registry::getLogger()
        );

        // cached
        $this->assertInstanceOf(
            Logger::class,
            Registry::getLogger()
        );
    }
}