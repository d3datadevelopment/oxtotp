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

namespace D3\Totp\Modules\Application\Controller\Admin;

use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class d3_totp_LoginController extends d3_totp_LoginController_parent
{
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
     * @return mixed|string
     * @throws DatabaseConnectionException
     */
    public function checklogin()
    {
        $return = parent::checklogin();

        $totp = $this->d3GetTotpObject();
        $totp->loadByUserId(Registry::getSession()->getVariable("auth"));

        if ($this->d3TotpLoginMissing($totp)) {
            $userId = $this->d3TotpGetSession()->getVariable('auth');

            /** @var d3_totp_user $user */
            $user = oxNew(User::class);
            $user->logout();

            $this->d3TotpGetSession()->setVariable(d3totp_conf::SESSION_CURRENTUSER, $userId);

            return "d3totpadminlogin";
        }

        return $return;
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function d3TotpLoginMissing($totp)
    {
        return $totp->isActive()
            && false == $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_AUTH);
    }
}
