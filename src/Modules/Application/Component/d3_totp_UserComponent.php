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

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use Doctrine\DBAL\DBALException;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;

class d3_totp_UserComponent extends d3_totp_UserComponent_parent
{
    /**
     * @param User $oUser
     *
     * @return string
     * @throws DatabaseConnectionException
     */
    protected function _afterLogin($oUser)
    {
        if (!$oUser instanceof User) {
            throw oxNew( InvalidArgumentException::class, 'user argument must an instance of User class');
        }

        if ($oUser->getId()) {
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
        }

        return parent::_afterLogin($oUser);
    }

    /**
     * @return d3totp
     */
    public function d3GetTotpObject(): d3totp
    {
        return oxNew(d3totp::class);
    }

    /**
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function d3TotpCheckTotpLogin()
    {
        $sTotp = implode('', Registry::getRequest()->getRequestEscapedParameter('d3totp', []));

        /** @var d3_totp_user $oUser */
        $oUser = oxNew(User::class);
        $sUserId = Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER);
        $oUser->load($sUserId);

        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId($sUserId);

        try {
            if (!$this->d3TotpIsNoTotpOrNoLogin($totp) && $this->d3TotpHasValidTotp($sTotp, $totp)) {
                // relogin, don't extract from this try block
                $this->d3TotpGetSession()->setVariable(d3totp_conf::SESSION_AUTH, $oUser->getId());
                $this->d3TotpGetSession()->setVariable(d3totp_conf::OXID_FRONTEND_AUTH, $oUser->getId());
                $this->setUser(null);
                $this->setLoginStatus(USER_LOGIN_SUCCESS);
                $this->_afterLogin($oUser);

                $this->d3TotpClearSessionVariables();

                return false;
            }
        } catch (d3totp_wrongOtpException $oEx) {
            $this->d3TotpGetUtilsView()->addErrorToDisplay($oEx, false, false, "", 'd3totplogin');
        }

        return 'd3totplogin';
    }

    /**
     * @return UtilsView
     */
    public function d3TotpGetUtilsView()
    {
        return Registry::getUtilsView();
    }

    /**
     * @return Utils
     */
    public function d3TotpGetUtils()
    {
        return Registry::getUtils();
    }

    public function d3TotpCancelTotpLogin()
    {
        $this->d3TotpClearSessionVariables();

        return false;
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function d3TotpIsNoTotpOrNoLogin($totp)
    {
        return false == Registry::getSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER)
            || false == $totp->isActive();
    }

    /**
     * @param string $sTotp
     * @param d3totp $totp
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function d3TotpHasValidTotp($sTotp, $totp)
    {
        return Registry::getSession()->getVariable(d3totp_conf::SESSION_AUTH) ||
            $totp->verify($sTotp);
    }

    public function d3TotpClearSessionVariables()
    {
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_CURRENTCLASS);
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_CURRENTUSER);
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_NAVFORMPARAMS);
    }

    /**
     * @return Session
     */
    public function d3TotpGetSession()
    {
        return Registry::getSession();
    }
}
