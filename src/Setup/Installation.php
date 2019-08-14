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

namespace D3\Totp\Setup;

use D3\ModCfg\Application\Model\d3database;
use D3\ModCfg\Application\Model\Install\d3install_updatebase;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\ConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

class Installation extends d3install_updatebase
{
    protected $_aUpdateMethods = array(
        array('check' => 'doesTotpTableNotExist',
            'do'      => 'addTotpTable'),
        array('check' => 'doesTotpBCTableNotExist',
            'do'      => 'addTotpBCTable'),
        array('check' => 'checkFields',
            'do'      => 'fixFields'),
        array('check' => 'checkIndizes',
            'do'      => 'fixIndizes'),
        array('check' => 'checkSEONotExists',
            'do'      => 'addSEO'),
    );

    public $aMultiLangTables = array();

    public $aFields = array(
        'OXID'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'OXID',
            'sType'       => 'CHAR(32)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => '',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'OXUSERID'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'OXUSERID',
            'sType'       => 'CHAR(32)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => '',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'USETOTP'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'USETOTP',
            'sType'       => 'TINYINT(1)',
            'blNull'      => false,
            'sDefault'    => 0,
            'sComment'    => '',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'SEED'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'SEED',
            'sType'       => 'VARCHAR(256)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => '',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'OXTIMESTAMP'     => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'OXTIMESTAMP',
            'sType'       => 'TIMESTAMP',
            'blNull'      => false,
            'sDefault'    => 'CURRENT_TIMESTAMP',
            'sComment'    => 'Timestamp',
            'sExtra'      => '',
            'blMultilang' => false,
        ),

        'bc_OXID'    => array(
            'sTableName'  => 'd3totp_backupcodes',
            'sFieldName'  => 'OXID',
            'sType'       => 'CHAR(32)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => '',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'bc_OXUSERID'    => array(
            'sTableName'  => 'd3totp_backupcodes',
            'sFieldName'  => 'OXUSERID',
            'sType'       => 'CHAR(32)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'user id',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'bc_BACKUPCODE'    => array(
            'sTableName'  => 'd3totp_backupcodes',
            'sFieldName'  => 'BACKUPCODE',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'bc_OXTIMESTAMP'     => array(
            'sTableName'  => 'd3totp_backupcodes',
            'sFieldName'  => 'OXTIMESTAMP',
            'sType'       => 'TIMESTAMP',
            'blNull'      => false,
            'sDefault'    => 'CURRENT_TIMESTAMP',
            'sComment'    => 'Timestamp',
            'sExtra'      => '',
            'blMultilang' => false,
        )
    );

    public $aIndizes = array(
        'OXID' => array(
            'sTableName' => 'd3totp',
            'sType'      => d3database::INDEX_TYPE_PRIMARY,
            'sName'      => 'PRIMARY',
            'aFields'    => array(
                'OXID' => 'OXID',
            ),
        ),
        'OXUSERID' => array(
            'sTableName' => 'd3totp',
            'sType'      => d3database::INDEX_TYPE_UNIQUE,
            'sName'      => 'OXUSERID',
            'aFields'    => array(
                'OXUSERID' => 'OXUSERID',
            ),
        ),
        'bc_OXID' => array(
            'sTableName' => 'd3totp_backupcodes',
            'sType'      => d3database::INDEX_TYPE_PRIMARY,
            'sName'      => 'PRIMARY',
            'aFields'    => array(
                'OXID' => 'OXID',
            ),
        ),
        'bc_OXUSERID' => array(
            'sTableName' => 'd3totp_backupcodes',
            'sType'      => d3database::INDEX_TYPE_INDEX,
            'sName'      => 'OXUSERID',
            'aFields'    => array(
                'OXUSERID' => 'OXUSERID',
            ),
        ),
        'bc_BACKUPCODE' => array(
            'sTableName' => 'd3totp_backupcodes',
            'sType'      => d3database::INDEX_TYPE_INDEX,
            'sName'      => 'BACKUPCODE',
            'aFields'    => array(
                'BACKUPCODE' => 'BACKUPCODE',
            ),
        ),
    );

    protected $_aRefreshMetaModuleIds = array('d3totp');

    /**
     * @return bool
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function doesTotpTableNotExist()
    {
        return $this->_checkTableNotExist('d3totp');
    }

    /**
     * @return bool
     * @throws ConnectionException
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function addTotpTable()
    {
        $blRet = false;
        if ($this->doesTotpTableNotExist()) {
            $this->setInitialExecMethod(__METHOD__);
            $blRet  = $this->_addTable2(
                'd3totp',
                $this->aFields,
                $this->aIndizes,
                'totp setting',
                'InnoDB'
            );
        }

        return $blRet;
    }

    /**
     * @return bool
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function doesTotpBCTableNotExist()
    {
        return $this->_checkTableNotExist('d3totp_backupcodes');
    }

    /**
     * @return bool
     * @throws ConnectionException
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function addTotpBCTable()
    {
        $blRet = false;
        if ($this->doesTotpBCTableNotExist()) {
            $this->setInitialExecMethod(__METHOD__);
            $blRet  = $this->_addTable2(
                'd3totp_backupcodes',
                $this->aFields,
                $this->aIndizes,
                'totp backup codes',
                'InnoDB'
            );
        }

        return $blRet;
    }

    /**
     * @return bool
     * @throws DatabaseConnectionException
     */
    public function checkSEONotExists()
    {
        $query = "SELECT 1 FROM " . getViewName('oxseo') . " WHERE oxstdurl = 'index.php?cl=d3_account_totp'";

        return !$this->d3GetDb()->getOne($query);
    }

    /**
     * @return DatabaseInterface
     * @throws DatabaseConnectionException
     */
    public function d3GetDb()
    {
        return DatabaseProvider::getDb();
    }

    /**
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function addSEO()
    {
        $query = [
            "INSERT INTO `oxseo` (`OXOBJECTID`, `OXIDENT`, `OXSHOPID`, `OXLANG`, `OXSTDURL`, `OXSEOURL`, `OXTYPE`, `OXFIXED`, `OXEXPIRED`, `OXPARAMS`, `OXTIMESTAMP`) VALUES
('39f744f17e974988e515558698a29df4', '76282e134ad4e40a3578e121a6cb1f6a', 1, 1, 'index.php?cl=d3_account_totp', 'en/2-factor-authintication/', 'static', 0, 0, '', NOW()),
('39f744f17e974988e515558698a29df4', 'c1f8b5506e2b5d6ac184dcc5ebdfb591', 1, 0, 'index.php?cl=d3_account_totp', '2-faktor-authentisierung/', 'static', 0, 0, '', NOW());"
        ];

        return $this->_executeMultipleQueries($query);
    }
}