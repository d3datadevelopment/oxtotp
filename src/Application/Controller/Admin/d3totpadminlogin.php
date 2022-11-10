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

namespace D3\Totp\Application\Controller\Admin;

use D3\Totp\Application\Model\d3backupcodelist;
use D3\Totp\Application\Model\d3totp;
use D3\Totp\Application\Model\d3totp_conf;
use D3\Totp\Application\Model\Exceptions\d3totp_wrongOtpException;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;

class d3totpadminlogin extends AdminController
{
    protected $_sThisTemplate = 'd3totpadminlogin.tpl';

    /**
     * @return bool
     */
    protected function _authorize(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if (Registry::getSession()->hasVariable(d3totp_conf::SESSION_AUTH) &&
            !Registry::getSession()->hasVariable(d3totp_conf::SESSION_CURRENTUSER)
        ) {
            $this->getUtils()->redirect('index.php?cl=admin_start');
            if (!defined('OXID_PHP_UNIT')) {
                // @codeCoverageIgnoreStart
                exit;
                // @codeCoverageIgnoreEnd
            }
        }

        if (!Registry::getSession()->hasVariable(d3totp_conf::OXID_ADMIN_AUTH) &&
            !Registry::getSession()->hasVariable(d3totp_conf::SESSION_CURRENTUSER)
        ) {
            $this->getUtils()->redirect('index.php?cl=login');
        }

        return parent::render();
    }

    /**
     * @return d3backupcodelist
     */
    public function d3GetBackupCodeListObject()
    {
        return oxNew(d3backupcodelist::class);
    }

    /**
     * @return string|void
     * @throws DatabaseConnectionException
     */
    public function getBackupCodeCountMessage()
    {
        /** @var d3_totp_user $user */
        $user = oxNew(User::class);
        $userId = $user->d3TotpGetCurrentUser();

        $oBackupCodeList = $this->d3GetBackupCodeListObject();
        $iCount = $oBackupCodeList->getAvailableCodeCount($userId);

        if ($iCount < 4) {
            return sprintf(
                Registry::getLang()->translateString('D3_TOTP_AVAILBACKUPCODECOUNT'),
                $iCount
            );
        }
    }

    public function d3CancelLogin()
    {
        $oUser = $this->d3GetUserObject();
        $oUser->logout();
        return "login";
    }

    /**
     * @return d3totp
     */
    public function d3GetTotpObject()
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return User
     */
    public function d3GetUserObject()
    {
        return oxNew(User::class);
    }

    /**
     * @return string|void
     * @throws DatabaseConnectionException
     */
    public function checklogin()
    {
        $session = Registry::getSession();
        /** @var d3_totp_user $user */
        $user = oxNew(User::class);
        $userId = $user->d3TotpGetCurrentUser();

        try {
            $sTotp = Registry::getRequest()->getRequestEscapedParameter('d3totp');

            $totp = $this->d3GetTotpObject();
            $totp->loadByUserId($userId);

            $this->d3TotpHasValidTotp($sTotp, $totp);

            $adminProfiles = $session->getVariable("aAdminProfiles");

            $session->initNewSession();
            $session->setVariable("aAdminProfiles", $adminProfiles);
            $session->setVariable(d3totp_conf::OXID_ADMIN_AUTH, $userId);
            $session->setVariable(d3totp_conf::SESSION_AUTH, $userId);
            $session->deleteVariable(d3totp_conf::SESSION_CURRENTUSER);

            return "admin_start";
        } catch (d3totp_wrongOtpException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e);
            Registry::getLogger()->error($e->getMessage(), ['UserId'   => $userId]);
            Registry::getLogger()->debug($e->getTraceAsString());
        }
    }

    /**
     * @param string $sTotp
     * @param d3totp $totp
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function d3TotpHasValidTotp($sTotp, $totp)
    {
        return Registry::getSession()->getVariable(d3totp_conf::SESSION_AUTH) ||
            (
                $sTotp && $totp->verify($sTotp)
            );
    }

    /**
     * @return Utils
     */
    public function getUtils(): Utils
    {
        return Registry::getUtils();
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb(): array
    {
        $aPaths = [];
        $aPath = [];
        $iBaseLanguage = Registry::getLang()->getBaseLanguage();
        $aPath['title'] = Registry::getLang()->translateString('D3_WEBAUTHN_BREADCRUMB', $iBaseLanguage, false);
        $aPath['link'] = $this->getLink();

        $aPaths[] = $aPath;

        return $aPaths;
    }
}