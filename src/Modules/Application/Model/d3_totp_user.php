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

namespace D3\Totp\Modules\Application\Model;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class d3_totp_user extends d3_totp_user_parent
{
    public function logout()
    {
        $return = parent::logout();

        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_AUTH);
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_CURRENTUSER);

        return $return;
    }

    /**
     * @return d3totp
     */
    public function d3getTotp()
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return Session
     */
    public function d3TotpGetSession()
    {
        return Registry::getSession();
    }

    /**
     * @return string|null
     */
    public function d3TotpGetCurrentUser(): ?string
    {
        return $this->d3TotpGetSession()->hasVariable(d3totp_conf::SESSION_CURRENTUSER) ?
            $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_CURRENTUSER) :
            (isAdmin() ?
                $this->d3TotpGetSession()->getVariable(d3totp_conf::OXID_ADMIN_AUTH) :
                $this->d3TotpGetSession()->getVariable(d3totp_conf::OXID_FRONTEND_AUTH));
    }
}
