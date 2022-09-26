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

use D3\Totp\Application\Controller\Admin\d3user_totp;
use D3\Totp\Application\Controller\Admin\d3force_2fa;
use D3\Totp\Application\Controller\d3_account_totp;
use D3\Totp\Application\Controller\d3totplogin;
use D3\Totp\Modules\Application\Component\d3_totp_UserComponent;
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\Modules\Application\Controller\d3_totp_OrderController;
use D3\Totp\Modules\Application\Controller\d3_totp_PaymentController;
use D3\Totp\Modules\Application\Controller\d3_totp_UserController;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\Modules\Core\d3_totp_utils;
use D3\Totp\Setup as ModuleSetup;
use D3\ModCfg\Application\Model\d3utils;
use OxidEsales\Eshop\Application\Component\UserComponent;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Controller\UserController;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Application\Model as OxidModel;

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

$logo = (class_exists(d3utils::class) ? d3utils::getInstance()->getD3Logo() : 'D&sup3;');

$sModuleId = 'd3totp';
/**
 * Module information
 */
$aModule = [
    'id'            => $sModuleId,
    'title'         => [
        'de'        => $logo.' Zwei-Faktor-Authentisierung',
        'en'        => $logo.' two-factor authentication',
    ],
    'description'   => [
        'de'        => 'Zwei-Faktor-Authentisierung (TOTP) f&uuml;r OXID eSales Shop',
        'en'        => 'Two-factor authentication (TOTP) for OXID eSales shop',
    ],
    'thumbnail'     => 'picture.png',
    'version'       => '1.0.0.0',
    'author'        => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'         => 'support@shopmodule.com',
    'url'           => 'http://www.oxidmodule.com/',
    'extend'        => [
        UserController::class              => d3_totp_UserController::class,
        PaymentController::class           => d3_totp_PaymentController::class,
        OrderController::class             => d3_totp_OrderController::class,
        OxidModel\User::class              => d3_totp_user::class,
        LoginController::class             => d3_totp_LoginController::class,
        Utils::class                       => d3_totp_utils::class,
        UserComponent::class               => d3_totp_UserComponent::class,
    ],
    'controllers'           => [
        'd3user_totp'       =>  d3user_totp::class,
        'd3force_2fa'       =>  d3force_2fa::class,
        'd3totplogin'       =>  d3totplogin::class,
        'd3_account_totp'   =>  d3_account_totp::class,
    ],
    'templates'                 => [
        'd3user_totp.tpl'       => 'd3/totp/Application/views/admin/tpl/d3user_totp.tpl',
        'd3totplogin.tpl'       => 'd3/totp/Application/views/tpl/d3totplogin.tpl',
        'd3_account_totp.tpl'   => 'd3/totp/Application/views/tpl/d3_account_totp.tpl',
    ],
    'settings'                => [
        [
            'group' => 'main',
            'name' => 'D3_TOTP_ADMIN_FORCE_2FA',
            'type' => 'bool',
            'value' => false,
        ]
    ],
    'events'                => [
        'onActivate'        => '\D3\Totp\Setup\Events::onActivate',
        'onDeactivate'      => '\D3\Totp\Setup\Events::onDeactivate',
    ],
    'blocks'                => [
        [
            'template'      => 'login.tpl',
            'block'         => 'admin_login_form',
            'file'          => 'Application/views/admin/blocks/d3totp_login_admin_login_form.tpl',
        ],
        [
            'template'      => 'page/account/inc/account_menu.tpl',
            'block'         => 'account_menu',
            'file'          => 'Application/views/blocks/page/account/inc/account_menu.tpl',
        ],
    ]
];
