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

namespace D3\Totp\Modules\Application\Controller\Admin;

use D3\Totp\Application\Model\d3totp;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;

class d3_totp_LoginController extends d3_totp_LoginController_parent
{
    /**
     * @return string
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function render()
    {
        $auth = Registry::getSession()->getVariable("auth");

        $return = parent::render();

        if ($auth
            && oxNew(d3totp::class)->UserUseTotp($auth)
            && false == Registry::getSession()->getVariable("totp_auth")
        ) {
            // set auth as secured parameter;
            $return = 'd3login_totp.tpl';
        }

        return $return;
    }

    /**
     * @return mixed|string
     * @throws DBALException
     * @throws DatabaseConnectionException
     */
    public function checklogin()
    {
        $return = parent::checklogin();

        if ($return == "admin_start") {
            if ((bool) $this->getSession()->checkSessionChallenge()
                && count(\OxidEsales\Eshop\Core\Registry::getUtilsServer()->getOxCookie())
                && Registry::getSession()->getVariable("auth")
                && oxNew(d3totp::class)->UserUseTotp(Registry::getSession()->getVariable("auth"))
                && false == Registry::getSession()->getVariable("totp_auth")
            ) {
                $return = "login";
            }
        }

        return $return;
    }
}