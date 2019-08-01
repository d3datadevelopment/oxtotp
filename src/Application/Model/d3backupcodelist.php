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

use D3\Totp\Application\Controller\Admin\d3user_totp;
use Exception;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;

class d3backupcodelist extends ListModel
{
    protected $_sObjectsInListName = d3backupcode::class;

    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = 'd3totp_backupcodes';

    protected $_backupCodes = [];

    /**
     * @param $sUserId
     * @throws DatabaseConnectionException
     */
    public function generateBackupCodes($sUserId)
    {
        $this->deleteAllFromUser($sUserId);

        for ($i = 1; $i <= 10; $i++) {
            $oBackupCode = oxNew(d3backupcode::class);
            $this->_backupCodes[] = $oBackupCode->generateCode($sUserId);
            $this->offsetSet(md5(rand()), $oBackupCode);
        }

        /** @var d3user_totp $oActView */
        $oActView = Registry::getConfig()->getActiveView();
        $oActView->setBackupCodes($this->_backupCodes);
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        /** @var d3backupcode $oBackupCode */
        foreach ($this->getArray() as $oBackupCode) {
            $oBackupCode->save();
        }
    }

    /**
     * @return d3backupcode
     */
    public function getBaseObject()
    {
        /** @var d3backupcode $object */
        $object = parent::getBaseObject();

        return $object;
    }

    /**
     * @param $totp
     * @return bool
     * @throws DatabaseConnectionException
     */
    public function verify($totp)
    {
        $oDb = DatabaseProvider::getDb();

        $query = "SELECT oxid FROM ".$this->getBaseObject()->getViewName().
            " WHERE ".$oDb->quoteIdentifier('backupcode')." = ".$oDb->quote($this->getBaseObject()->d3EncodeBC($totp))." AND ".
            $oDb->quoteIdentifier("oxuserid") ." = ".$oDb->quote($this->d3GetUser()->getId());

        $sVerify = $oDb->getOne($query);

        $this->getBaseObject()->delete($sVerify);

        return (bool) $sVerify;
    }

    /**
     * @param $sUserId
     * @throws DatabaseConnectionException
     */
    public function deleteAllFromUser($sUserId)
    {
        $oDb = DatabaseProvider::getDb();

        $query = "SELECT OXID FROM ".$oDb->quoteIdentifier($this->getBaseObject()->getCoreTableName()).
            " WHERE ".$oDb->quoteIdentifier('oxuserid')." = ".$oDb->quote($sUserId);

        $this->selectString($query);

        /** @var d3backupcode $oBackupCode */
        foreach ($this->getArray() as $oBackupCode) {
            $oBackupCode->delete();
        }
    }

    /**
     * @param $sUserId
     * @return int
     * @throws DatabaseConnectionException
     */
    public function getAvailableCodeCount($sUserId)
    {
        $oDb = DatabaseProvider::getDb();

        $query = "SELECT count(*) FROM ".$oDb->quoteIdentifier($this->getBaseObject()->getViewName()).
            " WHERE ".$oDb->quoteIdentifier('oxuserid')." = ".$oDb->quote($sUserId);

        return (int) $oDb->getOne($query);
    }

    public function d3GetUser()
    {
        return $this->getBaseObject()->d3GetUser();
    }
}