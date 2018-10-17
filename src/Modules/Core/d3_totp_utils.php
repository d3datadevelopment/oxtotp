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

use D3\Totp\Modules\Application\Model\d3_totp_user;
use OxidEsales\Eshop\Application\Model\User;

class d3_totp_utils extends d3_totp_utils_parent
{
    public function checkAccessRights()
    {
        $blAuth = parent::checkAccessRights();

        $userID = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("auth");
        /** @var d3_totp_user $user */
        $user = oxNew(User::class);
        $user->load($userID);

        if ($blAuth && $user->d3UseTotp()) {
            //check TOTP
        }

        return $blAuth;
    }
}