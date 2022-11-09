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

namespace D3\Totp\Modules\Application\Controller;

use D3\Totp\Application\Model\d3totp;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

trait d3_totp_getUserTrait
{
    /**
     * @return bool|object|User
     * @throws DatabaseConnectionException
     */
    public function getUser()
    {
        $oUser = parent::getUser();

        if ($oUser instanceof User && $oUser->getId()) {
            $totp = $this->d3GetTotpObject();
            $totp->loadByUserId($oUser->getId());

            if ($totp->isActive()
                && !$this->d3TotpGetSessionObject()->getVariable(d3totp::TOTP_SESSION_VARNAME)
            ) {
                return false;
            }
        }

        return $oUser;
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
    public function d3TotpGetSessionObject()
    {
        return Registry::getSession();
    }
}
