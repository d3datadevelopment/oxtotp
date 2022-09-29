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

namespace D3\Totp\Setup;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
// @codeCoverageIgnoreStart
class Events
{
    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function onActivate(): void
    {
        self::addTotpTable();
        self::addTotpBackupCodesTable();
        self::addSeoItem1();
        self::addSeoItem2();
    }

    /**
     * @codeCoverageIgnore
     */
    public static function onDeactivate()
    {
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function addTotpTable(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS `d3totp` (
            `OXID` CHAR(32) NOT NULL ,
            `OXUSERID` CHAR(32) NOT NULL ,
            `USETOTP` TINYINT(1) NOT NULL  DEFAULT 0,
            `SEED` VARCHAR(256) NOT NULL ,
            `OXTIMESTAMP` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp', 
            PRIMARY KEY (`OXID`) , 
            UNIQUE KEY  `OXUSERID` (`OXUSERID`) 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='totp setting';";

        DatabaseProvider::getDb()->execute( $query );
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function addTotpBackupCodesTable(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS `d3totp_backupcodes` (
            `OXID` CHAR(32) NOT NULL ,
            `OXUSERID` CHAR(32) NOT NULL  COMMENT 'user id',
            `BACKUPCODE` VARCHAR(64) NOT NULL  COMMENT 'BackupCode',
            `OXTIMESTAMP` TIMESTAMP   NOT NULL  DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp', 
            PRIMARY KEY (`OXID`) ,  
            KEY  `OXUSERID` (`OXUSERID`) ,  
            KEY  `BACKUPCODE` (`BACKUPCODE`) 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='totp backup codes';";

        DatabaseProvider::getDb()->execute( $query );
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function addSeoItem1(): void
    {
        if (!DatabaseProvider::getDb()->getOne('SELECT 1 FROM oxseo WHERE oxident = "76282e134ad4e40a3578e121a6cb1f6a"')) {
            $query = "INSERT INTO `oxseo` 
                (
                    `OXOBJECTID`, `OXIDENT`, `OXSHOPID`, 
                    `OXLANG`, `OXSTDURL`, `OXSEOURL`, 
                    `OXTYPE`, `OXFIXED`, `OXEXPIRED`, 
                    `OXPARAMS`, `OXTIMESTAMP`
                ) VALUES (
                    '39f744f17e974988e515558698a29df4', '76282e134ad4e40a3578e121a6cb1f6a', 1, 
                    1, 'index.php?cl=d3_account_totp', 'en/2-factor-authintication/', 
                    'static', 0, 0, 
                    '', NOW()
                );";

            DatabaseProvider::getDb()->execute( $query );
        }
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function addSeoItem2(): void
    {
        if (!DatabaseProvider::getDb()->getOne('SELECT 1 FROM oxseo WHERE oxident = "c1f8b5506e2b5d6ac184dcc5ebdfb591"')) {
            $query = "INSERT INTO `oxseo` 
                (
                    `OXOBJECTID`, `OXIDENT`, `OXSHOPID`, 
                    `OXLANG`, `OXSTDURL`, `OXSEOURL`, 
                    `OXTYPE`, `OXFIXED`, `OXEXPIRED`, 
                    `OXPARAMS`, `OXTIMESTAMP`
                ) VALUES (
                    '39f744f17e974988e515558698a29df4', 'c1f8b5506e2b5d6ac184dcc5ebdfb591', 1, 
                    0, 'index.php?cl=d3_account_totp', '2-faktor-authentisierung/', 
                    'static', 0, 0, 
                    '', NOW()
                );";

            DatabaseProvider::getDb()->execute( $query );
        }
    }
}
// @codeCoverageIgnoreEnd