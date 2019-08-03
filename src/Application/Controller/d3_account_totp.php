<?php


namespace D3\Totp\Application\Controller;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Doctrine\DBAL\DBALException;
use Exception;
use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;

class d3_account_totp extends AccountController
{
    protected $_sThisTemplate = 'd3_account_totp.tpl';

    public $aBackupCodes = [];

    public function render()
    {
        $sRet = parent::render();

        // is logged in ?
        $oUser = $this->getUser();
        if (!$oUser) {
            return $this->_sThisTemplate = $this->_sThisLoginTemplate;
        }

        $this->addTplParam('user', $this->getUser());

        return $sRet;
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
        $oBackupCodeList = oxNew(d3backupcodelist::class);
        return $oBackupCodeList->getAvailableCodeCount($this->getUser()->getId());
    }

    public function create()
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') === '1') {
            try {
                /** @var d3_totp_user $oUser */
                $oUser = $this->getUser();

                /** @var d3totp $oTotp */
                $oTotp = oxNew(d3totp::class);
                $oTotpBackupCodes = oxNew(d3backupcodelist::class);

                $aParams = [
                    'd3totp__usetotp' => 1,
                    'd3totp__oxuserid'  => $oUser->getId()
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
     * @throws DBALException
     */
    public function delete()
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') != 1) {

            $oUser = $this->getUser();
            /** @var d3totp $oTotp */
            $oTotp = oxNew(d3totp::class);
            if ($oUser && $oUser->getId()) {
                $oTotp->loadByUserId($oUser->getId());
                $oTotp->delete();
            }
        }
    }
}