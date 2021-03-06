<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      http://www.oxidmodule.com
 */

namespace D3\Totp\Application\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

class d3backupcode extends BaseModel
{
    protected $_sCoreTable = 'd3totp_backupcodes';

    /**
     * @param $sUserId
     * @return string
     * @throws DatabaseConnectionException
     */
    public function generateCode($sUserId)
    {
        $sCode = $this->getRandomTotpBackupCode();
        $this->assign(
            [
                'oxuserid'    => $sUserId,
                'backupcode' => $this->d3EncodeBC($sCode, $sUserId),
            ]
        );

        return $sCode;
    }

    public function getRandomTotpBackupCode()
    {
        return d3RandomGenerator::getRandomTotpBackupCode();
    }

    /**
     * @param $code
     * @return false|string
     * @throws DatabaseConnectionException
     */
    public function d3EncodeBC($code, $sUserId)
    {
        $oDb = DatabaseProvider::getDb();
        $oUser = $this->d3GetUserObject();
        $oUser->load($sUserId);
        $salt = $oUser->getFieldData('oxpasssalt');
        $sSelect = "SELECT BINARY MD5( CONCAT( " . $oDb->quote($code) . ", UNHEX( ".$oDb->quote($salt)." ) ) )";

        return $oDb->getOne($sSelect);
    }

    public function d3GetUser()
    {
        if ($this->getUser()) {
            return $this->getUser();
        }

        $sUserId = Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTUSER);
        $oUser = oxNew(User::class);
        $oUser->load($sUserId);
        return $oUser;
    }

    /**
     * @return User
     */
    public function d3GetUserObject()
    {
        return oxNew(User::class);
    }
}