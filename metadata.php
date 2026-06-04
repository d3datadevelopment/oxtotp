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

use D3\Totp\Application\Controller\Admin\d3totpadminlogin;
use D3\Totp\Application\Controller\Admin\d3user_totp;
use D3\Totp\Application\Controller\Admin\d3force_2fa;
use D3\Totp\Application\Controller\d3_account_totp;
use D3\Totp\Application\Controller\d3totplogin;
use D3\Totp\Application\Model\Constants;
use D3\Totp\Modules\Application\Component\d3_totp_UserComponent;
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\Modules\Application\Controller\d3_totp_OrderController;
use D3\Totp\Modules\Application\Controller\d3_totp_PaymentController;
use D3\Totp\Modules\Application\Controller\d3_totp_UserController;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\Modules\Core\d3_totp_utils;
use D3\Totp\Modules\Core\totpSystemEventHandler;
use D3\Totp\Setup as ModuleSetup;
use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Controller\UserController;
use OxidEsales\Eshop\Core\SystemEventHandler;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Application\Model as OxidModel;

$sMetadataVersion = '2.1';

$aModule = [
    'id'            => Constants::OXID_MODULE_ID,
    'title'         => [
        'de'        => '(D3) zweiter Faktor - Einmalpasswort',
        'en'        => '(D3) second factor - one-time password',
    ],
    'description'   => [
        'de'        => 'Einmalpasswort (TOTP) als zweiter Faktor bei der Anmeldung im OXID eSales Shop',
        'en'        => 'One-time password (TOTP) as second factor for login in OXID eSales shop',
    ],
    'version'       => '3.0.0.0',
    'author'        => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'         => 'support@shopmodule.com',
    'url'           => 'https://www.oxidmodule.com/',
    'thumbnail'     => 'picture.svg',
    'extend'        => [
        UserController::class              => d3_totp_UserController::class,
        PaymentController::class           => d3_totp_PaymentController::class,
        OrderController::class             => d3_totp_OrderController::class,
        OxidModel\User::class              => d3_totp_user::class,
        LoginController::class             => d3_totp_LoginController::class,
        Utils::class                       => d3_totp_utils::class,
        UserComponent::class               => d3_totp_UserComponent::class,
        SystemEventHandler::class          => totpSystemEventHandler::class,
    ],
    'controllers'           => [
        'd3user_totp'       =>  d3user_totp::class,
        'd3force_2fa'       =>  d3force_2fa::class,
        'd3totplogin'       =>  d3totplogin::class,
        'd3_account_totp'   =>  d3_account_totp::class,
        'd3totpadminlogin'  =>  d3totpadminlogin::class,
    ],
    'templates'                 => [],
    'settings'                => [
        [
            'group' => 'd3totp_main',
            'name' => 'D3_TOTP_ADMIN_FORCE_2FA',
            'type' => 'bool',
            'value' => false,
        ],
        [
            'name' => 'D3_TOTP_MASTER_KEY',
            'type' => 'string',
            'value' => '',
        ],
        [
            'name' => 'D3_TOTP_MASTER_KEY_VERSION',
            'type' => 'int',
            'value' => 1,
        ],
    ],
    'events'                => [
        'onActivate'        => ModuleSetup\Events::class.'::onActivate',
        'onDeactivate'      => ModuleSetup\Events::class.'::onDeactivate',
    ],
    'blocks'                => [],
];
