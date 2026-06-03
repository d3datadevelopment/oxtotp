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

namespace D3\Totp\Modules\Application\Component;

use Assert\Assert;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Application\Model\Exceptions\totpExceptionInterface;
use D3\Totp\Application\Model\Exceptions\wrongOtpException;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3_totp_UserComponent extends d3_totp_UserComponent_parent
{
    /**
     * @param User $oUser
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    protected function afterLogin($oUser)
    {
        Assert::that($oUser)->isInstanceOf(User::class, 'user argument must an instance of User class');

        try {
            Assert::that($oUser->getId())->notBlank('user must logged in');
            $totp = $this->d3GetTotpObject();
            $totp->loadByUserId($oUser->getId());

            if ($totp->isActive()
                && !$this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_AUTH)
            ) {
                $this->d3TotpGetSession()->setVariable(
                    d3totp_conf::SESSION_CURRENTCLASS,
                    $this->getParent()->getClassKey() != 'd3totplogin' ? $this->getParent()->getClassKey() : 'start'
                );

                $oUser->logout();

                $this->d3TotpGetSession()->setVariable(d3totp_conf::SESSION_CURRENTUSER, $oUser->getId());
                $this->d3TotpGetSession()->setVariable(
                    d3totp_conf::SESSION_NAVFORMPARAMS,
                    $this->getParent()->getViewConfig()->getNavFormParams()
                );

                $sUrl = Registry::getConfig()->getShopHomeUrl() . 'cl=d3totplogin';
                $this->d3TotpGetUtils()->redirect($sUrl, false);
            }
        } catch (InvalidArgumentException) {
        }

        return parent::afterLogin($oUser);
    }

    /**
     * @return d3totp
     */
    public function d3GetTotpObject(): d3totp
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return false|string
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function d3TotpCheckTotpLogin(): false|string
    {
        $totpCode = implode('', Registry::getRequest()->getRequestEscapedParameter('d3totp') ?: []);
        $totpBcCode = trim((string) Registry::getRequest()->getRequestEscapedParameter('d3totpbc'));

        /** @var d3_totp_user $oUser */
        $oUser = oxNew(User::class);
        $sUserId = Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER);
        $oUser->load($sUserId);

        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId($sUserId);

        try {
            if (!$this->d3TotpIsNoTotpOrNoLogin($totp) && $this->d3TotpHasValidTotp($totpCode, $totpBcCode, $totp)) {
                // relogin, don't extract from this try block
                $this->d3TotpGetSession()->setVariable(d3totp_conf::SESSION_AUTH, $oUser->getId());
                $this->d3TotpGetSession()->setVariable(d3totp_conf::OXID_FRONTEND_AUTH, $oUser->getId());
                $this->setUser($oUser);
                $this->setLoginStatus(USER_LOGIN_SUCCESS);
                $this->afterLogin($oUser);

                $this->d3TotpClearSessionVariables();

                return false;
            }
        } catch (totpExceptionInterface $oEx) {
            $this->d3TotpGetUtilsView()->addErrorToDisplay($oEx, false, false, "", 'd3totplogin');
        }

        return 'd3totplogin';
    }

    /**
     * @return UtilsView
     */
    public function d3TotpGetUtilsView(): UtilsView
    {
        return Registry::getUtilsView();
    }

    /**
     * @return Utils
     */
    public function d3TotpGetUtils(): Utils
    {
        return Registry::getUtils();
    }

    public function d3TotpCancelTotpLogin(): bool
    {
        $this->d3TotpClearSessionVariables();

        return false;
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function d3TotpIsNoTotpOrNoLogin(d3totp $totp): bool
    {
        return false == Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER)
            || false == $totp->isActive();
    }

    /**
     * @param string $sTotp
     * @param d3totp $totp
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws wrongOtpException
     */
    public function d3TotpHasValidTotp(string $totpCode, string $totpBcCode, d3totp $totp): bool
    {
        /** @var d3_totp_user $user */
        $user = oxNew(User::class);
        $sUserId = Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER);
        $user->load($sUserId);

        return Registry::getSession()->getVariable(d3totp_conf::SESSION_AUTH) ||
            $totp->verify($user, $totpCode, $totpBcCode);
    }

    public function d3TotpClearSessionVariables(): void
    {
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_CURRENTCLASS);
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_CURRENTUSER);
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_NAVFORMPARAMS);
    }

    /**
     * @return Session
     */
    public function d3TotpGetSession(): Session
    {
        return Registry::getSession();
    }
}
