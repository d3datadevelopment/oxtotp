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

namespace D3\Totp\Modules\Application\Component;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;

class d3_totp_UserComponent extends d3_totp_UserComponent_parent
{
    /**
     * @return string|void
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function login_noredirect()
    {
        parent::login_noredirect();

        $oUser = $this->getUser();

        if ($oUser && $oUser->getId()) {
            $totp = oxNew(d3totp::class);
            $totp->loadByUserId($oUser->getId());

            if ($totp->isActive()
                && false == Registry::getSession()->getVariable(d3totp::TOTP_SESSION_VARNAME)
            ) {
                Registry::getSession()->setVariable(
                    d3totp::TOTP_SESSION_CURRENTCLASS,
                    $this->getParent()->getClassKey() != 'd3totplogin' ? $this->getParent()->getClassKey() : 'start');
                Registry::getSession()->setVariable(d3totp::TOTP_SESSION_CURRENTUSER, $oUser->getId());
                Registry::getSession()->setVariable(
                    d3totp::TOTP_SESSION_NAVFORMPARAMS,
                    $this->getParent()->getViewConfig()->getNavFormParams()
                );

                $oUser->logout();

                return "d3totplogin";
            }
        }
    }

    /**
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function checkTotplogin()
    {
        $sTotp = Registry::getRequest()->getRequestEscapedParameter('d3totp', true);

        $sUserId = Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTUSER);
        $oUser = oxNew(User::class);
        $oUser->load($sUserId);

        $totp = oxNew(d3totp::class);
        $totp->loadByUserId($sUserId);

        try {
            if (false == $this->isNoTotpOrNoLogin($totp) && $this->hasValidTotp($sTotp, $totp)) {
                $this->d3TotpRelogin($oUser, $sTotp);
                $this->d3TotpClearSessionVariables();

                return false;
            }
        } catch (d3totp_wrongOtpException $oEx) {
            Registry::getUtilsView()->addErrorToDisplay($oEx, false, false, "", 'd3totplogin');
        }

        return 'd3totplogin';
    }

    public function cancelTotpLogin()
    {
        $this->d3TotpClearSessionVariables();

        return false;
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function isNoTotpOrNoLogin($totp)
    {
        return false == Registry::getSession()->getVariable(d3totp::TOTP_SESSION_CURRENTUSER)
            || false == $totp->isActive();
    }

    /**
     * @param string $sTotp
     * @param d3totp $totp
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function hasValidTotp($sTotp, $totp)
    {
        return Registry::getSession()->getVariable(d3totp::TOTP_SESSION_VARNAME) ||
            (
                $sTotp && $totp->verify($sTotp)
            );
    }

    /**
     * @param User $oUser
     * @param $sTotp
     */
    public function d3TotpRelogin(User $oUser, $sTotp)
    {
        Registry::getSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, $sTotp);
        Registry::getSession()->setVariable('usr', $oUser->getId());
        $this->setUser(null);
        $this->setLoginStatus(USER_LOGIN_SUCCESS);
        $this->_afterLogin($oUser);
    }

    public function d3TotpClearSessionVariables()
    {
        Registry::getSession()->deleteVariable(d3totp::TOTP_SESSION_CURRENTCLASS);
        Registry::getSession()->deleteVariable(d3totp::TOTP_SESSION_CURRENTUSER);
        Registry::getSession()->deleteVariable(d3totp::TOTP_SESSION_NAVFORMPARAMS);
    }
}