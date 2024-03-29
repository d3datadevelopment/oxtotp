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
use D3\Totp\Modules\Application\Controller\Admin\d3_totp_LoginController;
use D3\Totp\Modules\Application\Model\d3_totp_user;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Controller\Admin\LoginController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Utils;
use Psr\Log\LoggerInterface;

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
     * @return d3totp|mixed
     */
    public function d3TotpGetTotpObject()
    {
        return oxNew(d3totp::class);
    }

    /**
     * @return bool
     * @throws DatabaseConnectionException
     */
    protected function isTotpIsNotRequired(): bool
    {
        /** @var d3_totp_user $user */
        $user = $this->d3TotpGetUserObject();
        $userId = $user->d3TotpGetCurrentUser();

        $totp = $this->d3TotpGetTotpObject();
        $totp->loadByUserId($userId);

        return $this->d3TotpGetSession()->hasVariable(d3totp_conf::SESSION_ADMIN_AUTH) ||
            !$totp->isActive();
    }

    /**
     * @return bool
     */
    protected function isTotpLoginNotPossible(): bool
    {
        $user = $this->d3TotpGetUserObject();
        return !$user->d3TotpGetCurrentUser();
    }

    /**
     * @return string
     * @throws DatabaseConnectionException
     */
    public function render(): string
    {
        if ($this->isTotpLoginNotPossible()) {
            $this->d3TotpGetUtils()->redirect('index.php?cl=login', false);
        } elseif ($this->isTotpIsNotRequired()) {
            $this->d3TotpGetUtils()->redirect('index.php?cl=admin_start', false);
        }

        $this->addTplParam('selectedProfile', Registry::getRequest()->getRequestEscapedParameter('profile'));
        $this->addTplParam('selectedChLanguage', Registry::getRequest()->getRequestEscapedParameter('chlanguage'));

        /** @var d3_totp_LoginController $loginController */
        $loginController = $this->d3GetLoginController();
        $loginController->d3totpAfterLoginSetLanguage();

        return parent::render();
    }

    /**
     * @return d3backupcodelist
     */
    public function d3GetBackupCodeListObject(): d3backupcodelist
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

    /**
     * @return string
     */
    public function d3CancelLogin(): string
    {
        /** @var d3_totp_user $oUser */
        $oUser = $this->d3TotpGetUserObject();
        $oUser->logout();
        return "login";
    }

    /**
     * @return User
     */
    public function d3TotpGetUserObject(): User
    {
        return oxNew(User::class);
    }

    /**
     * @return string|void
     * @throws DatabaseConnectionException
     */
    public function checklogin()
    {
        $session = $this->d3TotpGetSession();
        /** @var d3_totp_user $user */
        $user = oxNew(User::class);
        $userId = $user->d3TotpGetCurrentUser();

        try {
            $sTotp = implode('', Registry::getRequest()->getRequestEscapedParameter('d3totp') ?: []);

            $totp = $this->d3TotpGetTotpObject();
            $totp->loadByUserId($userId);

            $this->d3TotpHasValidTotp($sTotp, $totp);

            $selectedProfile = Registry::getRequest()->getRequestEscapedParameter('profile');
            $selectedLanguage = Registry::getRequest()->getRequestEscapedParameter('chlanguage');

            $session->initNewSession();
            $session->setVariable(d3totp_conf::SESSION_ADMIN_PROFILE, $selectedProfile);
            $session->setVariable(d3totp_conf::SESSION_ADMIN_CHLANGUAGE, $selectedLanguage);
            $session->setVariable(d3totp_conf::OXID_ADMIN_AUTH, $userId);
            $session->setVariable(d3totp_conf::SESSION_ADMIN_AUTH, $userId);
            $session->deleteVariable(d3totp_conf::SESSION_ADMIN_CURRENTUSER);

            /** @var d3_totp_LoginController $loginController */
            $loginController = $this->d3GetLoginController();
            $loginController->d3totpAfterLogin();

            return "admin_start";
        } catch (d3totp_wrongOtpException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e);
            $this->getLogger()->error($e->getMessage(), ['UserId'   => $userId]);
            $this->getLogger()->debug($e->getTraceAsString());
        }
    }

    /**
     * @param string|null $sTotp
     * @param d3totp $totp
     * @return bool
     * @throws DatabaseConnectionException
     * @throws d3totp_wrongOtpException
     */
    public function d3TotpHasValidTotp(string $sTotp = null, d3totp $totp): bool
    {
        return $this->d3TotpGetSession()->getVariable(d3totp_conf::SESSION_ADMIN_AUTH)
            || $totp->verify($sTotp);
    }

    /**
     * @return Utils
     */
    public function d3TotpGetUtils(): Utils
    {
        return Registry::getUtils();
    }

    /**
     * @return Session
     */
    public function d3TotpGetSession(): Session
    {
        return Registry::getSession();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return Registry::getLogger();
    }

    /**
     * @return LoginController
     */
    public function d3GetLoginController(): LoginController
    {
        return oxNew(LoginController::class);
    }
}
