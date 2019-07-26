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
        'BC1'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC1',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #1',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC2'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC2',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #2',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC3'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC3',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #3',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC4'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC4',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #4',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC5'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC5',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #5',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC6'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC6',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #6',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC7'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC7',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #7',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC8'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC8',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #8',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC9'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC9',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #9',
            'sExtra'      => '',
            'blMultilang' => false,
        ),
        'BC10'    => array(
            'sTableName'  => 'd3totp',
            'sFieldName'  => 'BC10',
            'sType'       => 'VARCHAR(64)',
            'blNull'      => false,
            'sDefault'    => false,
            'sComment'    => 'BackupCode #10',
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
        )
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
}