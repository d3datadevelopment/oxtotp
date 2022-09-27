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
            $this->redirect('index.php?cl=d3force_2fa');
            if (false == defined('OXID_PHP_UNIT')) {
                // @codeCoverageIgnoreStart
                exit;
                // @codeCoverageIgnoreEnd
            }
        }

        //staten der prüfung vom einmalpasswort
        if ($blAuth && $totp->isActive() && false === $totpAuth) {
            $this->redirect('index.php?cl=login');
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
