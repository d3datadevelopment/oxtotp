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

namespace D3\Totp\Application\Model;

use D3\Totp\Application\Controller\Admin\d3user_totp;
use Exception;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
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
            $oBackupCode = $this->getD3BackupCodeObject();
            $this->_backupCodes[] = $oBackupCode->generateCode($sUserId);
            $this->offsetSet(md5(rand()), $oBackupCode);
        }

        /** @var d3user_totp $oActView */
        $oActView = $this->d3GetConfig()->getActiveView();
        $oActView->setBackupCodes($this->_backupCodes);
    }

    /**
     * @return d3backupcode
     */
    public function getD3BackupCodeObject()
    {
        return oxNew(d3backupcode::class);
    }

    /**
     * @return Config
     */
    public function d3GetConfig()
    {
        return Registry::getConfig();
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
        $oDb = $this->d3GetDb();

        $query = "SELECT oxid FROM ".$this->getBaseObject()->getViewName().
            " WHERE ".$oDb->quoteIdentifier('backupcode')." = ".$oDb->quote($this->getBaseObject()->d3EncodeBC($totp, $this->d3GetUser()->getId()))." AND ".
            $oDb->quoteIdentifier("oxuserid") ." = ".$oDb->quote($this->d3GetUser()->getId());

        $sVerify = $oDb->getOne($query);

        if ($sVerify) {
            $this->getBaseObject()->delete($sVerify);
        }

        return (bool) $sVerify;
    }

    /**
     * @return DatabaseInterface
     * @throws DatabaseConnectionException
     */
    public function d3GetDb()
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * @param $sUserId
     * @throws DatabaseConnectionException
     */
    public function deleteAllFromUser($sUserId)
    {
        $oDb = $this->d3GetDb();

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
        $oDb = $this->d3GetDb();

        $query = "SELECT count(*) FROM ".$oDb->quoteIdentifier($this->getBaseObject()->getViewName()).
            " WHERE ".$oDb->quoteIdentifier('oxuserid')." = ".$oDb->quote($sUserId);

        return (int) $oDb->getOne($query);
    }

    public function d3GetUser()
    {
        return $this->getBaseObject()->d3GetUser();
    }
}