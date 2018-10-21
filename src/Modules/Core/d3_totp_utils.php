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

        $userID = Registry::getSession()->getVariable("auth");
        $totpAuth = (bool) Registry::getSession()->getVariable(d3totp::TOTP_SESSION_VARNAME);
        /** @var d3totp $totp */
        $totp = oxNew(d3totp::class);
        $totp->loadByUserId($userID);

        if ($blAuth && $totp->isActive() && false === $totpAuth) {
            Registry::getUtils()->redirect('index.php?cl=login', true, 302);
            exit;
        }

        return $blAuth;
    }
}