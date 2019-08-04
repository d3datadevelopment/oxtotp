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

namespace D3\Totp\Application\Controller\Admin;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class d3user_totp extends AdminDetailsController
{
    protected $_sSaveError = null;

    protected $_sThisTemplate = 'd3user_totp.tpl';

    public $aBackupCodes = [];

    /**
     * @return string
     */
    public function render()
    {
        parent::render();

        $soxId = $this->getEditObjectId();

        if (isset($soxId) && $soxId != "-1") {
            /** @var d3_totp_user $oUser */
            $oUser = $this->getUserObject();
            if ($oUser->load($soxId)) {
                $this->addTplParam("oxid", $oUser->getId());
            } else {
                $this->addTplParam("oxid", '-1');
            }
            $this->addTplParam("edit", $oUser);
        }

        if ($this->_sSaveError) {
            $this->addTplParam("sSaveError", $this->_sSaveError);
        }

        return $this->_sThisTemplate;
    }

    /**
     * @return User
     */
    public function getUserObject()
    {
        return oxNew(User::class);
    }

    /**
     * @return d3totp
     */
    public function getTotpObject()
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return d3backupcodelist
     */
    public function getBackupcodeListObject()
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        parent::save();

        $aParams = Registry::getRequest()->getRequestEscapedParameter("editval");

        try {
            $pwd = Registry::getRequest()->getRequestEscapedParameter("pwd");

            /** @var d3_totp_user $oUser */
            $oUser = $this->getUserObject();
            $oUser->load($this->getEditObjectId());

            if (false == $oUser->isSamePassword($pwd)) {
                $oException = oxNew(StandardException::class, 'D3_TOTP_ERROR_PWDONTPASS');
                throw $oException;
            }

            /** @var d3totp $oTotp */
            $oTotp = $this->getTotpObject();
            $oTotpBackupCodes = $this->getBackupcodeListObject();
            if ($aParams['d3totp__oxid']) {
                $oTotp->load($aParams['d3totp__oxid']);
            } else {
                $aParams['d3totp__usetotp'] = 1;
                $seed = Registry::getRequest()->getRequestEscapedParameter("secret");
                $otp = Registry::getRequest()->getRequestEscapedParameter("otp");

                $oTotp->saveSecret($seed);
                $oTotp->assign($aParams);
                $oTotp->verify($otp, $seed);
                $oTotpBackupCodes->generateBackupCodes($this->getEditObjectId());
                $oTotp->setId();
            }
            $oTotp->save();
            $oTotpBackupCodes->save();
        } catch (Exception $oExcp) {
            $this->_sSaveError = $oExcp->getMessage();
        }
    }

    /**
     * @throws DatabaseConnectionException
     */
    public function delete()
    {
        $aParams = Registry::getRequest()->getRequestEscapedParameter("editval");

        /** @var d3totp $oTotp */
        $oTotp = $this->getTotpObject();
        if ($aParams['d3totp__oxid']) {
            $oTotp->load($aParams['d3totp__oxid']);
            $oTotp->delete();
        }
    }

    /**
     * @param $aCodes
     */
    public function setBackupCodes($aCodes)
    {
        $this->aBackupCodes = $aCodes;
    }

    /**
     * @return string
     */
    public function getBackupCodes()
    {
        return implode(PHP_EOL, $this->aBackupCodes);
    }

    /**
     * @return int
     * @throws DatabaseConnectionException
     */
    public function getAvailableBackupCodeCount()
    {
        $oBackupCodeList = $this->getBackupcodeListObject();
        return $oBackupCodeList->getAvailableCodeCount($this->getUser()->getId());
    }
}