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
use D3\Totp\Application\Controller\OtpManagementControllerTrait;
use D3\Totp\Application\Model\Constants;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Exception;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3user_totp extends AdminDetailsController
{
    use OtpManagementControllerTrait;

    protected null|string $_sSaveError = null;

    protected $_sThisTemplate = '@'.Constants::OXID_MODULE_ID.'/admin/d3user_totp';

    public array $aBackupCodes = [];

    /**
     * @return string
     */
    public function render(): string
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
    public function getUserObject(): User
    {
        return oxNew(User::class);
    }

    /**
     * @throws DBALDriverException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function save(): void
    {
        parent::save();

        $aParams = Registry::getRequest()->getRequestEscapedParameter("editval") ?? [];

        try {
            $oTotp = $this->getTotpObject();

            Assert::that($oTotp->checkIfAlreadyExist($this->getCurrentUserId()))->false('D3_TOTP_ALREADY_EXIST');

            $oTotpBackupCodes = $this->getBackupcodeListObject();
            if (isset($aParams['d3totp__oxid'])) {
                $oTotp->load($aParams['d3totp__oxid']);
            } else {
                $aParams['d3totp__usetotp'] = 1;
                /** @var d3totp $init */
                $init = Registry::getSession()->getVariable(d3totp_conf::OTP_SESSION_VARNAME);
                Assert::that($init)->isInstanceOf(d3totp::class, 'D3_TOTP_INITOBJECT_MISSING');
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete(): void
    {
        $aParams = Registry::getRequest()->getRequestEscapedParameter("editval");

        $oTotp = $this->getTotpObject();
        if ($aParams['d3totp__oxid']) {
            $oTotp->load($aParams['d3totp__oxid']);
            $oTotp->delete();
            Registry::get(UtilsView::class)->addErrorToDisplay('D3_TOTP_REGISTERDELETED');
        }
    }

    public function getCurrentUserId(): string
    {
        return $this->getEditObjectId();
    }
}
