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

use D3\Extsearch\setup as ModuleSetup;
use D3\ModCfg\Application\Model\d3utils;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\VisualCmsModule\Application\Controller\Admin\VisualCmsAdmin as VisualCMSAdmin;
use OxidEsales\Eshop\Application\Controller as OxidController;
use OxidEsales\Eshop\Application\Model as OxidModel;
use OxidEsales\Eshop\Application\Component as OxidComponent;
use OxidEsales\Eshop\Core as OxidCore;

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

$sModuleId = 'd3totp';
/**
 * Module information
 */
$aModule = array(
    'id'          => $sModuleId,
    'title'       =>
        (class_exists(d3utils::class) ? d3utils::getInstance()->getD3Logo() : 'D&sup3;') . ' Zwei-Faktor-Authentisierung',
    'description' => array(
        'de' => 'Zwei-Faktor-Authentisierung (TOTP) f&uuml;r OXID eSales Shop',
        'en' => 'Two-factor authentication (TOTP) for OXID eSales shop',
    ),
    'thumbnail'   => 'picture.png',
    'version'     => '0.1',
    'author'      => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'       => 'support@shopmodule.com',
    'url'         => 'http://www.oxidmodule.com/',
    'extend'      => array(
        //OxidModel\User::class              => \D3\Totp\Modules\Application\Model\d3_totp_user::class,
        LoginController::class             => \D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController::class,
        Utils::class                       => \D3\Totp\Modules\Core\d3_totp_utils::class,
    ),
    'controllers'   => array(
    ),
    'templates'   => array(
    ),
    'events'      => [
    ],
    'settings' => array(
    ),
    'blocks'      => array(
    ),
    'd3FileRegister'    => array(
    ),
    'd3SetupClasses'    => array(
    ),
);

// CREATE TABLE `d3totp` (
//	`OXID` CHAR(32) NOT NULL,
//	`OXUSERID` CHAR(32) NOT NULL,
//	`USETOTP` TINYINT(1) NOT NULL DEFAULT '0',
//	`SEED` VARCHAR(100) NOT NULL DEFAULT '0',
//	PRIMARY KEY (`OXID`),
//	UNIQUE INDEX `Schlüssel 2` (`OXUSERID`)
//)
//ENGINE=InnoDB
//;