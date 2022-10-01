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

namespace D3\Totp\Application\Controller;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Exception;
use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;

class d3_account_totp extends AccountController
{
    protected $_sThisTemplate = 'd3_account_totp.tpl';

    public $aBackupCodes = [];

    public function render()
    {
        $sRet = parent::render();

        /** @var User|null $oUser */
        $oUser = $this->getUser();
        if (false === $oUser instanceof User) {
            return $this->_sThisTemplate = $this->_sThisLoginTemplate;
        }

        $this->addTplParam('user', $this->getUser());

        return $sRet;
    }

    /**
     * @param array $aCodes
     */
    public function setBackupCodes(array $aCodes)
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
     * @return d3backupcodelist
     */
    public function getBackupCodeListObject()
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @return int
     * @throws DatabaseConnectionException
     */
    public function getAvailableBackupCodeCount()
    {
        $oBackupCodeList = $this->getBackupCodeListObject();
        return $oBackupCodeList->getAvailableCodeCount($this->getUser()->getId());
    }

    /**
     * @return d3totp
     */
    public function getTotpObject()
    {
        return oxNew(d3totp::class);
    }

    public function create()
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') === '1') {
            try {
                /** @var d3_totp_user $oUser */
                $oUser = $this->getUser();

                /** @var d3totp $oTotp */
                $oTotp = $this->getTotpObject();
                $oTotpBackupCodes = $this->getBackupCodeListObject();

                $aParams = [
                    'd3totp__usetotp' => 1,
                    'd3totp__oxuserid'  => $oUser->getId(),
                ];
                $seed = Registry::getRequest()->getRequestEscapedParameter("secret");
                $otp = Registry::getRequest()->getRequestEscapedParameter("otp");
                $oTotp->saveSecret($seed);
                $oTotp->assign($aParams);
                $oTotp->verify($otp, $seed);
                $oTotpBackupCodes->generateBackupCodes($oUser->getId());
                $oTotp->setId();

                $oTotp->save();
                $oTotpBackupCodes->save();
            } catch (Exception $oExcp) {
                Registry::get(UtilsView::class)->addErrorToDisplay($oExcp);
            }
        }
    }

    /**
     * @throws DatabaseConnectionException
     */
    public function delete()
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') !== '1') {
            $oUser = $this->getUser();
            /** @var d3totp $oTotp */
            $oTotp = $this->getTotpObject();
            if ($oUser instanceof User && $oUser->getId()) {
                $oTotp->loadByUserId($oUser->getId());
                $oTotp->delete();
            }
        }
    }
}
