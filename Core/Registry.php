<?php

/**
 * Copyright (c) D3 Data Development (Inh. Thomas Dartsch)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\Totp\Core;

use D3\LoggerFactory\LoggerFactory;
use D3\Totp\Application\Model\Constants;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class Registry
{
    protected const LOGLEVEL_DEBUG      = 'debug';
    protected const LOGLEVEL_INFO       = 'info';
    protected const LOGLEVEL_NOTICE     = 'notice';
    protected const LOGLEVEL_WARNING    = 'warning';
    protected const LOGLEVEL_ERROR      = 'error';
    protected const LOGLEVEL_CRITICAL   = 'critical';
    protected const LOGLEVEL_ALERT      = 'alert';
    protected const LOGLEVEL_EMERGENCY  = 'emergency';

    public static array $registry = [];

    /**
     * @return Logger
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getLogger(): Logger
    {
        if (isset(self::$registry['logger']) && self::$registry['logger']) {
            return self::$registry['logger'];
        }

        if (defined('OXID_PHP_UNIT') && (!isset($_SESSION['ignoreTestState']) || !$_SESSION['ignoreTestState'])) {
            $logger = new Logger('d32fa_totp');
            $logger->pushHandler(new NullHandler());
            self::$registry['logger'] = $logger;
            return self::$registry['logger'];
        }
        /** @var ModuleSettingService $settingsService */
        $settingsService =  ContainerFactory::getInstance()->getContainer()->get(ModuleSettingServiceInterface::class);
        $moduleLogLevel = $settingsService->getString(
            Constants::CONFIG_LOGLEVEL,
            Constants::OXID_MODULE_ID
        )->toString();

        $monologLevel = self::convertToMonologLevel($moduleLogLevel);
        try {
            $keptFiles = max(
                $settingsService->getInteger(Constants::CONFIG_KEPTLOGFILES, Constants::OXID_MODULE_ID),
                1
            );
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            $keptFiles = 7;
        }
        // @codeCoverageIgnoreEnd
        try {
            $logErrorsOnly = $settingsService->getBoolean(
                Constants::CONFIG_LOGERRORSONLY,
                Constants::OXID_MODULE_ID
            );
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            $logErrorsOnly = false;
        }
        // @codeCoverageIgnoreEnd
        $factory = LoggerFactory::create();

        $factory->addOxidFileHandler()
            ->setBuffering();
        $fileHandler = $factory->addFileHandler(
            $factory->getOxidLogPath('2fa_totp.log'),
            $monologLevel,
            $keptFiles
        )->setBuffering();
        if ($logErrorsOnly) {
            // @codeCoverageIgnoreStart
            $fileHandler->setLogOnErrorOnly();
            // @codeCoverageIgnoreEnd
        }
        $factory->addUidProcessor();
        $logger = $factory->build('d32fa_totp');

        self::$registry['logger'] = $logger;

        return self::$registry['logger'];
    }

    /**
     * @codeCoverageIgnore
     * @param Logger $logger
     * @return void
     */
    public static function setLogger(Logger $logger): void
    {
        self::$registry['logger'] = $logger;
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    public static function resetLogger(): void
    {
        unset(self::$registry['logger']);
    }

    public static function convertToMonologLevel(string $loglevel): int
    {
        $loglevel = trim(strtolower($loglevel));

        return match (true) {
            $loglevel == self::LOGLEVEL_DEBUG => Logger::DEBUG,
            $loglevel == self::LOGLEVEL_INFO => Logger::INFO,
            $loglevel == self::LOGLEVEL_NOTICE => Logger::NOTICE,
            $loglevel == self::LOGLEVEL_WARNING => Logger::WARNING,
            $loglevel == self::LOGLEVEL_ERROR => Logger::ERROR,
            $loglevel == self::LOGLEVEL_CRITICAL => Logger::CRITICAL,
            $loglevel == self::LOGLEVEL_ALERT => Logger::ALERT,
            default => Logger::EMERGENCY,      // no appropriate Monolog level
        };
    }
}
