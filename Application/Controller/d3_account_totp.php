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

use Assert\Assert;
use D3\Totp\Application\Model\Constants;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Exception;
use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3_account_totp extends AccountController
{
    use OtpManagementControllerTrait;

    protected $_sThisTemplate = '@'.Constants::OXID_MODULE_ID.'/tpl/d3_account_totp';

    public array $aBackupCodes = [];

    public function render(): string
    {
        $sRet = parent::render();

        $this->addTplParam('user', $this->getUser());

        return $sRet;
    }

    public function getCurrentUserId(): string
    {
        return $this->getUser()->getId();
    }

    public function create(): void
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') === '1') {
            try {
                /** @var d3_totp_user $oUser */
                $oUser = $this->getUser();

                /** @var d3totp $oTotp */
                $oTotp = $this->getTotpObject();

                Assert::that($oTotp->checkIfAlreadyExist($this->getCurrentUserId()))->false('D3_TOTP_ALREADY_EXIST');

                $oTotpBackupCodes = $this->getBackupCodeListObject();

                $aParams = [
                    'd3totp__usetotp' => 1,
                    'd3totp__oxuserid'  => $oUser->getId(),
                ];
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
                $oTotpBackupCodes->generateBackupCodes($oUser->getId());
                $oTotp->setId();

                $oTotp->save();
                $oTotpBackupCodes->save();
            } catch (Exception $oExcp) {
                Registry::get(UtilsView::class)->addErrorToDisplay($oExcp->getMessage());
            }
        }
    }

    /**
     * @throws DatabaseConnectionException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete(): void
    {
        if (Registry::getRequest()->getRequestEscapedParameter('totp_use') !== '1') {
            $oUser = $this->getUser();
            $oTotp = $this->getTotpObject();
            if ($oUser instanceof User && $oUser->getId()) {
                $oTotp->loadByUserId($oUser->getId());
                $oTotp->delete();
            }
        }
    }
}
