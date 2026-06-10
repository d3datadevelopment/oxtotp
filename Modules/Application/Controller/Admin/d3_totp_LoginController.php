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
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\UtilsServer;

class d3_totp_LoginController extends d3_totp_LoginController_parent
{
    /**
     * @return d3totp
     */
    public function d3GetTotpObject(): d3totp
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return Session
     */
    public function d3TotpGetSession(): Session
    {
        return Registry::getSession();
    }

    /**
     * @return mixed|string
     */
    public function checklogin()
    {
        $this->d3TotpGetSession()->setVariable(
            d3totp_conf::SESSION_ADMIN_PROFILE,
            Registry::getRequest()->getRequestEscapedParameter('profile')
        );
        $this->d3TotpGetSession()->setVariable(
            d3totp_conf::SESSION_ADMIN_CHLANGUAGE,
            Registry::getRequest()->getRequestEscapedParameter('chlanguage')
        );

        return $this->parent__checklogin();
    }

    /**
     * mockable parent method
     * @codeCoverageIgnore
     */
    protected function parent__checklogin()
    {
        return parent::checklogin();
    }

    public function d3totpAfterLogin(): void
    {
        $myUtilsServer = $this->d3TotpGetUtilsServer();
        $sProfile = $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_PROFILE);

        // #533
        if (isset($sProfile)) {
            $aProfiles = $this->d3TotpGetSession()->getVariable("aAdminProfiles");
            if ($aProfiles && isset($aProfiles[$sProfile])) {
                // setting cookie to store last locally used profile
                $myUtilsServer->setOxCookie("oxidadminprofile", $sProfile . "@" . implode("@", $aProfiles[$sProfile]), time() + 31536000);
                $this->d3TotpGetSession()->setVariable("profile", $aProfiles[$sProfile]);
                $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_ADMIN_PROFILE);
            }
        } else {
            //deleting cookie info, as setting profile to default
            $myUtilsServer->setOxCookie("oxidadminprofile", "", time() - 3600);
        }

        $this->d3totpAfterLoginSetLanguage();
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_ADMIN_CHLANGUAGE);
    }

    public function d3totpAfterLoginSetLanguage(): void
    {
        $myUtilsServer = $this->d3TotpGetUtilsServer();
        $iLang = Registry::getRequest()->getRequestEscapedParameter('chlanguage');

        $aLanguages = $this->d3TotpGetLangObject()->getAdminTplLanguageArray();
        if (!isset($aLanguages[$iLang])) {
            $iLang = key($aLanguages);
        }

        $myUtilsServer->setOxCookie("oxidadminlanguage", $aLanguages[$iLang]->abbr, time() + 31536000);
        $this->d3TotpGetLangObject()->setTplLanguage($iLang);
    }

    /**
     * @param d3totp $totp
     * @return bool
     */
    public function d3TotpLoginMissing(d3totp $totp): bool
    {
        $adminTotpAuth = $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_AUTH);
        $adminAuth = $this->d3TotpGetSession()->getVariable(d3totp_conf::OXID_ADMIN_AUTH);

        return $totp->isActive()
            && (
                !$adminTotpAuth ||
                $adminTotpAuth != $adminAuth
            );
    }

    /**
     * @return UtilsServer
     */
    protected function d3TotpGetUtilsServer(): UtilsServer
    {
        return Registry::getUtilsServer();
    }

    /**
     * @return Language
     */
    protected function d3TotpGetLangObject(): Language
    {
        return Registry::getLang();
    }
}
