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

namespace D3\Totp\Modules\Core;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class totpSystemEventHandler extends totpSystemEventHandler_parent
{
    public function onAdminLogin()
    {
        $this->d3RequestTotp();

        parent::onAdminLogin();
    }

    protected function d3requestTotp()
    {
        $totp = $this->d3GetTotpObject();
        $userId = $this->d3TotpGetSession()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);
        $totp->loadByUserId($userId);

        if ($this->d3TotpLoginMissing($totp)) {
            /** @var d3_totp_user $user */
            $user = $this->d3TotpGetUserObject();
            $user->logout();

            $this->d3TotpGetSession()->setVariable(d3totp_conf::SESSION_ADMIN_CURRENTUSER, $userId);

            Registry::getUtils()->redirect(
                'index.php?cl=d3totpadminlogin&amp;'.
                'profile='.$this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_PROFILE).'&amp;'.
                'chlanguage='.$this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_CHLANGUAGE)
            );
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
     * @return Session
     */
    public function d3TotpGetSession()
    {
        return Registry::getSession();
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function d3TotpLoginMissing($totp)
    {
        return $totp->isActive()
            && false == $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_AUTH);
    }

    /**
     * @return d3_totp_user
     */
    protected function d3TotpGetUserObject(): d3_totp_user
    {
        return oxNew( User::class );
    }
}