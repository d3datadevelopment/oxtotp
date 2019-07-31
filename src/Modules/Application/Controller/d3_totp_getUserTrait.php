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

namespace D3\Totp\Modules\Application\Controller;

use D3\Totp\Application\Model\d3totp;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;

trait d3_totp_getUserTrait
{
    /**
     * @return bool|object|User
     * @throws DatabaseConnectionException
     * @throws DBALException
     */
    public function getUser()
    {
        $oUser = parent::getUser();

        if ($oUser && $oUser->getId()) {
            $totp = oxNew(d3totp::class);
            $totp->loadByUserId($oUser->getId());

            if ($totp->isActive()
                && false == Registry::getSession()->getVariable(d3totp::TOTP_SESSION_VARNAME)
            ) {
                return false;
            }
        }

        return $oUser;
    }
}