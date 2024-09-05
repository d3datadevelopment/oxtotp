<?php

declare(strict_types=1);

namespace D3\Totp\Application\Controller\Admin;

use D3\Totp\Application\Model\d3totp_conf;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class d3force_2fa extends d3user_totp
{
    public function render()
    {
        $this->addTplParam('force2FA', true);

        $userID = $this->d3TotpGetSessionObject()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);
        $this->_sEditObjectId = $userID;

        return parent::render();
    }


    protected function _authorize()
    {
        $userID = $this->d3TotpGetSessionObject()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);

        return ($this->d3IsAdminForce2FA() && !empty($userID));
    }

    /**
     * @return Session
     */
    private function d3TotpGetSessionObject()
    {
        return Registry::getSession();
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
