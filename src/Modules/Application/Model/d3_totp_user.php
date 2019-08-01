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

namespace D3\Totp\Modules\Application\Model;

use D3\Totp\Application\Model\d3totp;
use OxidEsales\Eshop\Core\Registry;

class d3_totp_user extends d3_totp_user_parent
{
    public function logout()
    {
        $return = parent::logout();

        Registry::getSession()->deleteVariable(d3totp::TOTP_SESSION_VARNAME);

        return $return;
    }
}