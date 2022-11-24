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

use D3\TestingTools\Production\IsMockable;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class d3_totp_LoginController extends d3_totp_LoginController_parent
{
    use IsMockable;

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
        $this->d3TotpGetSession()->setVariable(
            d3totp_conf::SESSION_ADMIN_PROFILE,
            Registry::getRequest()->getRequestEscapedParameter('profile')
        );
        $this->d3TotpGetSession()->setVariable(
            d3totp_conf::SESSION_ADMIN_CHLANGUAGE,
            Registry::getRequest()->getRequestEscapedParameter('chlanguage')
        );

        // parent::checklogin();
        return $this->d3CallMockableParent('checklogin');
    }

    public function d3totpAfterLogin()
    {
        $myUtilsServer = $this->d3TotpGetUtilsServer();
        $sProfile = $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_PROFILE);

        // #533
        if (isset($sProfile)) {
            $aProfiles = $this->d3TotpGetSession()->getVariable("aAdminProfiles");
            if ($aProfiles && isset($aProfiles[$sProfile])) {
                // setting cookie to store last locally used profile
                $myUtilsServer->setOxCookie("oxidadminprofile", $sProfile . "@" . implode("@", $aProfiles[$sProfile]), time() + 31536000, "/");
                $this->d3TotpGetSession()->setVariable("profile", $aProfiles[$sProfile]);
                $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_ADMIN_PROFILE);
            }
        } else {
            //deleting cookie info, as setting profile to default
            $myUtilsServer->setOxCookie("oxidadminprofile", "", time() - 3600, "/");
        }

        $this->d3totpAfterLoginSetLanguage();
        $this->d3TotpGetSession()->deleteVariable(d3totp_conf::SESSION_ADMIN_CHLANGUAGE);
    }

    public function d3totpAfterLoginSetLanguage()
    {
        $myUtilsServer = $this->d3TotpGetUtilsServer();
        $iLang = $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_CHLANGUAGE);

        $aLanguages = $this->d3TotpGetLangObject()->getAdminTplLanguageArray();
        if (!isset($aLanguages[$iLang])) {
            $iLang = key($aLanguages);
        }

        $myUtilsServer->setOxCookie("oxidadminlanguage", $aLanguages[$iLang]->abbr, time() + 31536000, "/");
        $this->d3TotpGetLangObject()->setTplLanguage( $iLang);
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

    /**
     * @return object|\OxidEsales\Eshop\Core\UtilsServer
     */
    protected function d3TotpGetUtilsServer()
    {
        return Registry::getUtilsServer();
    }

    /**
     * @return object|\OxidEsales\Eshop\Core\Language
     */
    protected function d3TotpGetLangObject()
    {
        return Registry::getLang();
    }
}
