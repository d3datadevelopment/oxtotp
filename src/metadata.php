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

use D3\Totp\Setup as ModuleSetup;
use D3\ModCfg\Application\Model\d3utils;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
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
    'version'       => '0.1',
    'author'        => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'         => 'support@shopmodule.com',
    'url'           => 'http://www.oxidmodule.com/',
    'extend'        => [
        OxidModel\User::class              => \D3\Totp\Modules\Application\Model\d3_totp_user::class,
        LoginController::class             => \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::class,
        Utils::class                       => \D3\Totp\Modules\Core\d3_totp_utils::class,
    ],
    'controllers'           => [
        'd3user_totp'       =>  \D3\Totp\Application\Controller\Admin\d3user_totp::class
    ],
    'templates'             => [
        'd3user_totp.tpl'   => 'd3/totp/Application/views/admin/tpl/d3user_totp.tpl',
    ],
    'events'                => [
        'onActivate'        => '\D3\Totp\Setup\Events::onActivate',
        'onDeactivate'      => '\D3\Totp\Setup\Events::onDeactivate',
    ],
    'settings'              => [
    ],
    'blocks'                => [
        [
            'template'      => 'login.tpl',
            'block'         => 'admin_login_form',
            'file'          => 'Application/views/admin/blocks/d3totp_login_admin_login_form.tpl',
        ]
    ],
    'd3FileRegister'        => [
    ],
    'd3SetupClasses'        => [
        ModuleSetup\Installation::class
    ]
];
