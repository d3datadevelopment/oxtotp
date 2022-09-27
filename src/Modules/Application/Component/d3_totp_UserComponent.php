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
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsView;

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
            $totp = $this->d3GetTotpObject();
            $totp->loadByUserId($oUser->getId());

            if ($totp->isActive()
                && !Registry::getSession()->getVariable(d3totp::TOTP_SESSION_VARNAME)
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
     * @return d3totp
     */
    public function d3GetTotpObject()
    {
        return oxNew(d3totp::class);
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

        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId($sUserId);

        try {
            if (!$this->isNoTotpOrNoLogin($totp) && $this->hasValidTotp($sTotp, $totp)) {
                $this->d3TotpRelogin($oUser, $sTotp);
                $this->d3TotpClearSessionVariables();

                return false;
            }
        } catch (d3totp_wrongOtpException $oEx) {
            $this->d3GetUtilsView()->addErrorToDisplay($oEx, false, false, "", 'd3totplogin');
        }

        return 'd3totplogin';
    }

    /**
     * @return UtilsView
     */
    public function d3GetUtilsView()
    {
        return Registry::getUtilsView();
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
        $this->d3GetSession()->setVariable(d3totp::TOTP_SESSION_VARNAME, $sTotp);
        $this->d3GetSession()->setVariable('usr', $oUser->getId());
        $this->setUser(null);
        $this->setLoginStatus(USER_LOGIN_SUCCESS);
        $this->_afterLogin($oUser);
    }

    public function d3TotpClearSessionVariables()
    {
        $this->d3GetSession()->deleteVariable(d3totp::TOTP_SESSION_CURRENTCLASS);
        $this->d3GetSession()->deleteVariable(d3totp::TOTP_SESSION_CURRENTUSER);
        $this->d3GetSession()->deleteVariable(d3totp::TOTP_SESSION_NAVFORMPARAMS);
    }

    /**
     * @return Session
     */
    public function d3GetSession()
    {
        return Registry::getSession();
    }
}