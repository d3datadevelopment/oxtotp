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
use D3\Totp\Modules\Application\Component\d3_totp_UserComponent;
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\Modules\Application\Controller\d3_totp_OrderController;
use D3\Totp\Modules\Application\Controller\d3_totp_PaymentController;
use D3\Totp\Modules\Application\Controller\d3_totp_UserController;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use D3\Totp\Modules\Core\d3_totp_utils;
use D3\Totp\Setup as ModuleSetup;
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
$sMetadataVersion = '2.1';

$sModuleId = 'd3totp';
$logo = '<img src="https://logos.oxidmodule.com/d3logo.svg" alt="(D3)" style="height:1em;width:1em">';

/**
 * Module information
 */
$aModule = [
    'id'            => $sModuleId,
    'title'         => [
        'de'        => $logo . ' Zwei-Faktor-Authentisierung',
        'en'        => $logo . ' two-factor authentication',
    ],
    'description'   => [
        'de'        => 'Zwei-Faktor-Authentisierung (TOTP) f&uuml;r OXID eSales Shop',
        'en'        => 'Two-factor authentication (TOTP) for OXID eSales shop',
    ],
    'version'       => '2.0.0.1',
    'author'        => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'         => 'support@shopmodule.com',
    'url'           => 'https://www.oxidmodule.com/',
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
        'd3totpadminlogin'  =>  d3totpadminlogin::class
    ],
    'templates'                 => [
        'd3user_totp.tpl'       => 'd3/totp/Application/views/admin/tpl/d3user_totp.tpl',
        'd3totplogin.tpl'       => 'd3/totp/Application/views/tpl/d3totplogin.tpl',
        'd3_account_totp.tpl'   => 'd3/totp/Application/views/tpl/d3_account_totp.tpl',
        'd3totpadminlogin.tpl'  => 'd3/totp/Application/views/admin/tpl/d3totplogin.tpl',
    ],
    'settings'                => [
        [
            'group' => 'd3totp_main',
            'name' => 'D3_TOTP_ADMIN_FORCE_2FA',
            'type' => 'bool',
            'value' => false,
        ],
    ],
    'events'                => [
        'onActivate'        => ModuleSetup\Events::class.'::onActivate',
        'onDeactivate'      => ModuleSetup\Events::class.'::onDeactivate',
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
        [
            'template'      => 'page/account/dashboard.tpl',
            'block'         => 'account_dashboard_col2',
            'file'          => 'Application/views/blocks/page/account/account_dashboard_col2_wave.tpl',
        ],
        [
            'theme'         => 'flow',
            'template'      => 'page/account/dashboard.tpl',
            'block'         => 'account_dashboard_col2',
            'file'          => 'Application/views/blocks/page/account/account_dashboard_col2_flow.tpl',
        ],
        [
            'template'      => 'widget/header/servicebox.tpl',
            'block'         => 'widget_header_servicebox_items',
            'file'          => 'Application/views/blocks/widget/header/widget_header_servicebox_items.tpl',
        ],
    ],
];
