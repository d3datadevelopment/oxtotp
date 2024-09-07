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

namespace D3\Totp\Application\Controller\Admin;

use Assert\Assert;
use D3\Totp\Application\Model\Constants;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3user_totp extends AdminDetailsController
{
    protected $_sSaveError = null;

    protected $_sThisTemplate = '@'.Constants::OXID_MODULE_ID.'/admin/d3user_totp';

    public $aBackupCodes = [];

    /**
     * @return string
     */
    public function render()
    {
        parent::render();

        $soxId = $this->getEditObjectId();

        if ($soxId && $soxId != "-1") {
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
    public function getTotpObject(): d3totp
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return d3backupcodelist
     */
    public function getBackupcodeListObject(): d3backupcodelist
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function save()
    {
        parent::save();

        $aParams = Registry::getRequest()->getRequestEscapedParameter("editval");

        try {
            $oTotp = $this->getTotpObject();

            Assert::that($oTotp->checkIfAlreadyExist($this->getEditObjectId()))->false('D3_TOTP_ALREADY_EXIST');

            $oTotpBackupCodes = $this->getBackupcodeListObject();
            if ($aParams['d3totp__oxid']) {
                $oTotp->load($aParams['d3totp__oxid']);
            } else {
                $aParams['d3totp__usetotp'] = 1;
                /** @var d3totp $init */
                $init = Registry::getSession()->getVariable(d3totp_conf::OTP_SESSION_VARNAME);
                $seed = $init->getSecret();
                $otp = Registry::getRequest()->getRequestEscapedParameter("otp");

                Assert::that($seed)->notBlank('D3_TOTP_EMPTY_SEED');
                Assert::that($otp)
                    ->integerish('D3_TOTP_MISSING_VALIDATION')
                    ->length(6, 'D3_TOTP_MISSING_VALIDATION');

                $oTotp->saveSecret($seed);
                $oTotp->assign($aParams);
                $oTotp->verify($otp, $seed);
                $oTotpBackupCodes->generateBackupCodes($this->getEditObjectId());
                $oTotp->setId();
            }
            $oTotp->save();
            $oTotpBackupCodes->save();
        } catch (Exception $exception) {
            $this->_sSaveError = $exception->getMessage();
        }
    }

    /**
     * @throws DatabaseConnectionException
     */
    public function delete()
    {
        $aParams = Registry::getRequest()->getRequestEscapedParameter("editval");

        $oTotp = $this->getTotpObject();
        if ($aParams['d3totp__oxid']) {
            $oTotp->load($aParams['d3totp__oxid']);
            $oTotp->delete();
            Registry::get(UtilsView::class)->addErrorToDisplay('D3_TOTP_REGISTERDELETED');
        }
    }

    /**
     * @param $aCodes
     */
    public function setBackupCodes($aCodes): void
    {
        $this->aBackupCodes = $aCodes;
    }

    /**
     * @return string
     */
    public function getBackupCodes(): string
    {
        return implode(PHP_EOL, $this->aBackupCodes);
    }

    /**
     * @return int
     * @throws DatabaseConnectionException
     */
    public function getAvailableBackupCodeCount(): int
    {
        $oBackupCodeList = $this->getBackupcodeListObject();
        return $oBackupCodeList->getAvailableCodeCount($this->getEditObjectId());
    }
}
