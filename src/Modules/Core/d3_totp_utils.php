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

namespace D3\Totp\Modules\Core;

use D3\Totp\Application\Model\d3totp;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class d3_totp_utils extends d3_totp_utils_parent
{
    /**
     * @return bool
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function checkAccessRights()
    {
        $blAuth = parent::checkAccessRights();

        $userID = $this->d3GetSessionObject()->getVariable("auth");
        $totpAuth = (bool) $this->d3GetSessionObject()->getVariable(d3totp::TOTP_SESSION_VARNAME);
        /** @var d3totp $totp */
        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId($userID);

        //checkt ob alle Admin 2FA aktiviert hat
        //todo braucht Unit Test
        if (
            $this->d3IsAdminForce2FA()
            && $blAuth
            && $totp->isActive() === false
        ) {
            $this->redirect('index.php?cl=d3force_2fa', true, 302);
            if (false == defined('OXID_PHP_UNIT')) {
                // @codeCoverageIgnoreStart
                exit;
                // @codeCoverageIgnoreEnd
            }
        }

        //staten der prüfung vom einmalpasswort
        if ($blAuth && $totp->isActive() && false === $totpAuth) {
            $this->redirect('index.php?cl=login', true, 302);
            if (false == defined('OXID_PHP_UNIT')) {
                // @codeCoverageIgnoreStart
                exit;
                // @codeCoverageIgnoreEnd
            }
        }

        return $blAuth;
    }

    /**
     * @return Session
     */
    public function d3GetSessionObject()
    {
        return Registry::getSession();
    }

    /**
     * @return d3totp
     */
    public function d3GetTotpObject()
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return bool
     */
    private function d3IsAdminForce2FA()
    {
        return $this->isAdmin() &&
            Registry::getConfig()->getConfigParam('D3_TOTP_ADMIN_FORCE_2FA') == true;
    }
}
