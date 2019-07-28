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
}